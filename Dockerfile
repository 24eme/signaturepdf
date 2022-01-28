FROM php:7.4-apache

ENV SERVERNAME=localhost

RUN apt update && apt install -y gettext-base librsvg2-bin pdftk imagemagick potrace

COPY . /usr/local/signaturepdf

RUN chown -R www-data:www-data /usr/local/signaturepdf && chmod 750 -R /usr/local/signaturepdf && \
    cp /usr/local/signaturepdf/php.ini /usr/local/etc/php/conf.d/uploads.ini && \
    cat /usr/local/signaturepdf/apache.conf | envsubst > /etc/apache2/sites-available/signaturepdf.conf && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD envsubst < /usr/local/signaturepdf/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    chown -R www-data:www-data /usr/local/signaturepdf && chmod 750 -R /usr/local/signaturepdf && \
    apache2-foreground
