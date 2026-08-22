/**
 * link-pioneer-books.js
 * Reads all src/content/books/*.md, groups by author, then updates the
 * `books:` YAML array in each matching pioneer's frontmatter.
 *
 * Usage:
 *   node scripts/link-pioneer-books.js          (dry run)
 *   node scripts/link-pioneer-books.js --apply  (write files)
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const booksDir = path.join(root, 'src', 'content', 'books');
const pioneersDir = path.join(root, 'src', 'content', 'pioneers');

// ── Pioneer file slug → book author string ────────────────────────────────
// Must match the `author:` value written by generate-book-entries.js
const PIONEER_AUTHOR_MAP = {
  'captain-joseph-bates':       'Joseph Bates',
  'ellen-white':                'Ellen G. White',
  'james-white':                'James White',
  'william-miller':             'William Miller',
  'john-norton-loughborough':   'John Norton Loughborough',
  'charles-fitch':              'Charles Fitch',
  'john-nevins-andrews':        'John Nevins Andrews',
  'alonzo-trevier-jones':       'Alonzo Trevier Jones',
  'william-tyndale':            'William Tyndale',
  'john-wycliffe':              'John Wycliffe',
  'john-huss':                  'John Huss',
  'martin-luther':              'Martin Luther',
  'hugh-latimer':               'Hugh Latimer',
  'nicholas-ridley':            'Nicholas Ridley',
  'john-knox':                  'John Knox',
  'jerome-of-prague':           'Jerome of Prague',
  'judson-sylvanus-washburn':   'Judson Sylvanus Washburn',
  'charles-s-longacre':         'Charles S. Longacre',
  'otis-nichols':               'Otis Nichols',
  'sylvester-bliss':            'Sylvester Bliss',
  'william-ings':               'William Ings',
  'william-miller':             'William Miller',
};

// Also include books whose author field matches the pioneer's `name:` value
// for hand-written entries that use full names differently.
const EXTRA_AUTHOR_ALIASES = {
  'captain-joseph-bates':  ['Captain Joseph Bates'],
  'ellen-white':           ['Ellen White', 'Ellen Gould Harmon White', 'Ellen G. White'],
  'james-white':           ['James S. White'],
};

// ── Parse frontmatter value ───────────────────────────────────────────────
function parseFrontmatterValue(raw) {
  const s = raw.trim();
  if (s.startsWith('"') || s.startsWith("'")) return s.slice(1, -1);
  return s;
}

function readFrontmatter(filePath) {
  const text = fs.readFileSync(filePath, 'utf8');
  const match = text.match(/^---\r?\n([\s\S]*?)\r?\n---/);
  if (!match) return { text, fields: {}, fmRaw: '' };
  const fmRaw = match[1];
  const fields = {};
  for (const line of fmRaw.split(/\r?\n/)) {
    const m = line.match(/^(\w[\w-]*):\s*(.*)$/);
    if (m) fields[m[1]] = parseFrontmatterValue(m[2]);
  }
  return { text, fields, fmRaw };
}

// ── Collect all book slugs grouped by author ─────────────────────────────
function loadBooksByAuthor() {
  const map = {}; // author → [slug, ...]
  for (const file of fs.readdirSync(booksDir)) {
    if (!file.endsWith('.md')) continue;
    const slug = file.replace(/\.md$/, '');
    const { fields } = readFrontmatter(path.join(booksDir, file));
    const author = fields.author || '';
    if (!author) continue;
    if (!map[author]) map[author] = [];
    map[author].push(slug);
  }
  // Sort each list alphabetically
  for (const key of Object.keys(map)) map[key].sort();
  return map;
}

// ── Format a books array for YAML inline ─────────────────────────────────
function formatBooksYaml(slugs) {
  if (slugs.length === 0) return 'books: []';
  const items = slugs.map(s => `"${s}"`).join(', ');
  return `books: [${items}]`;
}

// ── Update a pioneer file ─────────────────────────────────────────────────
function updatePioneerFile(filePath, newSlugs, apply) {
  const text = fs.readFileSync(filePath, 'utf8');
  // Replace `books: [...]` or `books: []` with new value
  const newLine = formatBooksYaml(newSlugs);
  const updated = text.replace(/^books:.*$/m, newLine);
  if (updated === text) return false; // no change
  if (apply) fs.writeFileSync(filePath, updated, 'utf8');
  return true;
}

// ── Main ─────────────────────────────────────────────────────────────────
const apply = process.argv.includes('--apply');
const booksByAuthor = loadBooksByAuthor();

console.log(`Mode: ${apply ? 'APPLY' : 'DRY RUN'}`);
console.log(`Book author groups found: ${Object.keys(booksByAuthor).length}\n`);

let updated = 0, skipped = 0;

for (const [pioneerSlug, primaryAuthor] of Object.entries(PIONEER_AUTHOR_MAP)) {
  const filePath = path.join(pioneersDir, `${pioneerSlug}.md`);
  if (!fs.existsSync(filePath)) {
    console.log(`  SKIP (no file): ${pioneerSlug}.md`);
    skipped++;
    continue;
  }

  // Collect all matching book slugs across primary + alias author names
  const authorNames = new Set([primaryAuthor]);
  for (const alias of (EXTRA_AUTHOR_ALIASES[pioneerSlug] || [])) authorNames.add(alias);

  const slugs = [];
  for (const name of authorNames) {
    for (const s of (booksByAuthor[name] || [])) {
      if (!slugs.includes(s)) slugs.push(s);
    }
  }
  slugs.sort();

  if (slugs.length === 0) {
    console.log(`  SKIP (no books found): ${pioneerSlug}`);
    skipped++;
    continue;
  }

  const changed = updatePioneerFile(filePath, slugs, apply);
  if (changed) {
    console.log(`  ${apply ? 'Updated' : '[DRY RUN]'} ${pioneerSlug}.md → ${slugs.length} books`);
    updated++;
  } else {
    console.log(`  UNCHANGED: ${pioneerSlug}.md (books already set)`);
    skipped++;
  }
}

console.log(`\n${apply ? 'Updated' : 'Would update'}: ${updated}`);
console.log(`Skipped: ${skipped}`);
