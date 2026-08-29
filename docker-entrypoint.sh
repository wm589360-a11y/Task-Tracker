#!/bin/bash
set -e

# Disable all MPM modules just to be safe
a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

# Enable the required MPM for mod_php
a2enmod mpm_prefork

# If Railway provides a dynamic PORT, update Apache config to listen on it
if [ -n "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Run the original Apache foreground command
exec apache2-foreground
