# vespa
Vespa Mining

# Installation

1. git clone du projet
2. composer update pour télécharger les dépendances
3. créer la base de données et le user dédié
4. jouer les scripts sql de création des tables et de données
5. Configurer les accès à la BDD dans le fichier index.php
6. Configurer l'emplacement des logs dans le fichier index.php
7. Faire pointer un virtual hosts apache sur le répertoire web avec au moins ces options :
  RewriteEngine on
	AddDefaultCharset utf-8
	DocumentRoot /vagrant/vespa/web
	<Directory /vagrant/vespa/web>
		Header set Access-Control-Allow-Origin "*"
		AllowOverride All
8. Télécharger les reports depuis files.inra-ifris.org et les placer dans le répertoire web/reports

Done.
