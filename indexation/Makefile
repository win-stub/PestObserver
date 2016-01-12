default: out/output.txt

%.txt: %.pdf
	pdf2txt.py -c UTF-8 -o "$@" "$<"

PDFFILES=$(wildcard reportsOCR/*.pdf)
TXTFILES=$(patsubst %.pdf,%.txt,$(PDFFILES))

out/output.txt: $(TXTFILES)
	R -e 'library("x.ent"); xparse()'
