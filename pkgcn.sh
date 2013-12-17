DATE_NOW=`date +%Y%m%d%H%M`
echo $DATE_NOW
rm -f ../2moons_cn_*.zip
cd language/cn
7z a -tzip '../../../2moons_cn_'$DATE_NOW'.zip' *
