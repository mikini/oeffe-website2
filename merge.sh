#!/bin/bash
rep=$(dirname $(realpath $0))
srcbranch=${1:-master}

echo merging branch $srcbranch into repository at $rep
sudo -u www-data git -C $rep merge origin/$srcbranch

