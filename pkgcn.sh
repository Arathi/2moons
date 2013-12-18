DATE_NOW=`date +%Y%m%d%H%M`
rm -f ../2moons_language_cn_*.zip
cd language/cn
7z a -tzip '../../../2moons_language_cn_'$DATE_NOW'.zip' *
