FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP
RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/uploads.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create supervisor directories
RUN mkdir -p /etc/supervisor/conf.d /var/log/supervisor

# Copy supervisor configurations
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/supervisor/scheduler.conf /etc/supervisor/conf.d/

# Copy existing application directory
COPY . .

# Start supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"] 
