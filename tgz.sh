#!/bin/bash
DATE_NOW=`date +%Y%m%d%H%M`
REPO_PATH=`pwd`
rm -f ../cn2moons_*.tar.gz
cd ..
cp -r $REPO_PATH 'cn2moons_'$DATE_NOW
cd 'cn2moons_'$DATE_NOW
rm -rf .git .gitignore package.bat package_cn.bat pkgcn.sh tgz.sh
tar czvf '../cn2moons_'$DATE_NOW'.tar.gz' *
cd ..
rm -rf 'cn2moons_'$DATE_NOW
