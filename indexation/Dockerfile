FROM r-base

RUN apt-get update \
 && apt-get install -y default-jdk libssl-dev libcurl4-openssl-dev libxml2-dev \
                       python2.7 python-pip libjson-perl

RUN pip install pdfminer

RUN R -e "install.packages('x.ent')"

RUN cd /opt \
 && wget --quiet http://igm.univ-mlv.fr/~unitex/Unitex3.0.zip \
 && unzip Unitex3.0.zip \
 && (cd Unitex3.0/Src/C++/build && make install) \
 && rm Unitex3.0.zip

COPY ini.json /usr/local/lib/R/site-library/x.ent/www/config/ini.json
COPY *.pl /usr/local/lib/R/site-library/x.ent/Perl/

RUN mkdir /work
WORKDIR /work

CMD ["make"]
