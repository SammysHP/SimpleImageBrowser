Simple Image Browser
====================

Written with the goal of a simple php image gallery that does not require a database or JavaScript.

![Screenshot](http://www.sammyshp.de/misc/sib_screen.jpg)

This script scans the configured directories for images (jpeg, jpg, png, gif) and shows them one by one with a thumbnail navigation. You can create several albums. Please note that no thumbnails are created, but the original images are resized by the browser (for simplicity and prefetching). This script is intended to be used with images of 800x800px max. Larger images will be resized by the browser (with proportions preserved).

Requirements
------------

* php

Installation & Configuration
----------------------------

* Clone this repository.
* Copy `config.php.skel` to `config.php`. Options are explained in the configuration file.
* Upload everything contained in this repository (except raw, which are the original Photoshop files) to your webspace.

Update
------

* Run `$ git pull`

Credits
-------

Sven Karsten Greiner

http://www.sammyshp.de/

License
-------

GNU General Public License, version 3 or later

See http://www.gnu.org/licenses/

