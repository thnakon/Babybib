# Smart Search Data Mapping Documentation

เอกสารฉบับนี้สรุปการเชื่อมโยงข้อมูล (Mapping) จากฐานข้อมูลภายนอก (External APIs) เข้าสู่ฟิลด์ในฟอร์มของระบบ Babybib

---

## 1. ฐานข้อมูลหนังสือ (ISBN / Keyword)
ใช้ข้อมูลจาก **Open Library** เป็นหลัก และ **Google Books** เป็นรอง

| ฟิลด์ในระบบ | ข้อมูลจาก Open Library | ข้อมูลจาก Google Books | หมายเหตุ |
| :--- | :--- | :--- | :--- |
| **ชื่อเรื่อง (title)** | `title` | `volumeInfo.title` (+ subtitle) | |
| **ปีพิมพ์ (year)** | `publish_date` (ดึงเฉพาะตัวเลข 4 หลัก) | `publishedDate` (ดึง 4 หลักแรก) | แปลงเป็น พ.ศ. (+543) เมื่อภาษาเป็นไทย |
| **สำนักพิมพ์ (publisher)** | `publishers[0].name` | `publisher` | |
| **จำนวนหน้า (pages)** | `number_of_pages` | `pageCount` | |
| **ผู้แต่ง (authors)** | `authors[].name` | `volumeInfo.authors[]` | แยกชื่อ/นามสกุลอัตโนมัติ |
| **รูปปก (thumbnail)** | `cover.medium` | `imageLinks.thumbnail` | ใช้รูปจาก Google Books หากมีความละเอียดสูงกว่า |

---

## 2. ฐานข้อมูลบทความวิชาการ (DOI)
ใช้ข้อมูลจาก **CrossRef** เป็นหลัก และ **OpenAlex** เป็นรอง

| ฟิลด์ในระบบ | ข้อมูลจาก CrossRef | ข้อมูลจาก OpenAlex | หมายเหตุ |
| :--- | :--- | :--- | :--- |
| **ชื่อบทความ (article_title)** | `title[0]` | `title` | กรอกลงฟิลด์ `article_title` |
| **ชื่อวารสาร (journal_name)** | `container-title[0]` | `primary_location.source.display_name` | |
| **ปีพิมพ์ (year)** | `published.date-parts` | `publication_year` | แปลงเป็น พ.ศ. (+543) เมื่อภาษาเป็นไทย |
| **เล่มที่ (volume)** | `volume` | `biblio.volume` | |
| **ฉบับที่ (issue)** | `issue` | `biblio.issue` | |
| **เลขหน้า (pages)** | `page` | - | |
| **DOI** | `https://doi.org/{doi}` | `https://doi.org/{doi}` | |
| **ผู้แต่ง (authors)** | `author[]` (given/family) | `authorships[].author` | |

---

## 3. ข้อมูลจากหน้าเว็บ (URL)
ใช้ตัวดึงข้อมูล **Web Scraper** (ดึงจาก Meta Tags ของเว็บไซต์)

| ฟิลด์ในระบบ | ข้อมูลที่ดึงได้ | หมายเหตุ |
| :--- | :--- | :--- |
| **ชื่อเรื่อง (title)** | `og:title` หรือ `<title>` | |
| **ชื่อเว็บไซต์ (website_name)** | `og:site_name` หรือชื่อโดเมน | |
| **ปีพิมพ์ (year)** | `article:published_time` หรือ Tags วันที่ | แปลงเป็น พ.ศ. (+543) เมื่อภาษาเป็นไทย |
| **URL** | ลิงก์ที่ผู้ใช้กรอก | |
| **ผู้แต่ง (authors)** | `author` หรือ `twitter:creator` | |

---

## 4. ตารางสรุปการ Mapping ในโค้ด (JavaScript)
เมื่อข้อมูลถูกส่งมายัง Frontend ฟังก์ชัน `selectSmartResult` จะกระจายข้อมูลลงฟิลด์ต่างๆ ดังนี้:

```javascript
const mappings = {
    'title':         item.title,
    'article_title': item.title,
    'year':          yearValue, // ผ่านการคำนวณ พ.ศ. แล้ว
    'publisher':     item.publisher,
    'pages':         item.pages,
    'doi':           item.doi,
    'url':           item.url,
    'volume':        item.volume,
    'issue':         item.issue,
    'journal_name':  item.journal_name || item.publisher,
    'website_name':  item.publisher
};
```

---
*ปรับปรุงล่าสุด: 25 กุมภาพันธ์ 2569*
