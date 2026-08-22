/**
 * apply-sundaylaw-layout.js
 *
 * Stamps the shared nav and footer into every .html page in the
 * sundaylaw-export-4-3-26/sundaylaw.com/ directory.
 *
 * Usage:
 *   node scripts/apply-sundaylaw-layout.js          (dry run — shows what would change)
 *   node scripts/apply-sundaylaw-layout.js --apply  (writes files)
 *
 * What it does per file:
 *   1. Replaces the nav block (from <!-- ========== NAVIGATION ========== -->
 *      through the closing </nav>) with the shared _partials/nav.html
 *   2. Replaces the block between <!-- SITE-FOOTER-START --> / <!-- SITE-FOOTER-END -->
 *      if it exists, OR injects the footer before </body> if it doesn't
 *   3. Removes duplicate inline sharePage / copyLink / toggleMobileMenu /
 *      closeMobileMenu / scrollToTop / openImageModal / closeImageModal /
 *      trackFormSubmit / trackDownload function definitions (now in site.js)
 *   4. Removes duplicate Google Translate script tags (kept in footer partial)
 *   5. Ensures proper SEO <meta> structure in <head>
 */

import fs   from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root      = path.resolve(__dirname, '..');
const siteDir   = path.join(root, 'sundaylaw-export-4-3-26', 'sundaylaw.com');

const navPartial    = fs.readFileSync(path.join(siteDir, '_partials', 'nav.html'),    'utf8');
const footerPartial = fs.readFileSync(path.join(siteDir, '_partials', 'footer.html'), 'utf8');

const apply = process.argv.includes('--apply');

/* ── Pages to process (root-level .html files only) ─────────────────────── */
const pages = fs.readdirSync(siteDir)
  .filter(f => f.endsWith('.html') && !f.startsWith('_'))
  .sort();

/* ── Regex helpers ───────────────────────────────────────────────────────── */

// Match the entire nav block using explicit markers.
const NAV_RE = /<!-- =+ NAVIGATION =+ -->[\s\S]*?<!-- \/NAVIGATION -->/i;
const NAV_RE_GLOBAL = /<!-- =+ NAVIGATION =+ -->[\s\S]*?<!-- \/NAVIGATION -->/gi;

// Match existing footer markers
const FOOTER_MARKERS_RE = /<!-- SITE-FOOTER-START -->[\s\S]*?<!-- SITE-FOOTER-END -->/i;

// Inline function definitions to remove (now centralised in site.js)
const INLINE_FN_PATTERNS = [
  // sharePage function
  /\/\*\*?[\s\S]*?\*\/?[\s]*function sharePage\s*\([\s\S]*?\n\}\s*\n?/g,
  /function sharePage\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // copyLink
  /function copyLink\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // toggleMobileMenu
  /function toggleMobileMenu\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // closeMobileMenu
  /function closeMobileMenu\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // scrollToTop
  /function scrollToTop\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // openImageModal
  /function openImageModal\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // closeImageModal
  /function closeImageModal\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // trackFormSubmit
  /function trackFormSubmit\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
  // trackDownload
  /function trackDownload\s*\([^)]*\)\s*\{[\s\S]*?\n\}/g,
];

// Duplicate Google Translate script tag (footer partial adds it once)
const GT_SCRIPT_RE = /<script[^>]*translate\.google\.com\/translate_a\/element\.js[^>]*><\/script>/gi;

// Old site.js / app.js includes to consolidate
// We keep app.js (page-specific logic) but skip if site.js already present
const SITE_JS_RE = /<script\s+src=["']\/js\/site\.js["'][^>]*><\/script>/i;

/* ── Process a single file ───────────────────────────────────────────────── */
function processFile(filePath) {
  let html = fs.readFileSync(filePath, 'utf8');
  let changed = false;

  // 1. Replace nav block (dedupe by removing all blocks and inserting one)
  if (NAV_RE.test(html)) {
    const allMatches = [...html.matchAll(NAV_RE_GLOBAL)];
    if (allMatches.length > 0) {
      const insertAt = allMatches[0].index ?? 0;
      const removedAll = html.replace(NAV_RE_GLOBAL, '').trimStart();
      html = removedAll.slice(0, insertAt) + navPartial.trim() + '\n\n' + removedAll.slice(insertAt);
      changed = true;
    }
  } else {
    // No existing nav marker — inject after <body>
    const bodyPos = html.indexOf('<body');
    const bodyEnd = html.indexOf('>', bodyPos) + 1;
    if (bodyPos !== -1) {
      html = html.slice(0, bodyEnd) + '\n\n' + navPartial.trim() + '\n\n' + html.slice(bodyEnd);
      changed = true;
    }
  }

  // 2. Replace footer block or inject before </body>
  if (FOOTER_MARKERS_RE.test(html)) {
    const next = html.replace(FOOTER_MARKERS_RE, footerPartial.trim());
    if (next !== html) { html = next; changed = true; }
  } else {
    // Remove any old duplicate Google Translate script tags first
    const stripped = html.replace(GT_SCRIPT_RE, '');
    if (stripped !== html) { html = stripped; changed = true; }

    // Inject footer before </body>
    const bodyClose = html.lastIndexOf('</body>');
    if (bodyClose !== -1) {
      html = html.slice(0, bodyClose) + '\n\n' + footerPartial.trim() + '\n\n' + html.slice(bodyClose);
      changed = true;
    }
  }

  // Remove accidental early closing tags before the injected footer.
  const earlyClosed = html.replace(/<\/body>\s*<\/html>\s*(?=\s*<!-- SITE-FOOTER-START -->)/i, '');
  if (earlyClosed !== html) { html = earlyClosed; changed = true; }

  // 2b. Remove duplicate inline helper functions now centralized in /js/site.js
  for (const fnRe of INLINE_FN_PATTERNS) {
    const stripped = html.replace(fnRe, '');
    if (stripped !== html) {
      html = stripped;
      changed = true;
    }
  }

  // 3. Remove site.js duplicate includes (keep only one — from footer partial)
  const siteJsMatches = [...html.matchAll(/<script\s+src=["']\/js\/site\.js["'][^>]*><\/script>/gi)];
  if (siteJsMatches.length > 1) {
    // Remove all but keep the last (the one from the footer partial)
    let count = 0;
    html = html.replace(/<script\s+src=["']\/js\/site\.js["'][^>]*><\/script>/gi, (m) => {
      count++;
      return count < siteJsMatches.length ? '' : m;
    });
    changed = true;
  }

  // 4. Ensure <html lang="en"> for SEO
  if (/<html(?!\s[^>]*lang=)/.test(html)) {
    html = html.replace('<html>', '<html lang="en">');
    changed = true;
  }

  // 5. Normalize final closing tags to one </body> and one </html>
  const endNormalized = html
    .replace(/(\s*<\/body>\s*<\/html>\s*)+$/i, '')
    .trimEnd() + '\n\n</body>\n</html>\n';
  if (endNormalized !== html) {
    html = endNormalized;
    changed = true;
  }

  return { html, changed };
}

/* ── Main ────────────────────────────────────────────────────────────────── */
console.log(`\nMode: ${apply ? 'APPLY' : 'DRY RUN'}`);
console.log(`Site dir: ${siteDir}`);
console.log(`Pages found: ${pages.length}\n`);

let updated = 0, skipped = 0, errors = 0;

for (const page of pages) {
  const filePath = path.join(siteDir, page);
  try {
    const { html, changed } = processFile(filePath);
    if (changed) {
      if (apply) {
        fs.writeFileSync(filePath, html, 'utf8');
        console.log(`  Updated : ${page}`);
      } else {
        console.log(`  [DRY RUN] Would update: ${page}`);
      }
      updated++;
    } else {
      console.log(`  Unchanged: ${page}`);
      skipped++;
    }
  } catch (err) {
    console.error(`  ERROR: ${page} — ${err.message}`);
    errors++;
  }
}

console.log(`\n${apply ? 'Updated' : 'Would update'}: ${updated}`);
console.log(`Unchanged: ${skipped}`);
if (errors) console.log(`Errors: ${errors}`);
