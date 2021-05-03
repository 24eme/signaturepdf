# Signature PDF

Interface de signature de PDF.

C'est pour le moment au stade de preuve de conception.

## License

Logiciel libre sous license AGPL V3

## Installation

Dépendances :

- php >= 5.6 
- rsvg-convert
- pdftk

Sur debian :

`
sudo aptitude install php rsvg-convert pdftk
`

Récupération des sources :

`
git clone https://github.com/24eme/signaturepdf.git
`

Pour le tester :

`
php -S localhost:8000 
`

## Librairies utilisées

- Fat-Free micro framework PHP : https://github.com/bcosca/fatfree (GPL v3)
- PDF.js librairie de lecture de PDF dans un canvas HTML : https://github.com/mozilla/pdf.js (Apache-2.0)
- Fabric.js librairie pour manipuler un canvas HTML : https://github.com/fabricjs/fabric.js (MIT)
- PDFtk outils de manipulation de PDF (GPL)
- librsvg outils de manipulation de SVG : https://gitlab.gnome.org/GNOME/librsvg (LGPL-2+)

