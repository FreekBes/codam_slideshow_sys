#!/usr/bin/env bash

sudo apt-get update
sudo apt-get upgrade
sudo apt-get install --no-install-recommends xserver-xorg x11-xserver-utils xinit openbox
sudo apt-get install --no-install-recommends chromium-browser
sudo wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
sudo apt-get update
sudo apt-get install apache2 php8.1 php8.1-gd ffmpeg git
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sudo service apache2 restart
cd /var/www/html
sudo rm 'index.html'
sudo git clone https://github.com/FreekBes/codam_slideshow_sys.git .
sudo mkdir media
sudo chown www-data media
sudo chmod 0755 media
sudo mkdir programmes
sudo chown www-data programmes
sudo chmod 0755 programmes
sudo mv utils/autostart.sh /etc/xdg/openbox/autostart
sudo rm -rf 'utils'
echo '[[ -z $DISPLAY && $XDG_VTNR -eq 1 ]] && startx -- -nocursor' >> ~/.bash_profile
sudo sh -c 'echo "pi ALL=(ALL) ALL" > /etc/sudoers.d/010_pi-nopasswd'