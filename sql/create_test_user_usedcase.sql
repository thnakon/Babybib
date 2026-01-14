-- =====================================================
-- Babybib Test Data: User "usedcase"
-- 300 Bibliographies + 30 Projects
-- =====================================================

-- Password: password123 (hashed with bcrypt)
SET @password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- 1. Create User
INSERT INTO users (username, email, password, name, surname, role, org_type, org_name, province, language, bibliography_count, project_count, is_lis_cmu, created_at)
VALUES ('usedcase', 'usedcase@example.com', @password_hash, 'ทดสอบ', 'ระบบ', 'user', 'university', 'มหาวิทยาลัยเชียงใหม่', 'เชียงใหม่', 'th', 300, 30, 1, NOW());

SET @user_id = LAST_INSERT_ID();

-- 2. Create 30 Projects
INSERT INTO projects (user_id, name, description, color, created_at) VALUES
(@user_id, 'วิจัยด้านบรรณารักษศาสตร์', 'โครงการวิจัยเกี่ยวกับการจัดการห้องสมุด', '#8B5CF6', NOW()),
(@user_id, 'การศึกษาสารสนเทศศาสตร์', 'รวบรวมงานวิจัยด้านสารสนเทศ', '#10B981', NOW()),
(@user_id, 'เทคโนโลยีสารสนเทศ', 'งานวิจัยด้าน IT และ Digital Library', '#3B82F6', NOW()),
(@user_id, 'การจัดการความรู้', 'Knowledge Management Research', '#F59E0B', NOW()),
(@user_id, 'ห้องสมุดดิจิทัล', 'Digital Library Projects', '#EF4444', NOW()),
(@user_id, 'การอนุรักษ์เอกสาร', 'Document Preservation Study', '#EC4899', NOW()),
(@user_id, 'บริการสารสนเทศ', 'Information Services Research', '#6366F1', NOW()),
(@user_id, 'การวิจัยผู้ใช้', 'User Studies and UX Research', '#14B8A6', NOW()),
(@user_id, 'Metadata Standards', 'Dublin Core and MARC21 Studies', '#8B5CF6', NOW()),
(@user_id, 'Open Access', 'Open Access Movement Research', '#22C55E', NOW()),
(@user_id, 'การจัดหมวดหมู่', 'Classification and Taxonomy', '#F97316', NOW()),
(@user_id, 'Bibliometrics', 'Citation Analysis Research', '#A855F7', NOW()),
(@user_id, 'การรู้สารสนเทศ', 'Information Literacy Studies', '#06B6D4', NOW()),
(@user_id, 'Archives Management', 'จดหมายเหตุและการจัดการ', '#84CC16', NOW()),
(@user_id, 'Data Science', 'Data Analytics in Libraries', '#F43F5E', NOW()),
(@user_id, 'Academic Writing', 'การเขียนเชิงวิชาการ', '#0EA5E9', NOW()),
(@user_id, 'Research Methods', 'ระเบียบวิธีวิจัย', '#7C3AED', NOW()),
(@user_id, 'Thesis Project', 'วิทยานิพนธ์', '#10B981', NOW()),
(@user_id, 'Special Collections', 'หนังสือหายาก', '#D946EF', NOW()),
(@user_id, 'E-Resources', 'ทรัพยากรอิเล็กทรอนิกส์', '#2563EB', NOW()),
(@user_id, 'Library Management', 'การบริหารห้องสมุด', '#059669', NOW()),
(@user_id, 'Cataloging', 'การทำรายการ', '#DC2626', NOW()),
(@user_id, 'Reference Services', 'บริการตอบคำถาม', '#7C3AED', NOW()),
(@user_id, 'Collection Development', 'การพัฒนาทรัพยากร', '#EA580C', NOW()),
(@user_id, 'Copyright Issues', 'ลิขสิทธิ์และกฎหมาย', '#4F46E5', NOW()),
(@user_id, 'Library Automation', 'ระบบห้องสมุดอัตโนมัติ', '#0D9488', NOW()),
(@user_id, 'Information Behavior', 'พฤติกรรมสารสนเทศ', '#C026D3', NOW()),
(@user_id, 'Scholarly Communication', 'การสื่อสารทางวิชาการ', '#0891B2', NOW()),
(@user_id, 'Social Media', 'สื่อสังคมกับห้องสมุด', '#BE185D', NOW()),
(@user_id, 'AI in Libraries', 'ปัญญาประดิษฐ์ในห้องสมุด', '#6D28D9', NOW());

-- Get first project ID for assigning bibliographies
SET @first_project_id = (SELECT id FROM projects WHERE user_id = @user_id ORDER BY id LIMIT 1);

-- 3. Create 300 Bibliographies (Mix of Thai and English)
-- Using resource_type_id 1 (หนังสือ/Book) and 2 (บทความวารสาร/Journal Article)

-- Insert Thai Books (50 entries)
INSERT INTO bibliographies (user_id, project_id, resource_type_id, language, bibliography_text, citation_parenthetical, citation_narrative, data, author_sort_key, year, created_at)
SELECT 
    @user_id,
    @first_project_id + (n % 30),
    1,
    'th',
    CONCAT(
        ELT(1 + (n % 15), 'สมชาย ใจดี', 'สมหญิง รักเรียน', 'ประยุกต์ วิชาการ', 'นภา แสงดาว', 'วิชัย พัฒนา', 'กมลา สุขใจ', 'อรุณ ศรีสว่าง', 'มณี ทองคำ', 'สุรชัย เก่งมาก', 'พิมพ์ใจ รักษ์ไทย', 'จิตรา สายธาร', 'ธนา บุญยิ่ง', 'ศิริพร ใฝ่รู้', 'วรรณา หนังสือดี', 'สุดา ห้องสมุด'),
        '. (',
        2560 + (n % 8),
        '). <i>',
        ELT(1 + (n % 10), 'การพัฒนาระบบห้องสมุดดิจิทัล', 'การจัดการความรู้ในองค์กร', 'พฤติกรรมการแสวงหาสารสนเทศ', 'การรู้สารสนเทศในยุคดิจิทัล', 'การอนุรักษ์เอกสารโบราณ', 'เทคโนโลยีสารสนเทศสำหรับห้องสมุด', 'การจัดหมวดหมู่ทรัพยากรสารสนเทศ', 'บริการตอบคำถามและช่วยค้นคว้า', 'การพัฒนาทรัพยากรห้องสมุด', 'ระบบห้องสมุดอัตโนมัติ'),
        '</i>. ',
        ELT(1 + (n % 5), 'สำนักพิมพ์จุฬาลงกรณ์', 'โรงพิมพ์มหาวิทยาลัย', 'สำนักพิมพ์แห่งจุฬาฯ', 'ศูนย์หนังสือจุฬา', 'สำนักพิมพ์มหาวิทยาลัยธรรมศาสตร์'),
        '.'
    ),
    CONCAT(
        '(',
        ELT(1 + (n % 15), 'สมชาย', 'สมหญิง', 'ประยุกต์', 'นภา', 'วิชัย', 'กมลา', 'อรุณ', 'มณี', 'สุรชัย', 'พิมพ์ใจ', 'จิตรา', 'ธนา', 'ศิริพร', 'วรรณา', 'สุดา'),
        ', ',
        2560 + (n % 8),
        ')'
    ),
    CONCAT(
        ELT(1 + (n % 15), 'สมชาย', 'สมหญิง', 'ประยุกต์', 'นภา', 'วิชัย', 'กมลา', 'อรุณ', 'มณี', 'สุรชัย', 'พิมพ์ใจ', 'จิตรา', 'ธนา', 'ศิริพร', 'วรรณา', 'สุดา'),
        ' (',
        2560 + (n % 8),
        ')'
    ),
    '{}',
    ELT(1 + (n % 15), 'สมชาย', 'สมหญิง', 'ประยุกต์', 'นภา', 'วิชัย', 'กมลา', 'อรุณ', 'มณี', 'สุรชัย', 'พิมพ์ใจ', 'จิตรา', 'ธนา', 'ศิริพร', 'วรรณา', 'สุดา'),
    2560 + (n % 8),
    DATE_SUB(NOW(), INTERVAL n DAY)
FROM (
    SELECT a.N + b.N * 10 AS n
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) b
) numbers
WHERE n < 50;

-- Insert Thai Journal Articles (50 entries)
INSERT INTO bibliographies (user_id, project_id, resource_type_id, language, bibliography_text, citation_parenthetical, citation_narrative, data, author_sort_key, year, created_at)
SELECT 
    @user_id,
    @first_project_id + (n % 30),
    2,
    'th',
    CONCAT(
        ELT(1 + (n % 15), 'กานต์ สารสนเทศ', 'ปิยะ ห้องสมุด', 'ชุติมา วิชาการ', 'ดวงใจ รักอ่าน', 'เอกชัย พัฒนา', 'ฐิติมา ความรู้', 'ณัฐพล วิจัย', 'ตรีนุช สำนักพิมพ์', 'ธีรยุทธ เทคโนโลยี', 'นวลจันทร์ ดิจิทัล', 'บัณฑิต การศึกษา', 'ปรีชา สารนิเทศ', 'ผกามาศ ห้องสมุด', 'พัชรี วารสาร', 'มนตรี ข้อมูล'),
        '. (',
        2562 + (n % 6),
        '). ',
        ELT(1 + (n % 10), 'การประยุกต์ใช้ AI ในห้องสมุด', 'การพัฒนาระบบสืบค้นอัจฉริยะ', 'พฤติกรรมผู้ใช้ห้องสมุดยุคใหม่', 'การจัดการข้อมูลวิจัย', 'การอนุรักษ์สื่อดิจิทัล', 'บริการห้องสมุดแบบออนไลน์', 'การประเมินคุณภาพวารสาร', 'เครือข่ายห้องสมุดดิจิทัล', 'การสร้างคลังข้อมูลสถาบัน', 'นวัตกรรมห้องสมุด'),
        '. <i>',
        ELT(1 + (n % 5), 'วารสารบรรณารักษศาสตร์', 'วารสารสารสนเทศศาสตร์', 'วารสารห้องสมุด', 'วารสารวิจัยสารสนเทศ', 'วารสารวิชาการ'),
        '</i>, ',
        10 + (n % 20),
        '(',
        1 + (n % 4),
        '), ',
        1 + (n * 15),
        '-',
        15 + (n * 15),
        '.'
    ),
    CONCAT(
        '(',
        ELT(1 + (n % 15), 'กานต์', 'ปิยะ', 'ชุติมา', 'ดวงใจ', 'เอกชัย', 'ฐิติมา', 'ณัฐพล', 'ตรีนุช', 'ธีรยุทธ', 'นวลจันทร์', 'บัณฑิต', 'ปรีชา', 'ผกามาศ', 'พัชรี', 'มนตรี'),
        ', ',
        2562 + (n % 6),
        ')'
    ),
    CONCAT(
        ELT(1 + (n % 15), 'กานต์', 'ปิยะ', 'ชุติมา', 'ดวงใจ', 'เอกชัย', 'ฐิติมา', 'ณัฐพล', 'ตรีนุช', 'ธีรยุทธ', 'นวลจันทร์', 'บัณฑิต', 'ปรีชา', 'ผกามาศ', 'พัชรี', 'มนตรี'),
        ' (',
        2562 + (n % 6),
        ')'
    ),
    '{}',
    ELT(1 + (n % 15), 'กานต์', 'ปิยะ', 'ชุติมา', 'ดวงใจ', 'เอกชัย', 'ฐิติมา', 'ณัฐพล', 'ตรีนุช', 'ธีรยุทธ', 'นวลจันทร์', 'บัณฑิต', 'ปรีชา', 'ผกามาศ', 'พัชรี', 'มนตรี'),
    2562 + (n % 6),
    DATE_SUB(NOW(), INTERVAL (50 + n) DAY)
FROM (
    SELECT a.N + b.N * 10 AS n
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) b
) numbers
WHERE n < 50;

-- Insert English Books (100 entries)
INSERT INTO bibliographies (user_id, project_id, resource_type_id, language, bibliography_text, citation_parenthetical, citation_narrative, data, author_sort_key, year, created_at)
SELECT 
    @user_id,
    @first_project_id + (n % 30),
    1,
    'en',
    CONCAT(
        ELT(1 + (n % 15), 'Smith, J.', 'Johnson, M.', 'Williams, K.', 'Brown, R.', 'Davis, S.', 'Miller, A.', 'Wilson, T.', 'Moore, L.', 'Taylor, C.', 'Anderson, P.', 'Thomas, E.', 'Jackson, H.', 'White, N.', 'Harris, D.', 'Martin, G.'),
        ' (',
        2018 + (n % 7),
        '). <i>',
        ELT(1 + (n % 10), 'Digital library systems and innovations', 'Information literacy in higher education', 'Knowledge management best practices', 'User experience design for libraries', 'Metadata standards and applications', 'Open access publishing strategies', 'Bibliometric analysis methods', 'Data curation and preservation', 'Research data management handbook', 'Scholarly communication trends'),
        '</i>. ',
        ELT(1 + (n % 10), 'Springer', 'Wiley', 'Elsevier', 'Cambridge University Press', 'Oxford University Press', 'Routledge', 'SAGE Publications', 'MIT Press', 'ALA Editions', 'Facet Publishing'),
        '.'
    ),
    CONCAT(
        '(',
        ELT(1 + (n % 15), 'Smith', 'Johnson', 'Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin'),
        ', ',
        2018 + (n % 7),
        ')'
    ),
    CONCAT(
        ELT(1 + (n % 15), 'Smith', 'Johnson', 'Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin'),
        ' (',
        2018 + (n % 7),
        ')'
    ),
    '{}',
    ELT(1 + (n % 15), 'Smith', 'Johnson', 'Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin'),
    2018 + (n % 7),
    DATE_SUB(NOW(), INTERVAL (100 + n) DAY)
FROM (
    SELECT a.N + b.N * 10 AS n
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
) numbers
WHERE n < 100;

-- Insert English Journal Articles (100 entries)
INSERT INTO bibliographies (user_id, project_id, resource_type_id, language, bibliography_text, citation_parenthetical, citation_narrative, data, author_sort_key, year, created_at)
SELECT 
    @user_id,
    @first_project_id + (n % 30),
    2,
    'en',
    CONCAT(
        ELT(1 + (n % 15), 'Garcia, L.', 'Martinez, R.', 'Robinson, K.', 'Clark, J.', 'Rodriguez, M.', 'Lewis, S.', 'Lee, A.', 'Walker, T.', 'Hall, C.', 'Allen, P.', 'Young, E.', 'King, H.', 'Wright, N.', 'Scott, D.', 'Green, G.'),
        ' (',
        2019 + (n % 6),
        '). ',
        ELT(1 + (n % 10), 'Artificial intelligence in library services', 'Machine learning for information retrieval', 'Digital transformation in academic libraries', 'Cloud computing for library systems', 'Big data analytics in libraries', 'Social media marketing for libraries', 'Virtual reality in library instruction', 'Blockchain for digital preservation', 'Internet of Things in smart libraries', 'Cybersecurity for library systems'),
        '. <i>',
        ELT(1 + (n % 5), 'Journal of Academic Librarianship', 'Library Quarterly', 'Journal of Documentation', 'Information Processing and Management', 'College and Research Libraries'),
        '</i>, ',
        40 + (n % 20),
        '(',
        1 + (n % 4),
        '), ',
        100 + (n * 10),
        '-',
        115 + (n * 10),
        '. https://doi.org/10.1000/example',
        n
    ),
    CONCAT(
        '(',
        ELT(1 + (n % 15), 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green'),
        ', ',
        2019 + (n % 6),
        ')'
    ),
    CONCAT(
        ELT(1 + (n % 15), 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green'),
        ' (',
        2019 + (n % 6),
        ')'
    ),
    '{}',
    ELT(1 + (n % 15), 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Green'),
    2019 + (n % 6),
    DATE_SUB(NOW(), INTERVAL (200 + n) DAY)
FROM (
    SELECT a.N + b.N * 10 AS n
    FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
         (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
) numbers
WHERE n < 100;

-- 4. Update bibliography counts per project
UPDATE projects p
SET bibliography_count = (
    SELECT COUNT(*) FROM bibliographies b WHERE b.project_id = p.id
)
WHERE p.user_id = @user_id;

-- 5. Verify the data
SELECT 'User Created' AS status, username, email, bibliography_count, project_count 
FROM users WHERE username = 'usedcase';

SELECT 'Projects Created' AS status, COUNT(*) as total_projects 
FROM projects WHERE user_id = @user_id;

SELECT 'Bibliographies Created' AS status, COUNT(*) as total_bibliographies 
FROM bibliographies WHERE user_id = @user_id;

SELECT 'Bibliographies by Language' AS status, language, COUNT(*) as count 
FROM bibliographies WHERE user_id = @user_id GROUP BY language;

SELECT 'Bibliographies by Type' AS status, resource_type_id, COUNT(*) as count 
FROM bibliographies WHERE user_id = @user_id GROUP BY resource_type_id;
