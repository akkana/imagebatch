#!/usr/bin/env python3

from PIL import Image
import sys


ORIENTATION = 0x112
ROTATE_KEYS = { 1: 0, 6: 90, 3: 180, 8: 270 }


def get_size(f):
    img = Image.open(f)

    w = img.width
    h = img.height

    exif = img.getexif()
    for k in exif:
        if k == ORIENTATION:
            if ROTATE_KEYS[exif[k]] == 90 or ROTATE_KEYS[exif[k]] == 270:
                # Swap w and h
                w, h = h, w
            break

    return w, h


if __name__ == '__main__':
    for f in sys.argv[1:]:
        try:
            w, h = get_size(f)
            print("%s -- %d x %d" % (f, w, h))
        except:
            print("%s: Can't get size" % f)

