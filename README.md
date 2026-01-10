# RIBA Survey System (Plain PHP 8.1)

Minimal, framework-free PHP 8.1 survey system scaffold using:
- PDO prepared statements
- Session-based auth
- Basic role checks (`admin`)
- Simple router with GET/POST routes

## Requirements
- PHP 8.1+
- MySQL/MariaDB

## Setup
1. Point your web server document root to `public/`.
2. Create `.env` from the example:
   ```bash
   cp .env.example .env
   ```
3. Create the database and tables (sample SQL below).
4. Insert at least one user and login at `/login`.

## Routes
- `GET /login` show login form
- `POST /login` authenticate
- `POST /logout` logout
- `GET /dashboard` dashboard (auth)
- `GET /surveys` list surveys (auth)
- `GET /surveys/create` create form (admin)
- `POST /surveys/create` create survey (admin)
- `GET /surveys/{id}/answer` answer survey (auth)
- `POST /surveys/{id}/answer` submit answers (auth)

## Sample SQL (MySQL/MariaDB)

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE surveys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_surveys_user FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE survey_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  survey_id INT NOT NULL,
  question_text TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_questions_survey FOREIGN KEY (survey_id) REFERENCES surveys(id)
);

CREATE TABLE survey_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  survey_id INT NOT NULL,
  question_id INT NOT NULL,
  user_id INT NOT NULL,
  answer_text TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_response (survey_id, question_id, user_id),
  CONSTRAINT fk_resp_survey FOREIGN KEY (survey_id) REFERENCES surveys(id),
  CONSTRAINT fk_resp_question FOREIGN KEY (question_id) REFERENCES survey_questions(id),
  CONSTRAINT fk_resp_user FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create an initial admin user (replace password)
INSERT INTO users (name, email, role, password_hash)
VALUES (
  'Admin',
  'admin@example.com',
  'admin',
  -- Generate with PHP: password_hash('secret', PASSWORD_DEFAULT)
  '$2y$10$replace_with_real_hash'
);
```

## Notes
- This is an initial scaffold; validation, security hardening, and admin features can be expanded.
- `storage/` is kept for logs/cache.
Initial project structure
