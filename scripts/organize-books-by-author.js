import fs from 'fs';
import path from 'path';

const repoRoot = process.cwd();
const pioneersDir = path.join(repoRoot, 'src', 'content', 'pioneers');
const sourceDir = process.argv.includes('--source')
  ? path.resolve(process.argv[process.argv.indexOf('--source') + 1])
  : path.join(repoRoot, 'sundaylaw-export-4-3-26', 'sundaylaw.com');
const targetRoot = process.argv.includes('--target')
  ? path.resolve(process.argv[process.argv.indexOf('--target') + 1])
  : path.join(repoRoot, 'public', 'books');
const overridesPath = path.join(repoRoot, 'scripts', 'book-author-overrides.json');
const applyChanges = process.argv.includes('--apply');

function slugify(value) {
  return value
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
}

function normalize(value) {
  return value
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function compact(value) {
  return normalize(value).replace(/\s+/g, '');
}

function titleCaseSlug(slug) {
  return slug
    .split('-')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function loadOverrides() {
  if (!fs.existsSync(overridesPath)) {
    return { exact: {}, contains: [] };
  }

  const raw = fs.readFileSync(overridesPath, 'utf8');
  const parsed = JSON.parse(raw);
  const exact = parsed.exact || {};
  const contains = parsed.contains || [];

  return { exact, contains };
}

function normalizeRule(rule) {
  if (typeof rule === 'string') {
    return { primary: rule, secondary: null };
  }

  if (!rule || !rule.primary) {
    return null;
  }

  return {
    primary: rule.primary,
    secondary: rule.secondary || null
  };
}

function resolveOverride(relativePath, fileName, overrides) {
  const rel = relativePath.replace(/\\/g, '/');
  const base = fileName;

  const exactCandidates = [rel, base];
  for (const candidate of exactCandidates) {
    const match = overrides.exact[candidate];
    const normalized = normalizeRule(match);
    if (normalized) {
      return normalized;
    }
  }

  const relNorm = normalize(rel);
  const baseNorm = normalize(base);
  for (const row of overrides.contains) {
    if (!row || !row.needle || !row.primary) {
      continue;
    }

    const needle = normalize(row.needle);
    if (!needle) continue;
    if (relNorm.includes(needle) || baseNorm.includes(needle)) {
      return { primary: row.primary, secondary: row.secondary || null };
    }
  }

  return null;
}

function findOrCreateAuthorBySlug(authors, slug) {
  const found = authors.find((author) => author.slug === slug);
  if (found) {
    return found;
  }

  return {
    name: titleCaseSlug(slug),
    slug,
    normalizedName: normalize(titleCaseSlug(slug)),
    workTitles: [],
    sourceFile: 'override'
  };
}

function readPioneerAuthors(dir) {
  const files = fs.readdirSync(dir).filter((file) => file.endsWith('.md'));
  const authors = [];

  for (const file of files) {
    const fullPath = path.join(dir, file);
    const text = fs.readFileSync(fullPath, 'utf8');
    const frontmatter = text.match(/^---\r?\n([\s\S]*?)\r?\n---/);
    if (!frontmatter) {
      continue;
    }

    const fm = frontmatter[1];
    const nameMatch = fm.match(/^name:\s*["']?([^"'\n]+)["']?\s*$/m);
    if (!nameMatch) {
      continue;
    }

    const name = nameMatch[1].trim();
    if (!name) {
      continue;
    }

    const titleMatches = [...fm.matchAll(/^\s*-\s*title:\s*["']([^"']+)["']\s*$/gm)];
    const workTitles = titleMatches.map((m) => m[1]);
    const bookSlugMatches = [...fm.matchAll(/^\s*-\s*["']([a-z0-9-]+)["']\s*$/gm)];
    const bookAliases = bookSlugMatches.map((m) => m[1].replace(/-/g, ' '));
    const matchAliases = [...workTitles, ...bookAliases];

    if (matchAliases.length === 0) {
      continue;
    }

    const authorSlug = slugify(name.replace(/^captain\s+/i, ''));

    authors.push({
      name,
      slug: authorSlug,
      normalizedName: normalize(name),
      workTitles: matchAliases,
      sourceFile: file
    });
  }

  return authors;
}

function collectPdfFiles(dir) {
  const results = [];
  const stack = [dir];

  while (stack.length > 0) {
    const current = stack.pop();
    const entries = fs.readdirSync(current, { withFileTypes: true });
    for (const entry of entries) {
      const full = path.join(current, entry.name);
      if (entry.isDirectory()) {
        stack.push(full);
        continue;
      }

      if (/\.pdf$/i.test(entry.name)) {
        results.push(full);
      }
    }
  }

  return results;
}

function buildAliasMap(authors) {
  const map = new Map();

  for (const author of authors) {
    const parts = author.normalizedName.split(' ').filter(Boolean);
    const aliases = new Set();
    aliases.add(author.normalizedName);

    if (parts.length >= 2) {
      aliases.add(`${parts[0]} ${parts[parts.length - 1]}`);
    }

    for (const title of author.workTitles) {
      aliases.add(normalize(title));
    }

    map.set(author.slug, aliases);
  }

  const addAliases = (slug, aliasList) => {
    if (!map.has(slug)) return;
    const set = map.get(slug);
    for (const alias of aliasList) {
      set.add(normalize(alias));
    }
  };

  addAliases('joseph-bates', [
    'captain joseph bates',
    'bates',
    'way marks',
    'waymarks',
    'opening heavens',
    'seal of god'
  ]);

  addAliases('john-norton-loughborough', [
    'j n loughborough',
    'jn loughborough',
    'loughborough',
    'rise and progress'
  ]);

  addAliases('william-miller', [
    'wm miller',
    'miller',
    'miller rules',
    'evidence from scripture and history'
  ]);

  addAliases('ellen-white', [
    'ellen g white',
    'e g white',
    'eg white',
    'great controversy'
  ]);

  addAliases('james-white', ['james white', 'present truth']);
  addAliases('james-white', ['white j', 'j white', 'jwhite', 'brothers miller dream']);

  return map;
}

function scoreAuthor(fileNameNorm, aliases) {
  let score = 0;
  for (const alias of aliases) {
    if (!alias || alias.length < 3) continue;
    if (fileNameNorm.includes(alias)) {
      score += alias.split(' ').length >= 3 ? 12 : 8;
    }
  }
  return score;
}

function scoreAllAuthors(fileNameNorm, authors, aliasMap) {
  return authors
    .map((author) => {
      const aliases = aliasMap.get(author.slug) || new Set();
      return { author, score: scoreAuthor(fileNameNorm, aliases) };
    })
    .filter((row) => row.score > 0)
    .sort((a, b) => b.score - a.score);
}

function findAuthorBySlug(authors, slug) {
  return authors.find((author) => author.slug === slug) || null;
}

function subjectOverride(fileNameNorm, authors) {
  if (fileNameNorm.includes('miller') && fileNameNorm.includes('dream')) {
    return findAuthorBySlug(authors, 'william-miller');
  }

  return null;
}

function detectSubjectAuthor(fileNameNorm, fileNameCompact, authors) {
  const subjectKeywords = [
    'memoir',
    'memoirs',
    'life',
    'biography',
    'autobiography',
    'history',
    'incidents',
    'experiences'
  ];

  const hasSubjectSignal = subjectKeywords.some((kw) => fileNameNorm.includes(kw) || fileNameCompact.includes(kw));
  if (!hasSubjectSignal) {
    return null;
  }

  let best = null;
  for (const author of authors) {
    const parts = author.normalizedName.split(' ').filter(Boolean);
    const aliasCandidates = [author.normalizedName, compact(author.normalizedName)];

    if (parts.length >= 2) {
      const firstLast = `${parts[0]} ${parts[parts.length - 1]}`;
      aliasCandidates.push(firstLast, compact(firstLast));
    }

    let score = 0;
    for (const candidate of aliasCandidates) {
      if (!candidate || candidate.length < 6) continue;
      if (candidate.includes(' ')) {
        if (fileNameNorm.includes(candidate)) {
          score += 50;
        }
        if (fileNameNorm.includes(`of ${candidate}`)) {
          score += 30;
        }
      } else if (fileNameCompact.includes(candidate)) {
        score += 45;
      }
    }

    if (!best || score > best.score) {
      best = { author, score };
    }
  }

  if (!best || best.score < 45) {
    return null;
  }

  return best.author;
}

function bestAuthorForFile(filePath, authors, aliasMap) {
  const base = path.basename(filePath, path.extname(filePath));
  const normalizedBase = normalize(base);
  const compactBase = compact(base);
  const ranked = scoreAllAuthors(normalizedBase, authors, aliasMap);

  const forcedSubject = subjectOverride(normalizedBase, authors);
  if (forcedSubject) {
    const secondary = ranked.find((entry) => entry.author.slug !== forcedSubject.slug && entry.score >= 8);
    return {
      primary: forcedSubject,
      secondary: secondary ? secondary.author : null
    };
  }

  const subjectAuthor = detectSubjectAuthor(normalizedBase, compactBase, authors);
  if (subjectAuthor) {
    const secondary = ranked.find((entry) => entry.author.slug !== subjectAuthor.slug && entry.score >= 8);
    return {
      primary: subjectAuthor,
      secondary: secondary ? secondary.author : null
    };
  }

  const best = ranked[0];
  if (!best || best.score < 8) {
    return null;
  }

  const secondary = ranked.find((entry) => entry.author.slug !== best.author.slug && entry.score >= 8);

  return {
    primary: best.author,
    secondary: secondary ? secondary.author : null
  };
}

function ensureDir(dir) {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

function main() {
  if (!fs.existsSync(pioneersDir)) {
    throw new Error(`Pioneers directory not found: ${pioneersDir}`);
  }
  if (!fs.existsSync(sourceDir)) {
    throw new Error(`Source directory not found: ${sourceDir}`);
  }

  ensureDir(targetRoot);

  const authors = readPioneerAuthors(pioneersDir);
  const aliasMap = buildAliasMap(authors);
  const overrides = loadOverrides();
  const pdfFiles = collectPdfFiles(sourceDir);

  const moved = [];
  const copied = [];
  const unmatched = [];
  const exists = [];

  for (const filePath of pdfFiles) {
    const relativePath = path.relative(sourceDir, filePath);
    const fileName = path.basename(filePath);
    const override = resolveOverride(relativePath, fileName, overrides);

    let match = null;
    if (override) {
      match = {
        primary: findOrCreateAuthorBySlug(authors, override.primary),
        secondary: override.secondary ? findOrCreateAuthorBySlug(authors, override.secondary) : null,
        via: 'override'
      };
    } else {
      const inferred = bestAuthorForFile(filePath, authors, aliasMap);
      if (inferred) {
        match = {
          ...inferred,
          via: 'inferred'
        };
      }
    }

    if (!match) {
      unmatched.push(relativePath);
      continue;
    }

    const primaryDir = path.join(targetRoot, match.primary.slug);
    const destPath = path.join(primaryDir, path.basename(filePath));

    if (fs.existsSync(destPath)) {
      exists.push({
        file: relativePath,
        destination: path.relative(repoRoot, destPath)
      });
      continue;
    }

    if (applyChanges) {
      ensureDir(primaryDir);
      fs.renameSync(filePath, destPath);
    }

    moved.push({
      file: relativePath,
      author: match.primary.name,
      via: match.via,
      destination: path.relative(repoRoot, destPath)
    });

    if (match.secondary) {
      const secondaryDir = path.join(targetRoot, match.secondary.slug);
      const secondaryPath = path.join(secondaryDir, path.basename(filePath));

      if (!fs.existsSync(secondaryPath)) {
        if (applyChanges) {
          ensureDir(secondaryDir);
          fs.copyFileSync(destPath, secondaryPath);
        }

        copied.push({
          file: relativePath,
          fromAuthor: match.primary.name,
          toAuthor: match.secondary.name,
          via: match.via,
          destination: path.relative(repoRoot, secondaryPath)
        });
      }
    }
  }

  const report = {
    sourceDir: path.relative(repoRoot, sourceDir),
    targetRoot: path.relative(repoRoot, targetRoot),
    applyChanges,
    totals: {
      authors: authors.length,
      pdfFiles: pdfFiles.length,
      moved: moved.length,
      copied: copied.length,
      unmatched: unmatched.length,
      alreadyExisted: exists.length
    },
    moved,
    copied,
    alreadyExisted: exists,
    unmatched: unmatched.slice(0, 200)
  };

  const reportPath = path.join(repoRoot, 'analysis_report_books_by_author.json');
  fs.writeFileSync(reportPath, `${JSON.stringify(report, null, 2)}\n`, 'utf8');

  console.log(`Source: ${report.sourceDir}`);
  console.log(`Target: ${report.targetRoot}`);
  console.log(`Mode: ${applyChanges ? 'APPLY' : 'DRY RUN'}`);
  console.log(`Authors loaded: ${report.totals.authors}`);
  console.log(`PDF files scanned: ${report.totals.pdfFiles}`);
  console.log(`Overrides loaded: ${Object.keys(overrides.exact).length + overrides.contains.length}`);
  console.log(`Moved: ${report.totals.moved}`);
  console.log(`Copied to secondary author folders: ${report.totals.copied}`);
  console.log(`Already existed: ${report.totals.alreadyExisted}`);
  console.log(`Unmatched: ${report.totals.unmatched}`);
  console.log(`Report written: ${path.relative(repoRoot, reportPath)}`);
}

main();