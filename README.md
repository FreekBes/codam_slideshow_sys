# Codam Slideshow System
A dashboard for managing slideshow screens, specifically built for [Codam](https://www.codam.nl/) by [Freek Bes](https://www.github.com/FreekBes) (IT Assistent at Codam).

## Installation

### A clean install
Get yourself a Raspberry Pi. I'm using a Raspberry Pi 3 model B. Install the lite version of [Raspberry Pi OS](https://www.raspberrypi.com/software/operating-systems/) (formerly known as Raspbian). Set it up with `sudo raspi-config`, with the following settings:

- set the keyboard layout under *Localisation Options* -> *Keyboard*
- **important**: change the password for the *pi* user under *System Options* -> *Password* (required for security reasons)
- set the boot options to *Console Autologin* under *System Options* -> *Boot / Auto Login*
- enable SSH if needed under *Interfacing Options* -> *SSH*
- set up wi-fi if required under *System Options* -> *Wireless LAN*
- set a hostname under *System Options* -> *Hostname* if wanted

When done, use <kbd>Tab</kbd> to go to the *Finish* button and press <kbd>Enter</kbd>. Then reboot the machine by running `reboot`.

After that, run the following command to finish installation: `curl https://github.com/FreekBes/codam_slideshow_sys/raw/main/utils/service.sh | sudo bash`.

Also, to make sure the service keeps running smoothly, I recommend rebooting every night just in case. Do so using cron: `sudo crontab -e` and add the following line: `0 6 * * * /sbin/shutdown -r now`

Now, make sure to run the steps described in [Setup](#setup)

### Install on an existing web server
Create a virtual server in Apache2, nginx or your favorite web server software. Make sure it is running PHP. The code in this repository was written for PHP 8.1, but it might also work on older versions.

Move the contents of this repository to the server's web-accessible directory (normally */var/www/html*).

## Setup
In the web-accessible directory on your server, create two directories: one called *media*, and another one called *programmes*. Make sure this directory is owned by the user your web server is running as (on Debian and Ubuntu, that is usually *www-data*). The chmod for these directories should be set to `0755`.

In the *include* folder, rename the *settings.php.default* to just *settings.php* and modify its contents:

```php
<?php
	define('ORGANIZATION_NAME', 'enter_your_company_name_here');
	define('DASHBOARD_USERNAME', 'enter_a_username_here');
	define('DASHBOARD_PASSWORD', 'enter_a_password_here');
	define('WWW_DIR', 'enter_server_www_directory');
?>
```

The *ORGANIZATION_NAME* value is used for the title of the dashboard page. If you set it to `Codam`, it will display "Announcements Dashboard for Codam" as the title of some pages and the login screen.

The *DASHBOARD_USERNAME* and *DASHBOARD_PASSWORD* fields are used for authentication with the dashboard page.

The *WWW_DIR* field points to the path of the web-accessible directory of the web server (normally */var/www/html*). Do **not** append a `/` to the end of this path.

## Showing the slideshow on a screen
If you didn't follow the steps in [A Clean Install](#a-clean-install), you must now create a way to show the slideshow on your screen.

### If you're using a web browser to show the slideshow
On the screen that needs to display the slideshow, create an instance of a web browser (preferrably full-screen by default) that loads the web page at the `your_web_server_url/show.php?day=today` URL. Obviously, replace *your_web_server_url* with the URL of the web server you set up. This can be/remain a local IP address, even *localhost*.

### If you're using Kodi
While this repository was originally built with Kodi and this service in mind, I've found it to be quite unstable at times, crashing every now and then. Which is why I recommend using the web browser method mentioned above.

If you're certain you want to use Kodi instead, you'll need to install the service that will handle displaying the media in the Kodi player. First, modify the path to your web server's web-accessible directory in *utils/kodidash.service* on line 11 (`ExecStart` value). Then, move the *utils/kodidash.service* file to */etc/systemd/system/kodidash.service*. Now you'll be able to start the service by running `systemctl start kodidash`.

If you want it to start upon system boot, run the command `systemctl enable kodidash` once.

## Modifying the slideshow using the dashboard
Go to the website that your webserver is set to use and log in with the credentials set in *include/settings.php*. After this, you will be redirected to the calendar overview. From this page, it is possible to modify all of the content displayed on the screen. Click on a day in the calendar to edit the programme for that specific day, or click on the *Edit default programme* button to modify the media used in the default programme.

Hover over a day to view the programme for that day. If a day is colored lightblue, the programme includes the default programme. Is the day colored a darker shade of blue, the default programme has been turned off for that day, and only that day's programme will be shown. If the day is colored red, the default programme has been turned off on that day, and the day does not have any media to display (then the default image – *0_10_default.jpeg* in your web-accessible root directory, which you are allowed to replace with your own file – will be shown).

### Programmes
A programme contains the media to display on the monitor and their corresponding settings, such as the length in seconds to display a piece of media for. In terms of media, both images (JPG, PNG, WEBP, BMP) and video (MP4 only) are supported. In the web view, videos will be muted though.

#### The default programme
The default programme is shown before the programme of the current day, if the programme of the current day didn't prevent this from happening (see [Day programmes](#day-programmes)).

#### Day programmes
Each date in a year can have its own, custom programme. Any media in these programmes will be displayed after the default programme has finished. Once the daily programme has ended, the monitor will start over and display the default programme again.

### Editing a programme
To edit a programme, click on the day you would like to edit in the calendar overview, or click on the *Edit default programme* button on the same page. You will be redirected to a page containing two "containers", one with all media currently uploaded to the server, and one with media to show in this programme. The latter container, which is the bottom one, is usually empty when setting up a programme for a day.

To add media to the programme, simply drag and drop the preferred media over from the top container to the bottom one. Reordering is also possible - just drag and drop the media in the bottom container to do so.

### Uploading new media
If the media you would like to display on the monitor is not in the top media list container yet (see [Editing a programme](#editing-a-programme)), you can simply upload media by clicking on the *Upload media* button in the programme editor. A pop-up window will open, where you will be able to select (multiple) media files. Only files of the JPG, JPEG, PNG, WEBP, BMP, GIF or MP4 types will work. After selecting any file(s) to upload, press the *Upload* button. The uploading may take a while - do not close the pop-up window, it will close automatically. Once the upload is complete, you will be able to drag and drop the newly uploaded media to the programme of your liking using the programme editor (new media will be shown all the way on the right of the top media list container).

### Uploading new media, but in a less complicated way
If you don't want to deal with programmes and all the complications that come with those, it is also possible to upload and configure media in a more simple way. Go to the calendar overview, then click the *Add media in a more plain way* link underneat the *Edit default programme* button. A pop-up window will open, where you will be able to select a file to display on the screen (same filetypes are supported as in the normal way: JPG, PNG, WEBP, BMP and MP4). After selecting a file, enter the amount of seconds to show the image/video for in the text input field.

If you want to show the image/video only between two specific dates, mark the *Only show between two dates...* checkbox and then enter the date range that you want to display the media in. There's handy buttons next to those inputs, which will enter today's date if you click them. The end date is inclusive, meaning that if you enter a range between, for example the 10th of January and the 15th of January, the media will also be displayed on the 15th of January, and only stop displaying on the 16th.

If you do not mark the checkbox specified above, the media will be added to the default programme, meaning that it will always be displayed (unless the default programme has been turned off on a specific date).

Once the media has been configured to your liking using the settings mentioned above, simply click *Upload* and let the web application do its thing. Do not close the window until it tells you the process has completed (an alert dialog will open at that point, saying "All done!").