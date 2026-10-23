FROM php:8.2-fpm-alpine

# Copia o instalador de extensões direto da imagem oficial do criador (evita falhas de download)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Copia o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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

WORKDIR /app

# Vendor de produção dentro da imagem: fica fora do bind mount (9p) e é reutilizado
# em todo boot. --optimize-autoloader evita sondagens de filesystem para classes
# conhecidas sem quebrar classes novas (o fallback PSR-4 continua ativo).
COPY project/composer.json project/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-progress --no-interaction

# Configurações otimizadas
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/custom-opcache.ini
COPY docker/php/zz-pool.conf /usr/local/etc/php-fpm.d/zz-pool.conf

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_CACHE=1
