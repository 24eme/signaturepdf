FROM php:7.4-apache

ENV SERVERNAME=localhost
ENV UPLOAD_MAX_FILESIZE=24M
ENV POST_MAX_SIZE=24M
ENV MAX_FILE_UPLOADS=201
ENV PDF_STORAGE_PATH=

RUN apt update && \
    apt install -y gettext-base librsvg2-bin pdftk imagemagick potrace

COPY . /usr/local/signaturepdf

RUN chown -R www-data:www-data /usr/local/signaturepdf && chmod 750 -R /usr/local/signaturepdf && \
    chmod 775 -R /usr/local/signaturepdf/entrypoint.sh && \
    envsubst < /usr/local/signaturepdf/config/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    envsubst < /usr/local/signaturepdf/config/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    envsubst < /usr/local/signaturepdf/config/config.ini.tpl > /usr/local/signaturepdf/config/config.ini && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD /usr/local/signaturepdf/entrypoint.sh
