import fs from 'fs';
import path from 'path';

const sourceFileArg = process.argv[2] || 'data/pioneers-30.txt';
const source = path.resolve(sourceFileArg);
const outputDir = path.resolve('src/content/pioneers');

function makeSlug(value) {
  const p = value
    .replace(/^.*\//, '') // name only
    .replace(/\.html?$/i, '')
    .replace(/%28/g, '(')
    .replace(/%29/g, ')')
    .replace(/_/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  const wikified = p
    .normalize('NFKD')
    .replace(/[\u0300-\u036F]/g, '')
    .replace(/\(.*\)/g, '')
    .trim();

  return wikified
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
}

function titleFromSlug(slug) {
  const spaced = slug.replace(/-/g, ' ');
  return spaced
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

const lines = fs.readFileSync(source, 'utf8').split(/\r?\n/).filter(Boolean);
for (let i = 0; i < lines.length; i += 1) {
  const raw = lines[i].trim();
  const slug = makeSlug(raw);
  const title = titleFromSlug(slug);
  const isChart = /chart|prophecy|tract/i.test(raw);

  const content = `---
name: "${title}"
title: "${title}"
birth: 0
death: 0
birthDate: ""
deathDate: ""
birthPlace: ""
excerpt: "${title} is a documented Advent pioneer entry brought in from your source list."
image: "images/pioneers/${slug}.jpg"
books: []
categories: ["${isChart ? 'chart' : 'pioneer'}"]
---

${title} was part of the early Adventist movement.
`;

  const outPath = path.join(outputDir, `${slug}.md`);
  if (fs.existsSync(outPath)) {
    console.log(`Skipping existing: ${slug}`);
    continue;
  }

  fs.writeFileSync(outPath, content, 'utf8');
  console.log(`Created: ${outPath}`);
}

console.log('Generate complete.');
