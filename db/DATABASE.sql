DROP DATABASE MOVIES;
CREATE DATABASE MOVIES;
USE MOVIES;
CREATE TABLE user(
    id INT AUTO_INCREMENT PRIMARY KEY,
    usr_mail VARCHAR(255) UNIQUE,
    passcode VARCHAR(100),
    refresh_token VARCHAR(255)
);
CREATE TABLE watchlists(
    wid INT AUTO_INCREMENT PRIMARY KEY,
    id INT,
    original_language VARCHAR(12) NOT NULL,
    original_title VARCHAR(255) NOT NULL,
    overview TEXT,
    popularity FLOAT,
    poster_path VARCHAR(255),
    release_date VARCHAR(11),
    title VARCHAR(255),
    video VARCHAR(11),
    vote_average FLOAT,
    vote_count INT,
    usr_mail VARCHAR(255) NOT NULL,
    date_added DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (usr_mail) REFERENCES user (usr_mail)
);
