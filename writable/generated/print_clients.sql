CREATE TABLE print_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    student_number VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255),
    course_code VARCHAR(255) NOT NULL,
    year_level INT NOT NULL,
    is_enrolled TINYINT(1)
);
