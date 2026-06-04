FROM composer/composer:latest-bin AS composer

FROM php:8.3-cli-bookworm

# System packages

RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    libsqlite3-dev \
    apt-transport-https \
    gnupg2 \
    curl \
    libaio1 \
    unixodbc-dev

# MSSQL dependencies for sqlsrv / pdo_sqlsrv
ENV ACCEPT_EULA=Y
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && echo "Package: unixodbc\nPin: origin \"packages.microsoft.com\"\nPin-Priority: 100\n" >> /etc/apt/preferences.d/microsoft \
    && echo "Package: unixodbc-dev\nPin: origin \"packages.microsoft.com\"\nPin-Priority: 100\n" >> /etc/apt/preferences.d/microsoft \
    && echo "Package: libodbc1:amd64\nPin: origin \"packages.microsoft.com\"\nPin-Priority: 100\n" >> /etc/apt/preferences.d/microsoft \
    && echo "Package: odbcinst\nPin: origin \"packages.microsoft.com\"\nPin-Priority: 100\n" >> /etc/apt/preferences.d/microsoft \
    && echo "Package: odbcinst1debian2:amd64\nPin: origin \"packages.microsoft.com\"\nPin-Priority: 100\n" >> /etc/apt/preferences.d/microsoft \
    && apt-get update \
    && apt-get install -y msodbcsql18 mssql-tools18 \
    && rm -rf /var/lib/apt/lists/*

# Oracle dependencies
ARG TARGETARCH
RUN if [ "$TARGETARCH" = "arm64" ] ; then \
    cd /tmp && curl -L https://download.oracle.com/otn_software/linux/instantclient/2370000/instantclient-basic-linux.arm64-23.7.0.25.01.zip -O \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/2370000/instantclient-sdk-linux.arm64-23.7.0.25.01.zip -O \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/2370000/instantclient-sqlplus-linux.arm64-23.7.0.25.01.zip -O \
    && unzip -o /tmp/instantclient-basic-linux.arm64-23.7.0.25.01.zip -d /usr/local/ \
    && unzip -o /tmp/instantclient-sdk-linux.arm64-23.7.0.25.01.zip -d /usr/local/ \
    && unzip -o /tmp/instantclient-sqlplus-linux.arm64-23.7.0.25.01.zip -d /usr/local/ \
    && ln -s /usr/local/instantclient_23_7 /usr/local/instantclient; \
  elif [ "$TARGETARCH" = "amd64" ]; then \
    cd /tmp && curl -L https://download.oracle.com/otn_software/linux/instantclient/2350000/instantclient-basic-linux.x64-23.5.0.24.07.zip -O \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/2350000/instantclient-sdk-linux.x64-23.5.0.24.07.zip -O \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/2350000/instantclient-sqlplus-linux.x64-23.5.0.24.07.zip -O \
    && unzip -o /tmp/instantclient-basic-linux.x64-23.5.0.24.07.zip -d /usr/local/ \
    && unzip -o /tmp/instantclient-sdk-linux.x64-23.5.0.24.07.zip -d /usr/local/ \
    && unzip -o /tmp/instantclient-sqlplus-linux.x64-23.5.0.24.07.zip -d /usr/local/ \
    && ln -s /usr/local/instantclient_23_5 /usr/local/instantclient; \
  fi
RUN ln -s /usr/local/instantclient/lib* /usr/lib
RUN ln -s /usr/local/instantclient/sqlplus /usr/bin/sqlplus

ENV LD_LIBRARY_PATH=/usr/local/instantclient

# PHP extensions

RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    # For Psalm, to make use of JIT for a 20%+ performance boost. \
    opcache

RUN pecl install sqlsrv && docker-php-ext-enable sqlsrv
RUN pecl install pdo_sqlsrv && docker-php-ext-enable pdo_sqlsrv
RUN echo 'instantclient,/usr/local/instantclient' | pecl install oci8 && docker-php-ext-enable oci8
RUN echo 'instantclient,/usr/local/instantclient' | pecl install pdo_oci && docker-php-ext-enable pdo_oci

# For code coverage (mutation testing)
RUN pecl install pcov && docker-php-ext-enable pcov

# Composer

COPY --from=composer /composer /usr/bin/composer

# Code
WORKDIR /code

