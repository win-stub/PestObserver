#! /bin/bash -e

REPORTS="downloadedBSV"
for dir in $(ls $REPORTS); do
  pdf_count="$(find "$REPORTS/$dir" -iname "*.pdf"|wc -l)"
  printf "%s PDF in %s\n" "${pdf_count}" "$dir"
done
