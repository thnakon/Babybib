/**
 * Babybib APA 7 Bibliography Formatter
 * ====================================
 * Contains all format functions for APA 7th Edition
 */

// Format author string for bibliography (APA 7)
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
            return names.slice(0, -1).join(', ') + ', และ ' + names[names.length - 1];
        }
        return names.slice(0, 19).join(', ') + ', ... ' + names[names.length - 1];
    } else {
        // English: Lastname, F. M. format
        const names = authors.map(a => {
            if (a.type === 'organization' || a.type === 'anonymous' || a.type === 'pseudonym') return a.display;
            const last = a.last || '';
            const f = a.first ? a.first.charAt(0).toUpperCase() + '.' : '';
            const m = a.middle ? ' ' + a.middle.charAt(0).toUpperCase() + '.' : '';
            let name = last ? `${last}, ${f}${m}`.trim() : a.display;
            if (a.type === 'editor' || isEditor) name += ' (Ed.)';
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
    const suffix = edition === '1' ? 'st' : edition === '2' ? 'nd' : edition === '3' ? 'rd' : 'th';
    return ` (${edition}${suffix} ed.)`;
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

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${title}</i>`;
    bib += formatEditionAPA7(data.edition, lang);
    bib += '. ';
    if (data.publisher) bib += `${data.publisher}.`;
    return bib;
}

function formatBookSeriesAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อหนังสือ (เล่มที่ X) (ครั้งที่พิมพ์). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const title = data.title || '';

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${title}</i>`;
    if (data.volume) bib += lang === 'th' ? ` (เล่มที่ ${data.volume})` : ` (Vol. ${data.volume})`;
    bib += formatEditionAPA7(data.edition, lang);
    bib += '. ';
    if (data.publisher) bib += `${data.publisher}.`;
    return bib;
}

function formatBookChapterAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อบท. ใน บรรณาธิการ (บ.ก.), ชื่อหนังสือ (หน้า xx-xx). สำนักพิมพ์.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `${data.chapter_title || data.title || ''}. `;
    bib += lang === 'th' ? 'ใน ' : 'In ';
    if (data.editors) bib += `${data.editors} ${lang === 'th' ? '(บ.ก.), ' : '(Ed.), '}`;
    bib += `<i>${data.book_title || ''}</i>`;
    if (data.pages) bib += lang === 'th' ? ` (หน้า ${data.pages})` : ` (pp. ${data.pages})`;
    bib += '. ';
    if (data.publisher) bib += `${data.publisher}.`;
    return bib;
}

function formatEbookDoiAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${data.title || ''}</i>`;
    bib += formatEditionAPA7(data.edition, lang);
    bib += '. ';
    if (data.publisher) bib += `${data.publisher}. `;
    if (data.doi) bib += formatDoiAPA7(data.doi);
    return bib;
}

function formatEbookNoDoiAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${data.title || ''}</i>`;
    bib += formatEditionAPA7(data.edition, lang);
    bib += '. ';
    if (data.publisher) bib += `${data.publisher}. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatJournalArticleAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อบทความ. ชื่อวารสาร, ปีที่(ฉบับที่), หน้า.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `${data.article_title || data.title || ''}. `;
    bib += `<i>${data.journal_name || ''}</i>`;
    if (data.volume) bib += `, <i>${data.volume}</i>`;
    if (data.issue) bib += `(${data.issue})`;
    if (data.pages) bib += `, ${data.pages}`;
    bib += '.';
    return bib;
}

function formatEjournalDoiAPA7(data, authorStr, lang) {
    let bib = formatJournalArticleAPA7(data, authorStr, lang);
    bib = bib.slice(0, -1); // Remove last period
    bib += ' ';
    if (data.doi) bib += formatDoiAPA7(data.doi);
    return bib;
}

function formatEjournalUrlAPA7(data, authorStr, lang) {
    let bib = formatJournalArticleAPA7(data, authorStr, lang);
    bib = bib.slice(0, -1); // Remove last period
    bib += ' ';
    if (data.url) bib += data.url;
    return bib;
}

function formatThesisUnpublishedAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี). ชื่อวิทยานิพนธ์ [วิทยานิพนธ์ปริญญาXXไม่ได้ตีพิมพ์]. มหาวิทยาลัย.
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const degree = formatDegreeAPA7(data.degree_type, lang);

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${data.title || ''}</i> `;
    if (lang === 'th') {
        bib += `[วิทยานิพนธ์${degree}ไม่ได้ตีพิมพ์]. `;
    } else {
        bib += `[Unpublished ${degree.toLowerCase()} thesis]. `;
    }
    if (data.institution) bib += `${data.institution}.`;
    return bib;
}

function formatThesisWebsiteAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');
    const degree = formatDegreeAPA7(data.degree_type, lang);

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${data.title || ''}</i> `;
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

    if (authorStr) bib += `${authorStr}. `;
    bib += `(${year}). `;
    bib += `<i>${data.title || ''}</i> `;
    if (lang === 'th') {
        bib += `[วิทยานิพนธ์${degree}, ${data.institution || ''}]. `;
    } else {
        bib += `[${degree} thesis, ${data.institution || ''}]. `;
    }
    if (data.database_name) bib += `${data.database_name}. `;
    if (data.accession_number) bib += `(${data.accession_number})`;
    return bib;
}

function formatWebpageAPA7(data, authorStr, lang) {
    // ผู้แต่ง. (ปี, เดือน วัน). ชื่อบทความ. ชื่อเว็บไซต์. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `<i>${data.page_title || data.title || ''}</i>. `;
    if (data.website_name) bib += `${data.website_name}. `;
    if (data.url) bib += data.url;
    return bib;
}

function formatNewspaperPrintAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `${data.article_title || data.title || ''}. `;
    bib += `<i>${data.newspaper_name || ''}</i>`;
    if (data.pages) bib += lang === 'th' ? `, หน้า ${data.pages}` : `, p. ${data.pages}`;
    bib += '.';
    return bib;
}

function formatNewspaperOnlineAPA7(data, authorStr, lang) {
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (authorStr) bib += `${authorStr}. `;
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `${data.article_title || data.title || ''}. `;
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
    if (data.volume) bib += lang === 'th' ? `เล่ม ${data.volume}` : `Vol. ${data.volume}`;
    if (data.section) bib += lang === 'th' ? ` ตอนที่ ${data.section}` : ` Section ${data.section}`;
    if (data.pages) bib += lang === 'th' ? `, หน้า ${data.pages}` : `, pp. ${data.pages}`;
    bib += '.';
    if (data.url) bib += ` ${data.url}`;
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

function formatAIGeneratedAPA7(data, lang) {
    // ชื่อ AI (เวอร์ชัน). (ปี, เดือน วัน). คำอธิบายพรอมต์ [Large language model]. URL
    let bib = '';
    const year = data.year || (lang === 'th' ? 'ม.ป.ป.' : 'n.d.');

    if (data.ai_name) bib += `${data.ai_name}`;
    if (data.version) bib += ` (${data.version})`;
    bib += '. ';
    bib += formatDateAPA7(year, data.month, data.day) + '. ';
    bib += `${data.prompt_description || data.title || ''} `;
    bib += '[Large language model]. ';
    if (data.url) bib += data.url;
    return bib;
}

// Format in-text citation (APA 7) - Both Thai and English
function formatInTextCitationAPA7(authors, year, lang, title = '') {
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
                // 1 author: (นามสกุล, ปี) / นามสกุล (ปี)
                paren = `(${name1}, ${yearText})`;
                narr = `${name1} (${yearText})`;
            } else if (authors.length === 2) {
                // 2 authors: (นามสกุล1 และ นามสกุล2, ปี) / นามสกุล1 และ นามสกุล2 (ปี)
                const name2 = getAuthorName(authors[1]);
                paren = `(${name1} และ ${name2}, ${yearText})`;
                narr = `${name1} และ ${name2} (${yearText})`;
            } else {
                // 3+ authors: (นามสกุล1 และคณะ, ปี) / นามสกุล1 และคณะ (ปี)
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
        // APA 7: Use first few words of title in quotes (for articles) or italics (for books)
        const shortTitle = title.length > 30 ? title.substring(0, 27) + '...' : title;

        if (lang === 'th') {
            paren = `("${shortTitle}", ${yearText})`;
            narr = `"${shortTitle}" (${yearText})`;
        } else {
            paren = `("${shortTitle}," ${yearText})`;
            narr = `"${shortTitle}" (${yearText})`;
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

    // For 3+ authors, APA 7 now uses et al. from first citation
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
function formatParentheticalAPA7(authors, year, lang, title = '') {
    const citation = formatInTextCitationAPA7(authors, year, lang, title);
    return citation.parenthetical;
}

// Wrapper function for narrative citation (used by generate.php)
function formatNarrativeAPA7(authors, year, lang, title = '') {
    const citation = formatInTextCitationAPA7(authors, year, lang, title);
    return citation.narrative;
}
