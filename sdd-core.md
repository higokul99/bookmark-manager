# System Design Document: Core Backend & Architecture

## 1. Database Schema (`schema.sql`)

```sql
CREATE DATABASE IF NOT EXISTS bookmark_manager;
USE bookmark_manager;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    pin_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    category_id INT NULL DEFAULT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

3. Core Processing Workflows
4-PIN Security Architecture
Regular user authorization utilizes actions/auth_verify.php. Users authenticate using only a 4-digit PIN (no username required).

A session tracker $_SESSION['login_attempts'] must increment on failure.

If failure count hits 3, save a timestamp $_SESSION['lockout_time']. Block verification routines until current_time minus lockout_time exceeds 300 seconds.

Sorting Priority Logic
When rendering interface components, execute strict MySQL ordering logic:

SELECT * FROM categories 
WHERE user_id = :user_id AND category_id IS NULL 
ORDER BY is_pinned DESC, name ASC;

SELECT * FROM categories 
WHERE user_id = :user_id AND category_id = :category_id 
ORDER BY name ASC;

SELECT * FROM bookmarks 
WHERE user_id = :user_id AND category_id = :category_id 
ORDER BY id DESC;