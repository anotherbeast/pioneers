#!/usr/bin/env python3
"""
Pioneer Duplicate Analysis Report Generator
Analyzes pioneer markdown files for duplicates and inconsistencies
"""

import os
import re
import json
from collections import defaultdict
from pathlib import Path
import sys

# Redirect output to capture it
output_lines = []

def print_report(text=""):
    """Print to both console and capture for file."""
    output_lines.append(text)
    print(text)

# Configuration
PIONEERS_DIR = r'c:\Users\weddi\OneDrive\Desktop\pioneers\src\content\pioneers'
EXCLUDED_PATTERNS = ['prophecy', 'henry-mead.jpg', 'early-tract']
SIMILARITY_THRESHOLD = 0.75

# Initialize
pioneers_dir = Path(PIONEERS_DIR)
data = []
md_files = sorted(pioneers_dir.glob('*.md'))

# Phase 1: Load all data
for md_file in md_files:
    filename = md_file.name
    if any(pattern in filename.lower() for pattern in EXCLUDED_PATTERNS):
        continue
    
    try:
        with open(md_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        fm_match = re.search(r'^---\n([\s\S]*?)\n---', content, re.MULTILINE)
        if not fm_match:
            continue
        
        frontmatter = fm_match.group(1)
        
        name_match = re.search(r'name:\s*["\']?([^"\'\n]+)["\']?', frontmatter)
        name = name_match.group(1).strip() if name_match else 'N/A'
        
        birth_match = re.search(r'birth:\s*(\d+)', frontmatter)
        birth = birth_match.group(1) if birth_match else None
        
        death_match = re.search(r'death:\s*(\d+)', frontmatter)
        death = death_match.group(1) if death_match else None
        
        data.append({
            'filename': filename,
            'name': name,
            'birth_year': birth,
            'death_year': death
        })
    except Exception as e:
        pass

# Begin output
print_report("=" * 80)
print_report("PIONEER DUPLICATE ANALYSIS REPORT")
print_report("=" * 80)
print_report("")
print_report(f"Total pioneer records loaded: {len(data)}")

# Phase 2: Find exact duplicates
name_counts = defaultdict(list)
for item in data:
    name_counts[item['name']].append(item)

exact_dups = {name: items for name, items in name_counts.items() if len(items) > 1}

# Phase 3: Find similar names
similar = []
variants = []

names_list = [item['name'] for item in data]

for i, name1 in enumerate(names_list):
    for name2 in names_list[i+1:]:
        if name1 == name2:
            continue
        
        # Check for word order variations
        words1 = set(name1.lower().split())
        words2 = set(name2.lower().split())
        
        if len(words1) >= 2 and words1 == words2:
            idx1 = next(j for j, item in enumerate(data) if item['name'] == name1)
            idx2 = next(j for j, item in enumerate(data) if item['name'] == name2)
            row1 = data[idx1]
            row2 = data[idx2]
            
            variants.append({
                'name1': name1,
                'name2': name2,
                'file1': row1['filename'],
                'file2': row2['filename'],
                'years1': f"({row1['birth_year']}-{row1['death_year']})",
                'years2': f"({row2['birth_year']}-{row2['death_year']})"
            })

# Phase 4: Find year duplicates
year_dict = defaultdict(list)

for item in data:
    if item['birth_year'] and item['death_year']:
        key = (item['birth_year'], item['death_year'])
        year_dict[key].append(item)

year_dups = {key: items for key, items in year_dict.items() if len(items) > 1}

# Phase 5: Check malformed names
malformed = []

for item in data:
    name = item['name']
    issues = []
    
    if 'N/A' in name or len(name) < 2:
        issues.append('Missing or very short name')
    elif name.count('.') > 5:
        issues.append('Too many periods')
    elif bool(re.search(r'[<>{}[\]\\]', name)):
        issues.append('Suspicious characters')
    elif name.startswith('Brother') and len(name.split()) < 2:
        issues.append('Incomplete Brother name')
    elif name.startswith('Dr ') and len(name.split()) < 3:
        issues.append('Incomplete doctor name')
    
    if issues:
        malformed.append({
            'name': name,
            'filename': item['filename'],
            'issues': issues
        })

# Output summary
print_report("")
print_report("=" * 80)
print_report("SUMMARY STATISTICS")
print_report("=" * 80)
print_report(f"Total records: {len(data)}")
print_report(f"Exact duplicate names: {len(exact_dups)}")
print_report(f"Similar names: {len(similar)}")
print_report(f"Word order variants: {len(variants)}")
print_report(f"Duplicate year combinations: {len(year_dups)}")
print_report(f"Malformed names: {len(malformed)}")

# Detailed findings
print_report("")
print_report("=" * 80)
print_report("DETAILED FINDINGS")
print_report("=" * 80)

# Exact duplicates
if exact_dups:
    print_report(f"\nEXACT DUPLICATE NAMES ({len(exact_dups)}):")
    for name, items in sorted(exact_dups.items()):
        print_report(f"\n  Name: '{name}' (appears {len(items)} times)")
        for item in items:
            print_report(f"    • {item['filename']}: ({item['birth_year']}-{item['death_year']})")
else:
    print_report(f"\nEXACT DUPLICATE NAMES: None found")

# Word variants
if variants:
    print_report(f"\nWORD ORDER VARIANTS ({len(variants)}):")
    for pair in variants:
        print_report(f"\n  \"{pair['name1']}\" {pair['years1']} vs \"{pair['name2']}\" {pair['years2']}")
        print_report(f"    Files: {pair['file1']} vs {pair['file2']}")
else:
    print_report(f"\nWORD ORDER VARIANTS: None found")

# Year duplicates
if year_dups:
    print_report(f"\nDUPLICATE YEAR COMBINATIONS ({len(year_dups)}):")
    for (birth, death), items in sorted(year_dups.items()):
        print_report(f"\n  Years {birth}-{death} (appears {len(items)} times):")
        for item in items:
            print_report(f"    • {item['name']}: {item['filename']}")
else:
    print_report(f"\nDUPLICATE YEAR COMBINATIONS: None found")

# Malformed
if malformed:
    print_report(f"\nPOTENTIALLY MALFORMED NAMES ({len(malformed)}):")
    for entry in malformed:
        print_report(f"\n  '{entry['name']}' ({entry['filename']})")
        for issue in entry['issues']:
            print_report(f"    - {issue}")
else:
    print_report(f"\nPOTENTIALLY MALFORMED NAMES: None found")

print_report("")
print_report("=" * 80)
print_report("END OF REPORT")
print_report("=" * 80)

# Write to file
report_path = Path(PIONEERS_DIR).parent.parent.parent / "DUPLICATE_ANALYSIS_REPORT.txt"
with open(report_path, 'w') as f:
    f.write('\n'.join(output_lines))

print(f"\n✓ Report saved to: {report_path}")
