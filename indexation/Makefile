.PHONY : default dico

R_LIB_PATH=$(shell R --slave -e "cat(.libPaths()[1])")
XENT_HOME:=$(R_LIB_PATH)/x.ent

default: out/output.txt

reportsOCR/%.txt: reports/%.pdf
	pdf2txt.py -c UTF-8 -o "$@" "$<"

PDFFILES=$(wildcard reports/*.pdf)
TXTFILES=$(patsubst reports/%.pdf,reportsOCR/%.txt,$(PDFFILES))

dico:
	mkdir -p data/csv_temp \
	&& perl -I ./Perl -I $(XENT_HOME)/Perl ./Perl/CreateCSV.pl

sql:
	perl -I ./Perl -I $(XENT_HOME)/Perl ./Perl/CreateSQL.pl

clean-sql:
	rm -f ./data/sql/* \
	&& rm -f ./data/csv/Report.csv

out/output.txt: $(TXTFILES)
	R -e 'library("x.ent"); xparse()'
