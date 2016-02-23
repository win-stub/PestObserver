.PHONY : default dico sql clean-sql test

R_LIBS_USER=$(abspath R-lib)
export R_LIBS_USER

XENT_HOME:=$(R_LIBS_USER)/x.ent
XENT_DATA_DIR:=$(XENT_HOME)/Perl/data

REPORTS_DIR:=web/reports
REPORTS_OCR_DIR:=reportsOCR
INDEX_OUTPUT_DIR:=$(XENT_HOME)/out
INDEX_OUTPUT_FILE:=$(INDEX_OUTPUT_DIR)/output.txt

PDFFILES:=$(wildcard $(REPORTS_DIR)/*.pdf)
XMLFILES:=$(wildcard $(REPORTS_OCR_DIR)/*.xml)
TXTFILES:=$(filter-out $(XMLFILES:.xml=.txt),$(patsubst $(REPORTS_DIR)/%.pdf,$(REPORTS_OCR_DIR)/%.txt,$(PDFFILES)))

default: $(INDEX_OUTPUT_FILE)

$(INDEX_OUTPUT_FILE): $(TXTFILES) $(XMLFILES)
	@mkdir -p $(INDEX_OUTPUT_DIR)
	rm -f $(INDEX_OUTPUT_FILE)
	Rscript -e 'x.ent::xparse()'

$(REPORTS_OCR_DIR)/%.txt: $(REPORTS_DIR)/%.pdf
	@mkdir -p $(REPORTS_OCR_DIR)
	pdf2txt.py -c UTF-8 -o "$@" "$<"

dico:
	cd indexation && \
	  perl -I Perl -I $(XENT_HOME)/Perl Perl/CreateCSV.pl $(XENT_HOME) && \
	  mkdir -p $(XENT_DATA_DIR)/csv && \
	  cp -v $(XENT_DATA_DIR)/csv_temp/* $(XENT_DATA_DIR)/csv

sql:
	cd indexation && \
	  rm -f data/sql/* data/csv/Report.csv && \
	  perl -I Perl -I $(XENT_HOME)/Perl Perl/CreateSQL.pl $(XENT_HOME)

test:
	cd indexation && \
	  perl -I Perl -I $(XENT_HOME)/Perl -MTest::Harness -e "runtests(glob('Perl/t/*.t'))"
