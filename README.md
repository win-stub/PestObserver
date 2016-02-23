# vespa
Vespa Mining

# Installation

1. git clone du projet
2. composer update pour télécharger les dépendances
3. créer la base de données et le user dédié
4. jouer les scripts sql de création des tables et de données
5. Créer le fichier parameters.json à partir de parameters.json.dist
6. Configurer les accès à la BDD dans le fichier parameters.json
7. Configurer l'emplacement des logs dans le fichier parameters.json
8. Faire pointer un virtual hosts apache sur le répertoire web avec au moins ces options :
  RewriteEngine on
	AddDefaultCharset utf-8
	DocumentRoot /vagrant/vespa/web
	<Directory /vagrant/vespa/web>
		Header set Access-Control-Allow-Origin "*"
		AllowOverride All
9. Télécharger les reports depuis files.inra-ifris.org et les placer dans le répertoire web/reports

Done.

# Configuration de l'indexation

## Prérequis

* [R](https://www.r-project.org/)

## Installer une version locale de [x.ent](https://github.com/win-stub/x.ent)

Exécuter depuis le répertoire du projet :

    $ ./install_x.ent.sh

Ceci télécharge et installe x.ent et ses dépendances dans un sous-répertoire `R-lib`.
