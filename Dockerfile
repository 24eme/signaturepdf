FROM php:8.2-apache

ENV SERVERNAME=localhost
ENV UPLOAD_MAX_FILESIZE=24M
ENV POST_MAX_SIZE=24M
ENV MAX_FILE_UPLOADS=201
ENV PDF_STORAGE_PATH=/data
ENV DISABLE_ORGANIZATION=false
ENV PDF_DEMO_LINK=true

RUN apt update && \
    apt install -y gettext-base librsvg2-bin pdftk imagemagick potrace ghostscript && \
    rm -rf /var/lib/apt/lists/*

COPY . /usr/local/signaturepdf

RUN envsubst < /usr/local/signaturepdf/config/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    envsubst < /usr/local/signaturepdf/config/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    envsubst < /usr/local/signaturepdf/config/config.ini.tpl > /usr/local/signaturepdf/config/config.ini && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD /usr/local/signaturepdf/entrypoint.sh
