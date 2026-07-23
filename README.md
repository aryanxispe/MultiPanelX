<div align="center">
  <img src="aryanispe.png" alt="MultiPanelX Logo" width="120" />
  
  # MultiPanelX 🎮
  
  **The Ultimate Open-Source Mod Panel & Key Selling Platform**
  
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

MultiPanelX is a high-performance, fully-featured mod panel designed for selling mod APKs, game hacks, and premium software licenses. It features a complete automated UPI payment flow, admin approval system, user wallets, and referral rewards—all wrapped in a stunning, modern UI with built-in Dark Mode.

---

## ✨ Key Features

### 🛡️ Admin Superpowers
- **Mod Management** — Add, edit, and organize your mods with custom descriptions and features.
- **Dynamic Pricing Plans** — Create custom plans (Hours, Days, Months) for each mod.
- **License Generator** — Automatically or manually generate license keys.
- **Order Approvals** — Review user UPI payments and approve/reject with a single click.
- **User Management** — Track users, view balances, and manage access.
- **Site Settings** — Update branding (Name, Logo, Telegram link, UPI ID) directly from the panel.
- **Revenue Tracking** — Dedicated transactions and history dashboards.

### 👤 User Experience
- **Sleek Dashboard** — Track active licenses, wallet balance, and order history.
- **Wallet System** — Add funds to a digital wallet for seamless checkout.
- **UPI Integration** — Built-in UPI QR code generator for manual/semi-automated payments.
- **Refer & Earn** — Built-in referral system to boost organic growth.
- **Dark/Light Mode** — Premium, responsive UI that looks beautiful on any device.

### 🔌 Developer API
- **Key Validation Endpoint** — Integrate your mods directly with the panel using the built-in REST API to check key validity, duration, and device binding.

---

## 📸 Screenshots

*(Add your screenshots here)*
<!-- Example:
![Dashboard](https://example.com/dashboard.png)
![Payment Flow](https://example.com/payment.png)
-->

---

## 🚀 Quick Start / Installation

### Prerequisites
- Web Server (Apache/LiteSpeed recommended for `.htaccess`)
- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.4+
- PDO PHP Extension enabled

### Step 1: Upload & Extract
1. Download the latest source code from the repository.
2. Upload the files to your web server's `public_html` or desired directory.

### Step 2: Database Setup
1. Create a new MySQL Database and User in your hosting panel (e.g., cPanel).
2. Open **phpMyAdmin** and select your newly created database.
3. Import the provided `database.sql` file.

### Step 3: Configuration
1. Open `config/database.php` in a text editor.
2. Replace the placeholder credentials with your actual database details:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'YOUR_DB_USERNAME');
   define('DB_PASS', 'YOUR_DB_PASSWORD');
   define('DB_NAME', 'YOUR_DB_NAME');
   ```

### Step 4: Login & Secure
1. Navigate to your website's `/login` page.
2. Login using the default admin credentials:
   - **Username:** `admin`
   - **Password:** `admin123`
3. **⚠️ CRITICAL:** Immediately go to your Profile and change the admin password!

### Step 5: Configure UPI
1. Go to **Admin Panel → Site Settings**.
2. Enter your real UPI ID so users can pay you.
3. Save changes. You're ready to sell! 💰

---

## 📡 API Documentation (For Mod Developers)

MultiPanelX comes with a validation API to check keys from inside your Android/PC mods. 
Access the full documentation from your admin panel at `/docs`.

**Example Validation Request:**
```http
GET /api.php?action=verify&key=YOUR_LICENSE_KEY&device_id=UNIQUE_DEVICE_ID
```

---

## 🛠️ Tech Stack
- **Backend:** PHP 8 (Vanilla)
- **Database:** MySQL / MariaDB (PDO)
- **Styling:** Vanilla CSS / Tailwind utilities (pre-compiled)
- **Icons:** Phosphor Icons / FontAwesome

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! 
Feel free to check [issues page](https://github.com/aryanxispe/MultiPanelX/issues).

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📜 License

Distributed under the MIT License. See `LICENSE` for more information.

---
<div align="center">
  <b>MultiPanelX</b> was created with ❤️ by <a href="https://github.com/aryanxispe">aryanxispe</a><br><br>
  💬 <b>Join Community:</b> <a href="https://t.me/+iJzQJhMZ4qA2ZmM1">Telegram Channel</a><br>
  💬 <b>Contact Admin:</b> <a href="https://t.me/ARYANISPE">@ARYANISPE on Telegram</a><br>
  ▶️ <b>Subscribe:</b> <a href="https://www.youtube.com/@aryanispe">YouTube Channel</a><br>
  📸 <b>Follow:</b> <a href="https://www.instagram.com/aryanispe/">Instagram</a><br>
  🌐 <b>Need Reliable Hosting?</b> Check out <a href="https://aryanispehost.in/">AryanispeHost.in</a>
</div>
