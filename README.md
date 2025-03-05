# Firebase Push Notification Admin Panel

A web-based admin panel for managing Firebase Cloud Messaging (FCM) push notifications.

![Push Notification Admin Panel](https://cdn.jsdelivr.net/gh/hscbmt/img@main/push.png)

## Features

- **User Authentication & Admin Management**: Secure login with admin role management.
- **Push Notification Sending**: Send notifications with image and action URL support.
- **Firebase Configuration Management**: Easily configure Firebase settings for seamless integration.
- **Dark/Light Theme Support**: Toggle between dark and light themes for a personalized experience.
- **Notification History Tracking**: Keep track of all sent notifications for historical reference.
- **Responsive Design**: Fully responsive for desktops, tablets, and mobile devices.

## Prerequisites

- **XAMPP** (with PHP 8.0+ and MySQL)
- **Web Browser**
- **Firebase Project** with FCM enabled

## Installation Guide

1. **Clone or Download the Repository**  
   Clone or download this repository to your XAMPP `htdocs` folder:
   ```bash
   cd C:/xampp/htdocs
   git clone [repository-url] pushUi
   ```

2. **Create a New MySQL Database**  
   - Create a database named `push_notification_db`.

3. **Import Database Schema**  
   - Open phpMyAdmin (http://localhost/phpmyadmin).
   - Select the `push_notification_db` database.
   - Import the `push_notification_db.sql` file.

4. **Configure Database Connection**  
   - Create a file at `config/database.php` with your database credentials:
   ```php
   <?php
   function connectDB() {
       $host = 'localhost';
       $dbname = 'push_notification_db';
       $username = 'root';
       $password = '';
   
       try {
           $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
           $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
           return $conn;
       } catch(PDOException $e) {
           die("Connection failed: " . $e->getMessage());
       }
   }
   ?>
   ```

5. **Set Up Firebase**  
   - Create a Firebase project at [Firebase Console](https://console.firebase.google.com/).
   - Enable Cloud Messaging.
   - Download your `google-services.json` file.
   - Note your Firebase Project ID.

## First-time Setup

1. **Start XAMPP**  
   Ensure Apache and MySQL services are running.

2. **Access the Application**  
   Navigate to the following URL in your browser:
   ```bash
   http://localhost/pushUi/
   ```

3. **Login with Default Credentials**  
   - Email: `admin@example.com`
   - Password: `123456`

4. **Configure Firebase and Default Topic**  
   Go to **Configuration** and set up the following:
   - Firebase Project ID
   - Default Topic (e.g., `'allDevices'`)
   - Paste the contents of your `google-services.json` file.

## Project Structure

```
pushUi/
├── assets/                  # Static assets (CSS, images)
├── config/                  # Configuration files
├── README.md                # This file
├── admin-management.php     # Admin user management
├── dashboard.php            # Admin dashboard
├── deleteNotification.php   # Delete notifications
├── index.php                # Main entry point
├── login.php                # Login page
├── logout.php               # Logout page
├── notification-config.php  # Notification configuration
├── profile.php              # Admin profile management
├── push-notification.php    # Push notification UI
└── sendNotification.php     # Logic for sending push notifications
```

## Demo Screenshots

### Dashboard

View the push notification history and send new notifications.

![Dashboard](https://cdn.jsdelivr.net/gh/hscbmt/img@main/push3.png)

### Notification Sending

Send push notifications with image attachments and action URLs.

![Send Notification](https://cdn.jsdelivr.net/gh/hscbmt/img@main/push1.png)

## Security Notes

- **Change the default admin password** after the first login to secure your application.
- **Keep your `google-services.json` file secure** to prevent unauthorized access to Firebase.
- **Regularly update your credentials** for Firebase and the database.
- **Enable proper error reporting** in production to catch issues early.

## Contributing

We welcome contributions! To contribute:

1. **Fork the repository**.
2. **Create a new feature branch**.
3. **Commit your changes**.
4. **Push your branch**.
5. **Create a Pull Request**.

## License

This project is licensed under the **MIT License**.

## Support

For support:

- Contact the system administrator.
- Create an issue in the repository for troubleshooting or feature requests.

---

This version should make it easier for users to understand the setup and usage of your push notification admin panel, with added images for a more engaging experience!
