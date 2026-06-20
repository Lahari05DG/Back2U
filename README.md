# 🔍 Bck2U — Lost & Found Platform

A modern, student-driven lost and found web application built for college campuses. Report found items, search for lost belongings, and connect with owners through real-time discussions.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 🔐 **Secure Auth** | Login & registration with password hashing |
| 📊 **Dashboard** | Animated stat cards — total items, claims, resolved, discussions |
| 📤 **Upload Items** | Drag & drop image upload with preview and validation |
| 🔎 **Smart Search** | Filter by keyword, category, and status |
| 🤝 **Claim System** | Smart claim detection — shows "Already Claimed" to other users |
| 💬 **Real-time Chat** | WhatsApp-style AJAX messaging (no page reloads) |
| 🌙 **Dark Mode** | Toggle with localStorage persistence |
| 🔔 **Notifications** | Live badge counts for new claims and messages |
| 📱 **Responsive** | Works on desktop, tablet, and mobile |
| ✅ **Resolve Items** | Mark items as returned with confirmation |

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (Custom Design System), JavaScript (Vanilla)
- **Backend**: PHP 8+
- **Database**: MySQL
- **Server**: XAMPP (Apache)
- **Fonts**: Inter (Google Fonts)
- **Icons**: Font Awesome 6.4.0

---

## 🚀 Setup Instructions

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed with Apache & MySQL running

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Lahari05DG/Back2U.git
   ```

2. **Move to XAMPP htdocs**
   ```
   Copy the Back2U folder to C:\xampp\htdocs\
   ```

3. **Create the database**
   
   Open phpMyAdmin (`http://localhost/phpmyadmin`) and run:
   ```sql
   CREATE DATABASE Back2U_db;
   USE Back2U_db;

   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(100) NOT NULL,
       email VARCHAR(100) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE items (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       image VARCHAR(255) NOT NULL,
       category VARCHAR(100) NOT NULL,
       description TEXT NOT NULL,
       location VARCHAR(255) NOT NULL,
       status VARCHAR(50) DEFAULT 'discussion',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id)
   );

   CREATE TABLE claims (
       id INT AUTO_INCREMENT PRIMARY KEY,
       item_id INT NOT NULL,
       claimer_id INT NOT NULL,
       lost_location VARCHAR(255) NOT NULL,
       lost_date DATE NOT NULL,
       description TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (item_id) REFERENCES items(id),
       FOREIGN KEY (claimer_id) REFERENCES users(id)
   );

   CREATE TABLE messages (
       id INT AUTO_INCREMENT PRIMARY KEY,
       item_id INT NOT NULL,
       sender_id INT NOT NULL,
       message TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (item_id) REFERENCES items(id),
       FOREIGN KEY (sender_id) REFERENCES users(id)
   );
   ```

4. **Create uploads folder**
   ```
   Create a folder named "uploads" inside the Back2U directory
   ```

5. **Open in browser**
   ```
   http://localhost/Back2U/
   ```

---

## 📁 Project Structure

```
Back2U/
├── css/
│   ├── style.css            # Main design system (36KB)
│   └── dark-mode.css        # Dark theme overrides
├── js/
│   ├── app.js               # Core interactivity (toasts, modals, dark mode)
│   └── chat.js              # AJAX chat system
├── php/
│   ├── db.php               # Database connection
│   ├── header.php           # Shared navbar component
│   ├── footer.php           # Shared footer component
│   ├── claim_form.php       # Claim submission form
│   ├── api_chat.php         # Chat REST API
│   ├── api_stats.php        # Dashboard stats API
│   └── api_notifications.php # Notification badge API
├── uploads/                 # User-uploaded images
├── index.php                # Login page
├── register.php             # Registration page
├── dashboard.php            # Main dashboard
├── upload_item.php          # Upload found items
├── find_item.php            # Search & filter items
├── chat.php                 # Discussion chat page
├── claim_item.php           # Claim handler
├── resolve_item.php         # Mark item as resolved
├── logout.php               # Session logout
└── .gitignore
```

---

## 📸 Pages

| Page | Description |
|------|-------------|
| **Login / Register** | Split-screen auth with indigo gradient branding |
| **Dashboard** | 4 stat cards + uploaded items grid + claimed items grid |
| **Upload Item** | Drag-drop zone with image preview |
| **Find Items** | Search bar + category/status filters + item cards |
| **Chat** | WhatsApp-style bubbles with real-time AJAX polling |
| **Claim Form** | Verify ownership with location, date, and description |

---

## 🎨 Design System

- **Primary Color**: Indigo `#4F46E5`
- **Font**: Inter (Google Fonts)
- **Border Radius**: 16px cards, 10px buttons, 25px pills
- **Shadows**: 4-level shadow system (sm, md, lg, xl)
- **Dark Mode**: Full theme with CSS variable overrides

---

## 👥 Contributors

- **Lahari05DG** — Developer

---

## 📄 License

This project is for educational purposes.

---

<p align="center">Made with ❤️ for college students</p>
