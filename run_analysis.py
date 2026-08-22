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

# Configuration
PIONEERS_DIR = r'c:\Users\weddi\OneDrive\Desktop\pioneers\src\content\pioneers'
EXCLUDED_PATTERNS = ['prophecy', 'henry-mead.jpg', 'early-tract']
SIMILARITY_THRESHOLD = 0.75

class PioneerAnalyzer:
    def __init__(self, directory):
        self.directory = Path(directory)
        self.data = []
        self.report = {}
        
    def extract_frontmatter(self, file_path):
        """Extract frontmatter fields from a markdown file."""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            fm_match = re.search(r'^---\n([\s\S]*?)\n---', content, re.MULTILINE)
            if not fm_match:
                return None
            
            frontmatter = fm_match.group(1)
            
            name_match = re.search(r'name:\s*["\']?([^"\'\n]+)["\']?', frontmatter)
            name = name_match.group(1).strip() if name_match else 'N/A'
            
            birth_match = re.search(r'birth:\s*(\d+)', frontmatter)
            birth = birth_match.group(1) if birth_match else None
            
            death_match = re.search(r'death:\s*(\d+)', frontmatter)
            death = death_match.group(1) if death_match else None
            
            return {'name': name, 'birth': birth, 'death': death}
        except Exception as e:
            print(f"Error reading {file_path.name}: {e}")
            return None
    
    def load_data(self):
        """Load all pioneer data from markdown files."""
        md_files = sorted(self.directory.glob('*.md'))
        
        for md_file in md_files:
            filename = md_file.name
            if any(pattern in filename.lower() for pattern in EXCLUDED_PATTERNS):
                continue
            
            fm_data = self.extract_frontmatter(md_file)
            if fm_data:
                self.data.append({
                    'filename': filename,
                    'name': fm_data['name'],
                    'birth_year': fm_data['birth'],
                    'death_year': fm_data['death']
                })
        
        print(f"✓ Loaded {len(self.data)} pioneer records")
        return len(self.data)
    
    def find_exact_duplicates(self):
        """Find exact duplicate names."""
        name_counts = defaultdict(list)
        for item in self.data:
            name_counts[item['name']].append(item)
        
        duplicates = {name: items for name, items in name_counts.items() if len(items) > 1}
        return duplicates
    
    def calculate_similarity(self, str1, str2):
        """Simple string similarity calculation."""
        longer = str1 if len(str1) > len(str2) else str2
        shorter = str2 if longer == str1 else str1
        
        if len(longer) == 0:
            return 1.0
        
        match_distance = self._match_distance(longer, shorter)
        return (len(longer) - match_distance) / float(len(longer))
    
    def _match_distance(self, longer, shorter):
        """Helper for similarity calculation."""
        longer_lower = longer.lower()
        shorter_lower = shorter.lower()
        
        if longer_lower == shorter_lower:
            return 0
        
        match_distance = abs(len(longer_lower) - len(shorter_lower))
        for i in range(len(shorter_lower)):
            if shorter_lower[i] not in longer_lower:
                match_distance += 1
        
        return match_distance
    
    def find_similar_names(self):
        """Find similar names and word order variants."""
        similar_pairs = []
        word_variants = []
        
        names_list = [item['name'] for item in self.data]
        
        for i, name1 in enumerate(names_list):
            for name2 in names_list[i+1:]:
                if name1 == name2:
                    continue
                
                # Check for word order variations
                words1 = set(name1.lower().split())
                words2 = set(name2.lower().split())
                
                if len(words1) >= 2 and words1 == words2:
                    # Get the corresponding data
                    idx1 = next(j for j, item in enumerate(self.data) if item['name'] == name1)
                    idx2 = next(j for j, item in enumerate(self.data) if item['name'] == name2)
                    row1 = self.data[idx1]
                    row2 = self.data[idx2]
                    
                    word_variants.append({
                        'name1': name1,
                        'name2': name2,
                        'file1': row1['filename'],
                        'file2': row2['filename'],
                        'years1': f"({row1['birth_year']}-{row1['death_year']})",
                        'years2': f"({row2['birth_year']}-{row2['death_year']})"
                    })
                
                # Check for similar spelling
                similarity = self.calculate_similarity(name1, name2)
                if similarity >= SIMILARITY_THRESHOLD:
                    idx1 = next(j for j, item in enumerate(self.data) if item['name'] == name1)
                    idx2 = next(j for j, item in enumerate(self.data) if item['name'] == name2)
                    row1 = self.data[idx1]
                    row2 = self.data[idx2]
                    
                    similar_pairs.append({
                        'name1': name1,
                        'name2': name2,
                        'similarity': similarity,
                        'file1': row1['filename'],
                        'file2': row2['filename'],
                        'birth1': row1['birth_year'],
                        'death1': row1['death_year'],
                        'birth2': row2['birth_year'],
                        'death2': row2['death_year']
                    })
        
        return similar_pairs, word_variants
    
    def find_year_duplicates(self):
        """Find duplicate birth/death year combinations."""
        year_dict = defaultdict(list)
        
        for item in self.data:
            if item['birth_year'] and item['death_year']:
                key = (item['birth_year'], item['death_year'])
                year_dict[key].append(item)
        
        duplicates = {key: items for key, items in year_dict.items() if len(items) > 1}
        return duplicates
    
    def check_malformed_names(self):
        """Check for obviously malformed names."""
        malformed = []
        
        for item in self.data:
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
        
        return malformed
    
    def generate_report(self):
        """Generate comprehensive analysis report."""
        print("\n" + "="*80)
        print("PIONEER DUPLICATE ANALYSIS REPORT")
        print("="*80)
        
        # Load data
        total = self.load_data()
        
        # Run analyses
        exact_dups = self.find_exact_duplicates()
        similar, variants = self.find_similar_names()
        year_dups = self.find_year_duplicates()
        malformed = self.check_malformed_names()
        
        print(f"\n" + "="*80)
        print("SUMMARY STATISTICS")
        print("="*80)
        print(f"Total pioneer records: {total}")
        print(f"Exact duplicate names: {len(exact_dups)}")
        print(f"Similar names detected: {len(similar)}")
        print(f"Word order variants: {len(variants)}")
        print(f"Duplicate year combinations: {len(year_dups)}")
        print(f"Potentially malformed names: {len(malformed)}")
        
        # Detailed findings
        print(f"\n" + "="*80)
        print("DETAILED FINDINGS")
        print("="*80)
        
        # Exact duplicates
        if exact_dups:
            print(f"\n❌ EXACT DUPLICATE NAMES ({len(exact_dups)}):")
            for name, items in sorted(exact_dups.items()):
                print(f"\n  Name: '{name}' (appears {len(items)} times)")
                for item in items:
                    print(f"    • {item['filename']}: ({item['birth_year']}-{item['death_year']})")
        else:
            print(f"\n✅ EXACT DUPLICATE NAMES: None found")
        
        # Similar names
        if similar:
            print(f"\n⚠️  SIMILAR NAMES ({len(similar)}):")
            for pair in sorted(similar, key=lambda x: x['similarity'], reverse=True):
                print(f"\n  \"{pair['name1']}\" vs \"{pair['name2']}\"")
                print(f"    Similarity: {pair['similarity']:.1%}")
                print(f"    • {pair['file1']}: ({pair['birth1']}-{pair['death1']})")
                print(f"    • {pair['file2']}: ({pair['birth2']}-{pair['death2']})")
        else:
            print(f"\n✅ SIMILAR NAMES: None found above threshold")
        
        # Word variants
        if variants:
            print(f"\n🔄 WORD ORDER VARIANTS ({len(variants)}):")
            for pair in variants:
                print(f"\n  \"{pair['name1']}\" {pair['years1']} vs \"{pair['name2']}\" {pair['years2']}")
                print(f"    • {pair['file1']} vs {pair['file2']}")
        else:
            print(f"\n✅ WORD ORDER VARIANTS: None found")
        
        # Year duplicates
        if year_dups:
            print(f"\n📅 DUPLICATE YEAR COMBINATIONS ({len(year_dups)}):")
            for (birth, death), items in sorted(year_dups.items()):
                print(f"\n  Years {birth}-{death} (appears {len(items)} times):")
                for item in items:
                    print(f"    • {item['name']}: {item['filename']}")
        else:
            print(f"\n✅ DUPLICATE YEAR COMBINATIONS: None found")
        
        # Malformed names
        if malformed:
            print(f"\n⚠️  POTENTIALLY MALFORMED NAMES ({len(malformed)}):")
            for entry in malformed:
                print(f"\n  '{entry['name']}' ({entry['filename']})")
                for issue in entry['issues']:
                    print(f"    - {issue}")
        else:
            print(f"\n✅ POTENTIALLY MALFORMED NAMES: None found")
        
        print(f"\n" + "="*80)
        print("END OF REPORT")
        print("="*80 + "\n")

# Run the analyzer
if __name__ == '__main__':
    analyzer = PioneerAnalyzer(PIONEERS_DIR)
    analyzer.generate_report()
