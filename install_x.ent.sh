#!/bin/bash -e

VESPA_HOME=$(cd "$(dirname "$0")" && pwd)
export R_LIBS_USER=${VESPA_HOME}/R-lib
XENT_HOME=${R_LIBS_USER}/x.ent
XENT_CONFIG_FILE=${XENT_HOME}/www/config/ini.json

# Emplacement de Unitex sur le serveur...
: ${UNITEX_HOME:=/srv/lisis-lab/scripts/Unitex/Unitex3.0}

mkdir -p $R_LIBS_USER

Rscript - <<EOF
  if(!require('devtools')) {
    install.packages('devtools', repos='http://cran.us.r-project.org')
  }
  devtools::install_github('win-stub/x.ent', upgrade_dependencies=FALSE)
EOF

sed -e "s:%VESPA_HOME%:${VESPA_HOME}:g" -e "s:%UNITEX_HOME%:${UNITEX_HOME}:g" \
  < indexation/ini.json.dist > $XENT_CONFIG_FILE

echo "
------------------------------------------

Installation terminée dans ${R_LIBS_USER}.

Pour utiliser x.ent:
 \$ export R_LIBS_USER=${R_LIBS_USER}
 \$ R
 > library('x.ent')
 ...

Penser à modifier ${XENT_CONFIG_FILE} !
"
