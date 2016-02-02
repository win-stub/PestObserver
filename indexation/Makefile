.PHONY : default dico sql clean-sql test

R_LIB_PATH=$(shell R --slave -e "cat(.libPaths()[1])")
XENT_HOME:=$(R_LIB_PATH)/x.ent

default: out/output.txt

reportsOCR/%.txt: reports/%.pdf
	pdf2txt.py -c UTF-8 -o "$@" "$<"

PDFFILES=$(wildcard reports/*.pdf)
TXTFILES=$(patsubst reports/%.pdf,reportsOCR/%.txt,$(PDFFILES))

dico:
	perl -I ./Perl -I $(XENT_HOME)/Perl ./Perl/CreateCSV.pl $(XENT_HOME)
	mkdir -p $(XENT_HOME)/Perl/data/csv
	cp -v $(XENT_HOME)/Perl/data/csv_temp/* $(XENT_HOME)/Perl/data/csv

sql:
	perl -I ./Perl -I $(XENT_HOME)/Perl ./Perl/CreateSQL.pl "$(XENT_HOME)"

clean-sql:
	rm -f ./data/sql/* \
	&& rm -f ./data/csv/Report.csv

out/output.txt: $(TXTFILES)
	R -e 'library("x.ent"); xparse()'

test:
	perl -I ./Perl -I $(XENT_HOME)/Perl -MTest::Harness -e "runtests(glob('./Perl/t/*.t'))"
