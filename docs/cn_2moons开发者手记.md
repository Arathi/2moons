#cn_2moons开发者手记#

## 1.关于最新版本信息的获取
googlecode的svn上的原版最新版本号的自动获取
最新版的版本号可以在
<a href="https://code.google.com/p/2moons/source/browse/trunk">`https://code.google.com/p/2moons/source/browse/trunk`</a>
上面获取。

下载这个页面(可以用HttpClient)，版本号即为
`<td class="flipper"><b>rxxxx</b></td>`中的xxxx。

可以不用正则表达式，找一下`<td class="flipper"><b>r`，后面的数字就是。

## 2.关于自动检出最新版本的代码
Java可以使用SvnKit来检出和更新svn源码库，或者调用antsvn。

关于SvnKit的使用，参照过去的SvnAR的代码。

关于antsvn，可以参考SrcAR的代码。

不过实际上antsvn用的也是SvnKit实现的。

## 3.关于自动汉化的实现
暂无

## 4.暂时不需要改动的文件列表
    CHANGELOG.php
    CUSTOM.php

## 5.各个文件的特征
### ADMIN.php 管理员
文件较大，但基本正常，部分行尾部存在注释
需要注意的地方：
`$LNG['ma_modes']` 值为一个数组，不过似乎不需要翻译。

### BANNER.php
正常，不过最后一行结束没有换行。建议翻译时加上。

### FAQ.php FAQ
存在<<<BODY ... BODY; 结构。
<s>目前尚不了解这种结构，可能需要研究smarty模版引擎。</s>

### FLEET.php 舰队
正常，不过很多句子是长句。

### INGAME.php 游戏时显示的文本
文件较大，基本正常，需要注意的地方：

`$LNG['user_level']` 的值是一个数组，而且内部需要翻译<br/>
`$LNG['ti_create_info']` 这个变量的值有多行

### INSTALL.php 安装时	
文件不大，格式正常，没有参照翻译。

### L18N.php 本地化
（这里有错误，文件名应该是L10N或者I18N）

这个文件的汉化的问题较多：

* 两句setlocale函数中的值的替换

    替换为本地相关的

* 两个小array(`$LNG['week_day']`和`$LNG['months']`)的值的替换

    替换成国人对星期和月份的表示方法

* 大array(`$LNG['timezones']`)中键和值的替换

    时区信息一般不会有差异<br/>
    对于万一官方修改该文件的解决方案是：<br/>
    1. 在原始的文件没有发生改动时，就不用管它，直接换掉。<br/>
    2. 一旦原始文件发生了改动，通知汉化工作人员

### LANG.cfg 语言信息
文件很小，整个文件中只有一个数组(多行)的赋值
有4个属性，name、tag、author、date
原始的文件如果没有添加新的值，就保持原结构
自动翻译需要修改时间为最新的

### PUBLIC.php 公共
文件内容不算太多，基本正常，
需要注意$LNG['gameInformations']，多行array。

### TECH.php 科技
这个文件中大量存在带有键值的多行array。

### 结论
综上所述，一个文件是否需要提交合并，取决于该文件是否发生了改动。

如果相对于上一个版本发生了变动，则需要评估一下源语言的修改量，再把能自动翻译的部分提取出来，通知汉化工作人员。
