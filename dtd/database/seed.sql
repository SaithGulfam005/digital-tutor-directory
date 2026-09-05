-- Seed data for Digital Tutor Directory
-- Note: These sample accounts should be changed or removed in production.
USE digital_tutor_directory;

INSERT INTO categories (name, slug) VALUES
('Development', 'development'),
('Design', 'design'),
('Business', 'business'),
('Marketing', 'marketing'),
('Data Science', 'data-science');

-- Sample password hash (change these in production)
SET @pwd = '$2y$10$CcGWpc0dMYdhLh55pK62T.N4vglLxy2M/r9ENdwKXfBEQvK1DuvKq';

INSERT INTO users (name, email, phone, password_hash, role, status, bio) VALUES
('Admin User', 'admin@digitaltutor.com', '+92 300 0000001', @pwd, 'admin', 'active', 'Platform administrator'),
('Dr. Sarah Khan', 'sarah.khan@digitaltutor.com', '+92 300 2223344', @pwd, 'teacher', 'active', 'Senior developer and educator with 12+ years in full-stack development.'),
('Ahmed Hassan', 'ahmed@digitaltutor.com', '+92 321 3334455', @pwd, 'teacher', 'active', 'Award-winning UI/UX designer and design systems expert.'),
('Maria Lopez', 'maria@digitaltutor.com', '+92 333 4445566', @pwd, 'teacher', 'active', 'Data scientist specializing in ML and business intelligence.'),
('James Wilson', 'james@digitaltutor.com', '+92 345 5556677', @pwd, 'teacher', 'active', 'Digital marketing strategist for global brands.'),
('Ali Raza', 'ali.raza@email.com', '+92 300 1112233', @pwd, 'student', 'active', 'Passionate learner focused on web development and design.'),
('Fatima Noor', 'fatima.noor@email.com', '+92 321 4455667', @pwd, 'student', 'active', NULL),
('Sana Javed', 'sana.javed@email.com', '+92 302 9988776', @pwd, 'teacher', 'pending', NULL),
('Imran Qureshi', 'imran.q@email.com', '+92 303 8877665', @pwd, 'teacher', 'pending', NULL);

INSERT INTO teacher_profiles (user_id, qualification, cnic, subject, experience, rating, verification_status, verified_at) VALUES
((SELECT id FROM users WHERE email='sarah.khan@digitaltutor.com'), 'PhD Computer Science', '42101-9876543-2', 'Development', '12 years', 4.90, 'verified', '2024-08-15'),
((SELECT id FROM users WHERE email='ahmed@digitaltutor.com'), 'MSc Design', '35202-7654321-9', 'Design', '8 years', 4.70, 'verified', '2024-09-01'),
((SELECT id FROM users WHERE email='maria@digitaltutor.com'), 'PhD Statistics', '61101-9876543-2', 'Data Science', '10 years', 4.90, 'verified', '2024-07-20'),
((SELECT id FROM users WHERE email='james@digitaltutor.com'), 'MBA Marketing', '37405-5544332-8', 'Marketing', '7 years', 4.60, 'verified', '2024-10-05'),
((SELECT id FROM users WHERE email='sana.javed@email.com'), 'MSc Mathematics', '42101-1234567-1', 'Development', '5 years', 0, 'pending', NULL),
((SELECT id FROM users WHERE email='imran.q@email.com'), 'BSc Computer Science', '35202-7654321-9', 'Development', '3 years', 0, 'pending', NULL);

INSERT INTO courses (teacher_id, category_id, title, slug, description, price, status, rating) VALUES
((SELECT id FROM users WHERE email='sarah.khan@digitaltutor.com'), 1, 'Complete Web Development', 'complete-web-development', 'Master HTML, CSS, JavaScript, and modern frameworks.', 49.99, 'published', 4.80),
((SELECT id FROM users WHERE email='ahmed@digitaltutor.com'), 2, 'UI/UX Design Masterclass', 'ui-ux-design-masterclass', 'Learn user research, wireframing, and prototyping.', 39.99, 'published', 4.70),
((SELECT id FROM users WHERE email='maria@digitaltutor.com'), 5, 'Data Science with Python', 'data-science-with-python', 'Python, pandas, ML basics, and visualization.', 59.99, 'published', 4.90),
((SELECT id FROM users WHERE email='james@digitaltutor.com'), 4, 'Digital Marketing Fundamentals', 'digital-marketing-fundamentals', 'SEO, social media, and campaign strategy.', 29.99, 'published', 4.60),
((SELECT id FROM users WHERE email='sarah.khan@digitaltutor.com'), 1, 'Mobile App Development', 'mobile-app-development', 'Build cross-platform apps with modern tools.', 54.99, 'published', 4.80),
((SELECT id FROM users WHERE email='maria@digitaltutor.com'), 3, 'Business Analytics', 'business-analytics', 'Data-driven decision making for business.', 44.99, 'published', 4.50),
((SELECT id FROM users WHERE email='sana.javed@email.com'), 1, 'Advanced React Patterns', 'advanced-react-patterns', 'Hooks, context, and performance.', 42.99, 'pending', 0),
((SELECT id FROM users WHERE email='imran.q@email.com'), 3, 'Financial Accounting Basics', 'financial-accounting-basics', 'Intro to accounting principles.', 34.99, 'pending', 0);

INSERT INTO lessons (course_id, title, duration, sort_order, content_url) VALUES
(1, 'Introduction to the Web', '12:30', 1, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'),
(1, 'HTML Fundamentals', '18:45', 2, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4'),
(1, 'CSS Layouts', '22:10', 3, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4'),
(1, 'JavaScript Basics', '25:00', 4, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4'),
(1, 'Building Your First App', '30:15', 5, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4'),
(1, 'Deployment & Next Steps', '14:20', 6, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4'),
(2, 'Design Thinking', '15:00', 1, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'),
(2, 'User Research Methods', '20:30', 2, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4'),
(2, 'Wireframing in Figma', '18:00', 3, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4'),
(3, 'Python Setup', '10:00', 1, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4'),
(3, 'Data with Pandas', '24:00', 2, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4'),
(3, 'Visualization', '19:30', 3, 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4');

INSERT INTO enrollments (student_id, course_id, progress, status, last_access) VALUES
((SELECT id FROM users WHERE email='ali.raza@email.com'), 1, 68, 'active', CURDATE()),
((SELECT id FROM users WHERE email='ali.raza@email.com'), 2, 35, 'active', CURDATE() - INTERVAL 1 DAY),
((SELECT id FROM users WHERE email='ali.raza@email.com'), 3, 100, 'completed', CURDATE() - INTERVAL 10 DAY),
((SELECT id FROM users WHERE email='ali.raza@email.com'), 5, 12, 'active', CURDATE() - INTERVAL 2 DAY),
((SELECT id FROM users WHERE email='ali.raza@email.com'), 4, 100, 'completed', CURDATE() - INTERVAL 45 DAY),
((SELECT id FROM users WHERE email='fatima.noor@email.com'), 2, 50, 'active', CURDATE());

INSERT INTO payments (reference, student_id, course_id, amount, method, status, teacher_share, created_at) VALUES
('PAY-10041', (SELECT id FROM users WHERE email='ali.raza@email.com'), 1, 49.99, 'Card', 'completed', 34.99, NOW() - INTERVAL 1 DAY),
('PAY-10040', (SELECT id FROM users WHERE email='fatima.noor@email.com'), 2, 39.99, 'JazzCash', 'completed', 27.99, NOW() - INTERVAL 2 DAY),
('PAY-10039', (SELECT id FROM users WHERE email='ali.raza@email.com'), 3, 59.99, 'Card', 'completed', 41.99, NOW() - INTERVAL 3 DAY),
('PAY-10038', (SELECT id FROM users WHERE email='ali.raza@email.com'), 4, 29.99, 'Card', 'failed', 0, NOW() - INTERVAL 4 DAY),
('PAY-10037', (SELECT id FROM users WHERE email='fatima.noor@email.com'), 5, 54.99, 'Bank Transfer', 'pending', 0, NOW() - INTERVAL 5 DAY);

INSERT INTO contact_messages (name, email, subject, message) VALUES
('Test User', 'test@email.com', 'General inquiry', 'Hello, I have a question about your platform.');
