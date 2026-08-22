/**
 * generate-book-entries.js
 * Scans public/books/**\/*.pdf and writes src/content/books/<slug>.md
 * for each PDF that doesn't already have a content entry.
 *
 * Usage:
 *   node scripts/generate-book-entries.js          (dry run)
 *   node scripts/generate-book-entries.js --apply  (write files)
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const booksPublicDir = path.join(root, 'public', 'books');
const booksContentDir = path.join(root, 'src', 'content', 'books');

// ── Author map: folder slug → human-readable author name ──────────────────
const authorMap = {
  '1843-prophecy-chart': 'Pioneer Prophetic Charts',
  '1850-prophecy-chart': 'Pioneer Prophetic Charts',
  '1904-image-of-papacy': 'SDA Corporate History',
  '1904-image-of-papacy/trinity': 'Trinitarian Controversy',
  '3angels': "Three Angels' Messages",
  'adventist-history': 'Adventist History',
  'alonzo-trevier-jones': 'Alonzo Trevier Jones',
  'apocrypha': 'Apocryphal Writings',
  'bible': 'Holy Bible',
  'charles-fitch': 'Charles Fitch',
  'charles-s-longacre': 'Charles S. Longacre',
  'ellen-white': 'Ellen G. White',
  'james-white': 'James White',
  'john-loughborough-rise-and-progress-of-seventh-day-adventists-1892': 'John Norton Loughborough',
  'john-nevins-andrews': 'John Nevins Andrews',
  'john-norton-loughborough': 'John Norton Loughborough',
  'joseph-bates': 'Joseph Bates',
  'judson-sylvanus-washburn': 'Judson Sylvanus Washburn',
  'otis-nichols': 'Otis Nichols',
  'pdf/1872': 'Historical Documents',
  'prophecy-charts': 'Prophetic Charts',
  'religious-liberty': 'Religious Liberty',
  'sylvester-bliss': 'Sylvester Bliss',
  'william-ings': 'William Ings',
  'william-miller': 'William Miller',
  'william-tyndale': 'William Tyndale',
};

// ── Importance blurbs per folder ───────────────────────────────────────────
function getImportance(folderSlug) {
  if (folderSlug.startsWith('ellen-white'))
    return 'Essential prophetic and theological writings of the Advent movement.';
  if (folderSlug.startsWith('joseph-bates'))
    return 'Foundational Sabbath and advent writings by Captain Joseph Bates.';
  if (folderSlug.startsWith('william-miller'))
    return 'Original Millerite advent movement writings and resources.';
  if (folderSlug.startsWith('james-white'))
    return 'Foundational Adventist writings by James White, co-founder of the SDA church.';
  if (folderSlug.startsWith('charles-fitch'))
    return 'Abolitionist and Millerite minister whose preaching shaped the Advent reformation.';
  if (folderSlug.startsWith('john-norton-loughborough') || folderSlug.startsWith('john-loughborough'))
    return 'Eyewitness history of the Advent movement by one of its earliest ministers.';
  if (folderSlug.startsWith('john-nevins-andrews'))
    return 'Scholarly defense of the Seventh-day Sabbath and prophetic truth.';
  if (folderSlug.startsWith('1843-prophecy-chart') || folderSlug.startsWith('1850-prophecy-chart') || folderSlug.startsWith('prophecy-charts'))
    return 'Original prophetic charts used by Millerite and early Adventist preachers.';
  if (folderSlug.startsWith('1904-image-of-papacy/trinity'))
    return 'Documents tracing the post-1844 introduction of trinitarian doctrine into Adventism.';
  if (folderSlug.startsWith('1904-image-of-papacy'))
    return 'Documents tracing the corporate transformation of the SDA church after 1904.';
  if (folderSlug.startsWith('religious-liberty'))
    return 'Historic documents defending religious liberty and opposing Sunday legislation.';
  if (folderSlug.startsWith('3angels'))
    return "Exposition of the Three Angels' Messages of Revelation 14.";
  if (folderSlug.startsWith('apocrypha'))
    return 'Apocryphal writings preserved in the Christian tradition.';
  if (folderSlug.startsWith('adventist-history'))
    return 'Historical documentation of the Adventist movement.';
  if (folderSlug.startsWith('pdf/1872'))
    return 'Foundational 1872 Adventist doctrinal statements and historical documents.';
  if (folderSlug.startsWith('sylvester-bliss'))
    return 'Biography and historical writings on William Miller and the Millerite movement.';
  if (folderSlug.startsWith('william-tyndale'))
    return 'Reformation-era writings on the English Bible and religious freedom.';
  if (folderSlug.startsWith('alonzo-trevier-jones'))
    return 'Landmark writings on Sunday law, religious liberty, and prophetic fulfillment.';
  if (folderSlug.startsWith('otis-nichols'))
    return 'Prophetic chart resources from Otis Nichols, early Adventist illustrator.';
  if (folderSlug.startsWith('judson-sylvanus-washburn'))
    return 'Historical writings by Judson Sylvanus Washburn on Adventist doctrine.';
  if (folderSlug.startsWith('charles-s-longacre'))
    return 'Religious liberty and Christology writings by Charles S. Longacre.';
  if (folderSlug.startsWith('william-ings'))
    return 'Advent movement writings by William Ings.';
  if (folderSlug.startsWith('bible'))
    return 'The Holy Bible, foundational Scripture for all prophetic study.';
  return 'Historical Adventist pioneer document.';
}

// ── Title from filename ────────────────────────────────────────────────────
function toTitle(filename) {
  let s = filename.replace(/\.pdf$/i, '');
  // Split camelCase
  s = s.replace(/([a-z])([A-Z])/g, '$1 $2');
  s = s.replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2');
  // Underscores and dashes → spaces (but preserve leading years like 1850_)
  s = s.replace(/[_]+/g, ' ').replace(/-+/g, ' ');
  // Normalize whitespace
  s = s.replace(/\s+/g, ' ').trim();
  // Title case (skip short prepositions/articles unless first word)
  const lower = new Set(['a', 'an', 'the', 'and', 'but', 'or', 'for', 'of', 'in', 'on', 'to', 'by', 'at']);
  return s.replace(/\b\w+/g, (word, offset) => {
    if (offset > 0 && lower.has(word.toLowerCase())) return word.toLowerCase();
    return word.charAt(0).toUpperCase() + word.slice(1);
  });
}

// ── Extract year from filename or folder ───────────────────────────────────
function extractYear(str) {
  const m = str.match(/\b(1[6-9]\d{2}|200\d)\b/);
  return m ? m[1] : '';
}

// ── Slug from folder + filename ────────────────────────────────────────────
function makeSlug(folderSlug, filename) {
  const base = filename.replace(/\.pdf$/i, '');
  return (folderSlug + '--' + base)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

// ── Description ────────────────────────────────────────────────────────────
function getDescription(title, author, folderSlug) {
  if (folderSlug.startsWith('ellen-white') && title.toLowerCase().includes('testimony'))
    return `${title} — one of Ellen G. White's personal testimonies addressed to the Adventist church.`;
  if (folderSlug.startsWith('1904-image-of-papacy'))
    return `${title} — a primary source document on the corporate and doctrinal changes in the SDA church.`;
  if (folderSlug.startsWith('1843-prophecy-chart') || folderSlug.startsWith('1850-prophecy-chart'))
    return `${title} — original prophetic chart used in Millerite and early Adventist evangelism.`;
  if (folderSlug.startsWith('religious-liberty'))
    return `${title} — a historical document on religious liberty and the separation of church and state.`;
  return `${title} by ${author}.`;
}

// ── Collect all PDFs under public/books ───────────────────────────────────
function collectPdfs(dir) {
  const results = [];
  const stack = [dir];
  while (stack.length) {
    const current = stack.pop();
    for (const entry of fs.readdirSync(current, { withFileTypes: true })) {
      const full = path.join(current, entry.name);
      if (entry.isDirectory()) {
        stack.push(full);
      } else if (entry.name.toLowerCase().endsWith('.pdf')) {
        const rel = path.relative(dir, full).replace(/\\/g, '/');
        results.push(rel);
      }
    }
  }
  return results.sort();
}

// ── Main ──────────────────────────────────────────────────────────────────
const apply = process.argv.includes('--apply');
const pdfs = collectPdfs(booksPublicDir);

let written = 0, skipped = 0;

for (const rel of pdfs) {
  const parts = rel.split('/');
  const filename = parts[parts.length - 1];
  const folderSlug = parts.slice(0, -1).join('/');

  const slug = makeSlug(folderSlug, filename);
  const mdPath = path.join(booksContentDir, slug + '.md');

  if (fs.existsSync(mdPath)) {
    skipped++;
    continue;
  }

  const author = authorMap[folderSlug] || folderSlug;
  const title = toTitle(filename);
  const year = extractYear(filename) || extractYear(folderSlug) || 'Unknown';
  const description = getDescription(title, author, folderSlug);
  const importance = getImportance(folderSlug);
  const link = `/books/${rel}`;

  const body = `${title} — available as a downloadable PDF.`;

  const content = [
    '---',
    `title: ${JSON.stringify(title)}`,
    `author: ${JSON.stringify(author)}`,
    `published: ${JSON.stringify(year)}`,
    `description: ${JSON.stringify(description)}`,
    `importance: ${JSON.stringify(importance)}`,
    `link: ${JSON.stringify(link)}`,
    '---',
    '',
    body,
    '',
  ].join('\n');

  if (apply) {
    fs.writeFileSync(mdPath, content, 'utf8');
    console.log(`  Written: ${slug}.md`);
  } else {
    console.log(`  [DRY RUN] ${slug}.md`);
  }
  written++;
}

console.log(`\nMode: ${apply ? 'APPLY' : 'DRY RUN'}`);
console.log(`${apply ? 'Written' : 'Would write'}: ${written}`);
console.log(`Skipped (already exists): ${skipped}`);
