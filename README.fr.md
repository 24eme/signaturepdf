<sup>**[Français](README.md)** | [English](README.en.md)</sup>

# Signature de PDF 

Logiciel web libre permettant de signer un PDF.

## Instances

Liste des instances permettant d'utiliser ce logiciel :

- [pdf.24eme.fr](https://pdf.24eme.fr)
- [pdf.libreon.fr](https://pdf.libreon.fr)
- [pdf.hostux.net](https://pdf.hostux.net)
- [pdf.nebulae.co](https://pdf.nebulae.co)
- [pdf.kaosx.ovh](https://pdf.kaosx.ovh)
- [pdf.ti-nuage.fr](https://pdf.ti-nuage.fr)
- [pdf.cemea.org](https://pdf.cemea.org)
- [pdf.spirio.fr](https://pdf.spirio.fr)
- [pdf.sequanux.org](https://pdf.sequanux.org)
- [pdf.deblan.org](https://pdf.deblan.org)
- [signpdf.liber-it.fr](https://signpdf.liber-it.fr)

_N'hésitez pas à rajouter la votre via une issue ou une pull request_

## License

Logiciel libre sous license AGPL V3

## Installation

### Debian/Ubuntu

Dépendances :

- php >= 5.6
- rsvg-convert
- pdftk
- imagemagick
- potrace

Installation des dépendances :

```
sudo aptitude install php librsvg2-bin pdftk imagemagick potrace
```

Récupération des sources :

```
git clone https://github.com/24eme/signaturepdf.git
```

Pour le lancer :

```
php -S localhost:8000 -t public
```

#### Configuration de PHP

```
upload_max_filesize = 24M # Taille maximum du fichier PDF à signer
post_max_size = 24M # Taille maximum du fichier PDF à signer
max_file_uploads = 201 # Nombre de pages maximum du PDF, ici 200 pages + le PDF d'origine
```

#### Configuration d'apache

```
DocumentRoot /path/to/signaturepdf/public

<Directory /path/to/signaturepdf/public>
    Require all granted
    FallbackResource /index.php
    php_value max_file_uploads 201
    php_value upload_max_filesize 24M
    php_value post_max_size 24M
</Directory>
```

### Déployer avec docker

#### Construction de l'image

```bash
docker build -t signaturepdf .
```

#### Lancement d'un conteneur

```bash
docker run -d --name=signaturepdf -p 8080:80 signaturepdf
```

[localhost:8080](http://localhost:8080)

#### Configuration

Les variables suivantes permettent de configurer le déployement :

| Variable               | description                                                        | exemple                          | defaut    |
| ---------------------- | ------------------------------------------------------------------ | -------------------------------- | --------- |
| `SERVERNAME`           | url de déploiement                                                 | `pdf.24eme.fr`                   | localhost |
| `UPLOAD_MAX_FILESIZE`  | Taille maximum du fichier PDF à signer                             | 48M                              | 24M       |
| `POST_MAX_SIZE`        | Taille maximum du fichier PDF à signer                             | 48M                              | 24M       |
| `MAX_FILE_UPLOADS`     | Nombre de pages maximum du PDF, ici 200 pages + le PDF d'origine   | 401                              | 201       |
| `PDF_STORAGE_PATH`     | chemin vers lequel les fichiers pdf uploadés pourront être stockés | /data                            | /data     |
| `DISABLE_ORGANIZATION` | Desactiver la route Organiser                                      | true                             | false     |
| `PDF_DEMO_LINK`        | Afficher, retirer ou changer le lien de PDF de démo                | false, `link` or `relative path` | true      |

```bash
docker run -d --name=signaturepdf -p 8080:80 -e SERVERNAME=pdf.example.org -e UPLOAD_MAX_FILESIZE=48M -e POST_MAX_SIZE=48M -e MAX_FILE_UPLOADS=401 -e PDF_STORAGE_PATH=/data signaturepdf
```

### Alpine

Voici un script permettant d'installer la solution sous Linux Alpine (testé en version 3.15).
Pensez à éditer la variable "domain" en début de script pour correspondre à l'URL avec laquelle elle sera appelée.

Les composants principaux sont :

- php 8 + php-fpm
- Nginx
- pdftk (installation "manuelle" nécessitant openjdk8)
- imagick
- potrace
- librsvg

Ce que fait le script :

- Installation des dépendances
- Configuration de php et php-fpm
- Configuration d'Nginx
- Configuration du config.ini
- Git clone du repo

```
#!/bin/sh

domain='sign.example.com'

apk update
apk add bash nginx git php8 php8-fpm php8-session php8-gd php8-fileinfo openjdk8 imagemagick potrace librsvg

cd /tmp
wget https://gitlab.com/pdftk-java/pdftk/-/jobs/924565145/artifacts/raw/build/libs/pdftk-all.jar
mv pdftk-all.jar pdftk.jar

cat <<EOF >>pdftk
#!/usr/bin/env bash
/usr/bin/java -jar "\$0.jar" "\$@"
EOF

chmod 775 pdftk*
mv pdftk* /usr/bin

sed -i 's/user = nobody/user = nginx/g' /etc/php8/php-fpm.d/www.conf
sed -i 's/;listen.owner = nginx/listen.owner = nginx/g' /etc/php8/php-fpm.d/www.conf

sed -i 's/post_max_size = 8M/post_max_size = 50M/g' /etc/php8/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/g' /etc/php8/php.ini
sed -i 's/max_file_uploads = 20 /max_file_uploads = 300/g' /etc/php8/php.ini

service php-fpm8 restart

cd /var/www
git clone https://github.com/24eme/signaturepdf.git

cat <<EOF >>/etc/nginx/http.d/signaturepdf.conf
server {

        listen 80 default_server;
        listen [::]:80 default_server;

        server_name ${domain};

        client_max_body_size 0;

        root /var/www/signaturepdf/public/;

        index           index.php index.html;

        location / {
        # URLs to attempt, including pretty ones.
        try_files   \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ [^/]\.php(/|$) {
                root /var/www/signaturepdf/public/;

                fastcgi_split_path_info  ^(.+\.php)(/.+)$;
                fastcgi_index            index.php;
                include                  fastcgi_params;

                fastcgi_buffer_size 128k;
                fastcgi_buffers 128 128k;
                fastcgi_param   PATH_INFO       \$fastcgi_path_info;
                fastcgi_param   SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
                fastcgi_pass 127.0.0.1:9000;

        }

}
EOF

rm /etc/nginx/http.d/default.conf
rm -R /var/www/localhost

service nginx restart

rc-update add nginx
rc-update add php-fpm8

mkdir /var/www/signaturepdf/tmp
chown nginx /var/www/signaturepdf/tmp

cat <<EOF >>/var/www/signaturepdf/config/config.ini
PDF_STORAGE_PATH=/var/www/signaturepdf/tmp
EOF
```

## Configuration

### Activation et configuration du mode partage de signature à plusieurs

Ce mode permet de proposer la signature d'un pdf à plusieurs personnes mais il nécessite que les PDF soient stockés sur le serveur, il convient donc de définir un dossier qui contiendra ces PDF.

Il n'est pas obligatoire d'activer ce mode pour que l'application fonctionne c'est une option.

Créer le fichier `config/config.ini`

```
cp config/config.ini{.example,}
```

Dans ce fichier `config/config.ini`, il suffit ce configurer la variable `PDF_STORAGE_PATH` avec le chemin vers lequel les fichiers pdf uploadés pourront être stockés :

```
PDF_STORAGE_PATH=/path/to/folder
```

Créer ce dossier :

```
mkdir /path/to/folder
```

Le serveur web devra avoir les droits en écriture sur ce dossier.

Par exemple pour apache :

```
chown www-data /path/to/folder/to/store/pdf
```

### Desactivation du mode Organiser

Pour desactiver le mode Organiser, ajouter `DISABLE_ORGANIZATION=true` dans le fichier
`config/config.ini`.

### Cacher ou modifier le lien de PDF de démo

Pour cacher le lien de pdf de démo, ajouter `PDF_DEMO_LINK=false` dans le fichier
`config/config.ini`.

### Champs chargés par défaut pour l'édition de métadonnéés

Dans le fichier de configuration `config/config.ini` il est possible de rajouter autant de champs que l'on souhaite avec le type HTML de l'input (text, date, number email, etc ...) qui seront préchargées pour chaque PDF.

```
METADATA_DEFAULT_FIELDS[field1].type = "text"
METADATA_DEFAULT_FIELDS[field2].type = "text"
METADATA_DEFAULT_FIELDS[field3].type = "date"
METADATA_DEFAULT_FIELDS[field4].type = "number"
```

## Mise à jour

La dernière version stable est sur la branche `master`, pour la mise à jour il suffit de récupérer les dernières modifications :

```
git pull -r
```

## Tests

Pour exécuter les tests fonctionnels :

```
make test
```

Les tests sont réalisés avec `puppeteer` et `jest`.

Pour lancer les tests et voir le navigateur (en mode debug) :

```
DEBUG=1 make test
```

## Librairies utilisées

- **Fat-Free** micro framework PHP : https://github.com/bcosca/fatfree (GPLv3)
- **Bootstrap** framework html, css et javascript : https://getbootstrap.com/ (MIT)
- **PDF.js** librairie de lecture de PDF dans un canvas HTML : https://github.com/mozilla/pdf.js (Apache-2.0)
- **Fabric.js** librairie pour manipuler un canvas HTML : https://github.com/fabricjs/fabric.js (MIT)
- **PDFtk** outils de manipulation de PDF (GPL)
- **librsvg** outils de manipulation de SVG : https://gitlab.gnome.org/GNOME/librsvg (LGPL-2+)
- **potrace** outils de transformation d'image bitamp en image vectorisé : http://potrace.sourceforge.net/ (GPLv2)
- **OpenType.js** outils de transformation d'un texte et sa police en chemin : https://github.com/opentypejs/opentype.js (MIT)
- **ImageMagick** ensemble d'outils de manipulation d'images : https://imagemagick.org/ (Apache-2.0)
- **Caveat** police de caractères style écriture à la main : https://github.com/googlefonts/caveat (OFL-1.1)
- **PDF-LIB** librairie js permettant de manipuler un PDF qui est utilisé pour écrire dans les métadonnées : https://pdf-lib.js.org/ (MIT)

Pour les tests :

- **Jest** Framework de Test Javascript : https://jestjs.io/ (MIT)
- **Puppeteer** librairie Node.js pour contrôler un navigateur : https://github.com/puppeteer/puppeteer (Apache-2.0)

## Contributions

### Traductions

Pour mettre à jour la traduction, exécutez simplement `make` qui mettra a jour le fichier `.pot`,
qui fera un merge avec les fichiers `.po` qui permetteront de recréer les fichiers `.mo` mis a jour.

Des traductions peuvent etre ajoutées sur Weblate : https://hosted.weblate.org/projects/signature-pdf/application/

### Contributeurs

Ces personnes sont auteurices du code de ce logiciel :

Vincent LAURENT (24ème), Jean-Baptiste Le Metayer (24ème), Xavier Garnier (Logilab), Simon Chabot (Logilab), Tangui Morlier (24ème), Gabriel POMA (24ème), Tanguy Le Faucheur (24ème), Étienne Deparis, battosai30

### Financements

- 1 365 € HT de la société Logilab pour le développement du mode signature partagé à plusieurs
- 1 950 € HT de la société Logilab pour le développement de l'édition des métadonnées
- 100 € HT de don de la société Spirkop
- 100 € HT de don de la société PDG IT
- 1 040 € HT de la fondation NLNet pour l'internationalisation du logiciel

Les modules signature et organiser ont été réalisés sur le temps de travail de salariés du 24ème.
