# SocialPulse 🚀

A comprehensive social media management platform designed to help you schedule, publish, and analyze your content across multiple social media platforms from a single, beautifully designed dashboard.

## Features ✨

*   **Multi-Platform Publishing**: Compose and publish posts to Facebook, Twitter, and Instagram simultaneously.
*   **Automated Scheduling**: Schedule posts for the future with a built-in interactive content calendar.
*   **Live Preview**: See exactly how your post will look on each platform before hitting publish.
*   **Advanced Analytics**: Track engagement, reach, and follower growth with dynamic Chart.js visualizations.
*   **Team Management**: Role-based access control (Admin, Editor, Viewer) to securely manage your team.
*   **Report Generation**: Export comprehensive performance reports directly to CSV.
*   **Cron Job Automation**: A dedicated PHP script to reliably handle your scheduled publishing queue.
*   **Premium UI/UX**: Built with modern CSS glassmorphism, responsive layouts, and a dark/light mode toggle.

## Tech Stack 🛠️

*   **Frontend**: Vanilla HTML5, CSS3 (Custom Variables, Flexbox, CSS Grid), Vanilla JavaScript (ES6)
*   **Backend**: PHP 8+ (Custom MVC architecture, PDO)
*   **Database**: MySQL
*   **Icons**: FontAwesome 6
*   **Charts**: Custom HTML5 Canvas (Designed to easily integrate with Chart.js)

## Installation & Setup ⚙️

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/yasiralichanna/SocialPulse.git
    cd SocialPulse
    ```

2.  **Environment Setup**:
    *   Ensure you have a local server environment running (e.g., XAMPP, WAMP, or MAMP).
    *   Move the cloned project folder into your server's web root (e.g., `htdocs` for XAMPP).

3.  **Database Configuration**:
    *   Open `phpMyAdmin` (usually `http://localhost/phpmyadmin`).
    *   Create a new database or simply import the provided schema.
    *   Import the `sql/schema.sql` file. This will create all necessary tables and insert sample seed data.

4.  **Application Configuration**:
    *   Open `config/database.php`.
    *   Update the database connection credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) if they differ from the default XAMPP settings (`root` with no password).

5.  **Run the App**:
    *   Navigate to your local project URL in your browser (e.g., `http://localhost/SocialPulse`).

## Demo Login 🔑

The `schema.sql` file provides default user accounts for testing:

*   **Admin**: `admin@socialpulse.com` / `admin123`
*   **Editor**: `sarah@socialpulse.com` / `admin123`
*   **Viewer**: `mike@socialpulse.com` / `admin123`

## Cron Job Configuration ⏱️

To automatically publish scheduled posts, you need to configure a background cron job on your server to run the `cron/publisher.php` script periodically (e.g., every 5 minutes).

**Example Cron Entry (Linux):**
```bash
*/5 * * * * /usr/bin/php /var/www/html/SocialPulse/cron/publisher.php >/dev/null 2>&1
```
*(On Windows, you can use the Task Scheduler to trigger PHP.exe targeting this file)*

## Author ✍️

Built as a backend development internship project by **Yasir Ali Channa**.
