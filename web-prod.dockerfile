FROM nginx:1.10

ADD ./vhost.prod.conf /etc/nginx/conf.d/default.conf


WORKDIR /var/www
