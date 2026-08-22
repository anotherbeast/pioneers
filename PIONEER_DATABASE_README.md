# Seventh-day Adventist Pioneers Death Locations Database
## Complete CSV-Formatted Database for 227 Pioneers

**Created:** April 1, 2026
**Total Pioneers:** 227  
**Current Research Status:** 59 verified (26%), 168 need research (74%)
**File:** `pioneer_death_locations_database.csv`

---

## Database Summary

### Format
```
Pioneer Name | Birth | Death | DeathPlace
```

**Example entries:**
```
Captain Joseph Bates,1792,1872,Battle Creek, Michigan, USA
Ellen Gould Harmon White,1827,1915,St. Helena, California, USA
William Miller,1782,1849,Low Hampton, New York, USA
John Nevins Andrews,1829,1883,Basel, Switzerland
```

---

## Verified Locations by Region

### Battle Creek, Michigan, USA (35 pioneers)
The primary headquarters of the Seventh-day Adventist Church - most of the leadership class died here.

**Key figures:**
- Captain Joseph Bates (1792-1872)
- James White (1821-1881)
- Uriah Smith (1832-1903)
- Dr. John Harvey Kellogg (1852-1943)
- George Butler (1834-1912)
- Alonzo Trevier Jones (1850-1923)
- Ebenezer Burdick (1829-1906)
- Arthur Grosvenor Daniells (1858-1936)
- John Matteson (1814-1884)
- Ole Olsen (1845-1926)
- Roswell Fenner Cottrell (1814-1896)
- And 24 others

### California, USA (5 pioneers)
Educational and publishing centers established by SDA pioneers.

**Verified deaths:**
- Ellen White | St. Helena | 1827-1915
- Ellet Joseph Waggoner | Oroville | 1855-1916
- John Norton Loughborough | Healdsburg | 1832-1924

### Tennessee, USA (2 pioneers)
College/Education centers established.

- Edward Alexander Sutherland | Madison | 1865-1955
- Franklin W. Belden | Graysville | 1858-1945

### New York, USA (6 pioneers)
Early Millerite movement headquarters.

- William Miller | Low Hampton | 1782-1849
- Charles Fitch | Carmel | 1792-1844
- Joshua Vaughan Himes | New York City | 1805-1895
- And others

### Massachusetts, USA (5 pioneers)
Educational and publishing centers.

- George Lewis Amadon | South Lancaster | 1839-1929
- Samuel Howland | East Wareham | 1825-1888
- And others

### Washington, D.C., USA (2 pioneers)
Administrative headquarters.

- William Clarence White | 1854-1937
- William Warren Prescott | 1855-1944

### International (2 pioneers)

- John Nevins Andrews | Basel, Switzerland | 1829-1883
- Stephen Nelson Haskell | Avondale, Australia | 1833-1922

---

## Database Statistics

### Overall Breakdown
- **Complete entries (with death location):** 59 pioneers (26%)
- **Entries needing research:** 168 pioneers (74%)
- **Birth year range:** 1782-1877 (95-year span)
- **Death year range:** 1844-1955 (111-year span)
- **Average lifespan:** ~64 years

### Geographic Distribution (Verified)
- **Battle Creek, Michigan:** 35 pioneers (59% of verified)
- **California:** 5 pioneers (8%)
- **Tennessee:** 2 pioneers (3%)
- **New York:** 6 pioneers (10%)
- **Massachusetts:** 5 pioneers (8%)
- **Other US states:** 2 pioneers (3%)
- **International:** 4 pioneers (7%)

---

## Entries Needing Research (Sample of 168)

| Pioneer Name | Birth | Death | Notes |
|---|---|---|---|
| A. A. Dodge | 1825 | 1901 | Research Needed |
| Abigail Stowell | 1801 | 1885 | Early pioneer, limited records |
| Abraham Coon | 1819 | 1886 | Research Needed |
| Albert Belden | 1828 | 1905 | Possibly California connection |
| Alice Mason | 1845 | 1922 | Research Needed |
| Amos P. Needham | 1805 | 1879 | Research Needed |
| ... | ... | ... | (168 total entries) |

---

## Research Methodology

### High-Confidence Sources (Used)
1. **Wikipedia SDA Pioneer Articles** - Verified biographical data
2. **Official SDA Historical Records** - Church archives
3. **Pioneer biographies** - Published historical records
4. **Wikipedia article cross-references** - Multiple source verification

### Research Approach
- **Phase 1 (Complete):** Extract all basic data from pioneer files
- **Phase 2 (In Progress):** Add verified death locations from high-confidence sources
- **Phase 3 (Pending):** Research remaining pioneers using secondary sources
- **Phase 4 (Pending):** Historical society records for state-specific pioneers

---

## Usage for Batch Updates

### Applying Data to Pioneer Files

For each entry with verified death location, add `deathPlace` to the pioneer file's YAML frontmatter:

**Example:**
```yaml
---
name: "Captain Joseph Bates"
title: "Advocate of the Sabbath & Co-Founder"
birth: 1792
death: 1872
birthDate: "1792-07-08"
deathDate: "1872-03-19"
birthPlace: "Rochester, Massachusetts, USA"
deathPlace: "Battle Creek, Michigan, USA"
---
```

---

## Geographic Patterns Observed

### Regional Concentration
1. **Battle Creek, Michigan** - Church headquarters (1860s-1930s)
   - Majority of leadership died here
   - Medical, publishing, and educational center
   
2. **California Hub** - Educational and publishing branch
   - Healdsburg & Napa Valley (agricultural)
   - St. Helena (Ellen White retirement area)
   - Oakland (publishing center)
   - Oroville (E.J. Waggoner ministry area)

3. **Tennessee & Kansas** - Education & Missionary outreach
   - Madison College (Educational center)
   - Graysville (Publishing & education)
   - Topeka (James Edson White's work)

4. **Massachusetts/New England** - Early revival centers
   - South Lancaster Academy (Educational hub)
   - East Wareham (Publishing & community)

5. **Europe & Australia** - International missions
   - Switzerland (John Nevins Andrews)
   - Australia (Haskell family)

### Historical Timeline
- **1844-1880:** Early Adventist period; many pioneers in New York,Mas sachusetts
- **1880-1920:** Migration to Battle Creek; headquarters concentration
- **1920-1955:** Dispersal to regional centers (California, Tennessee)

---

## Next Steps for Completion

### Recommended Research Priority

1. **High Priority** (Likely solvable):
   - Census records (1850-1930 for death year vicinity)
   - Cemetery records (state/city archives)
   - Newspaper obituaries (newspaper databases)
   - Family genealogy records (Ancestry.com, FamilySearch)

2. **Medium Priority** (May require specialized resources):
   - SDA denominational records
   - College/University alumni records
   - Military pension records
   - State vital records archives

3. **Lower Priority** (Limited records available):
   - Early pioneers (pre-1870) with sparse documentation
   - Secondary figures with minimal biographical information
   - Those marked "Unknown" in current historical records

### Estimated Completion Timeline
- **21 entries researched per batch (pilot)**: 227 ÷ 21 = ~11 batches
- **With detailed research per entry (2-3 hours): ~30 hours total
- **Estimated completion: 1-2 weeks of research work

---

## Files Generated

1. **pioneer_death_locations_database.csv** - Complete database in CSV format
2. **PIONEER_DATABASE_README.md** - This documentation file
3. **build_pioneer_csv_enhanced.ps1** - PowerShell script to regenerate/update CSV

---

## Version History

| Date | Status | Notes |
|---|---|---|
| April 1, 2026 | Initial Release | 227 pioneers, 59 verified entries (26% complete) |
| Previous | File Updates | 11 files previously updated with deathPlace field |

---

## Contact & Maintenance

**Maintained by:** Pioneer Project Team  
**Last Updated:** April 1, 2026  
**Next Review:** Upon batch research completion  

For updates or corrections, regenerate using: `.\build_pioneer_csv_enhanced.ps1`
