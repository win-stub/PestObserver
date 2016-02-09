FROM r-base

RUN apt-get update \
 && apt-get install -y default-jdk libssl-dev libcurl4-openssl-dev libxml2-dev \
                       python2.7 python-pip libjson-perl

RUN pip install pdfminer

RUN cd /opt \
 && wget --quiet http://igm.univ-mlv.fr/~unitex/Unitex3.0.zip \
 && unzip Unitex3.0.zip \
 && (cd Unitex3.0/Src/C++/build && make install) \
 && rm Unitex3.0.zip

RUN R -e "install.packages('devtools')"

# RUN R -e "library('devtools'); install_github('win-stub/x.ent')"
RUN R -e "library('devtools'); install_github('ut7/x.ent', ref='d376ee5')"

COPY . /vespa

COPY indexation/ini.json /usr/local/lib/R/site-library/x.ent/www/config/ini.json

WORKDIR /vespa

CMD ["make"]
