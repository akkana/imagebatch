# Image Batch Processing

These scripts make it easy to upload large numbers of images (from a
scanner or digital camera, for example) and turn them into web pages.
They run on Unix systems (other restrictions as noted) though it might
not be too hard to port them to other operating systems. They're
written in a motley collection of csh, perl and python. You may freely
use and modify these programs under the [GNU Public
License](http://www.gnu.org/), v.2 or later.

Warning: These are not really set up as a nice usable package. I'm
under the impression that since there are a gazillion web gallery packages,
nobody's actually going to want mine except maybe as a learning
tool or a starting point to write your own. If you actually are trying
to use these scripts and having trouble figuring them out, please let
me know. I'll be happy to answer questions or write better documentation.

Requirements: You should have ImageMagick (for scaling and thumbnail
drop shadows), libjpeg-progs (for jpegtran and jpegexiforient) and
jhead (for cleaning up unwanted EXIF like embedded thumbnails).

[Here's a sample page: JunkDNA Art](http://junkdnaart.com)

## How to use

Here's a typical quickie workflow:

-   After uploading and triaging images (typically with
    [Pho](https://github.com/akkana/pho) and/or
    [MetaPho](https://github.com/akkana/metapho)), I copy the images I
    want to use into a separate directory (leaving the originals in place)
    and cd there.
-   Use rotateall to rotate anything that needs it that doesn't already
    have an EXIF rotation tag, e.g.
    ```rotateall -right img004.jpg img007.jpg -left img011.jpg```
-   Resize everything to a reasonable size for the web, e.g.
    ```resizeall -size 1024 *.jpg```
-   Run mkwebphotos, which does the rest.
-   View the *index.php* you've just made in a browser and open it in
    an editor. Edit the page title and image descriptions as needed.

For a longer trip, with subdirectories, I use a more complex workflow:

-   Upload images and view and catalog them with my [MetaPho](https://github.com/akkana/metapho) image tagger. Tag images in each subdirectory separately (I generally have one directory for each day of shooting), and use the special tag "web" for any images you'll want to include in the web page. Go ahead and tag the images with other tags as well.
-   Create a new, empty directory "web" inside the trip directory. 
    This may be a symbolic link to a directory in your website. This step is optional; if there's no "./web" subdirectory, mkwebphotos will generate thumbnails and index.php files inside the current image directory, but will not scale or otherwise modify the images in the current directory.
-   Run mkwebphotos. 
    This will copy all photos tagged "web" into the web subdirectory
    if there is one, making subdirectories as needed. It will also run
    jhead -dt to remove things like embedded thumbnails, and will
    rotate images with exiftran (so those vertical format images show
    up properly in browsers). 
    Then it will make an *index.php* file in each directory that
    contains images or subdirectories. 
-   View the *index.php* you've just made in a browser and edit it.
    The index.html files will show tags as descriptions for each image
    or directory. This probably isn't what you want, so here's where
    you spend forever adding in real descriptions for the photos. And
    of course you'll want to change the page titles to something
    descriptive, and add some descriptive text. 
    If you have a hierarchy of directories, mkwebphotos will have
    chosen the first image in each subdirectory as the thumbnail for
    that directory on the parent page. You'll probably want to pick a
    different image, whatever is the most striking photo in each
    directory.

*mkwebphotos* will produce PHP pages by default.
If you don't want to use PHP, uncomment the tblimages line in
mkwebphotos and it will make an index.html page.
(Note: I haven't used the non-PHP mode in a while; it might be buggy.)

# Descriptions of the individual scripts

## The main scripts

### resizeall (python)
Rescale images to a smaller size (can also make thumbnails).

### mkthumb (symbolic link to resizeall)
If resizeall is called as "mkthumb", it will automatically put itself in thumbnail mode, and (unless told otherwise) will generate drop shadows. Just make a link from resizeall to mkthumb.

### rotateall (csh)
Rotate all the images, -left -cw -right -ccw -180 or -0. You can switch directions in midstream, e.g. rotateall -cw foo.jpg bar.jpg -ccw baz.jpg
    If you have jpegtran installed, it will use that for lossless jpeg rotation.
    It will remove all EXIF rotation information. To remove the EXIF rotation information without actually rotating anything, use ```rotateall -0```

### mkphplist (python)
Find images under the current directory and generate a file suitable for using showpix.php. This is faster than the Perl CGI, and more self contained, assuming you have PHP available. Called by mkwebphotos.

### mkwebphotos (python)
Given a directory hierarchy containing images already sized, copy the images, make thumbnails and make a starter index.php.

## Files for displaying the gallery pages:

### gallerypage-base.php (PHP)
The PHP that does the work of generating the web pages. Put this in a subdirectory of your web site called software, or else change the path that mkphplist uses for its require.

### css/gallery.css
### css/gallerypic.css
### css/gallery-ie.css
CSS files that make the fancy list-based gallery pages work. I'm indebted to [this brunildo example](http://www.brunildo.org/test/ImgThumbIBL3.html) and [the related discussion](http://archivist.incutio.com/viewlist/css-discuss/84544) without which I might still be stuck on 4-column wide tables (like on this [sample page](http://shallowsky.com/images/stevcrk_1_20/) showing what tblimages creates with the -t option).

## Some older scripts, only needed for specialized uses

### imgsize (Perl)
Print the size of an image file. Based on code adapted from the excellent wwwis program, which is indispensable for setting width and height tags in html pages.

### thumbpage (perl)
Make a big HTML page of thumbnails suitable for printing from a browser. Useful if you want to keep a printed archive of your photo collection.

### tblimages (csh)
Take a bunch of thumbnail images (assuming that full-sized images are
in the same directory) and make a web page out of them.  You might need
this if you're making non-PHP pages. Lots of options:

    -t
        uses tables instead of fancy CSS lists (4 wide by default, but you can pass -1 through -9 to change that),
    -p
        makes it link to PHP pages instead of just the raw images,
    -c
        links to CGI pages instead of PHP or raw images,
    -r
        makes it recurse through subdirectories looking for images,
    -b
        puts a border on the thumbnails (if you're not using dropshadows)
    -n
        doesn't add HTML headers (in case you're making several snippets to paste together),
    -a
        adds annotation (see thumbpage to see how that works).

### filestolower (csh)
csh: Convert filenames to lower case -- Windows image processing programs are forever converting my lower-case names to upper case.

### mkstatic (sh)
Make a static.html page from index.html (e.g. to put on a CD or some other medium that can't run PHP or CGI).

### showpix-base.php (PHP)
PHP for showing individual images; unable to handle the thumbnail page and generally not as flexible as the newer gallerypage-base.php. The format of the image lists is the same, so if you have old image list showpix.php files that worked with showpix-base.php, you can probably just change the require line to use gallerypage-base.php instead.

### showpix.pl (perl)
CGI: Display full-sized images one by one, letting you step forward and backward, and allowing a short description for each image. (There seems to be no way to tell apache not to execute this .pl file, even with a .htaccess, so I've removed the .pl.)

### mkpixlist (perl)
Find images under the current directory and generate a file suitable for using showpix.cgi.
