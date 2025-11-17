-- Create table for clothing try-on sessions
CREATE TABLE IF NOT EXISTS clothing_tryon_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    dress_id INT NOT NULL,
    user_image_path VARCHAR(500) NOT NULL,
    result_image_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dress_id) REFERENCES dresses(id) ON DELETE CASCADE
);
