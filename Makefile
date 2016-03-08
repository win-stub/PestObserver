.PHONY : default dico index sql test

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

default: help

help:
	@echo "Usage: make (index|dico|sql|load-sql|download-bsv|copy-bsv|test)"

# Updates the index in R-lib/x.ent/out/output.txt
index: $(INDEX_OUTPUT_FILE)

$(INDEX_OUTPUT_FILE): $(TXTFILES) $(XMLFILES)
	@mkdir -p $(INDEX_OUTPUT_DIR)
	rm -f $(INDEX_OUTPUT_FILE)
	Rscript -e 'x.ent::xparse(verbose=TRUE)'

# Creates a .txt file for each .pdf file
$(REPORTS_OCR_DIR)/%.txt: $(REPORTS_DIR)/%.pdf
	@mkdir -p $(REPORTS_OCR_DIR)
	pdf2txt.py -c UTF-8 -o "$@" "$<" || touch "$@"

# Creates dictionary files in indexation/data/csv/
dico:
	cd indexation && \
	  mkdir -p data/csv && \
	  rm -f data/csv/dico*.csv && \
	  perl -I Perl -I $(XENT_HOME)/Perl Perl/CreateCSV.pl $(XENT_HOME) && \
	  mv -v $(XENT_DATA_DIR)/csv_temp/* data/csv

# Creates SQL files in indexation/data/sql/
sql:
	cd indexation && \
	  mkdir -p $(XENT_DATA_DIR)/csv && \
	  rm -f $(XENT_DATA_DIR)/csv/dico*.csv && \
	  cp data/csv/dico*.csv $(XENT_DATA_DIR)/csv/ && \
	  rm -f data/sql/* data/csv/Report.csv && \
	  perl -I Perl -I $(XENT_HOME)/Perl Perl/CreateSQL.pl $(XENT_HOME)

# Loads SQL files into the database
load-sql:
	cd indexation/data/sql && \
	  for f in plant area disease report plant_bioagressor plant_disease; do \
	    >&2 echo $$f; \
	    awk '{if(NR % 100 == 1) { printf "." > "/dev/stderr" }; print}' $$f.sql; \
	    >&2 echo; \
	  done | mysql -h$(if $(MYSQL_HOST),$(MYSQL_HOST),$(error MYSQL_HOST is not defined)) \
	    -u$(if $(MYSQL_USER),$(MYSQL_USER),$(error MYSQL_USER is not defined)) \
	    -p$(if $(MYSQL_PASSWORD),$(MYSQL_PASSWORD),$(error MYSQL_PASSWORD is not defined)) \
	    $(if $(MYSQL_DB),$(MYSQL_DB),$(error MYSQL_DB is not defined))

download-bsv:
	cd indexation && \
	  perl -I Perl Perl/DownloadBSV.pl --to=data/downloadedBSV

copy-bsv:
	cd indexation && \
	  perl -I Perl Perl/CopyBSV.pl --from=data/downloadedBSV --to=../web/reports

# Runs tests in indexation/Perl/t
test:
	cd indexation && \
	  perl -I Perl -I $(XENT_HOME)/Perl -MTest::Harness -e "runtests(glob('Perl/t/*.t'))"
