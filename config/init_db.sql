CREATE DATABASE IF NOT EXISTS QuizSP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QuizSP;

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    login VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'USER', 'MODERATEUR', 'JSP1', 'JSP2', 'JSP3', 'JSP4') NOT NULL DEFAULT 'USER',
    actif BOOLEAN NOT NULL DEFAULT 1,
    date_derniere_connexion DATETIME DEFAULT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quizz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE question (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quizz_id INT NOT NULL,
    texte_question TEXT NOT NULL,
    type_question ENUM('texte', 'choix_unique', 'choix_multiple') NOT NULL DEFAULT 'texte',
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE
);

CREATE TABLE reponse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    texte_reponse TEXT NOT NULL,
    est_correcte BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES question(id) ON DELETE CASCADE
);

CREATE TABLE resultat_quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quizz_id INT NOT NULL,
    score INT NOT NULL,
    date_passage DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (quizz_id) REFERENCES quizz(id) ON DELETE CASCADE
);