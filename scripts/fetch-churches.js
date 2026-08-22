#!/usr/bin/env node
/**
 * fetch-churches.js
 * ---------------------------------------------------------------------------
 * Fetches SDA church data from:
 *   1. ProPublica Nonprofit Explorer API (free, no API key required)
 *      https://projects.propublica.org/nonprofits/api/v2/
 *   2. OpenStreetMap Overpass API (free, no API key required)
 *      https://overpass-api.de/api/interpreter
 *
 * Usage:
 *   node scripts/fetch-churches.js
 *
 * Output:
 *   Writes/merges data into src/data/churches.json
 *   New entries are APPENDED — existing slugs are preserved (manual curation wins).
 *
 * Run this before your Astro build to refresh church data:
 *   node scripts/fetch-churches.js && npm run build
 * ---------------------------------------------------------------------------
 */

import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUTPUT_PATH = path.join(__dirname, '..', 'src', 'data', 'churches.json');

// ─── Helpers ────────────────────────────────────────────────────────────────

function slugify(str) {
  return str
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

function formatEin(rawEin) {
  const digits = String(rawEin).replace(/\D/g, '');
  if (digits.length === 9) return `${digits.slice(0, 2)}-${digits.slice(2)}`;
  return rawEin;
}

function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function safeFetch(url, label) {
  try {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json', 'User-Agent': 'SundayLaw.com Church Directory Builder' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  } catch (err) {
    console.warn(`  ⚠ Failed to fetch ${label}: ${err.message}`);
    return null;
  }
}

// ─── ProPublica Nonprofit API ────────────────────────────────────────────────

const PROPUBLICA_BASE = 'https://projects.propublica.org/nonprofits/api/v2';
const SDA_QUERIES = [
  'seventh day adventist church',
  'seventh-day adventist church',
  'SDA church',
];

async function fetchProPublica() {
  const results = new Map(); // ein → org object

  for (const query of SDA_QUERIES) {
    console.log(`\n  ProPublica: searching "${query}"...`);
    let page = 0;

    while (true) {
      const url = `${PROPUBLICA_BASE}/search.json?q=${encodeURIComponent(query)}&page=${page}`;
      const data = await safeFetch(url, `ProPublica page ${page}`);
      if (!data || !data.organizations || data.organizations.length === 0) break;

      for (const org of data.organizations) {
        // Filter: must be religious (NTEE X*), must mention adventist in name
        const name = (org.name || '').toLowerCase();
        const ntee = (org.ntee_code || '').toUpperCase();
        if (!ntee.startsWith('X')) continue;
        if (!name.includes('adventist') && !name.includes('sda')) continue;

        const ein = formatEin(org.ein);
        if (!results.has(ein)) {
          results.set(ein, {
            propublica: org,
            ein,
            name: org.name,
            city: org.city ? titleCase(org.city) : '',
            stateCode: org.state || '',
            nteeCode: org.ntee_code || 'X21',
            revenue: org.totrevenue || 0,
          });
        }
      }

      console.log(`    page ${page}: ${data.organizations.length} orgs (total collected: ${results.size})`);
      if (data.organizations.length < 25) break; // last page
      page++;
      await sleep(400); // be polite to the API
    }
  }

  return [...results.values()];
}

// ─── OpenStreetMap Overpass API ──────────────────────────────────────────────

const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

async function fetchOverpass() {
  const query = `
[out:json][timeout:60];
(
  node["amenity"="place_of_worship"]["denomination"="seventh_day_adventist"];
  way["amenity"="place_of_worship"]["denomination"="seventh_day_adventist"];
  relation["amenity"="place_of_worship"]["denomination"="seventh_day_adventist"];
);
out center tags;
`.trim();

  console.log('\n  Overpass API: querying SDA places of worship worldwide...');
  const data = await safeFetch(
    `${OVERPASS_URL}?data=${encodeURIComponent(query)}`,
    'Overpass SDA churches'
  );
  if (!data || !data.elements) return [];

  console.log(`    Found ${data.elements.length} OSM elements`);
  return data.elements.map(el => {
    const tags = el.tags || {};
    const lat = el.lat ?? el.center?.lat ?? null;
    const lng = el.lon ?? el.center?.lon ?? null;
    return {
      osm_id: el.id,
      name: tags.name || tags['name:en'] || '',
      address: [tags['addr:housenumber'], tags['addr:street']].filter(Boolean).join(' '),
      city: tags['addr:city'] || tags['addr:town'] || '',
      stateCode: tags['addr:state'] || '',
      country: tags['addr:country'] || 'US',
      zip: tags['addr:postcode'] || '',
      phone: tags.phone || tags['contact:phone'] || '',
      website: tags.website || tags['contact:website'] || '',
      facebook: tags['contact:facebook'] || '',
      latitude: lat,
      longitude: lng,
    };
  });
}

// ─── Merge Logic ─────────────────────────────────────────────────────────────

function titleCase(str) {
  return str.replace(/\w\S*/g, w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
}

const STATE_MAP = {
  AL: 'Alabama', AK: 'Alaska', AZ: 'Arizona', AR: 'Arkansas', CA: 'California',
  CO: 'Colorado', CT: 'Connecticut', DE: 'Delaware', FL: 'Florida', GA: 'Georgia',
  HI: 'Hawaii', ID: 'Idaho', IL: 'Illinois', IN: 'Indiana', IA: 'Iowa',
  KS: 'Kansas', KY: 'Kentucky', LA: 'Louisiana', ME: 'Maine', MD: 'Maryland',
  MA: 'Massachusetts', MI: 'Michigan', MN: 'Minnesota', MS: 'Mississippi',
  MO: 'Missouri', MT: 'Montana', NE: 'Nebraska', NV: 'Nevada', NH: 'New Hampshire',
  NJ: 'New Jersey', NM: 'New Mexico', NY: 'New York', NC: 'North Carolina',
  ND: 'North Dakota', OH: 'Ohio', OK: 'Oklahoma', OR: 'Oregon', PA: 'Pennsylvania',
  RI: 'Rhode Island', SC: 'South Carolina', SD: 'South Dakota', TN: 'Tennessee',
  TX: 'Texas', UT: 'Utah', VT: 'Vermont', VA: 'Virginia', WA: 'Washington',
  WV: 'West Virginia', WI: 'Wisconsin', WY: 'Wyoming', DC: 'District of Columbia',
};

function buildChurchEntry(source, existing = {}) {
  const name = source.name || existing.name || '';
  const slug = existing.slug || slugify(name) || slugify(`church-${source.ein || source.osm_id}`);

  return {
    slug,
    name: name || existing.name,
    address: existing.address || source.address || '',
    city: existing.city || (source.city ? titleCase(source.city) : ''),
    state: existing.state || STATE_MAP[source.stateCode] || source.stateCode || '',
    stateCode: existing.stateCode || source.stateCode || '',
    country: existing.country || source.country || 'USA',
    zip: existing.zip || source.zip || '',
    phone: existing.phone || source.phone || '',
    website: existing.website || source.website || '',
    facebook: existing.facebook || source.facebook || '',
    instagram: existing.instagram || '',
    twitter: existing.twitter || '',
    youtube: existing.youtube || '',
    latitude: existing.latitude ?? source.latitude ?? null,
    longitude: existing.longitude ?? source.longitude ?? null,
    pastor: existing.pastor || '',
    conference: existing.conference || '',
    unionConference: existing.unionConference || '',
    division: existing.division || 'North American Division',
    ein: existing.ein || source.ein || '',
    nteeCode: existing.nteeCode || source.nteeCode || 'X21',
    revenue: existing.revenue || source.revenue || 0,
    description: existing.description || '',
    verified: existing.verified || (!!source.ein),
    source: existing.source || (source.ein ? 'IRS/ProPublica' : 'OpenStreetMap'),
    lastUpdated: new Date().toISOString().split('T')[0],
  };
}

// ─── Main ────────────────────────────────────────────────────────────────────

async function main() {
  console.log('=== SDA Church Directory Builder ===');
  console.log('Sources: ProPublica Nonprofit API + OpenStreetMap Overpass API\n');

  // Load existing seed data
  let existing = [];
  try {
    const raw = await fs.readFile(OUTPUT_PATH, 'utf8');
    existing = JSON.parse(raw);
    console.log(`Loaded ${existing.length} existing church records from churches.json`);
  } catch {
    console.log('No existing churches.json found — starting fresh');
  }

  const existingBySlug = new Map(existing.map(c => [c.slug, c]));
  const existingByEin = new Map(existing.filter(c => c.ein).map(c => [c.ein, c]));

  // Fetch from APIs
  let newPP = [];
  let newOSM = [];

  try {
    newPP = await fetchProPublica();
    console.log(`\nProPublica: fetched ${newPP.length} SDA organizations`);
  } catch (err) {
    console.warn(`ProPublica fetch failed: ${err.message}`);
  }

  try {
    newOSM = await fetchOverpass();
    console.log(`Overpass: fetched ${newOSM.length} OSM church nodes`);
  } catch (err) {
    console.warn(`Overpass fetch failed: ${err.message}`);
  }

  // Merge ProPublica data
  let added = 0;
  let updated = 0;

  for (const pp of newPP) {
    if (existingByEin.has(pp.ein)) {
      // Update existing record with new data (non-destructive — manual data wins)
      const ex = existingByEin.get(pp.ein);
      ex.revenue = pp.revenue || ex.revenue;
      ex.nteeCode = pp.nteeCode || ex.nteeCode;
      ex.lastUpdated = new Date().toISOString().split('T')[0];
      updated++;
    } else {
      // New record from ProPublica
      const entry = buildChurchEntry(pp);
      existingBySlug.set(entry.slug, entry);
      existingByEin.set(entry.ein, entry);
      added++;
    }
  }

  // Merge Overpass data — match by name proximity
  for (const osm of newOSM) {
    if (!osm.name) continue;
    const slug = slugify(osm.name);

    // Try to match to existing record
    let matched = existingBySlug.get(slug);
    if (!matched) {
      // Check for name similarity
      for (const [, ex] of existingBySlug) {
        if (ex.city && osm.city && ex.city.toLowerCase() === osm.city.toLowerCase() &&
            osm.name.toLowerCase().includes(ex.city.toLowerCase())) {
          matched = ex;
          break;
        }
      }
    }

    if (matched) {
      // Enrich existing with OSM location data
      if (!matched.latitude && osm.latitude) matched.latitude = osm.latitude;
      if (!matched.longitude && osm.longitude) matched.longitude = osm.longitude;
      if (!matched.phone && osm.phone) matched.phone = osm.phone;
      if (!matched.website && osm.website) matched.website = osm.website;
      if (!matched.address && osm.address) matched.address = osm.address;
      updated++;
    } else {
      // New record from OSM only
      const entry = buildChurchEntry(osm);
      if (entry.slug && entry.name) {
        existingBySlug.set(entry.slug, entry);
        added++;
      }
    }
  }

  // Serialize final list (existing always first to preserve manual curation order)
  const finalList = [...existingBySlug.values()];

  await fs.writeFile(OUTPUT_PATH, JSON.stringify(finalList, null, 2), 'utf8');

  console.log('\n=== Done ===');
  console.log(`Total churches: ${finalList.length}`);
  console.log(`Added: ${added} | Updated: ${updated}`);
  console.log(`Output: ${OUTPUT_PATH}`);
  console.log('\nNext: npm run build');
}

main().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});
