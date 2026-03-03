# Smart Search Data Mapping Documentation

เอกสารฉบับนี้สรุปการเชื่อมโยงข้อมูล (Mapping) จากฐานข้อมูลภายนอก (External APIs) เข้าสู่ฟิลด์ในฟอร์มของระบบ Babybib

---

## ภาพรวมการทำงานของ Smart Search v3 (Thai-First)

ระบบ Smart Search v3 ใช้สถาปัตยกรรม **Thai Layer → Global Layer** เพื่อค้นหาข้อมูลจากแหล่งไทยก่อน แล้วเสริมด้วยแหล่งสากล

### สถาปัตยกรรม

```
User Input → Type Detection (ISBN/DOI/URL/Keyword)
                ↓
        ┌ 🇹🇭 Thai Layer (Priority) ─────────────────┐
        │  Books:  Google Books Thai (langRestrict=th) │
        │  Papers: Semantic Scholar (multi-language)   │
        │  Papers: CrossRef Keyword (Thai journals)    │
        └─────────────────────────────────────────────┘
                ↓ (merge + deduplicate)
        ┌ 🌍 Global Layer (Fallback) ─────────────────┐
        │  Books:  Open Library + Google Books         │
        │  Papers: CrossRef (DOI) + OpenAlex (DOI)     │
        │  Web: Web Scraper (Meta Tags)                │
        └─────────────────────────────────────────────┘
                ↓
        Deduplicate → Sort by confidence → Return top 20
```

### การตรวจจับประเภท (Type Detection)

| ประเภท | ตัวอย่าง Input | แหล่งที่ค้นหา |
|:---|:---|:---|
| **ISBN** | `9786160449651` | 🇹🇭 Google Books Thai → 🌍 Open Library → Google Books |
| **DOI** | `10.1000/xyz123` | CrossRef → Semantic Scholar → OpenAlex |
| **URL** | `https://example.com` | Web Scraper (Meta Tags) |
| **Keyword** | `ปัญญาประดิษฐ์` | 🇹🇭 Google Books Thai → Semantic Scholar → 🌍 Open Library → Google Books → CrossRef keyword |

---

## 1. 🇹🇭 Google Books Thai (ISBN + Keyword)

| ฟิลด์ในระบบ | ข้อมูลจาก Google Books (Thai) |
|:---|:---|
| **ชื่อเรื่อง (title)** | `volumeInfo.title` (+ subtitle) |
| **ผู้แต่ง (authors)** | `volumeInfo.authors[]` |
| **ปีพิมพ์ (year)** | `publishedDate` |
| **สำนักพิมพ์ (publisher)** | `publisher` |
| **จำนวนหน้า (pages)** | `pageCount` |
| **รูปปก (thumbnail)** | `imageLinks.thumbnail` |

**API:** `https://www.googleapis.com/books/v1/volumes?q={query}&langRestrict=th`  
**Confidence:** 92

---

## 2. 🇹🇭 Semantic Scholar (DOI + Keyword)

| ฟิลด์ในระบบ | ข้อมูลจาก Semantic Scholar |
|:---|:---|
| **ชื่อบทความ (title)** | `title` |
| **ผู้แต่ง (authors)** | `authors[].name` |
| **ปีพิมพ์ (year)** | `year` |
| **DOI** | `externalIds.DOI` |
| **URL** | `url` |
| **ชื่อวารสาร (journal_name)** | `journal.name` หรือ `venue` |
| **เล่มที่ (volume)** | `journal.volume` |
| **เลขหน้า (pages)** | `journal.pages` |

**API:** `https://api.semanticscholar.org/graph/v1/paper/search?query={query}&fields=title,authors,year,venue,externalIds,publicationTypes,journal,url`  
**Confidence:** 90  
**หมายเหตุ:** ฟรี 100%, รองรับหลายภาษารวมไทย, ไม่ต้อง API key, rate limit 1 req/sec

---

## 3. 🇹🇭 CrossRef Keyword Search (Keyword)

| ฟิลด์ในระบบ | ข้อมูลจาก CrossRef |
|:---|:---|
| **ชื่อบทความ (title)** | `title[0]` |
| **ผู้แต่ง (authors)** | `author[].given/family` |
| **ปีพิมพ์ (year)** | `published.date-parts` |
| **DOI** | `DOI` |
| **ชื่อวารสาร (journal_name)** | `container-title[0]` |
| **เล่มที่ / ฉบับที่** | `volume` / `issue` |

**API:** `https://api.crossref.org/works?query={query}&rows=3`  
**Confidence:** 78 (ลดลงเพื่อไม่ให้บทความมากเกินไป)

---

## 4. 🌍 Open Library (ISBN + Keyword)

| ฟิลด์ในระบบ | ข้อมูลจาก Open Library |
|:---|:---|
| **ชื่อเรื่อง (title)** | `title` |
| **ผู้แต่ง (authors)** | `authors[].name` / `author_name[]` |
| **ปีพิมพ์ (year)** | `publish_date` / `first_publish_year` |
| **สำนักพิมพ์ (publisher)** | `publishers[0].name` |
| **จำนวนหน้า (pages)** | `number_of_pages` |
| **รูปปก (thumbnail)** | `cover.medium` |

**API:** `https://openlibrary.org/api/books?bibkeys=ISBN:{isbn}&format=json&jscmd=data`  
**Confidence:** ISBN 95, Keyword 88

---

## 5. 🌍 Google Books (ISBN + Keyword)

**API:** `https://www.googleapis.com/books/v1/volumes?q={query}`  
**Confidence:** 85  
**Mapping:** เหมือน Google Books Thai (ดู Section 1)

---

## 6. 🌍 CrossRef + OpenAlex (DOI)

**CrossRef API:** `https://api.crossref.org/works/{doi}` — Confidence: 98  
**OpenAlex API:** `https://api.openalex.org/works/doi:{doi}` — Confidence: 88  
**Mapping:** ดู Section 3 (CrossRef)

---

## 7. 🌐 Web Scraper (URL)

| ฟิลด์ในระบบ | ข้อมูลที่ดึงได้ |
|:---|:---|
| **ชื่อเรื่อง (title)** | `og:title` หรือ `<title>` |
| **ชื่อเว็บไซต์ (website_name)** | `og:site_name` |
| **ปีพิมพ์ (year)** | `article:published_time` |
| **URL** | ลิงก์ที่ผู้ใช้กรอก |

**API:** `{SITE_URL}/api/scraper/web.php?url={url}`  
**Confidence:** 75

---

## 8. ตาราง Source ทั้งหมด (เรียงตาม Priority)

| Source | ชื่อ | Layer | ภาษา | Confidence |
|:---|:---|:---|:---|:---|
| `google_books_th` | Google Books Thai | 🇹🇭 Thai | TH | **92** |
| `semantic_scholar` | Semantic Scholar | 🇹🇭 Thai | TH/EN | **90** |
| `crossref` | CrossRef (DOI) | 🌍 Global | EN | **98** |
| `openlibrary` | Open Library | 🌍 Global | EN | 88-95 |
| `google_books` | Google Books | 🌍 Global | EN | 85 |
| `openalex` | OpenAlex (DOI) | 🌍 Global | EN | 88 |
| `crossref_search` | CrossRef Keyword | 🌍 Global | TH/EN | 78 |
| `web` | Web Scraper | 🌐 | ทุกภาษา | 75 |

---

## 9. Rate Limiting & Caching

| การตั้งค่า | ค่า |
|:---|:---|
| Rate Limit | 30 requests / นาที (ต่อ IP) |
| Cache (File-based) | 5 นาที ต่อ query |
| Semantic Scholar Rate Limit | 1 req/sec |
| API Timeout ทั่วไป | 8 วินาที |

---

*ปรับปรุงล่าสุด: 27 กุมภาพันธ์ 2569 — v3 Thai-First Architecture*
