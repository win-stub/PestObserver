.PHONY : default dico sql clean-sql test

R_LIB_PATH:=$(shell R --slave -e "cat(.libPaths()[1])")
XENT_HOME:=$(R_LIB_PATH)/x.ent

REPORTS_DIR:=web/reports
REPORTS_OCR_DIR:=reportsOCR
INDEX_OUTPUT_DIR:=$(XENT_HOME)/out

PDFFILES:=$(wildcard $(REPORTS_DIR)/*.pdf)
XMLFILES:=$(wildcard $(REPORTS_OCR_DIR)/*.xml)
TXTFILES:=$(filter-out $(XMLFILES:.xml=.txt),$(patsubst $(REPORTS_DIR)/%.pdf,$(REPORTS_OCR_DIR)/%.txt,$(PDFFILES)))

default: $(INDEX_OUTPUT_DIR)/output.txt

$(INDEX_OUTPUT_DIR)/output.txt: $(TXTFILES) $(XMLFILES)
	@mkdir -p "$(INDEX_OUTPUT_DIR)"
	Rscript -e 'library("x.ent"); xparse()'

$(REPORTS_OCR_DIR)/%.txt: $(REPORTS_DIR)/%.pdf
	@mkdir -p "$(REPORTS_OCR_DIR)"
	pdf2txt.py -c UTF-8 -o "$@" "$<"

dico:
	perl -I indexation/Perl -I "$(XENT_HOME)/Perl" indexation/Perl/CreateCSV.pl "$(XENT_HOME)"
	mkdir -p "$(XENT_HOME)"/Perl/data/csv
	cp -v "$(XENT_HOME)"/Perl/data/csv_temp/* "$(XENT_HOME)"/Perl/data/csv

sql:
	perl -I indexation/Perl -I "$(XENT_HOME)/Perl" indexation/Perl/CreateSQL.pl "$(XENT_HOME)"

clean-sql:
	rm -f data/sql/*
	rm -f data/csv/Report.csv

test:
	perl -I indexation/Perl -I "$(XENT_HOME)/Perl" -MTest::Harness -e "runtests(glob('indexation/Perl/t/*.t'))"
