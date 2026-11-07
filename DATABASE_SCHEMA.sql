-- ========================================
-- CONCERT SYSTEM DATABASE
-- Complete SQL Schema with Relationships
-- ========================================

-- 1. USERS Table (User Accounts & Permissions)
-- ========================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin),
    INDEX idx_role (role)
);

-- 2. CONCERTS Table (Concert Information)
-- ========================================
CREATE TABLE concerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    venue VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    description LONGTEXT NOT NULL,
    ticket_price DECIMAL(10, 2) NOT NULL,
    total_ticket INT NOT NULL,
    image VARCHAR(255) NULL,
    seating INT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_artist (artist),
    INDEX idx_venue (venue),
    INDEX idx_date (date),
    INDEX idx_created_by (created_by)
);

-- 3. ORDERS Table (Concert Bookings)
-- ========================================
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    concert_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    payment_method VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (concert_id) REFERENCES concerts(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_concert_id (concert_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- 4. REVIEWS Table (Concert Reviews & Ratings)
-- ========================================
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    concert_id BIGINT UNSIGNED NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    comment LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (concert_id) REFERENCES concerts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, concert_id),
    INDEX idx_user_id (user_id),
    INDEX idx_concert_id (concert_id),
    INDEX idx_rating (rating)
);

-- 5. CONCERT_USER Table (Many-to-Many: Favorites)
-- ========================================
CREATE TABLE concert_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    concert_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (concert_id) REFERENCES concerts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, concert_id),
    INDEX idx_user_id (user_id),
    INDEX idx_concert_id (concert_id)
);

-- 6. PAYMENT_CARDS Table (User Payment Methods)
-- ========================================
CREATE TABLE payment_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    card_type VARCHAR(50) NOT NULL, -- 'visa' or 'master'
    card_number LONGTEXT NOT NULL,
    expiry_date VARCHAR(5) NOT NULL, -- MM/YY
    cvv LONGTEXT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    country VARCHAR(100) NOT NULL,
    address LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_card_type (card_type)
);

-- 7. PASSWORD_RESETS Table (Password Reset Tokens)
-- ========================================
CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    
    PRIMARY KEY (email),
    INDEX idx_token (token)
);

-- 8. PERSONAL_ACCESS_TOKENS Table (Sanctum API Tokens)
-- ========================================
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tokenable_type_id (tokenable_type, tokenable_id),
    INDEX idx_token (token)
);

-- ========================================
-- RELATIONSHIPS SUMMARY
-- ========================================
-- USERS → CONCERTS (1:N)  created_by = users.id
-- USERS → ORDERS (1:N)    user_id = users.id
-- USERS → REVIEWS (1:N)   user_id = users.id
-- USERS → CONCERT_USER (1:N) user_id = users.id (Many-to-Many via)
-- USERS → PAYMENT_CARDS (1:N) user_id = users.id
-- USERS → PERSONAL_ACCESS_TOKENS (1:N) tokenable_id = users.id
-- CONCERTS → ORDERS (1:N) concert_id = concerts.id
-- CONCERTS → REVIEWS (1:N) concert_id = concerts.id
-- CONCERTS → CONCERT_USER (1:N) concert_id = concerts.id (Many-to-Many via)
-- ========================================
