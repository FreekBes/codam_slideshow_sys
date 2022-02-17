# Dashboard for Kodi
A dashboard for slideshow screens running on Kodi. Currently in development; this is a prototype.

## Setup
Create a virtual server in Apache2, nginx or your favorite web server software. Make sure it is running PHP. The code in this repository was written for PHP8.1, but it might also work on older versions.

In the web-accessible directory on your server, create a directory called *media*. Make sure this directory is owned by the user your web server is running as (on Debian and Ubuntu, that is usually *www-data*). If you're unsure and don't care about security (which you should, though...), it is also possible to chmod this directory to 777 to give every user all permissions for this folder, including the web server.
