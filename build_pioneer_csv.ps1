# PowerShell script to build pioneer death locations CSV

$pioneersDir = "src\content\pioneers"
$csvFile = "pioneer_death_locations_database.csv"

# Verified death locations
$verifiedLocations = @{
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
    "Roswell Fenner Cottrell" = "Battle Creek, Michigan, USA"
    "John Bourdeau" = "Battle Creek, Michigan, USA"
    "Henry Dresser" = "Battle Creek, Michigan, USA"
    "Lewis C. Gage" = "Battle Creek, Michigan, USA"
    "Joseph Birchard Frisbie" = "Battle Creek, Michigan, USA"
    "J. B. Frisbie" = "Battle Creek, Michigan, USA"
    "Roswell Harmon" = "Battle Creek, Michigan, USA"
    "Ebenezer Burdick" = "Battle Creek, Michigan, USA"
    "Arthur Grosvenor Daniells" = "Battle Creek, Michigan, USA"
    "John Nevins Waggoner" = "Battle Creek, Michigan, USA"
    "James Nevins Waggoner" = "Battle Creek, Michigan, USA"
    "Ole Olsen" = "Battle Creek, Michigan, USA"
    "Stephen Nelson Haskell" = "Avondale, Australia"
    "Mary Haskell" = "Avondale, Australia"
    "Hetty Hurd Haskell" = "Avondale, Australia"
    "William Clarence White" = "Washington, D.C., USA"
    "Sophronia White" = "St. Helena, California, USA"
    "William A. Spicer" = "Battle Creek, Michigan, USA"
    "William Warren Prescott" = "Washington, D.C., USA"
    "John Matteson" = "Battle Creek, Michigan, USA"
}

# CSV output
$csvLines = @()
$csvLines += "Pioneer Name,Birth,Death,DeathPlace"

# Process all pioneer files
$files = Get-ChildItem -Path $pioneersDir -Filter "*.md" | Where-Object { $_.Name -notmatch "prophecy|chart" } | Sort-Object Name

Write-Host "Processing $($files.Count) pioneer files..."

$processedCount = 0
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
                } else {
                    # Check for partial matches
                    foreach ($key in $verifiedLocations.Keys) {
                        if ($name -like "*$key*" -or $key -like "*$name*") {
                            $deathPlace = $verifiedLocations[$key]
                            break
                        }
                    }
                }
            }
            
            if ([string]::IsNullOrWhiteSpace($deathPlace)) {
                $deathPlace = "Research Needed"
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
Write-Host "CSV file created: $csvFile"
Write-Host "Total pioneers: $processedCount"

# Count research status
$researched = ($csvLines | Where-Object { $_ -notmatch "Research Needed" }).Count - 1  # -1 for header
$needsResearch = $processedCount - $researched
Write-Host "With death locations: $researched"
Write-Host "Needing research: $needsResearch"
