FROM centos:7.2.1511
MAINTAINER Kosta Harlan <kosta@savaslabs.com>

ENV PURPOSE='Durham Atletico Apache Docker Image' \
    HTTPDCONF='/etc/httpd/conf/httpd.conf' \
    HTTPDMPM='event' \
    MPMCONF='/etc/httpd/conf.modules.d/00-mpm.conf' \
    HOSTCONF='/etc/httpd/conf.d/vhost.conf' \
    SSLCONF='/etc/httpd/conf.d/ssl.conf' \
    SSLPROTO='SSLProtocol +TLSv1 +TLSv1.1 +TLSv1.2' \
    SSLCIPHERS='SSLCipherSuite ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128:AES256:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!3DES:!MD5:!PSK'

RUN yum install -y httpd mod_ssl && \
    yum clean all

# SSL Hardening
RUN sed -i -e "/SSLProtocol all -SSLv2.*/d" \
           -e "/^SSLCipherSuite.*/d" \
           -e "/SSLEngine on/d" \
           -e "/<VirtualHost _default_:443>/d" \
           -e "/<\/VirtualHost>/d" $SSLCONF

RUN echo -e "\
SSLHonorCipherOrder On\n\
${SSLPROTO}\n\
${SSLCIPHERS}\n\
\n\
SSLOptions +StrictRequire\n\
\n\
SSLOptions +StdEnvVars\n\
RequestHeader set X-SSL-Protocol %{SSL_PROTOCOL}s\n\
RequestHeader set X-SSL-Cipher %{SSL_CIPHER}s\n\
" >> $SSLCONF

RUN rm /etc/httpd/conf.d/welcome.conf
ADD .docker/httpd/https-redirect.conf /etc/httpd/conf.d/https-redirect.conf
ADD .docker/httpd/vhost.conf /etc/httpd/conf.d/vhost.conf
ADD .docker/httpd/proxy_fcgi.conf /etc/httpd/conf.d/proxy_fcgi.conf
ADD .docker/httpd/proxy_fcgi.load /etc/httpd/conf.d/proxy_fcgi.load
ADD .docker/httpd/deflate.conf /etc/httpd/conf.d/deflate.conf

ADD .docker/httpd/apache-config.sh /apache-config.sh
RUN chmod a+x /apache-config.sh

ADD .docker/httpd/ssl/apache.key /etc/httpd/ssl/apache.key
ADD .docker/httpd/ssl/apache.crt /etc/httpd/ssl/apache.crt

EXPOSE 80
EXPOSE 443

ENTRYPOINT [ "/apache-config.sh" ]
