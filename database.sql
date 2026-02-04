-- Luxury Watch Shop schema
CREATE DATABASE IF NOT EXISTS watch_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE watch_shop;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS trade_requests;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    brand VARCHAR(80) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'processing',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(40),
    location VARCHAR(100),
    preferred_datetime DATETIME,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE trade_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(120) NOT NULL,
    watch_condition VARCHAR(80) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sample users (password = "password")
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Avery Cole', 'admin@example.com', '$2y$10$QxLhZZPAbYvZly.g0XnrLervcZ04tjheQ52so9mJpgcnjmPJo/CRO', 'admin'),
('Evelyn Hart', 'customer@example.com', '$2y$10$N.wxoxz5ChCjw4WRV.nop.XjC2U99/ofyMtHZDIgsQehatoElpsKu', 'customer'),
('James Miller', 'james@example.com', '$2y$10$N.wxoxz5ChCjw4WRV.nop.XjC2U99/ofyMtHZDIgsQehatoElpsKu', 'customer'),
('Sofia Lee', 'sofia@example.com', '$2y$10$N.wxoxz5ChCjw4WRV.nop.XjC2U99/ofyMtHZDIgsQehatoElpsKu', 'customer'),
('Daniel Rhodes', 'daniel@example.com', '$2y$10$N.wxoxz5ChCjw4WRV.nop.XjC2U99/ofyMtHZDIgsQehatoElpsKu', 'customer'),
('Priya Rao', 'priya@example.com', '$2y$10$N.wxoxz5ChCjw4WRV.nop.XjC2U99/ofyMtHZDIgsQehatoElpsKu', 'customer');

-- Sample products
INSERT INTO products (name, brand, price, description, image_url, stock, featured) VALUES
('Royal Oak 15500ST', 'Audemars Piguet', 42500.00, 'Blue Grande Tapisserie dial with stainless steel bracelet.', 'assets/Image/watch1.jpg', 5, 1),
('Submariner Date 126610LN', 'Rolex', 14500.00, 'Ceramic bezel, 41mm steel case, calibre 3235 movement.', 'assets/Image/DeepBlue.jpg', 12, 1),
('Speedmaster Moonwatch', 'Omega', 6700.00, 'Professional chronograph inspired by lunar missions.', 'assets/Image/Speedmaster-Professional-Moonwatch-Watch-REF-310.30.42.50.01.001-1024x683.jpg', 9, 1),
('Santos de Cartier', 'Cartier', 8200.00, 'Iconic square case with interchangeable bracelet.', 'assets/Image/The-Best-One-Watch-Collection.jpg', 7, 0),
('Big Bang Unico', 'Hublot', 18900.00, 'Skeleton dial, HUB1280 automatic flyback chronograph.', 'assets/Image/shutterstock_2193519341-1.webp', 4, 0),
('Navitimer B01', 'Breitling', 9200.00, 'In-house chronograph calibre B01 with slide rule bezel.', 'assets/Image/Tissot_PRX_T1374101109100_rannekello_1080x.webp', 6, 0),
('Overseas Dual Time', 'Vacheron Constantin', 27900.00, 'Dual time complication with quick-change straps.', 'assets/Image/Women-s-Tissot-1853-PRX-Elegant-Swiss-35mm-Blue-Dial-Watch-T1372101104100_730f3831-72c1-4b69-9d30-d3097fd76970.0991b0f1671c24c593b16e20c19e99cc.avif', 3, 1),
('Luminor Marina', 'Panerai', 7300.00, '44mm brushed steel case, sandwich dial, P.9010 calibre.', 'assets/Image/photo-1584378687113-8739c327634c.jpeg', 10, 0);

-- Sample orders
INSERT INTO orders (user_id, product_id, total, status, shipping_address, created_at) VALUES
(2, 3, 22200.00, 'processing', '123 Market St\nSan Francisco, CA', '2024-01-12 09:15:00'),
(2, 2, 14500.00, 'paid', '123 Market St\nSan Francisco, CA', '2024-01-15 13:20:00');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 3, 1, 6700.00),
(1, 4, 1, 8200.00),
(1, 8, 1, 7300.00),
(2, 2, 1, 14500.00);

-- Sample appointment and trade requests
INSERT INTO appointments (user_id, name, email, phone, location, preferred_datetime, message) VALUES
(3, 'James Miller', 'james@example.com', '+1 555 0192', 'NYC Flagship', '2024-01-10 14:30:00', 'Interested in viewing the Royal Oak and Overseas.'),
(4, 'Sofia Lee', 'sofia@example.com', '+1 555 1444', 'LA Lounge', '2024-01-11 11:00:00', 'Looking for a Submariner in stock.');

INSERT INTO trade_requests (user_id, name, email, brand, model, watch_condition, message) VALUES
(5, 'Daniel Rhodes', 'daniel@example.com', 'Rolex', 'Explorer II', 'Excellent', 'Considering a trade toward a GMT-Master II'),
(6, 'Priya Rao', 'priya@example.com', 'Omega', 'Seamaster 300', 'Good', 'Interested in consigning within 30 days.');
