-- Create keepsake database and foundational tables
CREATE DATABASE IF NOT EXISTS keepsake CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE keepsake;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table[cite: 3]
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Seed standard categories[cite: 3]
INSERT INTO categories (name, slug) VALUES 
('Music & Playlists', 'music-playlists'),
('Favorite Places', 'favorite-places'),
('Movies & Series', 'movies-series'),
('Food & Cafés', 'food-cafes'),
('Books & Learning', 'books-learning');

-- Items table (universal record system)[cite: 3]
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    external_url VARCHAR(508),
    image_url VARCHAR(508),
    is_favorite TINYINT(1) DEFAULT 0,
    visibility ENUM('private', 'public') DEFAULT 'private',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;