FROM php:7.4-apache

ENV SERVERNAME=localhost
ENV UPLOAD_MAX_FILESIZE=24M
ENV POST_MAX_SIZE=24M
ENV MAX_FILE_UPLOADS=201

RUN apt update && apt install -y gettext-base librsvg2-bin pdftk imagemagick potrace

COPY . /usr/local/signaturepdf

RUN chown -R www-data:www-data /usr/local/signaturepdf && chmod 750 -R /usr/local/signaturepdf && \
    envsubst < /usr/local/signaturepdf/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    envsubst < /usr/local/signaturepdf/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD envsubst < /usr/local/signaturepdf/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    envsubst < /usr/local/signaturepdf/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    chown -R www-data:www-data /usr/local/signaturepdf && chmod 750 -R /usr/local/signaturepdf && \
    apache2-foreground
