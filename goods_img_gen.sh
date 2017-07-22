#! /bin/bash
# convert and scale xcf into web friendly jpg

cd $(dirname $0)
for i in img/goods/base/*; do
  basename=$(basename $i)
  echo Processing $basename
  for s in 160 300 600 1200; do
    gm convert ${i} -resize ${s}x $(dirname $i)/../${basename%.*}_${s}.jpg
  done
done
