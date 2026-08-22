#!/usr/bin/env python3
import os
import re
import json
from collections import defaultdict

path = r'c:\Users\weddi\OneDrive\Desktop\pioneers\src\content\pioneers'
data = []

# Read all pioneeer files
for filename in sorted(os.listdir(path)):
    if not filename.endswith('.md'):
        continue
    if 'prophecy' in filename or 'early-tract' in filename or 'henry-mead.jpg' in filename:
        continue
    
    filepath = os.path.join(path, filename)
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            
        # Extract frontmatter
        fm_match = re.search(r'^---\n([\s\S]*?)\n---', content, re.MULTILINE)
        if fm_match:
            frontmatter = fm_match.group(1)
            
            name = 'N/A'
            birth = '?'
            death = '?'
            
            name_match = re.search(r'name:\s*"?([^"\n]+)"?', frontmatter)
            if name_match:
                name = name_match.group(1).strip().strip('"')
            
            birth_match = re.search(r'birth:\s*(\d+)', frontmatter)
            if birth_match:
                birth = birth_match.group(1)
            
            death_match = re.search(r'death:\s*(\d+)', frontmatter)
            if death_match:
                death = death_match.group(1)
            
            data.append({
                'filename': filename,
                'name': name,
                'birth': birth,
                'death': death
            })
    except Exception as e:
        print(f'Error reading {filename}: {e}')

# Save full data
with open(os.path.join(path, 'all_pioneers.json'), 'w') as f:
    json.dump(data, f, indent=2)

print(f'✓ Extracted {len(data)} pioneers')

# Analysis 1: Exact duplicate names
name_counts = defaultdict(list)
for item in data:
    name_counts[item['name']].append(item)

exact_duplicates = {name: items for name, items in name_counts.items() if len(items) > 1}
print(f'\n1. EXACT DUPLICATE NAMES: {len(exact_duplicates)}')
for name, items in sorted(exact_duplicates.items()):
    for item in items:
        print(f'  - {name} ({item["birth"]}-{item["death"]}) in {item["filename"]}')

# Analysis 2: Similar names (variations)
def name_variations(name):
    """Generate variations of a name for matching"""
    # Remove middle names/initials for comparison
    parts = name.split()
    if len(parts) >= 2:
        # Try with just first and last name
        yield f'{parts[0]} {parts[-1]}'
        # Try without initials
        yield ' '.join(p for p in parts if len(p) > 1)
    yield name.lower()

similar_pairs = []
for i, item1 in enumerate(data):
    for item2 in data[i+1:]:
        name1 = item1['name'].lower()
        name2 = item2['name'].lower()
        
        # Check for similar variations (e.g., "Joseph Ellet" vs "Ellet Joseph")
        if name1 != name2:
            # Check if names have same words in different order
            words1 = set(name1.split())
            words2 = set(name2.split())
            common_words = words1 & words2
            
            # If they share 2+ words and are not exact matches
            if len(common_words) >= 2 and name1 != name2:
                similar_pairs.append({
                    'name1': item1['name'],
                    'file1': item1['filename'],
                    'birth1': item1['birth'],
                    'death1': item1['death'],
                    'name2': item2['name'],
                    'file2': item2['filename'],
                    'birth2': item2['birth'],
                    'death2': item2['death'],
                    'common_words': len(common_words)
                })

print(f'\n2. SIMILAR NAMES (same words in different order): {len(similar_pairs)}')
for pair in sorted(similar_pairs, key=lambda x: x['common_words'], reverse=True):
    print(f'  - "{pair["name1"]}" ({pair["birth1"]}-{pair["death1"]}) [{pair["file1"]}]')
    print(f'    vs "{pair["name2"]}" ({pair["birth2"]}-{pair["death2"]}) [{pair["file2"]}]')

# Analysis 3: Same birth/death year combinations
year_pairs = defaultdict(list)
for item in data:
    if item['birth'] != '?' and item['death'] != '?':
        key = (item['birth'], item['death'])
        year_pairs[key].append(item)

duplicate_years = {key: items for key, items in year_pairs.items() if len(items) > 1}
print(f'\n3. DUPLICATE BIRTH/DEATH YEAR COMBINATIONS: {len(duplicate_years)}')
for (birth, death), items in sorted(duplicate_years.items()):
    print(f'  Years {birth}-{death}:')
    for item in items:
        print(f'    - {item["name"]} ({item["filename"]})')

# Analysis 4: Spelling variations (e.g., Fanny vs Fannie)
print(f'\n4. SPELLING VARIATION CANDIDATES:')
name_lower = defaultdict(list)
for item in data:
    # Create a simplified version for comparison
    simplified = re.sub(r'[aeiou]', '', item['name'].lower())
    name_lower[simplified].append(item)

spelling_vars = {k: v for k, v in name_lower.items() if len(v) > 1 and len(k) > 3}
count = 0
for simplified, items in sorted(spelling_vars.items()):
    actual_names = set(item['name'] for item in items)
    if len(actual_names) > 1:
        count += 1
        print(f'  Possible variations:')
        for item in items:
            print(f'    - {item["name"]} ({item["birth"]}-{item["death"]}) [{item["filename"]}]')

if count == 0:
    print('  None found in initial analysis')

# Analysis 5: Malformed names
print(f'\n5. POTENTIALLY MALFORMED NAMES:')
malformed = []
for item in data:
    # Check for obviously bad names
    if 'N/A' in item['name'] or len(item['name']) < 2:
        malformed.append(item)
    elif item['name'].startswith('Brother') or item['name'].startswith('Dr '):
        if len(item['name'].split()) < 2:
            malformed.append(item)

if malformed:
    for item in malformed:
        print(f'  - {item["name"]} ({item["birth"]}-{item["death"]}) [{item["filename"]}]')
else:
    print('  None found - names generally well-formed')

print(f'\n\nFull pioneer list saved to: {path}\\all_pioneers.json')
