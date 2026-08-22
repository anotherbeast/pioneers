#!/usr/bin/env python3
"""
Extract death place information from all pioneer markdown files.
Outputs in pipe-delimited CSV format for database integration.
"""

import os
import re
import glob
from pathlib import Path
from typing import Dict, List, Tuple

# Script to extract death place information from markdown frontmatter
def extract_frontmatter(file_path: str) -> Dict:
    """Parse YAML frontmatter from markdown file."""
    frontmatter = {}
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
            
        # Match frontmatter between --- markers
        match = re.match(r'^---\n(.*?)\n---', content, re.DOTALL)
        if match:
            yaml_content = match.group(1)
            # Simple YAML parser for the key fields we need
            for line in yaml_content.split('\n'):
                if ':' in line:
                    key, value = line.split(':', 1)
                    key = key.strip()
                    value = value.strip().strip('"\'')
                    frontmatter[key] = value
        return frontmatter
    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        return {}

def get_confidence_level(death_place: str) -> str:
    """Determine confidence level based on death place specificity."""
    if "Research Needed" in death_place or not death_place:
        return "Low"
    elif death_place and "," in death_place:  # Has location details
        return "High"
    else:
        return "Medium"

def main():
    """Main function to extract and process all pioneer death locations."""
    
    # Find all pioneer markdown files
    pioneers_dir = Path("src/content/pioneers")
    md_files = sorted(pioneers_dir.glob("*.md"))
    
    # Filter out non-pioneer files
    pioneer_files = [f for f in md_files if not f.name.startswith(("1843-", "1850-", "early-"))]
    
    pioneers_data: List[Dict] = []
    
    print(f"Found {len(pioneer_files)} pioneer markdown files")
    print("Extracting death place information...\n")
    
    # Extract data from each file
    for md_file in pioneer_files:
        frontmatter = extract_frontmatter(str(md_file))
        
        if frontmatter:
            name = frontmatter.get('name', '')
            birth = frontmatter.get('birth', '')
            death = frontmatter.get('death', '')
            death_place = frontmatter.get('deathPlace', 'Research Needed')
            
            if name and birth and death:
                confidence = get_confidence_level(death_place)
                pioneers_data.append({
                    'name': name,
                    'birth': birth,
                    'death': death,
                    'deathPlace': death_place,
                    'confidence': confidence
                })
    
    # Sort by name
    pioneers_data.sort(key=lambda x: x['name'])
    
    # Print CSV header
    print("Name|Birth|Death|DeathPlace|Confidence")
    print("-" * 88)
    
    # Print CSV data
    for pioneer in pioneers_data:
        csv_line = f"{pioneer['name']}|{pioneer['birth']}|{pioneer['death']}|{pioneer['deathPlace']}|{pioneer['confidence']}"
        print(csv_line)
    
    # Print summary statistics
    print("\n" + "=" * 88)
    print(f"Total pioneers: {len(pioneers_data)}")
    
    verified = [p for p in pioneers_data if p['deathPlace'] != 'Research Needed']
    print(f"Verified death places: {len(verified)} ({len(verified)/len(pioneers_data)*100:.1f}%)")
    
    research_needed = [p for p in pioneers_data if p['deathPlace'] == 'Research Needed']
    print(f"Requiring research: {len(research_needed)} ({len(research_needed)/len(pioneers_data)*100:.1f}%)")
    
    # Group by death location
    locations = {}
    for pioneer in verified:
        loc = pioneer['deathPlace']
        if loc not in locations:
            locations[loc] = []
        locations[loc].append(pioneer['name'])
    
    print("\nVerified Death Locations:")
    for loc in sorted(locations.keys()):
        print(f"  {loc}: {len(locations[loc])} pioneers")

if __name__ == "__main__":
    main()
