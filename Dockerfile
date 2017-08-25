FROM richarvey/nginx-php-fpm

WORKDIR /var/www

RUN git clone https://github.com/hyglok/xhprof-remote-gui
RUN cp -a /var/www/xhprof-remote-gui/. /var/www && chmod 777 traces
RUN apk add --update --no-cache \
           graphviz \
           ttf-freefont

VOLUME ["/var/www/traces"]