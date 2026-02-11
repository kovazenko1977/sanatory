# 📦 Инструкция по установке

## Быстрый старт

### 1. Скачайте проект

```bash
git clone https://github.com/kovazenko1977/sanatory.git
cd sanatory
```

### 2. Создайте файл конфигурации

```bash
cp config/config.php.example config/config.php
```

### 3. Создайте необходимые директории

```bash
mkdir -p data/backups
mkdir -p uploads/documents
mkdir -p uploads/contracts
```

### 4. Установите права доступа

```bash
chmod -R 755 data/
chmod -R 755 uploads/
```

### 5. Настройте веб-сервер

#### Вариант A: Apache

Создайте файл `/etc/apache2/sites-available/sanatory.conf`:

```apache
<VirtualHost *:80>
    ServerName sanatory.local
    DocumentRoot /path/to/sanatory
    
    <Directory /path/to/sanatory>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Запретить доступ к конфигурации и данным
    <Directory /path/to/sanatory/config>
        Require all denied
    </Directory>
    
    <Directory /path/to/sanatory/data>
        Require all denied
    </Directory>
</VirtualHost>
```

Активируйте сайт:
```bash
sudo a2ensite sanatory
sudo systemctl reload apache2
```

#### Вариант B: Nginx

Создайте файл `/etc/nginx/sites-available/sanatory`:

```nginx
server {
    listen 80;
    server_name sanatory.local;
    root /path/to/sanatory;
    index index.php;

    # Запретить доступ к конфигурации и данным
    location ~ ^/(config|data)/ {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Активируйте сайт:
```bash
sudo ln -s /etc/nginx/sites-available/sanatory /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

#### Вариант C: Встроенный сервер PHP (только для разработки!)

```bash
php -S localhost:8000 -t .
```

Откройте в браузере: `http://localhost:8000/admin/`

### 6. Установите демо-данные (опционально)

```bash
php install-demo-data.php
```

### 7. Откройте в браузере

Перейдите по адресу: `http://sanatory.local/admin/` или `http://localhost:8000/admin/`

---

## ⚠️ Решение проблем

### Проблема: HTTP ERROR 500

#### Причина 1: Отсутствует файл config.php

**Решение:**
```bash
# Проверьте, существует ли файл
ls -la config/config.php

# Если файла нет, создайте его
cp config/config.php.example config/config.php
```

#### Причина 2: Нет прав на запись

**Решение:**
```bash
# Дайте права на запись
chmod -R 755 data/
chmod -R 755 uploads/

# Если не помогло, попробуйте
chmod -R 777 data/
chmod -R 777 uploads/
```

#### Причина 3: Не созданы директории

**Решение:**
```bash
mkdir -p data/backups
mkdir -p uploads/documents
mkdir -p uploads/contracts
```

#### Причина 4: Ошибка в PHP коде

**Решение:**

1. Включите отображение ошибок в `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. Проверьте логи ошибок:
   - Apache: `tail -f /var/log/apache2/error.log`
   - Nginx: `tail -f /var/log/nginx/error.log`
   - PHP-FPM: `tail -f /var/log/php7.4-fpm.log`

3. Проверьте версию PHP:
   ```bash
   php -v
   ```
   Требуется PHP 7.4 или выше.

### Проблема: Белая страница (ничего не отображается)

**Решение:**

1. Проверьте логи ошибок PHP
2. Убедитесь, что все классы загружаются правильно
3. Проверьте, что файл `config/config.php` существует

### Проблема: "Class not found"

**Решение:**

Убедитесь, что в `config/config.php` есть автозагрузчик классов:

```php
spl_autoload_register(function ($class) {
    $prefix = 'Sanatorium\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = ROOT_PATH . '/classes/' . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
```

### Проблема: Не сохраняются данные

**Решение:**

1. Проверьте права доступа:
   ```bash
   ls -la data/
   ```

2. Убедитесь, что PHP может писать в директорию:
   ```bash
   sudo chown -R www-data:www-data data/
   chmod -R 755 data/
   ```

3. Проверьте, что директория `data/` существует:
   ```bash
   mkdir -p data/backups
   ```

### Проблема: Не загружаются файлы

**Решение:**

1. Проверьте права доступа:
   ```bash
   chmod -R 755 uploads/
   sudo chown -R www-data:www-data uploads/
   ```

2. Проверьте настройки PHP:
   ```bash
   php -i | grep upload_max_filesize
   php -i | grep post_max_size
   ```

3. Увеличьте лимиты в `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

---

## 🔒 Безопасность для продакшена

### 1. Отключите отображение ошибок

В `config/config.php`:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 2. Реализуйте авторизацию

Замените функцию `checkAuth()` в `config/config.php` на реальную систему авторизации.

### 3. Настройте HTTPS

#### Apache:
```apache
<VirtualHost *:443>
    ServerName sanatory.local
    DocumentRoot /path/to/sanatory
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
    
    # ... остальные настройки
</VirtualHost>
```

#### Nginx:
```nginx
server {
    listen 443 ssl;
    server_name sanatory.local;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # ... остальные настройки
}
```

### 4. Ограничьте доступ к критичным директориям

Убедитесь, что директории `config/` и `data/` недоступны через веб-сервер (см. примеры конфигурации выше).

### 5. Регулярно делайте бэкапы

```bash
# Создайте скрипт для бэкапа
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf backup_$DATE.tar.gz data/ uploads/
```

---

## 📝 Дополнительная информация

- [README.md](README.md) - Общая информация о проекте
- [QUICKSTART.md](QUICKSTART.md) - Быстрый старт
- [FILE_STRUCTURE.md](FILE_STRUCTURE.md) - Структура файлов
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Описание проекта

---

## 💬 Поддержка

Если у вас возникли проблемы:

1. Проверьте логи ошибок PHP
2. Убедитесь, что все требования выполнены
3. Проверьте права доступа к файлам
4. Создайте issue на GitHub: https://github.com/kovazenko1977/sanatory/issues
