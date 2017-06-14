#! /bin/bash
# convert and scale xcf into web friendly jpg

for i in img/goods/*.xcf; do
  echo Processing $i
  for s in 160 300 600; do
    gm convert ${i} -resize ${s}x ${i%.xcf}_${s}.jpg
  done
done
