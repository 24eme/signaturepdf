# Signature de PDF

Logiciel web libre permettant de signer un PDF.

## Instances

Liste des instances permettant d'utiliser ce logiciel :

- [pdf.24eme.fr](https://pdf.24eme.fr)

## License

Logiciel libre sous license AGPL V3

## Installation

Dépendances :

- php >= 5.6 
- rsvg-convert
- pdftk
- imagemagick
- potrace

Sur debian :

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

### Configuration de PHP

```
upload_max_filesize = 24M # Taille maximum du fichier PDF à signer
post_max_size = 24M # Taille maximum du fichier PDF à signer
max_file_uploads = 201 # Nombre de pages maximum du PDF, ici 200 pages + le PDF d'origine
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


