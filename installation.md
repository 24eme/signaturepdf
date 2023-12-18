# Installation

## [Debian/Ubuntu](#debian-ubuntu)

Dependencies:

- php >= 5.6
- rsvg-convert
- pdftk
- imagemagick
- potrace
- ghostcript

Installing dependencies:
```
sudo apt-get install php librsvg2-bin pdftk imagemagick potrace ghostscript locales
```

Getting the source code:

```
git clone https://github.com/24eme/signaturepdf.git
```

To run it:

```
php -S localhost:8000 -t public
```

### PHP Configuration

```
upload_max_filesize = 24M # Maximum size of the PDF file to sign
post_max_size = 24M # Maximum size of the PDF file to sign
max_file_uploads = 201 # Maximum number of pages in the PDF, here 200 pages + the original PDF
```

### Apache Configuration

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
### Troubleshooting

#### The translation is not done, the language remains in English in the interface

Check that your locales are properly installed:

```
sudo apt-get install locales
sudo dpkg-reconfigure locales
```

Then if you use apache, you have to restart it:

```
sudo service apache2 restart
```

## [Deploy with Docker](#docker)

### Building the image

```bash
docker build -t signaturepdf .
```

### Running a container

```bash
docker run -d --name=signaturepdf -p 8080:80 signaturepdf
```

[localhost:8080](http://localhost:8080)

### Configuration

The following variables can be used to configure the deployment:

| Variable               | description                                                           | exemple                          | defaut      |
|------------------------|-----------------------------------------------------------------------|----------------------------------|-------------|
| `SERVERNAME`           | Deployment URL                                                        | `pdf.24eme.fr`                   | localhost   |
| `UPLOAD_MAX_FILESIZE`  | Maximum size of the PDF file to sign                                  | 48M                              | 24M         |
| `POST_MAX_SIZE`        | Maximum size of the PDF file to sign                                  | 48M                              | 24M         |
| `MAX_FILE_UPLOADS`     | Maximum number of pages in the PDF, here 200 pages + the original PDF | 401                              | 201         |
| `PDF_STORAGE_PATH`     | Path where uploaded PDF files can be stored                           | /data                            | /data       |
| `DISABLE_ORGANIZATION` | Disable the Organize route                                            | true                             | false       |
| `PDF_DEMO_LINK`        | Show, hide, or change the demo PDF link                               | false, `link` or `relative path` | true        |
| `DEFAULT_LANGUAGE`     | Default language for the application                                  | en_US.UTF-8                      | fr_FR.UTF-8 |

```bash
docker run -d --name=signaturepdf -p 8080:80 -e SERVERNAME=pdf.example.org -e UPLOAD_MAX_FILESIZE=48M -e POST_MAX_SIZE=48M -e MAX_FILE_UPLOADS=401 -e PDF_STORAGE_PATH=/data signaturepdf
```

## [Alpine](#alpine)

Here is a script to install the solution on Linux Alpine (tested with version 3.15).
Remember to edit the "domain" variable at the beginning of the script to match the URL it will be called with.

The main components are:

- php 8 + php-fpm
- Nginx
- pdftk ("manual" installation requiring openjdk8)
- imagick
- potrace
- librsvg
- ghostscript

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

