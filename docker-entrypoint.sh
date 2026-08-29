#!/bin/bash
set -e

# Disable all MPM modules just to be safe
a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

# Enable the required MPM for mod_php
a2enmod mpm_prefork

# Run the original Apache foreground command
exec apache2-foreground
