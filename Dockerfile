FROM php:8.2-apache

ENV SERVERNAME=localhost
ENV UPLOAD_MAX_FILESIZE=24M
ENV POST_MAX_SIZE=24M
ENV MAX_FILE_UPLOADS=201
ENV PDF_STORAGE_PATH=/data
ENV DISABLE_ORGANIZATION=false
ENV DEFAULT_LANGUAGE=fr_FR.UTF-8
ENV PDF_STORAGE_ENCRYPTION=false
ENV NSS3_DIRECTORY=/nss3
ENV NSS3_PASSWORD=toto
ENV NSS3_NICK="PDF certificate"
ENV ADMIN_AUTHORIZED_IP=127.0.0.1

RUN apt update && \
    apt install -y vim locales gettext-base librsvg2-bin pdftk imagemagick potrace ghostscript gpg poppler-utils libnss3-tools && \
    docker-php-ext-install gettext && \
    rm -rf /var/lib/apt/lists/*

COPY . /usr/local/signaturepdf

RUN envsubst < /usr/local/signaturepdf/config/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    envsubst < /usr/local/signaturepdf/config/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    envsubst < /usr/local/signaturepdf/config/config.ini.tpl > /usr/local/signaturepdf/config/config.ini && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD ["/usr/local/signaturepdf/entrypoint.sh"]
