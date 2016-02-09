# Indexation BSV

Indexation des Bulletins de Santé Végétal (BSV) dans la base de données Vespa.

## Installation

    git clone git@github.com:ut7/PestObserver.git
    cd indexation

## Utilisation

Pour générer les fichiers dico*.csv

    make dico

Pour généré les fichiers sql destiné a créer les Tables dans la base Vespa

Il faut tout d'abord générer le fichier d'indexation (output.txt)

    make

Puis creer les fichiers SQL

    make sql

## Arboressence de répertoires

Répertoire des fichiers PDF ( le corpus )

    web/reports

Répertoire des fichiers TXT et XML correspondants au PDF

   reportsOCR

Répertoire de production des dico.csv :

    indexation/data/dicos

Répertoire de production des fichiers SQL :

    indexation/data/sql

Le fichier output.txt est généré dans l'arboressence d'installation d'x.ent

    …/x.ent/out/output.txt

