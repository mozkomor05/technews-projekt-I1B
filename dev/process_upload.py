#!/usr/bin/env python3

import argparse
import os
from PIL import Image


def dir_path(path):
    if os.path.isdir(path):
        return path
    else:
        raise argparse.ArgumentTypeError(f"readable_dir:{path} is not a valid path")


parser = argparse.ArgumentParser()
parser.add_argument('output', type=dir_path)
parser.add_argument('--input', type=dir_path, default='to-upload')
args = parser.parse_args()

dirs = os.listdir(args.input)
# [75, 250, 400, 760, 1920]
# w = resize width
# h = resize height
landscape = {
    75: 'h',
    250: 'w',
    400: 'w',
    760: 'w',
    1920: 'w'
}

for item in dirs:
    path = os.path.join(args.input, item)

    if os.path.isfile(path):
        im = Image.open(path)
        width, height = im.size
        filename, e = os.path.splitext(path)
        save_dest = os.path.join(args.output, os.path.basename(filename))
        include_org = False

        for dim, to_resize in landscape.items():
            im_thumb = im.copy()

            if to_resize == 'w':
                if dim > width:
                    include_org = True
                    continue

                size = (dim, height)
            else:
                if dim > height:
                    include_org = True
                    continue

                size = (width, dim)

            im_thumb.thumbnail(size, Image.LANCZOS)
            im_thumb.save(save_dest + f'-{to_resize}{dim}.jpg', 'JPEG', optimize=True, quality=60)

        if include_org:
            im_thumb.save(save_dest + '-ORG.jpg', 'JPEG', optimize=True, quality=60)
