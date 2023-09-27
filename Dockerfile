FROM php:8.2-apache

ENV SERVERNAME=localhost
ENV UPLOAD_MAX_FILESIZE=24M
ENV POST_MAX_SIZE=24M
ENV MAX_FILE_UPLOADS=201
ENV PDF_STORAGE_PATH=/data
ENV DISABLE_ORGANIZATION=false
ENV DEFAULT_LANGUAGE=fr_FR.UTF-8

RUN apt update && \
    apt install -y vim locales gettext-base librsvg2-bin pdftk imagemagick potrace ghostscript && \
    docker-php-ext-install gettext && \
    rm -rf /var/lib/apt/lists/*

RUN sed -i "/$DEFAULT_LANGUAGE/s/^# //g" /etc/locale.gen && \
    locale-gen
ENV LANG $DEFAULT_LANGUAGE
ENV LANGUAGE $DEFAULT_LANGUAGE
ENV LC_ALL $DEFAULT_LANGUAGE

COPY . /usr/local/signaturepdf

RUN envsubst < /usr/local/signaturepdf/config/php.ini > /usr/local/etc/php/conf.d/uploads.ini && \
    envsubst < /usr/local/signaturepdf/config/apache.conf > /etc/apache2/sites-available/signaturepdf.conf && \
    envsubst < /usr/local/signaturepdf/config/config.ini.tpl > /usr/local/signaturepdf/config/config.ini && \
         a2enmod rewrite && a2ensite signaturepdf

WORKDIR /usr/local/signaturepdf

CMD /usr/local/signaturepdf/entrypoint.sh
