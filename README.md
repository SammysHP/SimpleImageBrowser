Simple Image Browser
=============

Written with the goal of a simple php image gallery that does not require a database or JavaScript.

![Screenshot](http://i.imgur.com/49rK7.png)

This script scans the configured directories for images (jpeg, jpg, png, gif) and shows them one by one with a thumbnail navigation. You can create several albums. Please note that no thumbnails are created, but the original images are resized by the browser. This script is intended to be used with images of 800x800px max. Larger images will be resized by the browser (with proportions preserved).

Requirements
------------

* php

Installation & Configuration
----------------------------

* Copy `config.php.skel` to `config.php`. Options are explained in the configuration file.
* Upload everything contained in this repository (except raw, which are the original Photoshop files) to your webspace.