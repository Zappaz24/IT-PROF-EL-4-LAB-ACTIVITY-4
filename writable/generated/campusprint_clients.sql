CREATE TABLE campusprint_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(80) NOT NULL,
    student_number VARCHAR(15) NOT NULL UNIQUE,
    contact_email VARCHAR(120),
    course_code VARCHAR(255) NOT NULL,
    year_level INT NOT NULL,
    is_enrolled TINYINT(1)
);
