#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Extract frontmatter and death place information
function extractFrontmatter(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf-8');
    const match = content.match(/^---\n([\s\S]*?)\n---/);
    
    if (!match) return null;
    
    const yamlContent = match[1];
    const frontmatter = {};
    
    // Parse simple YAML lines
    yamlContent.split('\n').forEach(line => {
      if (line.includes(':')) {
        const [key, ...valueParts] = line.split(':');
        const value = valueParts.join(':').trim().replace(/^["']|["']$/g, '');
        frontmatter[key.trim()] = value;
      }
    });
    
    return frontmatter;
  } catch (error) {
    console.error(`Error reading ${filePath}:`, error.message);
    return null;
  }
}

function getConfidenceLevel(deathPlace) {
  if (!deathPlace || deathPlace === 'Research Needed') {
    return 'Low';
  }
  if (deathPlace.includes(',')) {
    return 'High';
  }
  return 'Medium';
}

function main() {
  const pioneersDir = path.join(__dirname, 'src', 'content', 'pioneers');
  
  // Get all markdown files
  const files = fs.readdirSync(pioneersDir)
    .filter(f => f.endsWith('.md') && !f.startsWith('1843-') && !f.startsWith('1850-') && !f.startsWith('early-'))
    .map(f => path.join(pioneersDir, f))
    .sort();
  
  console.log(`Found ${files.length} pioneer markdown files`);
  console.log('Extracting death place information...\n');
  
  const pioneersData = [];
  
  // Extract data from each file
  files.forEach(file => {
    const frontmatter = extractFrontmatter(file);
    
    if (frontmatter && frontmatter.name && frontmatter.birth && frontmatter.death) {
      const deathPlace = frontmatter.deathPlace || 'Research Needed';
      pioneersData.push({
        name: frontmatter.name,
        birth: frontmatter.birth,
        death: frontmatter.death,
        deathPlace: deathPlace,
        confidence: getConfidenceLevel(deathPlace)
      });
    }
  });
  
  // Sort by name
  pioneersData.sort((a, b) => a.name.localeCompare(b.name));
  
  // Print CSV header
  console.log('Name|Birth|Death|DeathPlace|Confidence');
  console.log('-'.repeat(88));
  
  // Print CSV data
  pioneersData.forEach(pioneer => {
    console.log(`${pioneer.name}|${pioneer.birth}|${pioneer.death}|${pioneer.deathPlace}|${pioneer.confidence}`);
  });
  
  // Print summary
  console.log('\n' + '='.repeat(88));
  console.log(`Total pioneers: ${pioneersData.length}`);
  
  const verified = pioneersData.filter(p => p.deathPlace !== 'Research Needed');
  console.log(`Verified death places: ${verified.length} (${(verified.length/pioneersData.length*100).toFixed(1)}%)`);
  
  const researchNeeded = pioneersData.filter(p => p.deathPlace === 'Research Needed');
  console.log(`Requiring research: ${researchNeeded.length} (${(researchNeeded.length/pioneersData.length*100).toFixed(1)}%)`);
  
  // Group by location
  const locations = {};
  verified.forEach(pioneer => {
    const loc = pioneer.deathPlace;
    if (!locations[loc]) {
      locations[loc] = [];
    }
    locations[loc].push(pioneer.name);
  });
  
  console.log('\nVerified Death Locations:');
  Object.keys(locations).sort().forEach(loc => {
    console.log(`  ${loc}: ${locations[loc].length} pioneers`);
  });
}

main();
