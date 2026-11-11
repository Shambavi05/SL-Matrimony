CREATE DATABASE IF NOT EXISTS srilanka_matrimonial_profiles
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE srilanka_matrimonial_profiles;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(150) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    marital_status VARCHAR(30) NOT NULL,
    dob DATE NOT NULL,
    birth_time TIME NOT NULL,
    height VARCHAR(20) NOT NULL,
    religion VARCHAR(80) NOT NULL,
    mother_tongue VARCHAR(80) NOT NULL,
    hobbies TEXT NOT NULL,
    food_preferences VARCHAR(80) NOT NULL,
    zodiac_sign VARCHAR(100) NOT NULL,
    nakshathira VARCHAR(100) NOT NULL,
    current_city VARCHAR(120) NOT NULL,
    residency_state VARCHAR(120) NOT NULL,
    citizenship VARCHAR(100) NOT NULL,
    residency_status VARCHAR(100) NOT NULL,
    higher_education VARCHAR(180) NOT NULL,
    employment_status VARCHAR(150) NOT NULL,
    short_description TEXT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_customer_email (email),
    KEY gender_created_at (gender, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id INT UNSIGNED NOT NULL,
    recipient_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_interest_pair (sender_id, recipient_id),
    KEY recipient_created_at (recipient_id, created_at),
    CONSTRAINT interests_sender_fk FOREIGN KEY (sender_id) REFERENCES customers (id) ON DELETE CASCADE,
    CONSTRAINT interests_recipient_fk FOREIGN KEY (recipient_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id INT UNSIGNED NOT NULL,
    package_name VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_created_at (customer_id, created_at),
    CONSTRAINT subscriptions_customer_fk FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submissions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY submission_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS porutham_results (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    boy_nakshathira VARCHAR(100) NOT NULL,
    girl_nakshathira VARCHAR(100) NOT NULL,
    porutham_json LONGTEXT NOT NULL,
    matched_count INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
