#!/bin/bash

# Script to refactor ApiException to AppException across the project
echo "Starting ApiException to AppException refactor..."

# Find all PHP files that contain ApiException
files=$(grep -r "ApiException" --include="*.php" app/ | cut -d: -f1 | sort | uniq)

echo "Found files containing ApiException:"
echo "$files"

# Process each file
for file in $files; do
    echo "Processing: $file"

    # Replace import statements
    sed -i '' 's/use App\\Exceptions\\ApiException;/use App\\Exceptions\\AppException;/g' "$file"

    # Replace class usage in throws annotations
    sed -i '' 's/@throws ApiException/@throws AppException/g' "$file"

    # Replace instantiation - this needs more careful handling
    # We'll replace basic patterns but complex ones may need manual review
    sed -i '' 's/new ApiException(/new AppException(/g' "$file"

    echo "Completed: $file"
done

echo "Refactor complete! Please review the changes and test thoroughly."
echo "Note: Complex ApiException usages may need manual review."
