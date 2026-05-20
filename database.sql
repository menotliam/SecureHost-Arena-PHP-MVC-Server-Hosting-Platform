-- SecureHost Arena

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS review_likes;
DROP TABLE IF EXISTS news_likes;
DROP TABLE IF EXISTS news_views;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS ads;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS news_categories;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS faq_categories;
DROP TABLE IF EXISTS faq_messages;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admin_notifications;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS user_service_options;
DROP TABLE IF EXISTS user_services;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS about;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    status ENUM('active', 'banned') DEFAULT 'active',
    credit INT DEFAULT 0,
    reset_token VARCHAR(255) NULL,
    role ENUM('admin', 'member') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('ticket', 'revenue') NOT NULL,
    source_key VARCHAR(120) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    url VARCHAR(255),
    payload LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_notifications_source_key (source_key),
    KEY idx_admin_notifications_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    ram_mb INT,
    cpu_cores INT,
    disk_gb INT,
    image_url VARCHAR(255),
    status ENUM('active', 'hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    ip_address VARCHAR(50),
    port INT,
    status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    current_ram_mb INT,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_service_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_service_id INT NOT NULL,
    option_key VARCHAR(100) NOT NULL,
    option_value VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_service_id) REFERENCES user_services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    duration_months INT DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 1,
    duration_months INT DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    status ENUM('published', 'draft') DEFAULT 'published',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) NULL,
    status ENUM('active', 'hidden') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faq_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) DEFAULT NULL,
    email VARCHAR(200) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    message TEXT NOT NULL,
    page_url VARCHAR(255) DEFAULT NULL,
    status VARCHAR(30) DEFAULT 'new',
    reply TEXT DEFAULT NULL,
    reply_by VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faq_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE about (
    id INT NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    services_heading VARCHAR(255) DEFAULT NULL,
    partners_heading VARCHAR(255) DEFAULT NULL,
    modpacks_heading VARCHAR(255) DEFAULT NULL,
    gallery_heading VARCHAR(255) DEFAULT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    uptime VARCHAR(50) DEFAULT NULL,
    support VARCHAR(255) DEFAULT NULL,
    performance VARCHAR(255) DEFAULT NULL,
    years_active VARCHAR(50) DEFAULT NULL,
    founded_year INT DEFAULT NULL,
    partners TEXT DEFAULT NULL,
    modpacks TEXT DEFAULT NULL,
    gallery TEXT DEFAULT NULL,
    services TEXT DEFAULT NULL,
    sections TEXT DEFAULT NULL,
    cta_heading VARCHAR(255) DEFAULT NULL,
    cta_text TEXT DEFAULT NULL,
    cta_button_text VARCHAR(100) DEFAULT NULL,
    cta_button_url VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    background VARCHAR(255) DEFAULT NULL,
    intro_gif VARCHAR(255) DEFAULT NULL,
    intro_duration DECIMAL(6,2) DEFAULT 5.52,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    key_name VARCHAR(100) PRIMARY KEY,
    value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    status ENUM('active', 'hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NULL,
    category_id INT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    thumbnail VARCHAR(255),
    content LONGTEXT NOT NULL,
    meta_keywords VARCHAR(255),
    meta_description TEXT,
    status ENUM('published', 'draft') DEFAULT 'published',
    views_count INT DEFAULT 0,
    publish_at DATETIME NULL,
    is_breaking TINYINT(1) DEFAULT 0,
    breaking_until DATETIME NULL,
    seo_score INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    link_url VARCHAR(255),
    position ENUM('sticky-sidebar', 'banner-top', 'banner-bottom') DEFAULT 'sticky-sidebar',
    status ENUM('active', 'inactive') DEFAULT 'active',
    start_at DATETIME NULL,
    end_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NULL,
    news_id INT NULL,
    rating INT NULL,
    comment TEXT NOT NULL,
    likes_count INT DEFAULT 0,
    status ENUM('pending', 'approved', 'hidden') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    review_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    UNIQUE KEY uq_review_likes_user_review (user_id, review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    ip_address VARCHAR(50),
    source VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    news_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    UNIQUE KEY uq_news_likes_user_news (user_id, news_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    admin_id INT NOT NULL,
    media_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (admin_id, media_id),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contacts (
    user_id INT NOT NULL,
    contact_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, contact_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo users.
-- Local demo admin: username admin, password ChangeMe123!
-- Local demo member: username demo_user, password DemoUser123!
-- Local demo suspended user: username suspended_user, password BanUser123!
INSERT INTO users (id, username, password, email, full_name, avatar, status, credit, reset_token, role, created_at) VALUES
(1, 'admin', '$2a$10$VAgendhLUImcVPmyVCEdvOh7purKZgGSC1phY80djZYhPoJIQwuEi', 'admin@securehost-arena.local', 'Demo Administrator', NULL, 'active', 1000, NULL, 'admin', '2026-05-01 09:00:00'),
(2, 'demo_user', '$2a$10$FzCzQEFC6c35QeSkXecGQu2jiv/aoN3aMqAxrf.JA.RcgMhGErNTS', 'demo.user@securehost-arena.local', 'Demo Customer', NULL, 'active', 300, NULL, 'member', '2026-05-01 09:15:00'),
(3, 'suspended_user', '$2a$10$HTWpQlRxqwVfJ7CtsSCMKugb9gADFkdSvYxZc7mnBrE.nMZRXJo82', 'suspended.user@securehost-arena.local', 'Suspended Demo User', NULL, 'banned', 0, NULL, 'member', '2026-05-01 09:30:00'),
(4, 'guest_contact', '$2b$10$3VoMsFPBiuOD.BosjS9fheeQSb7qPOhERWTd5ciYJsjjY22w7LT3.', 'guest@securehost-arena.local', 'Guest Contact', NULL, 'active', 0, NULL, 'member', '2026-05-01 09:45:00');

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Game Server Hosting', 'game-server-hosting', 'Managed server plans for multiplayer game communities.'),
(2, 'Web Hosting', 'web-hosting', 'Hosting plans for websites and web applications.'),
(3, 'Shared Hosting', 'shared-hosting', 'Entry-level shared hosting plans for small projects.');

INSERT INTO products (id, category_id, name, slug, description, price, ram_mb, cpu_cores, disk_gb, image_url, status, created_at) VALUES
(1, 1, 'Arena Starter 4GB', 'arena-starter-4gb', 'Entry game server plan for small teams and lightweight modpacks.', 500000.00, 4096, 4, 50, NULL, 'active', '2026-05-01 10:00:00'),
(2, 1, 'Arena Pro 8GB', 'arena-pro-8gb', 'Balanced game server plan for larger groups and heavier plugins.', 1000000.00, 8192, 8, 100, NULL, 'active', '2026-05-01 10:05:00'),
(3, 1, 'Arena Elite 16GB', 'arena-elite-16gb', 'High-performance game server plan for demanding communities.', 2000000.00, 16384, 16, 200, NULL, 'active', '2026-05-01 10:10:00'),
(4, 2, 'Web Basic', 'web-basic', 'Small web hosting plan for portfolio sites and demos.', 250000.00, 2048, 2, 20, NULL, 'active', '2026-05-01 10:15:00'),
(5, 2, 'Web Plus', 'web-plus', 'Expanded web hosting plan for active PHP/MySQL applications.', 750000.00, 4096, 4, 50, NULL, 'active', '2026-05-01 10:20:00'),
(6, 1, 'Minecraft Trial 1GB', 'minecraft-trial-1gb', 'Free trial plan for testing Minecraft server deployment.', 0.00, 1024, 1, 10, NULL, 'active', '2026-05-01 10:25:00');

INSERT INTO user_services (id, user_id, product_id, ip_address, port, status, current_ram_mb, expires_at) VALUES
(1, 2, 2, '192.0.2.21', 25565, 'active', 8192, '2026-06-15 12:00:00'),
(2, 2, 4, '192.0.2.32', 8080, 'active', 2048, '2026-06-01 12:00:00');

INSERT INTO user_service_options (user_service_id, option_key, option_value) VALUES
(1, 'engine', 'PaperMC'),
(1, 'version', '1.20.4'),
(2, 'runtime', 'PHP 8.2');

INSERT INTO carts (id, user_id, session_id, created_at) VALUES
(1, 2, NULL, '2026-05-18 10:00:00');

INSERT INTO cart_items (cart_id, product_id, quantity, duration_months) VALUES
(1, 3, 1, 1);

INSERT INTO orders (id, user_id, status, phone, address, total_amount, created_at) VALUES
(1, 2, 'completed', NULL, NULL, 500000.00, '2026-01-05 09:00:00'),
(2, 2, 'completed', NULL, NULL, 1000000.00, '2026-02-10 09:00:00'),
(3, 2, 'completed', NULL, NULL, 2000000.00, '2026-03-15 09:00:00'),
(4, 2, 'completed', NULL, NULL, 750000.00, '2026-04-20 09:00:00'),
(5, 2, 'pending', NULL, NULL, 2000000.00, '2026-05-18 10:30:00');

INSERT INTO order_items (order_id, product_id, price, quantity, duration_months) VALUES
(1, 1, 500000.00, 1, 1),
(2, 2, 1000000.00, 1, 1),
(3, 3, 2000000.00, 1, 1),
(4, 5, 750000.00, 1, 1),
(5, 3, 2000000.00, 1, 1);

INSERT INTO pages (id, admin_id, title, slug, content, status) VALUES
(1, 1, 'Security Policy', 'security-policy', '<p>SecureHost Arena uses demo data only. Production deployments require additional hardening.</p>', 'published');

INSERT INTO faqs (id, question, answer, category, status) VALUES
(1, 'How fast is server provisioning?', 'This demo simulates provisioning after an order is completed.', 'Provisioning', 'active'),
(2, 'Are uploaded files validated?', 'Raster uploads are checked for size, MIME type, and image structure. SVG branding uploads are sanitized.', 'Security', 'active'),
(3, 'Can admins triage support tickets?', 'Yes. Admins can review ticket status, priority metadata, and replies from the dashboard.', 'Support', 'active');

INSERT INTO faq_categories (id, title, slug, image) VALUES
(1, 'Provisioning', 'provisioning', NULL),
(2, 'Security', 'security', NULL),
(3, 'Support', 'support', NULL);

INSERT INTO about (
    id, title, subtitle, services_heading, partners_heading, modpacks_heading, gallery_heading,
    content, admin_id, uptime, support, performance, years_active, founded_year,
    partners, modpacks, sections, cta_heading, cta_text, cta_button_text, cta_button_url
) VALUES (
    1,
    'SecureHost Arena',
    'A secure PHP MVC platform for game server hosting operations.',
    'Services',
    'Infrastructure Partners',
    'Supported Game Platforms',
    'Platform Gallery',
    '<p>SecureHost Arena is a portfolio-grade hosting platform focused on secure web application design, admin operations, and support workflows.</p>',
    1,
    '99.9%',
    '24/7 demo support',
    'NVMe-ready architecture',
    'Demo',
    2026,
    '[{"name":"Cloudflare","url":"https://www.cloudflare.com","logo":""},{"name":"Cisco","url":"https://www.cisco.com","logo":""}]',
    '[{"name":"Minecraft","url":"#","logo":""},{"name":"Terraria","url":"#","logo":""},{"name":"Valheim","url":"#","logo":""}]',
    '[{"title":"Security-aware hosting workflows","content":"<p>The platform demonstrates authentication, role checks, CSRF protection, secure uploads, support tickets, and admin visibility.</p>"},{"title":"Operational dashboard","content":"<p>Admins can monitor users, tickets, orders, revenue, and provisioned services from one control surface.</p>"}]',
    'Ready to explore the demo?',
    'Create a local account, review hosting plans, and inspect the admin workflows.',
    'View Plans',
    '/products'
);

INSERT INTO news_categories (id, name, slug, description, status) VALUES
(1, 'Game Server Security', 'game-server-security', 'Security topics for game server hosting.', 'active'),
(2, 'Platform Updates', 'platform-updates', 'Product and operational updates.', 'active'),
(3, 'Guides', 'guides', 'Technical guides for customers and admins.', 'active');

INSERT INTO news (
    id, author_id, category_id, title, slug, thumbnail, content,
    meta_keywords, meta_description, status, views_count, is_breaking, seo_score, likes_count, created_at
) VALUES
(1, 1, 2, 'Introducing SecureHost Arena', 'introducing-securehost-arena', NULL, '<p>SecureHost Arena demonstrates a secure PHP MVC workflow for hosting plans, tickets, orders, and admin operations.</p>', 'php mvc, hosting, security', 'Introduction to SecureHost Arena.', 'published', 125, 1, 85, 4, '2026-05-01 11:00:00'),
(2, 1, 1, 'How Support Tickets Help Blue-Team Workflows', 'support-tickets-blue-team-workflows', NULL, '<p>Support tickets can become structured signals for triage, user support, and incident follow-up.</p>', 'blue team, support tickets, triage', 'Support ticket triage in SecureHost Arena.', 'published', 88, 0, 80, 3, '2026-05-02 11:00:00'),
(3, 1, 3, 'Secure Upload Handling in PHP', 'secure-upload-handling-php', NULL, '<p>Upload handling should validate file size, MIME type, image structure, and dangerous SVG content.</p>', 'php, secure upload, appsec', 'Secure upload handling concepts for PHP applications.', 'published', 96, 0, 82, 5, '2026-05-03 11:00:00');

INSERT INTO ads (id, title, image_url, link_url, position, status) VALUES
(1, 'Demo Game Server Discount', '/public/uploads/ads/demo-game-server.png', '/products', 'sticky-sidebar', 'active'),
(2, 'Secure Hosting Starter Pack', '/public/uploads/ads/demo-secure-hosting.png', '/products', 'banner-top', 'inactive');

INSERT INTO contacts (user_id, contact_id, name, email, subject, message, status, created_at) VALUES
(2, 1, 'Demo Customer', 'demo.user@securehost-arena.local', 'Provisioning question', 'Can I see when my demo server becomes active?', 'unread', '2026-05-18 12:00:00'),
(2, 2, 'Demo Customer', 'demo.user@securehost-arena.local', 'Upload security question', 'What validation is applied to profile avatar uploads?', 'read', '2026-05-18 12:30:00'),
(4, 1, 'Guest Visitor', 'guest@securehost-arena.local', 'Pre-sales question', 'Which plan is best for a small Minecraft community?', 'replied', '2026-05-18 13:00:00');

INSERT INTO reviews (id, user_id, product_id, news_id, rating, comment, likes_count, status, created_at) VALUES
(1, 2, 2, NULL, 5, 'The demo provisioning workflow is clear and easy to follow.', 2, 'approved', '2026-05-18 14:00:00'),
(2, 2, NULL, 3, NULL, 'The upload security article is useful for understanding file validation.', 1, 'approved', '2026-05-18 14:15:00'),
(3, 2, 1, NULL, 4, 'Good entry-level plan for a small test server.', 0, 'pending', '2026-05-18 14:30:00');

INSERT INTO review_likes (user_id, review_id) VALUES
(1, 1),
(1, 2);

INSERT INTO news_views (news_id, ip_address, source, created_at) VALUES
(1, '198.51.100.10', 'demo', '2026-05-18 15:00:00'),
(2, '198.51.100.11', 'demo', '2026-05-18 15:05:00'),
(3, '198.51.100.12', 'demo', '2026-05-18 15:10:00');

INSERT INTO news_likes (user_id, news_id) VALUES
(2, 1),
(2, 3);

INSERT INTO admin_notifications (id, type, source_key, title, message, url, payload, created_at) VALUES
(1, 'ticket', 'demo_ticket:2:1', 'New support ticket', 'Demo Customer - Provisioning question', '/admincontacts?user_id=2&contact_id=1', '{"user_id":2,"contact_id":1}', '2026-05-18 12:00:00'),
(2, 'ticket', 'demo_ticket:4:1', 'Guest support ticket', 'Guest Visitor - Pre-sales question', '/admincontacts?user_id=4&contact_id=1', '{"user_id":4,"contact_id":1}', '2026-05-18 13:00:00'),
(3, 'revenue', 'demo_order_completed:4', 'Order #4 completed', 'Demo Customer completed a 750000 VND order.', '/admin', '{"order_id":4,"user_id":2,"total_amount":750000}', '2026-04-20 09:00:00');

INSERT INTO settings (key_name, value) VALUES
('site_logo_text', 'SecureHost Arena'),
('site_logo_image', 'brand_fc60e5c3900c2e02258c13a9c8b3529c.png'),
('site_hotline', '+84 000 000 000'),
('site_contact_email', 'contact@securehost-arena.local'),
('site_address', 'Demo Lab, Ho Chi Minh City, Vietnam'),
('site_about_snippet', 'Security-focused game server hosting platform built with custom PHP MVC.'),
('site_map_embed_url', 'https://www.google.com/maps?q=Ho+Chi+Minh+City+Vietnam&output=embed'),
('home_hero_title_gradient', 'Secure Server Hosting'),
('home_hero_title_plain', 'for Game Communities'),
('home_hero_subtitle', 'Launch and manage demo game hosting plans with secure account flows, support tickets, and admin visibility.'),
('home_hero_bg_image', 'hero_bg_03e930272677695acb05c9dad6beba04.gif'),
('home_card_tech_title', 'Security-aware hosting operations'),
('home_review_key', 'product:1'),
('home_product_ids', '1,2,3,5'),
('home_about_kicker', 'Platform strengths'),
('home_about_heading', 'Why SecureHost Arena?'),
('home_about_lead', 'A portfolio project focused on secure PHP MVC development, operational dashboards, and blue-team friendly workflows.'),
('home_about_feat1_title', 'Secure by design'),
('home_about_feat1_text', 'CSRF tokens, role checks, password hashing, prepared statements, and safer upload handling are built into core flows.'),
('home_about_feat2_title', 'Operational visibility'),
('home_about_feat2_text', 'Admin dashboards surface users, tickets, orders, revenue, services, and notifications for fast triage.'),
('home_about_feat3_title', 'Realistic hosting model'),
('home_about_feat3_text', 'Products, carts, orders, service provisioning, reviews, and support tickets model a practical hosting business.'),
('profile_page_title', 'Member Profile'),
('profile_page_intro', 'Manage account information, password security, and avatar uploads.'),
('profile_section_avatar_title', 'Avatar'),
('profile_avatar_upload_label', 'Upload image'),
('profile_avatar_hint', 'JPG, PNG, GIF, or WEBP. Max 2MB.'),
('profile_section_personal_title', 'Personal information'),
('profile_section_password_title', 'Password security'),
('profile_label_display_name', 'Display name'),
('profile_label_email', 'Email address'),
('profile_label_current_password', 'Current password'),
('profile_label_new_password', 'New password'),
('profile_label_confirm_password', 'Confirm password'),
('profile_btn_save', 'Save changes'),
('profile_btn_update_password', 'Update password'),
('contact_gate_headline', 'Support'),
('contact_gate_headline_accent', 'Center'),
('contact_gate_subtitle', 'Open a structured support ticket for hosting, billing, or technical issues.'),
('contact_node_card_title', 'Support Node DEMO-01'),
('contact_node_region', 'Ho Chi Minh City'),
('contact_node_online_label', 'Online'),
('contact_node_latency_label', 'Latency'),
('contact_gate_cta_body', 'Create a ticket with issue type, order context, and technical details for admin triage.'),
('contact_gate_cta_button', 'Create Ticket'),
('contact_discord_typed_block', '> relay / support-center CONNECTED\n> channel latency 42ms\n> secure ticket queue ready _'),
('contact_discord_invite_url', ''),
('contact_page_title', 'Contact Support'),
('contact_page_intro', 'Send a support ticket. The demo admin queue will show the request for triage.'),
('contact_sidebar_title', 'Support details'),
('contact_main_term_title', 'Support Terminal'),
('contact_main_name_label', 'Name'),
('contact_main_email_label', 'Email'),
('contact_main_issue_label', 'Issue type'),
('contact_main_issue_hint', 'Choose the closest category for faster triage.'),
('contact_main_msg_label', 'Message'),
('contact_main_msg_placeholder', '> Describe the issue, reproduction steps, and order ID if available.'),
('contact_main_btn_send', 'Send Ticket'),
('contact_main_btn_reset', 'Reset'),
('contact_main_cat_heading', 'Choose ticket category'),
('contact_main_back', 'Back'),
('contact_main_status_title', 'Support status'),
('contact_main_status_online', 'Support online'),
('contact_main_topo_title', 'Support topology'),
('contact_main_stat_lbl_1', 'Avg response'),
('contact_main_stat_val_1', '~3m'),
('contact_main_stat_lbl_2', 'Active engineers'),
('contact_main_stat_lbl_3', 'Healthy nodes'),
('contact_cat_desc_purchase_issue', 'Attach context about a pending order.'),
('contact_cat_desc_forgot_password', 'Recover access to an account.'),
('contact_cat_desc_bugs_technical', 'Report technical bugs or server issues.'),
('contact_cat_desc_banned', 'Request review of a suspended account.'),
('contact_cat_desc_billing_payment', 'Ask about invoices, payments, or refunds.'),
('contact_cat_desc_others', 'Use this for anything else.'),
('contact_form_purchase_order_lbl', 'Pending order'),
('contact_form_purchase_guest', 'Log in to attach a pending order.'),
('contact_form_purchase_empty', 'No pending orders found.'),
('contact_form_purchase_opt', '-- Choose order --'),
('contact_form_forgot_pw_lbl', 'Previous password hint'),
('contact_form_forgot_pw_ph', 'Stored as a hash for demo triage.'),
('contact_form_banned_user_lbl', 'Username'),
('contact_form_banned_user_ph', 'Account username needing review');
