/**
 * Babybib APA 7<sup>th</sup> Bibliography Formatter
 * ====================================
 * Contains all format functions for APA 7<sup>th</sup> Edition
 */

// Format author string for bibliography (APA 7<sup>th</sup>)
function formatAuthorsBibAPA7(authors, lang, isEditor = false) {
    if (authors.length === 0) return '';

    if (lang === 'th') {
        // Thai: ชื่อ นามสกุล format
        const names = authors.map(a => {
            if (a.type === 'organization' || a.type === 'anonymous' || a.type === 'pseudonym') return a.display;
            if (a.type === 'editor') return a.display + ' (บ.ก.)';
            return a.display;
        });

        if (names.length === 1) return names[0];
        if (names.length === 2) return `${names[0]} และ ${names[1]}`;
        if (names.length <= 20) {
            // Thai APA: Usually no comma before "และ"
            return names.slice(0, -1).join(', ') + ' และ ' + names[names.length - 1];
        }
        return names.slice(0, 19).join(', ') + ' ... ' + names[names.length - 1];
    } else {
        // English: Lastname, F. M. format
        const names = authors.map((a, idx) => {
            if (a.type === 'organization' || a.type === 'anonymous' || a.type === 'pseudonym') return a.display;
            const last = a.last ? a.last.charAt(0).toUpperCase() + a.last.slice(1) : '';
            const f = a.first ? a.first.charAt(0).toUpperCase() + '.' : '';
            const m = a.middle ? ' ' + a.middle.charAt(0).toUpperCase() + '.' : '';
            let name = last ? `${last}, ${f}${m}`.trim() : a.display;

            // If the whole list is editors, we handle it outside or add (Ed.) individually
            // But per author it's usually (Ed.)
            if (a.type === 'editor' || isEditor) {
                const suffix = authors.length > 1 ? ' (Eds.)' : ' (Ed.)';
                // Only add suffix once or for each? APA says: Editor, A. A., & Editor, B. B. (Eds.).
                // So if it's the last author, we add the suffix.
                if (idx === authors.length - 1) name += suffix;
            }
            return name;
        });

        if (names.length === 1) return names[0];
        if (names.length === 2) return `${names[0]}, & ${names[1]}`;
        if (names.length <= 20) {
            return names.slice(0, -1).join(', ') + ', & ' + names[names.length - 1];
        }
        return names.slice(0, 19).join(', ') + ', ... ' + names[names.length - 1];
    }
}

// Format edition string
function formatEditionAPA7(edition, lang) {
    if (!edition) return '';
    if (lang === 'th') return ` (พิมพ์ครั้งที่ ${edition})`;

    // English ordinal suffix logic
    const ed = parseInt(edition);
    if (isNaN(ed)) return ` (${edition})`; // Handle "Rev." etc.

    let suffix = 'th';
    const lastDigit = ed % 10;
    const lastTwoDigits = ed % 100;

    if (lastDigit === 1 && lastTwoDigits !== 11) suffix = 'st';
    else if (lastDigit === 2 && lastTwoDigits !== 12) suffix = 'nd';
    else if (lastDigit === 3 && lastTwoDigits !== 13) suffix = 'rd';

    return ` (${ed}${suffix} ed.)`;
}

// Format date string
function formatDateAPA7(year, month, day) {
    if (!month) return `(${year})`;
    if (!day) return `(${year}, ${month})`;
    return `(${year}, ${month} ${day})`;
}

// Format DOI
function formatDoiAPA7(doi) {
    if (!doi) return '';
    return doi.includes('doi.org') ? doi : `https://doi.org/${doi.replace('doi:', '').trim()}`;
}

// Helper to format edition number without parentheses
function formatEditionNumberOnlyAPA7(edition, lang) {
    if (!edition) return '';
    if (lang === 'th') return `พิมพ์ครั้งที่ ${edition}`;

    const ed = parseInt(edition);
    if (isNaN(ed)) return edition;

    let suffix = 'th';
    const lastDigit = ed % 10;
    const lastTwoDigits = ed % 100;

    if (lastDigit === 1 && lastTwoDigits !== 11) suffix = 'st';
    else if (lastDigit === 2 && lastTwoDigits !== 12) suffix = 'nd';
    else if (lastDigit === 3 && lastTwoDigits !== 13) suffix = 'rd';

    return `${ed}${suffix} ed.`;
}

// Format degree type
function formatDegreeAPA7(degreeType, lang) {
    const types = {
        'bachelor': lang === 'th' ? 'ปริญญาบัณฑิต' : "Bachelor's",
        'master': lang === 'th' ? 'ปริญญามหาบัณฑิต' : "Master's",
        'doctoral': lang === 'th' ? 'ปริญญาดุษฎีบัณฑิต' : 'Doctoral'
    };
    return types[degreeType] || (lang === 'th' ? 'ปริญญามหาบัณฑิต' : "Master's");
}

// ===== FORMAT FUNCTIONS BY RESOURCE TYPE =====

function formatBookAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อหนังสือ (ครั้งที่พิมพ์). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        // Prevent double period if authorStr ends with .
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        // No author - Title moves to author position
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;

    // If author was present, title comes here
    if (authorStr) {
        bib += `<i>${title}</i>`;
        bib += formatEditionAPA7(data.edition, lang);
        bib += '. ';
    } else {
        // If title moved to author, we might still have edition
        const ed = formatEditionAPA7(data.edition, lang);
        if (ed) bib += ed + '. ';
    }

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub;
    }
    return bib;
}

function formatBookSeriesAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อหนังสือ (พิมพ์ครั้งที่, เล่มที่). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i>`;
    }

    // Combine Edition and Volume in same parentheses
    if (data.edition || data.volume) {
        bib += ' (';
        let parts = [];
        if (data.edition) parts.push(formatEditionNumberOnlyAPA7(data.edition, lang));
        if (data.volume) parts.push(lang === 'th' ? `เล่มที่ ${data.volume}` : `Vol. ${data.volume}`);
        bib += parts.join(', ');
        bib += ')';
    }

    bib += '. ';

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub;
    }
    return bib;
}

function formatBookChapterAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อบท. ใน บรรณาธิการ (บ.ก.), ชื่อหนังสือ (พิมพ์ครั้งที่, เล่มที่, หน้า xx-xx). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const chapterTitle = data.chapter_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${chapterTitle}. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `${chapterTitle}. `;
    }

    bib += lang === 'th' ? 'ใน ' : 'In ';
    if (data.editors) bib += `${data.editors} ${lang === 'th' ? '(บ.ก.), ' : '(Ed.), '}`;
    bib += `<i>${data.book_title || ''}</i>`;

    // Combine Edition, Volume, and Pages
    if (data.edition || data.volume || data.pages) {
        bib += ' (';
        let parts = [];
        if (data.edition) parts.push(formatEditionNumberOnlyAPA7(data.edition, lang));
        if (data.volume) parts.push(lang === 'th' ? `เล่มที่ ${data.volume}` : `Vol. ${data.volume}`);
        if (data.pages) parts.push(lang === 'th' ? `หน้า ${data.pages}` : `pp. ${data.pages}`);
        bib += parts.join(', ');
        bib += ')';
    }

    bib += '. ';

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub;
    }
    return bib;
}

function formatEbookDoiAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i>`;
        bib += formatEditionAPA7(data.edition, lang);
        bib += '. ';
    } else {
        const ed = formatEditionAPA7(data.edition, lang);
        if (ed) bib += ed + '. ';
    }

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub + ' ';
    }
    if (data.doi) bib += formatDoiAPA7(data.doi);
    return bib;
}

function formatEbookNoDoiAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i>`;
        bib += formatEditionAPA7(data.edition, lang);
        bib += '. ';
    } else {
        const ed = formatEditionAPA7(data.edition, lang);
        if (ed) bib += ed + '. ';
    }

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub + ' ';
    }
    if (data.url) bib += data.url;
    return bib;
}

function formatJournalArticleAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อบทความ. ชื่อวารสาร, ปีที่(ฉบับที่), หน้า.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const articleTitle = data.article_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${articleTitle}. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `${articleTitle}. `;
    }

    bib += `<i>${data.journal_name || ''}</i>`;
    if (data.volume) bib += `, <i>${data.volume}</i>`;
    if (data.issue) bib += `(${data.issue})`;
    if (data.pages) bib += `, ${data.pages}`;
    bib += '.';
    return bib;
}

function formatEjournalDoiAPA7(data, authorStr, lang) {
    let bib = formatJournalArticleAPA7(data, authorStr, lang);
    bib += ' '; // Keep the period from formatJournalArticleAPA7
    if (data.doi) bib += formatDoiAPA7(data.doi);
    return bib;
}

function formatEjournalUrlAPA7(data, authorStr, lang) {
    let bib = formatJournalArticleAPA7(data, authorStr, lang);
    bib += ' '; // Keep the period from formatJournalArticleAPA7
    if (data.url) bib += data.url;
    return bib;
}

function formatThesisUnpublishedAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อวิทยานิพนธ์ [วิทยานิพนธ์ปริญญาXXไม่ได้ตีพิมพ์]. มหาวิทยาลัย.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const degree = formatDegreeAPA7(data.degree_type, lang);
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i> `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i> `;
    }

    if (lang === 'th') {
        bib += `[วิทยานิพนธ์${degree}ไม่ได้ตีพิมพ์]. `;
    } else {
        bib += `[Unpublished ${degree.toLowerCase()} thesis]. `;
    }
    if (data.institution) {
        const cleanInst = data.institution.endsWith('.') ? data.institution : data.institution + '.';
        bib += cleanInst;
    }
    return bib;
}

function formatThesisWebsiteAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const degree = formatDegreeAPA7(data.degree_type, lang);
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i> `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i> `;
    }

    if (lang === 'th') {
        bib += `[วิทยานิพนธ์${degree}, ${data.institution || ''}]. `;
    } else {
        bib += `[${degree} thesis, ${data.institution || ''}]. `;
    }
    if (data.url) bib += data.url;
    return bib;
}

function formatThesisDatabaseAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const degree = formatDegreeAPA7(data.degree_type, lang);
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i> `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i> `;
    }

    if (lang === 'th') {
        bib += `[วิทยานิพนธ์${degree}, ${data.institution || ''}]. `;
    } else {
        bib += `[${degree} thesis, ${data.institution || ''}]. `;
    }
    if (data.database_name) bib += `${data.database_name}. `;
    if (data.accession_number) bib += `(${data.accession_number})`;
    return bib;
}

// ===== DICTIONARIES / ENCYCLOPEDIAS =====

function formatDictionaryAPA7(data, lang) {
    // ชื่อพจนานุกรม. (ปี). (พิมพ์ครั้งที่). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    bib += `<i>${title}</i>. `;
    bib += `(${year}). `;

    if (data.edition) bib += ` (${formatEditionNumberOnlyAPA7(data.edition, lang)}). `;

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub;
    }
    return bib;
}

function formatDictionaryOnlineAPA7(data, lang) {
    // คำศัพท์. (ปี). ใน ชื่อพจนานุกรม. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const entryWord = data.entry_word || data.title || '';

    bib += `${entryWord}. `;
    bib += `(${year}). `;
    bib += lang === 'th' ? 'ใน ' : 'In ';
    bib += `<i>${data.dictionary_name || ''}</i>. `;

    if (data.accessed_date) {
        bib += lang === 'th' ? `สืบค้น ${data.accessed_date}, จาก ` : `Retrieved ${data.accessed_date}, from `;
    }

    if (data.url) bib += data.url;
    return bib;
}

function formatEncyclopediaAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อบทความ. ใน ชื่อสารานุกรม (เล่มที่, หน้า). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const entryTitle = data.entry_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${entryTitle}. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `${entryTitle}. `;
    }

    bib += lang === 'th' ? 'ใน ' : 'In ';
    bib += `<i>${data.encyclopedia_name || ''}</i>`;

    if (data.volume || data.pages) {
        bib += ' (';
        let parts = [];
        if (data.volume) parts.push(lang === 'th' ? `เล่มที่ ${data.volume}` : `Vol. ${data.volume}`);
        if (data.pages) parts.push(lang === 'th' ? `หน้า ${data.pages}` : `pp. ${data.pages}`);
        bib += parts.join(', ');
        bib += ')';
    }

    bib += '. ';

    if (data.publisher) {
        const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
        bib += cleanPub;
    }
    return bib;
}

function formatEncyclopediaOnlineAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const entryTitle = data.entry_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${entryTitle}. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `${entryTitle}. `;
    }

    bib += lang === 'th' ? 'ใน ' : 'In ';
    bib += `<i>${data.encyclopedia_name || ''}</i>. `;

    if (data.accessed_date) {
        bib += lang === 'th' ? `สืบค้น ${data.accessed_date}, จาก ` : `Retrieved ${data.accessed_date}, from `;
    }

    if (data.url) bib += data.url;
    return bib;
}

// ===== REPORTS =====

// ===== CONFERENCES =====

function formatConferenceAPA7(data, authorStr, lang, type) {
    // type: 'published' (proceeding) or 'presentation' (no proceeding)
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const paperTitle = data.paper_title || data.presentation_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        if (type === 'published') {
            bib += `${paperTitle}. `;
        } else {
            bib += `<i>${paperTitle}</i>. `;
        }
    }

    bib += formatDateAPA7(year, data.month, data.day) + '. ';

    if (type === 'published') {
        if (authorStr) bib += `${paperTitle}. `;
        bib += lang === 'th' ? 'ใน ' : 'In ';
        if (data.editors) bib += `${data.editors} ${lang === 'th' ? '(บ.ก.), ' : '(Ed.), '}`;
        bib += `<i>${data.proceeding_title || ''}</i>`;

        if (data.pages) {
            const prefix = lang === 'th' ? 'หน้า' : (data.pages.includes('-') || data.pages.includes(',') ? 'pp.' : 'p.');
            bib += ` (${prefix} ${data.pages})`;
        }
        bib += '. ';
        if (data.publisher) {
            const cleanPub = data.publisher.endsWith('.') ? data.publisher : data.publisher + '.';
            bib += cleanPub;
        }
    } else {
        const signifier = type === 'paper' ? (lang === 'th' ? '[การนำเสนอบทความ]' : '[Paper presentation]') : `[${data.presentation_type || (lang === 'th' ? 'โปสเตอร์' : 'Poster')}]`;

        if (authorStr) bib += `<i>${paperTitle}</i> `;
        bib += signifier + '. ';
        bib += `${data.conference_name || data.proceeding_title || ''}`;
        if (data.location) bib += `, ${data.location}`;
        bib += '.';
    }
    return bib;
}

function formatReportAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อเรื่อง (เลขที่รายงาน). หน่วยงาน. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;

    if (authorStr) {
        bib += `<i>${title}</i>`;
    }

    if (data.report_number) {
        bib += ` (${data.report_number})`;
    }

    bib += '. ';

    // If author and institution/organization are different, include it
    const pub = data.institution || data.organization;
    if (pub) {
        // Simple check to avoid redundant publisher if same as author
        if (!authorStr || !authorStr.toLowerCase().includes(pub.toLowerCase())) {
            const cleanPub = pub.endsWith('.') ? pub : pub + '.';
            bib += cleanPub;
            if (data.url) bib += ' ';
        }
    }

    if (data.url) bib += data.url;
    return bib;
}

function formatWebpageAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี, เดือน วัน). ชื่อบทความ. ชื่อเว็บไซต์. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.page_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += formatDateAPA7(year, data.month, data.day) + '. ';

    if (authorStr) {
        bib += `<i>${title}</i>. `;
    }

    // Omit website name if same as author (optional but common in APA 7<sup>th</sup>)
    if (data.website_name) {
        if (!authorStr || !authorStr.toLowerCase().includes(data.website_name.toLowerCase())) {
            const cleanWeb = data.website_name.endsWith('.') ? data.website_name : data.website_name + '.';
            bib += cleanWeb + ' ';
        }
    }
    if (data.url) bib += data.url;
    return bib;
}

function formatSocialMediaAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี, เดือน วัน). ชื่อเรื่อง [Platform]. Platform name. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.content_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += formatDateAPA7(year, data.month, data.day) + '. ';

    if (authorStr) {
        bib += `<i>${title}</i> `;
    }

    if (data.platform) {
        bib += `[${data.platform}]. `;
        // Only add platform name if it's not the same as author
        if (!authorStr || !authorStr.toLowerCase().includes(data.platform.toLowerCase())) {
            bib += data.platform + '. ';
        }
    }

    if (data.url) bib += data.url;
    return bib;
}

function formatNewspaperPrintAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.article_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${title}. `;
    }

    bib += formatDateAPA7(year, data.month, data.day) + '. ';

    if (authorStr) {
        bib += `${title}. `;
    }

    bib += `<i>${data.newspaper_name || ''}</i>`;
    if (data.pages) {
        if (lang === 'th') {
            bib += `, หน้า ${data.pages}`;
        } else {
            const prefix = data.pages.includes('-') || data.pages.includes(',') ? 'pp.' : 'p.';
            bib += `, ${prefix} ${data.pages}`;
        }
    }
    bib += '.';
    return bib;
}

function formatNewspaperOnlineAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.article_title || data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `${title}. `;
    }

    bib += formatDateAPA7(year, data.month, data.day) + '. ';

    if (authorStr) {
        bib += `${title}. `;
    }

    bib += `<i>${data.newspaper_name || ''}</i>. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatRoyalGazetteAPA7(data, lang) {
    // ชื่อกฎหมาย. (ปี, วัน เดือน). ราชกิจจานุเบกษา. เล่มที่ ตอนที่, หน้า.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    bib += `${data.title || ''}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += lang === 'th' ? '<i>ราชกิจจานุเบกษา</i>. ' : '<i>Royal Thai Government Gazette</i>. ';

    let parts = [];
    if (data.volume) parts.push(lang === 'th' ? `เล่ม ${data.volume}` : `Vol. ${data.volume}`);
    if (data.section) parts.push(lang === 'th' ? `ตอนที่ ${data.section}` : `Section ${data.section}`);
    bib += parts.join(' ');

    if (data.pages) {
        bib += lang === 'th' ? `, หน้า ${data.pages}` : `, pp. ${data.pages}`;
    }
    bib += '.';
    if (data.url) bib += ` ${data.url}`;
    return bib;
}

function formatPatentAPA7(data, lang) {
    // ผู้ประดิษฐ์. (ปี). ชื่อสิทธิบัตร (หมายเลขสิทธิบัตร). สำนักงานสิทธิบัตร. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (data.inventors) {
        const cleanInv = data.inventors.endsWith('.') ? data.inventors : data.inventors + '.';
        bib += cleanInv + ' ';
    }

    bib += `(${year}). `;
    bib += `<i>${data.patent_title || data.title || ''}</i>`;

    if (data.patent_number) {
        bib += ` (${data.patent_number})`;
    }
    bib += '. ';

    if (data.patent_office) {
        const cleanOffice = data.patent_office.endsWith('.') ? data.patent_office : data.patent_office + '.';
        bib += cleanOffice + ' ';
    }

    if (data.url) bib += data.url;
    return bib;
}

function formatYoutubeVideoAPA7(data, lang) {
    // ชื่อช่อง. (ปี, เดือน วัน). ชื่อวิดีโอ [วิดีโอ]. YouTube. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (data.channel_name) bib += `${data.channel_name}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `<i>${data.video_title || data.title || ''}</i> `;
    bib += lang === 'th' ? '[วิดีโอ]. YouTube. ' : '[Video]. YouTube. ';
    if (data.url) bib += data.url;
    return bib;
}

function formatInfographicAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;
    if (authorStr) bib += `<i>${title}</i> `;
    bib += lang === 'th' ? '[อินโฟกราฟิก]. ' : '[Infographic]. ';
    if (data.website_name) bib += `${data.website_name}. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatSlidesAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) {
        const cleanAuthor = authorStr.endsWith('.') ? authorStr : authorStr + '.';
        bib += cleanAuthor + ' ';
    } else {
        bib += `<i>${title}</i>. `;
    }

    bib += `(${year}). `;
    if (authorStr) bib += `<i>${title}</i> `;
    bib += lang === 'th' ? '[สไลด์]. ' : '[Slides]. ';
    if (data.platform) bib += `${data.platform}. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatWebinarAPA7(data, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    if (data.presenters) bib += `${data.presenters}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `<i>${data.webinar_title || data.title || ''}</i> [Webinar]. `;
    if (data.organization) bib += `${data.organization}. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatPodcastAPA7(data, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    if (data.host) bib += `${data.host}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `${data.episode_title || data.title || ''} `;
    bib += lang === 'th' ? '[ตอนพ็อดคาสท์]. ใน ' : '[Podcast episode]. In ';
    if (data.podcast_name) bib += `<i>${data.podcast_name}</i>. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatAIGeneratedAPA7(data, lang) {
    // ชื่อ AI (เวอร์ชัน). (ปี, เดือน วัน). คำอธิบายพรอมต์ [Large language model]. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (data.ai_name) bib += `${data.ai_name}`;
    if (data.version) bib += ` (${data.version})`;
    bib += '. ';
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `<i>${data.prompt_description || data.title || ''}</i> `;
    bib += '[Large language model]. ';
    if (data.url) bib += data.url;
    return bib;
}

// Format in-text citation (APA 7<sup>th</sup>) - Both Thai and English
function formatInTextCitationAPA7(authors, year, lang, title = '', resourceType = 'book') {
    let paren = '';
    let narr = '';

    const yearText = year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authors.length > 0) {
        // Get author names based on type and language
        const getAuthorName = (author, isForNarrative = false) => {
            if (author.type === 'organization' || author.type === 'anonymous' || author.type === 'pseudonym') {
                return author.display;
            }
            if (lang === 'th') {
                // Thai: Use full display name (ชื่อ นามสกุล)
                return author.display;
            } else {
                // English: Use last name only
                return author.last || author.display;
            }
        };

        const name1 = getAuthorName(authors[0]);

        if (lang === 'th') {
            // === Thai Format ===
            if (authors.length === 1) {
                // 1 author: (ชื่อ นามสกุล, ปี) / ชื่อ นามสกุล (ปี)
                paren = `(${name1}, ${yearText})`;
                narr = `${name1} (${yearText})`;
            } else if (authors.length === 2) {
                // 2 authors: (ชื่อ1 นามสกุล1 และ ชื่อ2 นามสกุล2, ปี)
                const name2 = getAuthorName(authors[1]);
                paren = `(${name1} และ ${name2}, ${yearText})`;
                narr = `${name1} และ ${name2} (${yearText})`;
            } else {
                // 3+ authors: (ชื่อ1 นามสกุล1 และคณะ, ปี)
                paren = `(${name1} และคณะ, ${yearText})`;
                narr = `${name1} และคณะ (${yearText})`;
            }
        } else {
            // === English Format ===
            if (authors.length === 1) {
                // 1 author: (LastName, Year) / LastName (Year)
                paren = `(${name1}, ${yearText})`;
                narr = `${name1} (${yearText})`;
            } else if (authors.length === 2) {
                // 2 authors: (LastName1 & LastName2, Year) / LastName1 and LastName2 (Year)
                const name2 = getAuthorName(authors[1]);
                paren = `(${name1} & ${name2}, ${yearText})`;
                narr = `${name1} and ${name2} (${yearText})`;
            } else {
                // 3+ authors: (LastName1 et al., Year) / LastName1 et al. (Year)
                paren = `(${name1} et al., ${yearText})`;
                narr = `${name1} et al. (${yearText})`;
            }
        }
    } else if (title) {
        // No author - use title
        // APA 7<sup>th</sup>: Use first few words of title in quotes (for articles) or italics (for books)
        let shortTitle = title.length > 40 ? title.substring(0, 37) + '...' : title;

        // Resource types that are "stand-alone" (italics)
        const isStandAlone = ['book', 'book_series', 'ebook_doi', 'ebook_no_doi', 'report', 'research_report', 'government_report', 'institutional_report', 'thesis_unpublished', 'thesis_website', 'thesis_database', 'dictionary', 'youtube_video', 'podcast', 'social_media', 'royal_gazette'].includes(resourceType);

        if (isStandAlone) {
            paren = `(<i>${shortTitle}</i>, ${yearText})`;
            narr = `<i>${shortTitle}</i> (${yearText})`;
        } else {
            // Part of a whole (quotes) - Includes dictionary/encyclopedia entries
            if (lang === 'th') {
                paren = `("${shortTitle}", ${yearText})`;
                narr = `"${shortTitle}" (${yearText})`;
            } else {
                paren = `("${shortTitle}," ${yearText})`;
                narr = `"${shortTitle}" (${yearText})`;
            }
        }
    } else {
        // No author, no title - anonymous
        if (lang === 'th') {
            paren = `(ไม่ระบุผู้แต่ง, ${yearText})`;
            narr = `ไม่ระบุผู้แต่ง (${yearText})`;
        } else {
            paren = `(Anonymous, ${yearText})`;
            narr = `Anonymous (${yearText})`;
        }
    }

    return { parenthetical: paren, narrative: narr };
}

// Format citation with page number (for direct quotes)
function formatInTextCitationWithPageAPA7(authors, year, lang, page, title = '') {
    const citation = formatInTextCitationAPA7(authors, year, lang, title);

    if (!page) return citation;

    const pageLabel = lang === 'th' ? 'น.' : 'p.';

    // Insert page before closing parenthesis
    const parenWithPage = citation.parenthetical.replace(')', `, ${pageLabel} ${page})`);

    return {
        parenthetical: parenWithPage,
        narrative: citation.narrative
    };
}

// Get author display for first citation (all authors up to certain limit)
function formatFirstCitationAPA7(authors, year, lang) {
    const yearText = year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authors.length === 0) {
        return formatInTextCitationAPA7(authors, year, lang);
    }

    const getAuthorName = (author) => {
        if (author.type === 'organization' || author.type === 'anonymous' || author.type === 'pseudonym') {
            return author.display;
        }
        return lang === 'th' ? author.display : (author.last || author.display);
    };

    if (authors.length <= 2) {
        // Same as regular citation for 1-2 authors
        return formatInTextCitationAPA7(authors, year, lang);
    }

    // For 3+ authors, APA 7<sup>th</sup> now uses et al. from first citation
    // But we can provide option to show all if needed
    const name1 = getAuthorName(authors[0]);

    if (lang === 'th') {
        return {
            parenthetical: `(${name1} และคณะ, ${yearText})`,
            narrative: `${name1} และคณะ (${yearText})`
        };
    } else {
        return {
            parenthetical: `(${name1} et al., ${yearText})`,
            narrative: `${name1} et al. (${yearText})`
        };
    }
}

// Wrapper function for parenthetical citation (used by generate.php)
function formatParentheticalAPA7(authors, year, lang, title = '', resourceType = 'book') {
    const citation = formatInTextCitationAPA7(authors, year, lang, title, resourceType);
    return citation.parenthetical;
}

// Wrapper function for narrative citation (used by generate.php)
function formatNarrativeAPA7(authors, year, lang, title = '', resourceType = 'book') {
    const citation = formatInTextCitationAPA7(authors, year, lang, title, resourceType);
    return citation.narrative;
}
