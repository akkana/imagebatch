#!/usr/bin/env python3

# Losslessly autorotate JPEG images: a replacement for exifautotran
# which is no longer usable because of Debian Bug#947182.

from PIL import Image, ExifTags, ImageOps, __version__
from PIL import __version__ as PILversion
import sys


def autorotate(filename, outfile):
    """outfile may be the same as filename.
    """
    img = Image.open(filename)

    rotated = ImageOps.exif_transpose(img)

    rotated.save(outfile, exif=rotated.getexif())
    print("Saved to", outfile)


if __name__ == '__main__':
    pilversionparts = PILversion.split('.')
    if int(pilversionparts[0]) + float(pilversionparts[1]) / 10. < 7.2:
        print("PIL before 7.2 is buggy, bailing!")
        sys.exit(1)
    autorotate(sys.argv[1], "/tmp/rotated.jpg")

