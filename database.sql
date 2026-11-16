-- ShopZone Database Schema
-- Import this file via phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS shopzone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopzone;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    otp_code VARCHAR(10) DEFAULT NULL,
    otp_expires_at DATETIME DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) DEFAULT 'tag',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    description TEXT,
    short_description VARCHAR(500),
    image VARCHAR(255) DEFAULT NULL,
    gallery TEXT DEFAULT NULL,
    category_id INT,
    stock INT DEFAULT 0,
    promo_code VARCHAR(50) DEFAULT NULL,
    discount_percent INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    phone VARCHAR(20),
    promo_code VARCHAR(50) DEFAULT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, product_id)
);

-- Promo Codes Table
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percent INT NOT NULL,
    min_order DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed: Default Admin
INSERT INTO users (name, email, password, role, is_verified) VALUES
('Admin', 'admin@shopzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- Default admin password: password

-- Seed: Categories
INSERT INTO categories (name, slug, icon) VALUES
('Electronics', 'electronics', 'cpu'),
('Clothing', 'clothing', 'shirt'),
('Books', 'books', 'book-open'),
('Home & Garden', 'home-garden', 'home'),
('Sports', 'sports', 'activity'),
('Beauty', 'beauty', 'star');

-- Seed: Sample Products
INSERT INTO products (name, slug, price, sale_price, description, short_description, category_id, stock, featured) VALUES
('Wireless Noise-Cancelling Headphones', 'wireless-headphones', 129.99, 99.99, 'Premium wireless headphones with active noise cancellation, 30-hour battery life, and foldable design. Perfect for travel and work.', 'Premium ANC headphones with 30hr battery.', 1, 50, 1),
('Mechanical Gaming Keyboard', 'gaming-keyboard', 89.99, NULL, 'RGB backlit mechanical keyboard with tactile switches, anti-ghosting, and durable aluminum frame for the ultimate gaming experience.', 'RGB mechanical keyboard for gamers.', 1, 30, 1),
('Classic Denim Jacket', 'denim-jacket', 59.99, 45.00, 'Timeless denim jacket with a modern slim fit. Features two chest pockets and adjustable cuffs. Available in multiple sizes.', 'Slim-fit denim jacket, modern style.', 2, 100, 0),
('JavaScript: The Good Parts', 'js-good-parts', 24.99, NULL, 'A must-read for JavaScript developers. Covers the best features of JavaScript and helps you avoid the bad ones.', 'Essential JS book for every developer.', 3, 200, 0),
('Smart Fitness Tracker', 'fitness-tracker', 49.99, 39.99, 'Track your steps, heart rate, sleep, and workouts. Water-resistant with a 7-day battery life and smartphone notifications.', 'Smart tracker: steps, heart rate, sleep.', 5, 75, 1),
('Ceramic Coffee Mug Set', 'coffee-mug-set', 34.99, NULL, 'Set of 4 handcrafted ceramic mugs. Microwave and dishwasher safe. Elegant matte finish in modern earth tones.', 'Set of 4 handcrafted ceramic mugs.', 4, 60, 0);

-- Seed: Promo Codes
INSERT INTO promo_codes (code, discount_percent, min_order, max_uses) VALUES
('WELCOME10', 10, 20.00, 100),
('SAVE20', 20, 50.00, 50),
('MEGA30', 30, 100.00, 20);
