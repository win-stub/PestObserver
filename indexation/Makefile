.PHONY : default dico

R_LIB_PATH=$(shell R --slave -e "cat(.libPaths()[1])")
XENT_HOME:=$(R_LIB_PATH)/x.ent

default: out/output.txt

%.txt: %.pdf
	pdf2txt.py -c UTF-8 -o "$@" "$<"

PDFFILES=$(wildcard reports/*.pdf)
TXTFILES=$(patsubst reports/%.pdf,reportsOCR/%.txt,$(PDFFILES))

dico:
	mkdir -p data/csv_temp \
	&& perl -I $(XENT_HOME)/Perl $(XENT_HOME)/Perl/CreateCSV.pl

sql:
	perl -I $(XENT_HOME)/Perl $(XENT_HOME)/Perl/CreateSQL.pl

out/output.txt: $(TXTFILES)
	R -e 'library("x.ent"); xparse()'
