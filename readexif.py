#!/usr/bin/env python3


import sys
from PIL import Image, ExifTags, ImageOps


LENS_SPEC_KEY   = 0xa432
MAKER_NOTE_KEY  = 0x927c
skiptags = [ LENS_SPEC_KEY, MAKER_NOTE_KEY  ]

def print_exif(exif):
    print("\n======================")
    for k in sorted(exif.keys()):
        if k in ExifTags.TAGS.keys():
            name = ExifTags.TAGS[k]
        else:
            name = "???"
        if k in skiptags:
            print(name, "0x%x (skipped)" % k)
        else:
            print(name, "0x%x" % k, exif[k])


if __name__ == '__main__':
    for f in sys.argv[1:]:
        img = Image.open(f)
        print_exif(img.getexif())


