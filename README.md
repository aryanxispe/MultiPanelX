<div align="center">
  <img src="aryanispe.png" alt="MultiPanelX Logo" width="120" />
  
  # MultiPanelX 🎮
  
  **The Ultimate Open-Source Mod Panel & Software License Selling Platform**
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com/)
  [![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
  [![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](LICENSE)
  <br>
  [![Telegram Channel](https://img.shields.io/badge/Telegram_Channel-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)](https://t.me/+iJzQJhMZ4qA2ZmM1)
  [![Telegram Admin](https://img.shields.io/badge/Telegram_Admin-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)](https://t.me/ARYANISPE)
  [![YouTube](https://img.shields.io/badge/YouTube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/@aryanispe)
  [![Instagram](https://img.shields.io/badge/Instagram-E4405F?style=for-the-badge&logo=instagram&logoColor=white)](https://www.instagram.com/aryanispe/)
  [![Hosting](https://img.shields.io/badge/Web_Hosting-FF6B6B?style=for-the-badge&logo=google-cloud&logoColor=white)](https://aryanispehost.in/)
</div>

<br/>

**MultiPanelX** is an advanced, self-hosted, open-source mod panel and digital key distribution system built with PHP and MySQL. Designed for mod developers, game hackers, and software creators, it provides a complete end-to-end infrastructure for selling mod APKs, ESP tools, game scripts, and premium software licenses securely.

With a fully integrated **Indian UPI Payment Gateway System** (Manual/QR-based), automated license key generation, and a robust REST API for client-side device validation, MultiPanelX empowers you to launch your own software monetization platform in minutes.

---

## 🚀 Why Choose MultiPanelX? (SEO & Architecture)

MultiPanelX stands out from other standard reseller panels by offering a bespoke architecture tailored specifically for digital software distribution:

- **Zero-Dependency Core:** Built using vanilla PHP 8 and PDO, ensuring lightning-fast execution and maximum compatibility with shared hosting providers like cPanel, CyberPanel, and Hostinger.
- **RESTful Validation API:** A highly optimized `api.php` endpoint designed to handle hundreds of concurrent requests from your Mod Menus, Android Injectors, or PC Software to validate keys and lock them to specific Hardware IDs (HWID/Device ID).
- **Tailwind UI/UX:** The frontend is crafted using utility-first CSS (Tailwind), ensuring a 100% responsive, mobile-first experience with an ultra-premium Glassmorphism design and native Dark Mode support.
- **Bypass 3rd-Party Gateway Fees:** By utilizing a direct UPI QR code generation system based on user-inputted UTR/Transaction IDs, you bypass heavy payment gateway fees (Razorpay, Stripe, PayU) and receive payments directly to your bank account with 0% commission.

---

## ✨ Comprehensive Feature List

### 🛡️ Admin Superpowers (Dashboard)
- **Mod Management Engine:** Add, edit, and organize unlimited mods. Upload Mod APK files directly to the server, define feature lists, and set version numbers.
- **Dynamic Pricing Plans:** Create modular subscription plans for each mod. Support for dynamic durations: **Hours, Days, and Months**.
- **Automated License Generator:** Generate bulk license keys assigned to specific mods and durations. Track whether keys are `Available`, `Sold`, `Expired`, or `Blocked`.
- **Order Approvals (UPI Workflow):** Review user payment submissions (Transaction IDs), cross-verify with your bank app, and click "Approve" to instantly auto-assign a license key to the buyer.
- **User Management & Analytics:** Track user registrations, monitor individual wallet balances, view login history, and manage access roles.
- **Global Site Configuration:** Update site branding (Panel Name, Tagline, Telegram links, Support Email, and Admin UPI ID) directly from the UI without touching the code.
- **Transaction Ledger:** A dedicated financial dashboard tracking all deposits, purchases, and refunds.

### 👤 User Experience (Client Portal)
- **Sleek Client Dashboard:** Users can track their active software licenses, remaining duration, wallet balance, and order status at a glance.
- **Digital Wallet System:** Users can top-up their digital wallet via UPI for frictionless, one-click checkouts on future purchases.
- **Refer & Earn (Affiliate System):** Built-in referral system. Users get unique referral codes to invite friends, driving organic SEO and community growth for your panel.
- **Seamless UPI Integration:** Built-in UPI intent and dynamic QR code generation. Users simply scan, pay, and enter their 12-digit UTR/Txn ID.
- **Native Dark/Light Mode:** A persistent theme toggle that respects user preferences across sessions.

---

## 📸 Interface Preview

*(Upload your screenshots to GitHub and link them here)*
<!-- Example:
![Admin Dashboard](https://example.com/admin-dashboard.png)
![User Store](https://example.com/user-store.png)
-->

---

## 🔌 Developer API Documentation (Key Validation)

Integrate MultiPanelX directly into your Android C++ Mod Menu, Java Injector, Python Script, or C# Desktop App. The built-in REST API handles device binding and time validation automatically.

### Verify a License Key
**Endpoint:** `GET /api`

**Parameters:**
- `action` (required): Must be `verify`
- `key` (required): The license key string.
- `device_id` (required): The unique hardware ID of the user's device (e.g., Android Settings Secure ID, PC Mac Address).

**Example Request (cURL):**
```bash
curl "https://yourdomain.com/api?action=verify&key=ARY-ABCD-1234&device_id=d41d8cd98f00b204e9800998ecf8427e"
```

**Successful JSON Response (200 OK):**
```json
{
  "status": "success",
  "message": "Valid key",
  "mod_id": 1,
  "mod_name": "PUBGM ESP Hack",
  "expires_at": "2024-12-31 23:59:59",
  "device_id": "d41d8cd98f00b204e9800998ecf8427e"
}
```

**Error JSON Response (403 Forbidden):**
```json
{
  "status": "error",
  "message": "Device ID mismatch. Key is already bound to another device."
}
```

---

## 🚀 Quick Start / Installation Guide

### Server Requirements
- Operating System: Linux (Ubuntu/CentOS/CloudLinux) or Windows
- Web Server: Apache or LiteSpeed (Must support `.htaccess` for URL routing)
- PHP Version: 8.0, 8.1, or 8.2
- Database: MySQL 5.7+ or MariaDB 10.4+
- Required PHP Extensions: `pdo_mysql`, `mbstring`, `json`, `cURL`

### Installation Steps

#### Step 1: Upload & Extract
1. Download the latest `MultiPanelX.zip` from the [Releases](https://github.com/aryanxispe/MultiPanelX/releases) page.
2. Upload the ZIP file to your web server's `public_html` directory using cPanel File Manager or FTP.
3. Extract the contents.

#### Step 2: Database Initialization
1. Login to your hosting control panel (cPanel).
2. Go to **MySQL Databases** and create a new database and a new user. Assign all privileges.
3. Open **phpMyAdmin**, select your newly created database, and click the **Import** tab.
4. Upload the provided `database.sql` file to build the schema structure.

#### Step 3: Database Configuration
1. In your File Manager, open the file `config/database.php`.
2. Replace the placeholder constants with your actual database credentials:
   ```php
   define('DB_HOST', 'localhost'); // Usually localhost
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'your_db_name');
   ```

#### Step 4: Login & Secure Your Panel
1. Navigate to your website's `/login` page in your web browser.
2. Login using the default superadmin credentials:
   - **Username:** `admin`
   - **Password:** `admin123`
3. **⚠️ CRITICAL SECURITY STEP:** Immediately navigate to your **Profile** and change the admin password to a strong, secure phrase.

#### Step 5: Configure UPI Payments
1. Go to **Admin Panel → Site Settings**.
2. Scroll to the **Payment Settings (UPI)** section.
3. Enter your real UPI ID (e.g., `yourname@oksbi`) so users can pay you directly.
4. Save changes. You are now ready to monetize your software! 💰

---

## 🤝 Contributing & Support

MultiPanelX is an open-source project driven by the community. Contributions, bug reports, and feature requests are highly encouraged! 
Feel free to check the [Issues page](https://github.com/aryanxispe/MultiPanelX/issues).

1. Fork the Project on GitHub.
2. Create your Feature Branch (`git checkout -b feature/EpicNewFeature`).
3. Commit your Changes (`git commit -m 'Add some EpicNewFeature'`).
4. Push to the Branch (`git push origin feature/EpicNewFeature`).
5. Open a Pull Request for review.

---

## 📜 License & Disclaimer

MultiPanelX is distributed under the **MIT License**. See [LICENSE](LICENSE) for more information. 

*Disclaimer: This software is provided for educational and software licensing purposes. The developers of MultiPanelX are not responsible for the misuse of this platform to distribute malicious software or violate third-party Terms of Service.*

---
<div align="center">
  <b>MultiPanelX</b> was engineered with ❤️ by <a href="https://github.com/aryanxispe">aryanxispe</a><br><br>
  💬 <b>Join Community:</b> <a href="https://t.me/+iJzQJhMZ4qA2ZmM1">Telegram Channel</a><br>
  💬 <b>Contact Admin:</b> <a href="https://t.me/ARYANISPE">@ARYANISPE on Telegram</a><br>
  ▶️ <b>Subscribe:</b> <a href="https://www.youtube.com/@aryanispe">YouTube Channel</a><br>
  📸 <b>Follow:</b> <a href="https://www.instagram.com/aryanispe/">Instagram</a><br>
  🌐 <b>Need Reliable Hosting?</b> Check out <a href="https://aryanispehost.in/">AryanispeHost.in</a>
</div>
