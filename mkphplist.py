#!/usr/bin/env python

# mkphplist: find all non-thumbnail images under the current directory,
# and generate a showpix.php with the appropriate image list.
# Copyright 2004 by Akkana Peck.
# You may use and distribute this under the GPL.

from __future__ import print_function

import sys, os
import subprocess
import re

# Try to get an image's size and thumbnail size.
# Return thumbwidth, thumbheight, width, height as strings.
def getthumbsize(imgname, root=None):
    sys.stdout.flush()
    if root:
        imgname = os.path.join(root, imgname)

    def sizes_from_identify(filename):
        proc = subprocess.Popen(["identify", filename],
                                shell=False, stdout=subprocess.PIPE)
        s = proc.communicate()[0]
        if s:
            # Sometimes identify's output looks like
            # foo.jpg JPEG 900x718 900x718+0+0 ...
            # but on ubunu 20.04, even with the same 8:6.9.10.23 version, it's
            # foo.jpg JPEG 900x718+0+0 ...
            # so try to be alert for that difference.
            ws, hs = s.decode().split()[2].split("x")
            if '+' in hs:
                hs = hs.split('+')[0]
            return [ ws, hs ]

        return [0, 0]

    try:
        # Try for the real image size first:
        sizes = sizes_from_identify(imgname)

        # Now the thumbnail size:
        parts = os.path.splitext(imgname)
        thumbsizes = sizes_from_identify(parts[0] + "T" + parts[1])

        return thumbsizes[0], thumbsizes[1], sizes[0], sizes[1]

    except Exception as e:
        raise e
        return None, None

def have_image(imgname, pixlist):
    for p in pixlist:
        if type(p) is not str:
            if p[0] == imgname:
                return p
    return False

added_new = False
def mkline(imgname, pixlist, root=None):
    '''Add an image and its correct, updated sizes to the pixlist
       if it's not already there.
       The first time we add an image, we'll add a line before it
       that says "New images added" to make the new images easy to find.
    '''
    global added_new
    imgname = imgname.strip()
    if imgname[0:2] == "./":
        imgname = imgname[2:]

    img = have_image(imgname, pixlist)
    if img:
        tw, th, w, h = getthumbsize(imgname, root)
        if [tw, th, w, h] != img[2:]:
            print("Need to correct sizes:", tw, th, w, h, "vs", img[2:])
            img[2:] = [tw, th, w, h]
        else:
            print("Skipping", imgname)
        return

    tw, th, w, h = getthumbsize(imgname, root)

    if not added_new:
        pixlist.append('"New images added:",');
        added_new = True
    pixlist.append([imgname, "", tw, th, w, h])

def make_php_list(imgfiles, outfile=None, tags=None, root=None, title=None):
    '''Create or overwrite one index.php file, adding the given imgfiles.
       Will try to retain non-image content that was in any previous
       index.php file, but no guarantees.
    '''
    if not imgfiles:
        return ''
    print("make_php_list: outfile =", outfile, "title = ", title)

    contents = ''
    pixlist = []

    if not title:
        title = "Images"
    contents = ""

    # Are we modifying an existing file?
    try:
        if outfile:
            infp = open(outfile)
        else:
            # Even if no filename was passed in, if there's an index.php
            # in the current directory, we should use any info that's in it.
            if root:
                infp = open(os.path.join(root, "index.php"))
            else:
                infp = open("index.php")
    except:
        infp = None

    if infp:
        print("Modifying an existing infile")
        seen_pixlist = False
        for line in infp:
            strline = line.strip()

            # Is this the end of the old pixlist?
            if seen_pixlist:
                if strline == ');':
                    break
                # Try to match the name and the description,
                # followed by up to four optional integers.
                # We have to include the commas and spaces preceeding each
                # number as a match group in order to make it optional ...
                # so we'll ignore those groups if we get them.
                # Numbers not matched will show up in match.groups() as None.
                match = re.match('array *\("(.*\..*)", *"(.*)"' \
                                     + '(, *)?([0-9]+)?' * 4,
                                 strline)
                if match:
                    g = match.groups()
                    pixlist.append([g[0], g[1], g[3], g[5], g[7], g[9]])
                elif strline:
                    pixlist.append(strline)

                continue

            # We haven't seen the pixlist yet.
            contents += line

            if strline == '$pixlist = array(':
                seen_pixlist = True

            for p in pixlist:
                print(p)

        # Save the footer too.
        footer = '  );\n'
        for line in infp:
            footer += line

        infp.close()

    else:
        contents += """
<?php
$title = \"%s\";
$preamble = "";
$pixlist = array(
""" % title
        footer = """  );

require($_SERVER["DOCUMENT_ROOT"] . "/software/gallerypage-base.php");
?>
</body>
</html>

"""

    # Outputting to a file, or to a string?
    if outfile:
        # Make a backup first:
        bakfile = outfile + ".bak"
        print("Backing up to", bakfile)
        if os.path.exists(bakfile):
            os.rename(outfile, bakfile)

    for fil in imgfiles:
        mkline(fil, pixlist, root)

    for p in pixlist:
        if type(p) is str:
            contents += '\n    ' + p + '\n'
        else:
            if tags and not p[1]:
                tlist = []
                for t in list(tags.keys()):
                    if p[0] in tags[t]:
                        tlist.append(t)
                p[1] = ', '.join(tlist)
            contents += '    array ("%s", "%s"' % (p[0], p[1])
            for n in p[2:]:
                if n:
                    contents += ', %s' % n
            contents += '),\n'

    # Add the footer, either saved or stock.
    contents += footer

    return contents

if __name__ == '__main__':
    # If the first argument doesn't have a ., or ends in .html or .php,
    # use that as the filename. Otherwise, use stdout.
    outfile = None
    if len(sys.argv) > 1 and ('.' not in sys.argv[1] or
                              sys.argv[1].endswith('.php') or
                              sys.argv[1].endswith('.html')):
        outfile = sys.argv[1]
        sys.argv = sys.argv[1:]

    # Find the image files to use.
    imgfiles = []
    if len(sys.argv) <= 1:
        fp = os.popen("find . -name \"*.jpg\" | grep -v T.jpg")
        while True:
            line = fp.readline()
            if not line: break
            imgfiles.append(line)
    else:
        imgfiles = sys.argv[1:]

    contents = """<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
"""
    contents += make_php_list(imgfiles, outfile)

    if outfile:
        outfp = open(outfile, 'w')
    else:
        outfp = sys.stdout

    print(contents, file=outfp)

