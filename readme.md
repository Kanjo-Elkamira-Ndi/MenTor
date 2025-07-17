# MenTor 🎓

MenTor is a comprehensive web-based student management system designed to streamline academic and administrative processes for educational institutions. It provides functionalities for managing students, courses, enrollments, grades, programs, and schools, offering distinct dashboards for both administrators and students.

## ✨ Features

### Student Features:

*   **Dashboard:** Personalized overview of academic progress.
*   **My Courses:** View enrolled courses and their details.
*   **CA Marks:** Check continuous assessment marks.
*   **Transcripts:** Access and download academic transcripts.
*   **Profile Management:** Update personal information.

### Admin Features:

*   **User Management:** Add, edit, and manage student and admin accounts.
*   **Course Management:** Create, update, and delete courses.
*   **Enrollment Management:** Handle student enrollments in courses.
*   **Grade Management:** Record and update student grades for assessments.
*   **Program Management:** Define and manage academic programs.
*   **School Management:** Administer different schools or departments within the institution.
*   **Transcript Generation:** Generate and manage student transcripts.

## 🚀 Technologies Used

*   **Frontend:** HTML5, CSS3, JavaScript
*   **Backend:** PHP
*   **Database:** MySQL (managed with phpMyAdmin)

## 📁 Project Structure

```
MenTor/
├── api/                     # Backend API endpoints (PHP)
│   ├── admin/
│   └── student/
├── assets/                  # Static assets like images
│   └── images/
├── auth/                    # Authentication related scripts (login, logout, session check)
├── css/                     # Stylesheets
├── includes/                # Database connection and other utility files
├── js/                      # JavaScript files
├── pages/                   # HTML pages for different sections of the application
│   ├── AdminDashboard/
│   ├── student dashboard/
│   ├── images/
│   ├── login.html
│   └── register.html
├── mentor_db.sql            # Database schema and initial data
├── hash_password.php        # Utility for password hashing
├── index.html               # Landing page
└── transcripts/             # Generated student transcripts
```

## ⚙️ Installation & Setup

To set up MenTor on your local machine, follow these steps:

### 1. Clone the Repository

```bash
git clone <repository_url>
cd MenTor
```

### 2. Database Setup

1.  **Create Database:** Using phpMyAdmin or your preferred MySQL client, create a new database named `mentor_db`.
2.  **Import Schema:** Import the `mentor_db.sql` file located in the project root into your newly created database.

### 3. Web Server Configuration

*   Ensure you have a web server (like Apache or Nginx) with PHP installed.
*   Place the `MenTor` project directory in your web server's document root (e.g., `htdocs` for Apache).

### 4. Database Connection

*   Open `includes/db_connect.php`.
*   Update the database connection details (hostname, username, password, database name) if they differ from your local setup.

```php
<?php
$servername = "localhost";
$username = "your_db_username"; // e.g., root
$password = "your_db_password"; // e.g., empty for XAMPP/WAMP default
$dbname = "mentor_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

### 5. Access the Application

Open your web browser and navigate to the URL where you placed the project (e.g., `http://localhost/MenTor/` or `http://localhost/MenTor/index.html`).

## 🔑 Default Credentials

For initial testing, you can use the following admin credentials:

*   **Email:** `admin@mentor.edu`
*   **Password:** `admin` (This password is hashed in the database. You might need to re-hash it or use the `hash_password.php` script to create new admin users.)

## 🤝 Contributing

Contributions are welcome! Please feel free to fork the repository, create pull requests, or open issues for bugs and feature requests.


## 📞 Support

If you encounter any issues or have questions, please open an issue on the GitHub repository. (Note: Assuming a GitHub repository for support.)


