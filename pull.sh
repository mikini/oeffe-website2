#!/bin/bash
rep=$(dirname $(realpath $0))

echo $rep
sudo -u www-data git -C $rep pull

