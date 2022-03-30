#!/usr/bin/env bash

# Set script to exit immediately if a command exits with a non-zero status
set -e

# Echo on
set -x

# Update the system
sudo apt-get update
sudo apt-get upgrade -y

# Install windowing toolset
sudo apt-get install -y --no-install-recommends xserver-xorg x11-xserver-utils xinit openbox

# Install Chromium web browser
sudo apt-get install -y --no-install-recommends chromium-browser

# Add PHP ppa to apt sources
sudo wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
sudo apt-get update

# Install Apache2 web server alongside with PHP
sudo apt-get install -y apache2 php8.1 php8.1-gd

# Enable .htaccess files in web-accessible directories by modifying apache2.conf
sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sudo service apache2 restart

# Install utilities
sudo apt-get install -y ffmpeg git

# Set up web server files
cd /var/www/html
sudo rm 'index.html'
sudo git clone https://github.com/codam-coding-college/slideshow-system.git .
sudo mkdir media
sudo chown www-data media
sudo chmod 0755 media
sudo mkdir programmes
sudo chown www-data programmes
sudo chmod 0755 programmes

# Set up openbox autostart script
sudo mv utils/autostart.sh /etc/xdg/openbox/autostart

# Remove utilities folder for safety
sudo rm -rf 'utils'

# Enable automatically starting the X-server on login
echo '[[ -z $DISPLAY && $XDG_VTNR -eq 1 ]] && startx -- -nocursor' >> /home/pi/.bash_profile

# Require password on running sudo commands for user pi
sudo sh -c 'echo "pi ALL=(ALL) ALL" > /etc/sudoers.d/010_pi-nopasswd'
