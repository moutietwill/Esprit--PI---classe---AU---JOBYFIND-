CREATE DATABASE IF NOT EXISTS jobyfind;
USE jobyfind;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Entrepreneur', 'Mentor', 'Entreprise', 'Admin') DEFAULT 'Entrepreneur',
    phone VARCHAR(20),
    city VARCHAR(100),
    bio TEXT,
    linkedin_url VARCHAR(255),
    date_of_birth DATE,
    status ENUM('Actif', 'En attente', 'Suspendu') DEFAULT 'En attente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default Admin user so you can test the BackOffice right away
-- The hashed password is 'admin123' (we'll use PHP's password_hash 'admin123' in code, here assuming you will use it)
-- To be safe, we insert without password first, but it's NOT NULL. Let's provide a PHP generated hash for 'admin123': $2y$10$w6O...
INSERT INTO users (first_name, last_name, email, password, role, status) 
VALUES ('Super', 'Admin', 'admin@jobyfind.tn', '$2y$10$r9G.3M3tUu9E/4H1hN6K2egE0zJ0Q5B9A4qG5qZ01bK2rG6V1m1Q.', 'Admin', 'Actif')
ON DUPLICATE KEY UPDATE id=id;
