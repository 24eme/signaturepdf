<sup>[Français](README.md) | **[English](README.en.md)**</sup>

# PDF Signature

Free web software for signing PDFs.

## Instances
List of instances where you can use this software:
- [pdf.24eme.fr](https://pdf.24eme.fr)
- [pdf.libreon.fr](https://pdf.libreon.fr)
- [pdf.hostux.net](https://pdf.hostux.net)
- [pdf.nebulae.co](https://pdf.nebulae.co)

_Feel free to add yours through an issue or a pull request._

## License
Open-source software under the AGPL V3 license.

## Installation
### Debian/Ubuntu
Dependencies:
- php >= 5.6
- rsvg-convert
- pdftk
- imagemagick
- potrace

Installing dependencies:
```
sudo aptitude install php librsvg2-bin pdftk imagemagick potrace
```

Getting the source code:

```
git clone https://github.com/24eme/signaturepdf.git
```

To run it:

```
php -S localhost:8000 -t public
```

#### PHP Configuration

```
upload_max_filesize = 24M # Maximum size of the PDF file to sign
post_max_size = 24M # Maximum size of the PDF file to sign
max_file_uploads = 201 # Maximum number of pages in the PDF, here 200 pages + the original PDF
```

#### Apache Configuration

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

### Deploy with Docker

#### Building the image

```bash
docker build -t signaturepdf .
```

#### Running a container

```bash
docker run -d --name=signaturepdf -p 8080:80 signaturepdf
```

[localhost:8080](http://localhost:8080)

#### Configuration

The following variables can be used to configure the deployment:

| Variable               | description                                                           | exemple                          | defaut    |
| ---------------------- |-----------------------------------------------------------------------| -------------------------------- | --------- |
| `SERVERNAME`           | Deployment URL                                                        | `pdf.24eme.fr`                   | localhost |
| `UPLOAD_MAX_FILESIZE`  | Maximum size of the PDF file to sign                                  | 48M                              | 24M       |
| `POST_MAX_SIZE`        | Maximum size of the PDF file to sign                                  | 48M                              | 24M       |
| `MAX_FILE_UPLOADS`     | Maximum number of pages in the PDF, here 200 pages + the original PDF | 401                              | 201       |
| `PDF_STORAGE_PATH`     | Path where uploaded PDF files can be stored                           | /data                            | /data     |
| `DISABLE_ORGANIZATION` | Disable the Organize route                                            | true                             | false     |
| `PDF_DEMO_LINK`        | Show, hide, or change the demo PDF link                               | false, `link` or `relative path` | true      |

```bash
docker run -d --name=signaturepdf -p 8080:80 -e SERVERNAME=pdf.example.org -e UPLOAD_MAX_FILESIZE=48M -e POST_MAX_SIZE=48M -e MAX_FILE_UPLOADS=401 -e PDF_STORAGE_PATH=/data signaturepdf
```

### Alpine

Here is a script to install the solution on Linux Alpine (tested with version 3.15).
Remember to edit the "domain" variable at the beginning of the script to match the URL it will be called with.

The main components are:

- php 8 + php-fpm
- Nginx
- pdftk ("manual" installation requiring openjdk8)
- imagick
- potrace
- librsvg

What the script does:

- Installs dependencies
- Configures php and php-fpm
- Configures Nginx
- Configures the config.ini
- Clones the repo

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

### Enabling and Configuring Multi-Signature Mode

This mode allows multiple people to sign a PDF, but it requires that the PDFs be stored on the server.

It is not mandatory to enable this mode for the application to work; it is an option.

Create the `config/config.ini` file

```
cp config/config.ini{.example,}
```

In the `config/config.ini` file, configure the `PDF_STORAGE_PATH` variable with the path where uploaded PDF files can be stored:

```
PDF_STORAGE_PATH=/path/to/folder
```

Create this folder:

```
mkdir /path/to/folder
```

The web server should have write permissions on this folder.

For example, for Apache:

```
chown www-data /path/to/folder/to/store/pdf
```

### Disabling the Organize Mode

To disable the Organize mode, add `DISABLE_ORGANIZATION=true` to the 
`config/config.ini` file.

### Hiding or Modifying the Demo PDF Link

To hide the demo PDF link, add `PDF_DEMO_LINK=false` to the 
`config/config.ini` file.

### Default Fields for Metadata Editing

In the `config/config.ini` file, you can add as many fields as you want with the HTML input type (text, date, number, email, etc.) that will be preloaded for each PDF.

```
METADATA_DEFAULT_FIELDS[field1].type = "text"
METADATA_DEFAULT_FIELDS[field2].type = "text"
METADATA_DEFAULT_FIELDS[field3].type = "date"
METADATA_DEFAULT_FIELDS[field4].type = "number"
```

## Update

The latest stable version is on the `master` branch. To update, simply fetch the latest changes:

```
git pull -r
```

## Tests

To run functional tests:

```
make test
```

The tests are performed using `puppeteer` and `jest`.

To run the tests and view the browser (in debug mode):

```
DEBUG=1 make test
```

## Libraries Used

- **Fat-Free** PHP micro framework: https://github.com/bcosca/fatfree (GPLv3)
- **Bootstrap** HTML, CSS, and JavaScript framework: https://getbootstrap.com/ (MIT)
- **PDF.js** JavaScript library for rendering PDFs in an HTML canvas: https://github.com/mozilla/pdf.js (Apache-2.0)
- **Fabric.js** JavaScript library for manipulating an HTML canvas: https://github.com/fabricjs/fabric.js (MIT)
- **PDFtk** PDF manipulation tools (GPL)
- **librsvg** SVG manipulation tools: https://gitlab.gnome.org/GNOME/librsvg (LGPL-2+)
- **potrace** Bitmap to vector image conversion tools: http://potrace.sourceforge.net/ (GPLv2)
- **OpenType.js** Tools for converting text and its font into paths: https://github.com/opentypejs/opentype.js (MIT)
- **ImageMagick** Image manipulation toolset: https://imagemagick.org/ (Apache-2.0)
- **Caveat** Handwriting-style font: https://github.com/googlefonts/caveat (OFL-1.1)
- **PDF-LIB** JavaScript library for PDF manipulation used for writing metadata: https://pdf-lib.js.org/ (MIT)

For testing:

- **Jest** JavaScript Testing Framework: https://jestjs.io/ (MIT)
- **Puppeteer** Node.js library for controlling a web browser: https://github.com/puppeteer/puppeteer (Apache-2.0)

## Contributors

- Vincent LAURENT (24ème)
- Jean-Baptiste Le Metayer (24ème)
- Xavier Garnier (Logilab)
- Simon Chabot (Logilab)
- Gabriel POMA (24ème)

Logilab provided a financial contribution of €1,365 including tax to the company 24ème to develop the multi-signature mode.

The development of the software was primarily done during the working hours of 24ème employees.
