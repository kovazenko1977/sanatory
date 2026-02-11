# 📁 File Structure

```
sanatorium-system/
│
├── 📄 README.md                    # Main documentation
├── 📄 QUICKSTART.md                # Quick setup guide
├── 📄 PROJECT_SUMMARY.md           # Project overview
├── 📄 IMPLEMENTATION_STATUS.md     # Development status
├── 📄 FILE_STRUCTURE.md            # This file
├── 📄 .gitignore                   # Git ignore rules
├── 📄 composer.json                # PHP dependencies
├── 📄 install-demo-data.php        # Demo data generator
│
├── 📁 config/                      # Configuration
│   └── 📄 config.php               # Main config (auth, settings)
│
├── 📁 classes/                     # PHP Business Logic
│   ├── 📄 Database.php             # ✅ JSON file handler
│   ├── 📄 Room.php                 # ✅ Room management
│   ├── 📄 Booking.php              # ✅ Booking system
│   ├── 📄 Guest.php                # ✅ Guest management
│   ├── 📄 Service.php              # ⏳ TODO: Services
│   ├── 📄 Finance.php              # ⏳ TODO: Financial reports
│   ├── 📄 Marketing.php            # ⏳ TODO: Promocodes, loyalty
│   ├── 📄 Telegram.php             # ⏳ TODO: Telegram bot
│   └── 📄 PDF.php                  # ⏳ TODO: PDF generation
│
├── 📁 admin/                       # Admin Panel
│   ├── 📄 index.php                # ✅ Dashboard with charts
│   ├── 📄 rooms.php                # ⏳ TODO: Room management UI
│   ├── 📄 bookings.php             # ⏳ TODO: Booking management UI
│   ├── 📄 guests.php               # ⏳ TODO: Guest management UI
│   ├── 📄 calendar.php             # ⏳ TODO: Calendar view
│   ├── 📄 services.php             # ⏳ TODO: Services UI
│   ├── 📄 finances.php             # ⏳ TODO: Financial reports UI
│   ├── 📄 marketing.php            # ⏳ TODO: Marketing UI
│   ├── 📄 settings.php             # ⏳ TODO: Settings UI
│   ├── 📄 logout.php               # ⏳ TODO: Logout handler
│   │
│   ├── 📁 includes/                # Shared components
│   │   ├── 📄 header.php           # ✅ Navigation header
│   │   └── 📄 sidebar.php          # ✅ Sidebar menu
│   │
│   └── 📁 assets/                  # Static assets
│       ├── 📁 css/
│       │   ├── 📄 style.css        # ✅ Main styles
│       │   └── 📄 dark-theme.css   # ⏳ TODO: Dark theme styles
│       ├── 📁 js/
│       │   ├── 📄 main.js          # ⏳ TODO: Main JavaScript
│       │   ├── 📄 calendar.js      # ⏳ TODO: Calendar logic
│       │   ├── 📄 charts.js        # ⏳ TODO: Chart helpers
│       │   └── 📄 onboarding.js    # ⏳ TODO: User onboarding
│       └── 📁 img/
│           └── 📄 logo.png         # ⏳ TODO: Logo
│
├── 📁 api/                         # REST API Endpoints
│   ├── 📄 rooms.php                # ⏳ TODO: Room API
│   ├── 📄 bookings.php             # ⏳ TODO: Booking API
│   ├── 📄 guests.php               # ⏳ TODO: Guest API
│   ├── 📄 services.php             # ⏳ TODO: Service API
│   ├── 📄 finances.php             # ⏳ TODO: Finance API
│   ├── 📄 calendar.php             # ⏳ TODO: Calendar API
│   └── 📄 marketing.php            # ⏳ TODO: Marketing API
│
├── 📁 data/                        # JSON Storage (auto-created)
│   ├── 📄 rooms.json               # Room classes
│   ├── 📄 room_instances.json      # Room instances
│   ├── 📄 bookings.json            # Bookings
│   ├── 📄 guests.json              # Guests
│   ├── 📄 services.json            # Services
│   ├── 📄 prices.json              # Price calendar
│   ├── 📄 promocodes.json          # Promocodes
│   ├── 📄 waitlist.json            # Waitlist
│   ├── 📄 taxes.json               # Taxes
│   ├── 📄 contracts.json           # Contract metadata
│   ├── 📄 loyalty.json             # Loyalty program
│   ├── 📄 certificates.json        # Gift certificates
│   ├── 📄 telegram.json            # Telegram users
│   ├── 📄 settings.json            # System settings
│   └── 📁 logs/                    # Log files
│       └── 📄 YYYY-MM-DD.log       # Daily logs
│
├── 📁 uploads/                     # Uploaded Files
│   ├── 📁 documents/               # Guest documents
│   │   ├── 📁 guest_1/
│   │   ├── 📁 guest_2/
│   │   └── 📄 .gitkeep
│   └── 📁 contracts/               # Generated contracts
│       └── 📄 .gitkeep
│
├── 📁 templates/                   # PDF Templates
│   ├── 📄 contract_booking.html    # ⏳ TODO: Booking contract
│   ├── 📄 contract_checkin.html    # ⏳ TODO: Check-in contract
│   └── 📄 act.html                 # ⏳ TODO: Service act
│
├── 📁 wordpress/                   # WordPress Integration
│   └── 📄 sanatorium-plugin.php    # ⏳ TODO: WP plugin
│
├── 📁 telegram/                    # Telegram Bot
│   └── 📄 bot.php                  # ⏳ TODO: Bot webhook
│
└── 📁 vendor/                      # Composer dependencies (auto-created)
    └── ...

```

## 📊 File Status Legend

- ✅ **Complete** - Fully implemented and tested
- ⏳ **TODO** - Needs to be created
- 🔄 **In Progress** - Partially implemented

## 📈 Completion Status

### Core Classes (4/9 = 44%)
- ✅ Database.php
- ✅ Room.php
- ✅ Booking.php
- ✅ Guest.php
- ⏳ Service.php
- ⏳ Finance.php
- ⏳ Marketing.php
- ⏳ Telegram.php
- ⏳ PDF.php

### Admin Pages (1/9 = 11%)
- ✅ index.php (Dashboard)
- ⏳ rooms.php
- ⏳ bookings.php
- ⏳ guests.php
- ⏳ calendar.php
- ⏳ services.php
- ⏳ finances.php
- ⏳ marketing.php
- ⏳ settings.php

### API Endpoints (0/7 = 0%)
- ⏳ All API endpoints need to be created

### Templates (0/3 = 0%)
- ⏳ All PDF templates need to be created

### Integrations (0/2 = 0%)
- ⏳ WordPress plugin
- ⏳ Telegram bot

## 🎯 Priority Order for Development

### Phase 1: Essential UI (Next)
1. Create API endpoints (api/*.php)
2. Build admin pages (admin/*.php)
3. Add JavaScript interactivity

### Phase 2: Advanced Features
1. Service.php class
2. Finance.php class
3. Marketing.php class
4. Financial reports UI

### Phase 3: Integrations
1. FullCalendar integration
2. PDF generation
3. Telegram bot
4. WordPress plugin

## 📝 Notes

- **data/** folder is auto-created by Database class
- **uploads/** folders need proper permissions (777)
- **vendor/** is created by `composer install`
- All JSON files are created automatically on first use
- Logs are created daily in data/logs/

## 🔧 Key Files to Understand

1. **config/config.php** - Start here for configuration
2. **classes/Database.php** - Understand JSON operations
3. **classes/Booking.php** - Complex business logic example
4. **admin/index.php** - Dashboard implementation pattern
5. **install-demo-data.php** - See how to use the classes

## 📚 Documentation Files

- **README.md** - Installation and overview
- **QUICKSTART.md** - 5-minute setup guide
- **PROJECT_SUMMARY.md** - What's built and what's next
- **IMPLEMENTATION_STATUS.md** - Detailed progress tracking
- **FILE_STRUCTURE.md** - This file
