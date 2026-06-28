-- ═══════════════════════════════════════════
-- SocialPulse — Database Schema
-- Run: mysql -u root -p < schema.sql
-- ═══════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS socialpulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE socialpulse;

-- Users & Roles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor','viewer') DEFAULT 'viewer',
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Social accounts linked
CREATE TABLE social_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform ENUM('facebook','twitter','instagram') NOT NULL,
    account_name VARCHAR(100),
    access_token TEXT,
    token_expires_at DATETIME,
    status ENUM('connected','disconnected','expired') DEFAULT 'disconnected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    platforms JSON NOT NULL COMMENT '["facebook","twitter"]',
    image_url VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','scheduled','published','failed') DEFAULT 'draft',
    scheduled_at DATETIME DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    repeat_type ENUM('none','daily','weekly','monthly') DEFAULT 'none',
    timezone VARCHAR(10) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status_scheduled (status, scheduled_at)
);

-- Analytics per post per platform
CREATE TABLE post_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    platform ENUM('facebook','twitter','instagram') NOT NULL,
    likes INT DEFAULT 0,
    comments INT DEFAULT 0,
    shares INT DEFAULT 0,
    reach INT DEFAULT 0,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post_platform (post_id, platform)
);

-- Daily aggregated analytics
CREATE TABLE daily_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('facebook','twitter','instagram') NOT NULL,
    date DATE NOT NULL,
    followers INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0,
    total_reach INT DEFAULT 0,
    total_impressions INT DEFAULT 0,
    posts_count INT DEFAULT 0,
    UNIQUE KEY uk_platform_date (platform, date)
);

-- Report history
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('engagement','growth','content','full') NOT NULL,
    platform VARCHAR(20) DEFAULT 'all',
    date_range VARCHAR(10) DEFAULT '30d',
    file_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cron job logs
CREATE TABLE cron_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(200) NOT NULL,
    status ENUM('success','warning','error') DEFAULT 'success',
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at)
);

-- ═══ Seed Data ═══

-- Default admin (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Yasir Admin', 'admin@socialpulse.com', '$2y$10$qNM1IlJdtLiNivhLL55BW.21w.EMcRXjSfNq2OmOoA1d66DxXDnZ.', 'admin'),
('Sarah Editor', 'sarah@socialpulse.com', '$2y$10$qNM1IlJdtLiNivhLL55BW.21w.EMcRXjSfNq2OmOoA1d66DxXDnZ.', 'editor'),
('Mike Viewer', 'mike@socialpulse.com', '$2y$10$qNM1IlJdtLiNivhLL55BW.21w.EMcRXjSfNq2OmOoA1d66DxXDnZ.', 'viewer');

-- Sample social accounts
INSERT INTO social_accounts (user_id, platform, account_name, status) VALUES
(1, 'facebook', 'SocialPulse Official', 'connected'),
(1, 'twitter', '@socialpulse_app', 'connected'),
(1, 'instagram', '@socialpulse', 'disconnected');

-- Sample posts
INSERT INTO posts (user_id, content, platforms, status, scheduled_at, published_at) VALUES
(1, '🚀 Excited to announce our new product launch! Stay tuned for something amazing. #innovation #launch', '["facebook","twitter"]', 'published', NULL, NOW() - INTERVAL 2 HOUR),
(1, 'Behind the scenes of our creative process 🎨✨ What do you think?', '["instagram","facebook"]', 'published', NULL, NOW() - INTERVAL 5 HOUR),
(1, 'Top 5 tips for social media marketing in 2026. Thread 🧵👇', '["twitter"]', 'published', NULL, NOW() - INTERVAL 1 DAY),
(2, 'Join us for a live Q&A session this Friday at 3 PM EST! 🎙️', '["facebook","twitter","instagram"]', 'scheduled', NOW() + INTERVAL 2 DAY, NULL),
(2, 'Weekly roundup: Our best performing content this week 📊', '["facebook","twitter"]', 'scheduled', NOW() + INTERVAL 3 DAY, NULL),
(1, 'Happy Monday! What are your goals for this week? 💪', '["instagram"]', 'scheduled', NOW() + INTERVAL 5 DAY, NULL),
(1, 'Check out our latest blog post on AI trends!', '["facebook","twitter"]', 'draft', NULL, NULL);

-- Sample analytics
INSERT INTO post_analytics (post_id, platform, likes, comments, shares, reach, impressions, clicks) VALUES
(1, 'facebook', 245, 38, 67, 4520, 8900, 312),
(1, 'twitter', 189, 22, 95, 6200, 12400, 445),
(2, 'instagram', 567, 84, 23, 8900, 15600, 234),
(2, 'facebook', 178, 31, 44, 3400, 6700, 189),
(3, 'twitter', 423, 67, 156, 12500, 24800, 890);

-- Sample daily analytics (last 30 days)
INSERT INTO daily_analytics (platform, date, followers, engagement_rate, total_reach, total_impressions, posts_count)
SELECT 'facebook', DATE(NOW() - INTERVAL n DAY),
    18000 + FLOOR(RAND()*500), ROUND(3.5 + RAND()*3, 2), 1000 + FLOOR(RAND()*5000), 3000 + FLOOR(RAND()*10000), FLOOR(RAND()*4)
FROM (SELECT a.N + b.N*10 AS n FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
     (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2) b) nums WHERE n < 30;

INSERT INTO daily_analytics (platform, date, followers, engagement_rate, total_reach, total_impressions, posts_count)
SELECT 'twitter', DATE(NOW() - INTERVAL n DAY),
    14000 + FLOOR(RAND()*400), ROUND(2.8 + RAND()*2.5, 2), 800 + FLOOR(RAND()*4000), 2500 + FLOOR(RAND()*8000), FLOOR(RAND()*5)
FROM (SELECT a.N + b.N*10 AS n FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
     (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2) b) nums WHERE n < 30;

INSERT INTO daily_analytics (platform, date, followers, engagement_rate, total_reach, total_impressions, posts_count)
SELECT 'instagram', DATE(NOW() - INTERVAL n DAY),
    15000 + FLOOR(RAND()*600), ROUND(4.0 + RAND()*3.5, 2), 1200 + FLOOR(RAND()*6000), 4000 + FLOOR(RAND()*12000), FLOOR(RAND()*3)
FROM (SELECT a.N + b.N*10 AS n FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
     (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2) b) nums WHERE n < 30;
