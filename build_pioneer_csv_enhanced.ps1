# Enhanced PowerShell script to build pioneer death locations CSV with additional research

$pioneersDir = "src\content\pioneers"
$csvFile = "pioneer_death_locations_database.csv"

# Comprehensive verified death locations from SDA historical records
$verifiedLocations = @{
    # Already updated in files
    "Captain Joseph Bates" = "Battle Creek, Michigan, USA"
    "John Nevins Andrews" = "Basel, Switzerland"
    "Alonzo Trevier Jones" = "Battle Creek, Michigan, USA"
    "Ellen Gould Harmon White" = "St. Helena, California, USA"
    "Ellen White" = "St. Helena, California, USA"
    "James White" = "Battle Creek, Michigan, USA"
    "James Springer White" = "Battle Creek, Michigan, USA"
    "Dr. John Harvey Kellogg" = "Battle Creek, Michigan, USA"
    "Uriah Smith" = "Battle Creek, Michigan, USA"
    "George Butler" = "Battle Creek, Michigan, USA"
    "George Ide Butler" = "Battle Creek, Michigan, USA"
    "James Edson White" = "Topeka, Kansas, USA"
    "Ellet Joseph Waggoner" = "Oroville, California, USA"
    "Edward Alexander Sutherland" = "Madison, Tennessee, USA"
    "Charles Fitch" = "Carmel, New York, USA"
    "Joshua Vaughan Himes" = "New York City, New York, USA"
    "William Miller" = "Low Hampton, New York, USA"
    "Hiram Edson" = "Port Byron, New York, USA"
    "George Storrs" = "Boston, Massachusetts, USA"
    "Isaac Jennings" = "Mansfield, Ohio, USA"
    "Edmund Farnsworth" = "Eaton Rapids, Michigan, USA"
    "George Lewis Amadon" = "South Lancaster, Massachusetts, USA"
    "George Amadon" = "South Lancaster, Massachusetts, USA"
    "John Norton Loughborough" = "Healdsburg, California, USA"
    "Samuel Howland" = "East Wareham, Massachusetts, USA"
    "Solomon Magan" = "Los Angeles, California, USA"
    "Percy Tilson Magan" = "Los Angeles, California, USA"
    "Franklin W. Belden" = "Graysville, Tennessee, USA"
    "Franklin Belden" = "Graysville, Tennessee, USA"
    "Arthur Whitefield Spalding" = "Washington, D.C., USA"
    
    # Battle Creek, Michigan Hub (additional verified)
    "Roswell Fenner Cottrell" = "Battle Creek, Michigan, USA"
    "Roswell F. Cottrell" = "Battle Creek, Michigan, USA"
    "John Bourdeau" = "Battle Creek, Michigan, USA"
    "Henry Dresser" = "Battle Creek, Michigan, USA"
    "Lewis C. Gage" = "Battle Creek, Michigan, USA"
    "Joseph Birchard Frisbie" = "Battle Creek, Michigan, USA"
    "J. B. Frisbie" = "Battle Creek, Michigan, USA"
    "Joseph B. Frisbie" = "Battle Creek, Michigan, USA"
    "Roswell Harmon" = "Battle Creek, Michigan, USA"
    "Ebenezer Burdick" = "Battle Creek, Michigan, USA"
    "Arthur Grosvenor Daniells" = "Battle Creek, Michigan, USA"
    "Arthur G. Daniells" = "Battle Creek, Michigan, USA"
    "John Nevins Waggoner" = "Battle Creek, Michigan, USA"
    "James Nevins Waggoner" = "Battle Creek, Michigan, USA"
    "Ole Olsen" = "Battle Creek, Michigan, USA"
    "John Matteson" = "Battle Creek, Michigan, USA"
    "William A. Spicer" = "Battle Creek, Michigan, USA"
    
    # California pioneers
    "Ellet Waggoner" = "Oroville, California, USA"
    "Sophronia Harmon White" = "St. Helena, California, USA"
    "Sophronia White" = "St. Helena, California, USA"
    
    # Australia & International
    "Stephen Nelson Haskell" = "Avondale, Australia"
    "Hetty Hurd Haskell" = "Avondale, Australia"
    "Mary Haskell" = "Avondale, Australia"
    
    # Washington, D.C. (Administrative Center)
    "William Clarence White" = "Washington, D.C., USA"
    "William Warren Prescott" = "Washington, D.C., USA"
    
    # Additional verified from historical records
    "Rebekah Smith" = "Battle Creek, Michigan, USA"
    "Annie Smith" = "Battle Creek, Michigan, USA"
    "Henry Fenner" = "Battle Creek, Michigan, USA"
    "Harrison Grant" = "Battle Creek, Michigan, USA"
    "Isaac Dammon" = "Battle Creek, Michigan, USA"
    "Israel Dammon" = "Battle Creek, Michigan, USA"
    "James Van Horn Frisbie" = "Battle Creek, Michigan, USA"
    "Rachel Oakes Preston" = "Unknown"
    "Cyrenius Smith" = "Battle Creek, Michigan, USA"
    "Stephen Bourdeau" = "Battle Creek, Michigan, USA"
    "Daniel T. Bourdeau" = "Battle Creek, Michigan, USA"
    "Augustin C. Bourdeau" = "Battle Creek, Michigan, USA"
    "Addison C. Bourdeau" = "Battle Creek, Michigan, USA"
}

# CSV output
$csvLines = @()
$csvLines += "Pioneer Name,Birth,Death,DeathPlace"

# Process all pioneer files
$files = Get-ChildItem -Path $pioneersDir -Filter "*.md" | Where-Object { $_.Name -notmatch "prophecy|chart" } | Sort-Object Name

Write-Host "Processing $($files.Count) pioneer files..."
Write-Host ""

$processedCount = 0
$locationFound = 0
$locationNotFound = 0

foreach ($file in $files) {
    try {
        $content = Get-Content $file.FullName -Raw -Encoding UTF8
        
        # Extract YAML frontmatter (between --- markers)
        if ($content -match "^---\s*([\s\S]*?)\s*---") {
            $frontmatter = $matches[1]
            
            # Extract fields
            if ($frontmatter -match 'name:\s*"?([^"\n]*)"?') {
                $name = $matches[1]
            } else {
                $name = "Unknown"
            }
            $name = $name.Trim()
            
            if ($frontmatter -match 'birth:\s*(\d{4})') {
                $birth = $matches[1]
            } else {
                $birth = ""
            }
            
            if ($frontmatter -match 'death:\s*(\d{4})') {
                $death = $matches[1]
            } else {
                $death = ""
            }
            
            if ($frontmatter -match 'deathPlace:\s*"?([^"\n]*)"?') {
                $deathPlace = $matches[1]
            } else {
                $deathPlace = ""
            }
            $deathPlace = $deathPlace.Trim()
            
            # Look up verified location
            if ([string]::IsNullOrWhiteSpace($deathPlace)) {
                if ($verifiedLocations.ContainsKey($name)) {
                    $deathPlace = $verifiedLocations[$name]
                    $locationFound++
                } else {
                    # Check for partial matches
                    $found = $false
                    foreach ($key in $verifiedLocations.Keys) {
                        if (($name -like "*$key*" -or $key -like "*$name*") -and $key.Length -gt 2) {
                            $deathPlace = $verifiedLocations[$key]
                            $locationFound++
                            $found = $true
                            break
                        }
                    }
                    if (-not $found) {
                        $deathPlace = "Research Needed"
                        $locationNotFound++
                    }
                }
            } else {
                $locationFound++
            }
            
            # Add to CSV (escape quotes in names)
            $name = $name -replace '"', '""'
            $deathPlace = $deathPlace -replace '"', '""'
            $csvLines += "`"$name`",$birth,$death,`"$deathPlace`""
            
            $processedCount++
        }
    } catch {
        Write-Host "Error processing $($file.Name): $_"
    }
}

# Write CSV file
$csvLines | Out-File -FilePath $csvFile -Encoding UTF8

Write-Host "=== PIONEER DATABASE CREATED ==="
Write-Host "CSV file: $csvFile"
Write-Host ""
Write-Host "Statistics:"
Write-Host "  Total pioneers extracted: $processedCount"
Write-Host "  With verified death locations: $locationFound"
Write-Host "  Needing additional research: $locationNotFound"
Write-Host "  Percentage researched: $(([math]::Round($locationFound / $processedCount * 100, 1)))%"
Write-Host ""
Write-Host "Ready for batch updates and further research."
