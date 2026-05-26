# 🏥 MEDICONNECT — Smart Healthcare & Appointment Management System

> A comprehensive web-based healthcare platform built for seamless doctor-patient interaction, appointment scheduling, and medical record management.

---

## 👨‍💻 Author

**Abdur Rahman Jayed**

---

## 📌 About the Project

MEDICONNECT is a full-stack healthcare management system that digitalizes every aspect of patient-doctor interaction and hospital administration. It eliminates the inefficiencies of traditional paper-based healthcare by providing a centralized platform for patients, doctors, and administrators.

---

## ✨ Features

### 👤 Patient
- Register, login, and manage personal profile
- Search and filter doctors by specialty
- Book, view, and cancel appointments
- View prescriptions and medical history
- Request ambulance services with real-time tracking
- Make online payments via SSLCommerz
- Submit doctor reviews and ratings
- AI-powered symptom checker
- Medical history analysis

### 🩺 Doctor
- Register and manage professional profile
- View and manage appointment schedule
- Access patient records and medical history
- Write and manage prescriptions
- Video consultation via integrated video call system

### 🛡️ Admin
- Full dashboard with system overview
- Manage doctors, patients, hospitals, and pharmacies
- Manage appointments and reviews
- Add and manage ambulance fleet
- Manage medical tests and lab services

### 🌐 General
- Nearby pharmacy finder
- Real-time ambulance/resource finder
- Notification system
- Responsive design for all devices

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP (Core) |
| Database | MySQL |
| Payments | SSLCommerz |
| Video Calls | WebRTC-based video call module |
| AI Features | AI Symptom Checker |
| Dev Tools | VS Code, GitHub, XAMPP |

---

## 📁 Project Structure

```
mediconnect/
├── admin/                  # Admin panel pages
│   ├── dashboard.php
│   ├── doctors.php
│   ├── patients.php
│   ├── appointments.php
│   ├── hospitals.php
│   ├── pharmacies.php
│   ├── ambulances.php
│   ├── medical-tests.php
│   └── reviews.php
│
├── doctor/                 # Doctor portal pages
│   ├── dashboard.php
│   ├── appointments.php
│   ├── patients.php
│   ├── prescriptions.php
│   ├── add-prescription.php
│   └── profile.php
│
├── patient/                # Patient portal pages
│   ├── dashboard.php
│   ├── appointments.php
│   ├── medical-records.php
│   ├── medical-history.php
│   ├── prescriptions.php
│   ├── ambulance.php
│   ├── profile.php
│   └── review.php
│
├── payment/                # SSLCommerz payment gateway
│   ├── checkout.php
│   ├── success.php
│   ├── fail.php
│   ├── cancel.php
│   └── ipn.php
│
├── video-call/             # Video consultation module
│   ├── room.php
│   └── end-call.php
│
├── api/                    # REST API endpoints
│   ├── get-nearby-ambulances.php
│   ├── request-ambulance.php
│   └── update-tracking.php
│
├── ajax/                   # AJAX handlers
│   └── get_slots.php
│
├── includes/               # Core configuration & shared files
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   └── sslcommerz.php
│
├── assets/                 # Static assets (CSS, JS)
├── uploads/                # Uploaded files (profile photos etc.)
├── mediconnect.sql         # Database schema & seed data
├── index.php               # Homepage
├── login.php               # Patient login
├── register.php            # Patient registration
├── doctors.php             # Doctor listing & search
├── doctor-profile.php      # Individual doctor profile
├── hospitals.php           # Hospital listing
├── pharmacies.php          # Pharmacy listing
├── medical-tests.php       # Medical test booking
├── nearby-pharmacies.php   # Pharmacy finder
├── real-time-finder.php    # Real-time resource finder
├── ai-symptom-checker.php  # AI symptom checker
└── notifications.php       # Notification center
```

---

## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any PHP + MySQL environment)
- PHP >= 7.4
- MySQL >= 5.7
- A modern web browser

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/mediconnect.git
   ```

2. **Move to your server's web root**
   ```bash
   # For XAMPP on Windows
   Move the mediconnect/ folder to: C:/xampp/htdocs/

   # For XAMPP on Linux/Mac
   Move the mediconnect/ folder to: /opt/lampp/htdocs/
   ```

3. **Import the database**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Create a new database named `mediconnect`
   - Click **Import** and select `mediconnect.sql`

4. **Configure the database connection**

   Open `includes/config.php` and update if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');        // your MySQL password
   define('DB_NAME', 'mediconnect');
   define('SITE_URL', 'http://localhost/mediconnect');
   ```

5. **Start XAMPP** (Apache + MySQL)

6. **Visit the application**
   ```
   http://localhost/mediconnect
   ```

---

## 🔐 Default Login Credentials

| Role | URL | Email | Password |
|------|-----|-------|----------|
| Admin | `/admin/login.php` | *(set in DB)* | *(set in DB)* |
| Doctor | `/doctor-login.php` | *(register first)* | — |
| Patient | `/login.php` | *(register first)* | — |

> ⚠️ Change default credentials before deploying to production.

---

## 🗄️ Database

The database schema is included in `mediconnect.sql`. Key tables:

| Table | Description |
|-------|-------------|
| `patients` | Patient accounts and profiles |
| `doctors` | Doctor accounts, specialty, and availability |
| `appointments` | Appointment bookings and status |
| `prescriptions` | Doctor-issued prescriptions |
| `medical_records` | Patient medical history |
| `ambulances` | Ambulance fleet management |
| `hospitals` | Hospital listings |
| `pharmacies` | Pharmacy listings |
| `reviews` | Patient reviews for doctors |
| `notifications` | System notifications |

---

## 📸 Screenshots

> *(Add screenshots of your UI here)*

---

## 🔒 Security Notes

- Passwords are hashed before storage
- Sessions are used for authentication and role-based access control
- Separate login systems for Admin, Doctor, and Patient
- Parameterized queries used to prevent SQL injection

---

## 📄 License

This project was developed by Abdur Rahman Jayed. All rights reserved.

---

## 🙏 Acknowledgements

- [SSLCommerz](https://sslcommerz.com/) — Payment gateway integration
- [XAMPP](https://www.apachefriends.org/) — Local development environment
- All open-source libraries and tools used in this project
