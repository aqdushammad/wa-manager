# WA Manager – WhatsApp Per Page

**Author:** Aqdus Hammad  
**GitHub:** https://github.com/aqdushammad  
**Version:** 1.0.0  
**Requires WordPress:** 5.6+  
**Requires PHP:** 7.4+  

---

## What it does

Assign **different WhatsApp numbers to different pages** on your WordPress site. Perfect for Google Ads landing pages where each campaign needs its own WhatsApp contact.

- 🟢 Beautiful floating WhatsApp chat widget (popup design)
- 📄 Assign any WhatsApp number to any specific page(s)
- 🌐 Set a global fallback number for all other pages
- ✏️ Customize all widget text per account (header, button, agent name)
- 💬 Pre-fill a WhatsApp message per account
- 🔔 Optional notification badge on the float button
- 🔄 **Auto-updates from GitHub** — updates show in your WP dashboard

---

## Installation

1. Go to [Releases](https://github.com/aqdushammad/wa-manager/releases/latest) and download the latest ZIP
2. In WordPress go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and click **Install Now → Activate**
4. You'll see **WA Manager** in your WordPress sidebar

---

## How to use

### Add a WhatsApp account
1. Go to **WA Manager → Add Account**
2. Enter an internal name (e.g. "Sales Team UAE") and the WhatsApp number (e.g. `971526132864`)
3. Set a pre-filled message (optional)
4. Customize the widget text (header, button label, agent description)
5. Search and select which page(s) this number should appear on
6. Save

Repeat for each number / page combination.

### Global fallback
Enable "Show on all pages" on one account to use it as a fallback on pages that have no specific account assigned.

---

## How auto-updates work

This plugin checks the GitHub Releases API every 12 hours.

When you want to release a new version:

1. **Bump the version** in `wa-manager.php` — change `Version: 1.0.0` and `define('WAM_VERSION', '1.0.0')` to the new version
2. **Push to GitHub**
3. **Create a GitHub Release** with a tag matching the version:
   - Go to your repo → **Releases → Draft a new release**
   - Tag: `v1.1.0` (must match the version number)
   - Add release notes (these show in the WP update modal)
   - Publish release

Within 12 hours, all WordPress sites running this plugin will see **"Update Available"** in their dashboard. Clicking Update installs it automatically.

---

## File structure

```
wa-manager/
├── wa-manager.php                  ← Main plugin file
├── README.md
├── includes/
│   ├── class-wam-db.php            ← Database (accounts table)
│   ├── class-wam-admin.php         ← Admin menu & AJAX
│   ├── class-wam-frontend.php      ← Widget output on pages
│   └── class-wam-updater.php       ← GitHub auto-update system
├── admin/
│   ├── css/admin.css
│   ├── js/admin.js
│   └── views/
│       ├── list.php                ← Accounts list page
│       └── form.php                ← Add / Edit account form
└── public/
    ├── css/widget.css
    └── js/widget.js
```

---

## Changelog

### v1.0.0 — Initial Release
- Per-page WhatsApp account assignment
- Beautiful popup widget matching professional designs
- Admin dashboard to manage multiple accounts
- Global fallback account support
- Pre-filled WhatsApp messages
- GitHub auto-update integration

---

## License

GPL-2.0+ — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)
