#cn_2moons开发者手记#

## 1. 关于最新版本信息的获取 ##
主要是实现自动获取googlecode的svn上的原版最新版本号。

最新版的版本号可以在
<a href="https://code.google.com/p/2moons/source/browse/trunk">`https://code.google.com/p/2moons/source/browse/trunk`</a>
上面获取。

下载这个页面(可以用HttpClient)，版本号即为
`<td class="flipper"><b>rxxxx</b></td>`中的xxxx。

不用正则表达式就能匹配，找一下`<td class="flipper"><b>r`，后面的数字就是。

## 2. 关于自动检出最新版本的代码 ##
Java可以使用SvnKit来检出和更新svn源码库，或者调用antsvn。

关于SvnKit的使用，参照过去的SvnAR的代码。

关于antsvn，可以参考SrcAR的代码。

不过实际上antsvn用的也是SvnKit实现的。

## 3. 关于自动汉化的实现 ##
暂无相关计划

## 4. 暂时不需要改动的文件列表 ##
    CHANGELOG.php
    CUSTOM.php

## 5. 各个文件的特征 ##
### ADMIN.php 管理员
文件较大（1093个值），但基本正常，部分行尾部存在注释
需要注意的地方：
`$LNG['ma_modes']` 值为一个数组，不过似乎不需要翻译。

### <s>BANNER.php 横幅</s> （翻译完成）
文件只有8个值。格式正常，不过最后一行结束没有换行。建议翻译时加上。

### FAQ.php FAQ
文件中有17个值。格式有点特别，存在<<<BODY ... BODY; 结构。
<s>目前尚不了解这种结构，可能需要研究smarty模版引擎。</s>

### FLEET.php 舰队
总共181个值。格式正常，不过很多句子是长句。

### INGAME.php 游戏时显示的文本
文件较大（1019个值），格式基本正常，需要注意的地方：

`$LNG['user_level']` 的值是一个数组，而且内部需要翻译<br/>
`$LNG['ti_create_info']` 这个变量的值有多行

### INSTALL.php 安装时
文件不大（87个值），格式正常，没有参照翻译。

### <s>L18N.php 本地化</s> （翻译完成）
（这里有错误，文件名应该是L10N或者I18N）

这个文件的汉化的问题较多：

* 两句setlocale函数中的值的替换

    替换为本地相关的

* 两个小array(`$LNG['week_day']`和`$LNG['months']`)的值的替换

    替换成国人对星期和月份的表示方法

* 大array(`$LNG['timezones']`)中键和值的替换

    时区信息一般不会有差异，对于万一官方修改该文件的解决方案是：<br/>
    1. 在原始的文件没有发生改动时，就不用管它，直接换掉。<br/>
    2. 一旦原始文件发生了改动，通知汉化工作人员

    <i>注意：文件中有提到可以使用phpBB的本地化信息替换。</i>

### <s>LANG.cfg 语言信息</s> （翻译完成）
文件很小，整个文件中只有一个数组(多行)的赋值。

该数组有4个元素：`name`、`tag`、`author`、`date`

原始的文件如果没有添加新的值，就保持原结构。自动翻译需要修改时间为最新的。

### PUBLIC.php 公共
文件内容不算太多（103个值），格式基本正常。

需要注意的是`$LNG['gameInformations']`，这是个多行array。

### TECH.php 科技
总共338个值，这个文件中大量存在带有键值的多行array。

### 结论
综上所述，一个文件是否需要提交合并，取决于该文件是否发生了改动。

如果相对于上一个版本发生了变动，则需要评估一下源语言的修改量，再把能自动翻译的部分提取出来，至于不能自动翻译的部分，需要通知相关人员。

## 6. 建议使用的工具 ##
* git工具

    本项目在git@osc和github上托管，其中主要使用的是git@osc，项目的issue（问题追踪）和wiki（百科）都在git@osc上；虽然github更好更强大，但由于有时国内访问不便，照顾汉化团队中不便科学上网的同学，因此只是做一个镜像。

    至于git的客户端，Windows下推荐使用Msysgit，如果以前用惯了TortoiseSVN，也可以用TortoiseGit。

* 文本编辑器

    虽然本项目的主要部分还是汉化，因此基本只对language进行操作；但

    常用的文本编辑器都可以，当然有PHP的IDE就更好了，需要支持的功能包括：转换档案格式、PHP语法高亮等。

    推荐使用以下编辑器（按字母顺序排序）：

    - [Emacs](http://www.gnu.org/software/emacs/)
    - [Notepad++](http://notepad-plus-plus.org/)
    - [Sublime Text](http://www.sublimetext.com/2)
    - [UltraEdit](http://www.ultraedit.cn/)
    - [Vim](http://www.vim.org/)

* 对比与合并工具

    主要推荐使用Beyond Compare，不过这是款商业软件。

## 7. 关于数据库结构 ##
暂时不做相关的解读
