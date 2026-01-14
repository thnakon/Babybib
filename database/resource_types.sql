-- =====================================================
-- Babybib Resource Types
-- All 30+ resource types for APA7 bibliography
-- =====================================================

USE babybib_db;

-- =====================================================
-- Insert Resource Types
-- =====================================================

-- Category: หนังสือ (Books)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('book', 'หนังสือ', 'Book', 'books', 'fa-book', '{"fields":["authors","year","title","edition","publisher","place"]}', 1),
('book_series', 'หนังสือชุดหลายเล่มจบ', 'Book Series', 'books', 'fa-books', '{"fields":["authors","year","title","volume","edition","publisher","place"]}', 2),
('book_chapter', 'บทความในหนังสือ', 'Book Chapter', 'books', 'fa-book-open', '{"fields":["authors","year","chapter_title","editors","book_title","pages","publisher","place"]}', 3),
('ebook_doi', 'หนังสืออิเล็กทรอนิกส์ (มี DOI)', 'E-Book with DOI', 'books', 'fa-tablet-screen-button', '{"fields":["authors","year","title","edition","publisher","doi"]}', 4),
('ebook_no_doi', 'หนังสืออิเล็กทรอนิกส์ (ไม่มี DOI)', 'E-Book without DOI', 'books', 'fa-tablet', '{"fields":["authors","year","title","edition","publisher","url"]}', 5);

-- Category: วารสาร (Journals)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('journal_article', 'บทความวารสาร', 'Journal Article', 'journals', 'fa-newspaper', '{"fields":["authors","year","article_title","journal_name","volume","issue","pages"]}', 10),
('ejournal_doi', 'บทความวารสารอิเล็กทรอนิกส์ (มี DOI)', 'Electronic Journal Article with DOI', 'journals', 'fa-file-lines', '{"fields":["authors","year","article_title","journal_name","volume","issue","pages","doi"]}', 11),
('ejournal_no_doi', 'บทความวารสารอิเล็กทรอนิกส์ (ไม่มี DOI)', 'Electronic Journal Article without DOI', 'journals', 'fa-file', '{"fields":["authors","year","article_title","journal_name","volume","issue","pages","url"]}', 12),
('ejournal_print', 'วารสารอิเล็กทรอนิกส์ (แบบมีฉบับพิมพ์)', 'Electronic Journal (Print Version Available)', 'journals', 'fa-copy', '{"fields":["authors","year","article_title","journal_name","volume","issue","pages","url"]}', 13),
('ejournal_only', 'วารสารอิเล็กทรอนิกส์ (แบบไม่มีฉบับพิมพ์)', 'Electronic Journal Only', 'journals', 'fa-globe', '{"fields":["authors","year","article_title","journal_name","volume","issue","url"]}', 14);

-- Category: พจนานุกรม/สารานุกรม (Dictionaries/Encyclopedias)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('dictionary', 'พจนานุกรม', 'Dictionary', 'reference', 'fa-spell-check', '{"fields":["title","year","edition","publisher","place"]}', 20),
('dictionary_online', 'พจนานุกรมออนไลน์', 'Online Dictionary', 'reference', 'fa-magnifying-glass', '{"fields":["entry_word","year","dictionary_name","url","accessed_date"]}', 21),
('encyclopedia', 'สารานุกรม', 'Encyclopedia', 'reference', 'fa-book-atlas', '{"fields":["authors","year","entry_title","encyclopedia_name","volume","pages","publisher","place"]}', 22),
('encyclopedia_online', 'สารานุกรมออนไลน์', 'Online Encyclopedia', 'reference', 'fa-earth-americas', '{"fields":["authors","year","entry_title","encyclopedia_name","url","accessed_date"]}', 23);

-- Category: หนังสือพิมพ์ (Newspapers)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('newspaper_print', 'หนังสือพิมพ์แบบรูปเล่ม', 'Print Newspaper', 'newspapers', 'fa-newspaper', '{"fields":["authors","year","month","day","article_title","newspaper_name","pages"]}', 30),
('newspaper_online', 'หนังสือพิมพ์ออนไลน์', 'Online Newspaper', 'newspapers', 'fa-rss', '{"fields":["authors","year","month","day","article_title","newspaper_name","url"]}', 31);

-- Category: รายงาน (Reports)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('report', 'รายงาน', 'Report', 'reports', 'fa-file-contract', '{"fields":["authors","year","title","report_number","institution","place"]}', 40),
('research_report', 'รายงานการวิจัย', 'Research Report', 'reports', 'fa-flask', '{"fields":["authors","year","title","institution","place"]}', 41),
('government_report', 'รายงานที่จัดทำโดยหน่วยงานราชการหรือองค์กรอื่น', 'Government/Organization Report', 'reports', 'fa-landmark', '{"fields":["organization","year","title","report_number","url"]}', 42),
('institutional_report', 'รายงานที่จัดทำโดยบุคคลที่สังกัดหน่วยงาน', 'Institutional Author Report', 'reports', 'fa-building', '{"fields":["authors","year","title","institution","url"]}', 43);

-- Category: งานประชุม (Conferences)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('conference_proceeding', 'เอกสารการประชุมทางวิชาการ (ที่มี Proceeding)', 'Conference Proceeding (Published)', 'conferences', 'fa-users', '{"fields":["authors","year","paper_title","editors","proceeding_title","pages","publisher","place"]}', 50),
('conference_no_proceeding', 'เอกสารการประชุมทางวิชาการ (ที่ไม่มี Proceeding)', 'Conference Paper (Unpublished)', 'conferences', 'fa-user-group', '{"fields":["authors","year","month","paper_title","conference_name","location"]}', 51),
('conference_presentation', 'การนำเสนองานวิจัยหรือโปสเตอร์ในงานประชุมวิชาการ', 'Conference Presentation/Poster', 'conferences', 'fa-presentation-screen', '{"fields":["authors","year","month","presentation_title","presentation_type","conference_name","location"]}', 52);

-- Category: วิทยานิพนธ์ (Theses/Dissertations)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('thesis_unpublished', 'วิทยานิพนธ์ (ที่ไม่ได้ตีพิมพ์)', 'Unpublished Thesis/Dissertation', 'theses', 'fa-graduation-cap', '{"fields":["authors","year","title","degree_type","institution","place"]}', 60),
('thesis_website', 'วิทยานิพนธ์จากเว็บไซต์', 'Thesis from Website', 'theses', 'fa-globe', '{"fields":["authors","year","title","degree_type","institution","url"]}', 61),
('thesis_database', 'วิทยานิพนธ์จากฐานข้อมูลเชิงพาณิชย์', 'Thesis from Database', 'theses', 'fa-database', '{"fields":["authors","year","title","degree_type","institution","database_name","accession_number"]}', 62);

-- Category: ออนไลน์ (Online Sources)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('webpage', 'เอกสารอิเล็กทรอนิกส์ (เว็บเพจ)', 'Web Page/Electronic Document', 'online', 'fa-globe', '{"fields":["authors","year","month","day","page_title","website_name","url"]}', 70),
('social_media', 'สื่อออนไลน์ (วิดีโอออนไลน์ บทความในโซเชียลมีเดีย)', 'Social Media/Online Media', 'online', 'fa-share-nodes', '{"fields":["authors","year","month","day","content_title","platform","url"]}', 71),
('royal_gazette', 'ราชกิจจานุเบกษาออนไลน์', 'Royal Thai Government Gazette Online', 'online', 'fa-scroll', '{"fields":["title","year","volume","section","pages","url"]}', 72),
('patent_online', 'สิทธิบัตรออนไลน์', 'Online Patent', 'online', 'fa-certificate', '{"fields":["inventors","year","patent_title","patent_number","patent_office","url"]}', 73),
('personal_communication', 'การติดต่อสื่อสารส่วนบุคคล', 'Personal Communication', 'online', 'fa-envelope', '{"fields":["communicator_name","year","month","day","communication_type"]}', 74);

-- Category: สื่อภาพและเสียง (Audiovisual Media)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('infographic', 'อินโฟกราฟิก (Infographic)', 'Infographic', 'media', 'fa-chart-pie', '{"fields":["authors","year","title","website_name","url"]}', 80),
('slides_online', 'การนำเสนอด้วยสไลด์และเอกสารการสอนออนไลน์', 'Online Slides/Teaching Materials', 'media', 'fa-file-powerpoint', '{"fields":["authors","year","title","platform","url"]}', 81),
('webinar', 'สัมมนาออนไลน์ (Webinar)', 'Webinar', 'media', 'fa-video', '{"fields":["presenters","year","month","day","webinar_title","organization","url"]}', 82),
('youtube_video', 'วิดีโอใน Youtube หรือวิดีโอออนไลน์ต่าง ๆ', 'YouTube/Online Video', 'media', 'fa-youtube', '{"fields":["channel_name","year","month","day","video_title","url"]}', 83),
('podcast', 'พ็อดคาสท์ภาพและเสียง (แบบจบในตอน)', 'Podcast Episode (Single)', 'media', 'fa-podcast', '{"fields":["host","year","month","day","episode_title","podcast_name","url"]}', 84);

-- Category: อื่นๆ (Others)
INSERT INTO resource_types (code, name_th, name_en, category, icon, fields_config, sort_order) VALUES
('ai_generated', 'AI (เนื้อหาที่สร้างโดย AI)', 'AI Generated Content', 'others', 'fa-robot', '{"fields":["ai_name","year","month","day","prompt_description","version","url"]}', 90);
