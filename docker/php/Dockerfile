FROM php:8.2-fpm-alpine

# Copia o instalador de extensões direto da imagem oficial do criador (evita falhas de download)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    # Adiciona dependências de build essenciais para o Alpine compilar o PHP
    apk add --no-cache --virtual .build-deps linux-headers && \
    # Ferramentas necessárias para o ambiente/Composer
    apk add --no-cache git unzip zip && \
    # Instala as extensões PHP
    install-php-extensions pdo pdo_mysql zip opcache && \
    # Remove os cabeçalhos do Linux que só serviram para a compilação
    apk del .build-deps && \
    # Limpa caches e arquivos temporários de forma agressiva
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# COPIAR AS CONFIGURAÇÕES OTIMIZADAS PARA DENTRO DO CONTAINER
# Certifique-se de que os arquivos php.ini e opcache.ini estão na mesma pasta do Dockerfile
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY opcache.ini /usr/local/etc/php/conf.d/custom-opcache.ini

# Copia o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_CACHE=1

WORKDIR /app
