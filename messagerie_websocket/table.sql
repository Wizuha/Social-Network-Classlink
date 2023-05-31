CREATE TABLE group_chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    member_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   
);
CREATE TABLE private_chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    recever_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   
);
CREATE TABLE private_chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    recever_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   
);
CREATE TABLE group_chat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE group_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    member_id INT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES group_chat(id)
  
);
ALTER TABLE group_members
ADD COLUMN admin BOOLEAN DEFAULT FALSE;
 //metrre un ADMIN
 UPDATE group_members
SET admin = TRUE
WHERE group_id = 1 AND member_id = 1;



