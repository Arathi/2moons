#2Moons汉化者手册

## 一. 需要的工具
* __SVN客户端__

    下文会提到，官方版本的源码仓库在Google Code的svn上，因此获取官方版本需要SVN客户端。Windows下推荐使用[TortoiseSVN](http://tortoisesvn.net/downloads.html)。

* __git客户端__

    汉化项目托管在[git@osc](http://git.oschina.net/arathi/cn2moons)上，在[github](https://github.com/Arathi/2moons)上有个镜像，因此提交代码以及下载新的开发中的汉化版本都要用[git](http://git-scm.com/)客户端。Windows下推荐使用Msysgit，如果以前用习惯了TortoiseSVN，也可以再装个[TortoiseGit](https://code.google.com/p/tortoisegit/)。关于git工具的使用，建议阅读[《Pro Git》](http://git.oschina.net/progit/)一书。

* __文本编辑器__

    既然是汉化一个PHP的项目，文本编辑器就不能太水，该有的功能还是得有的。其他不多说，推荐使用以下编辑器（按字母顺序排序）：

    - [Emacs](http://www.gnu.org/software/emacs/)
    - [Notepad++](http://notepad-plus-plus.org/)
    - [Sublime Text](http://www.sublimetext.com/2)
    - [UltraEdit](http://www.ultraedit.cn/)
    - [Vim](http://www.vim.org/)

* __比较与合并工具__

    推荐使用[Beyond Compare](http://www.scootersoftware.com/)，不过这是款商业软件。

## 二. 官方版本获取
2Moons是个仍在开发中的项目，因此原始的德文版会经常更新，英文版是官方团队维护的，因此更新也较为及时。

2Moons项目建立在[Google Code](https://code.google.com/p/2moons/)上，使用Subversion对源码进行版本控制，SVN检出地址为：`http://2moons.googlecode.com/svn/trunk/`

通过及时更新SVN本地工作副本，我们可以获取最新官方版本的源代码。

## 三. 最新汉化版本获取
前面提到，本项目使用git进行版本控制，项目建立在git@osc和github上，其中由于github为英文界面，为了照顾对英语不感冒，而且不便科学上网的同学，此项目的issue和wiki是在国内的git@osc上管理的，github仅仅作为一个镜像，关闭了issue和wiki功能。在这里，我假设你们已经学完了《Pro Git》一书_（注：但是可能假设不成立）_。

克隆版本库的权限，每个人都有的。命令如下：

    # 从git@osc上克隆
    git clone http://git.oschina.net/arathi/cn2moons.git
    # 从github上克隆
    git clone

克隆出本地仓库以后，只要注意，在每次修改前，要和中央仓库保持同步（多人协作的模式下，先检出，后修改，不管用什么SCM，都要注意这一点）。

## 四. 需要汉化的文件
目前已经在language下添加了cn目录，程序中有自动获取语言包信息的机制，因此不需要修改程序文件就能使程序支持中文（以后有使程序默认使用cn语言包的改造计划，不过貌似改造量并不大）。

cn目录是从en目录复制出来的，因为其他语言的翻译不好招……简短的词翻译还是没问题的，现在有问题的是长句，其实长句也没什么，最有问题的……嗯，长难句。

cn目录下有一个template目录，里面存放着邮件模板、已安装提示信息、游戏规则等，这些文件的翻译后面再说。

cn目录下还有一个ignore目录，这个目录是为了统计方便而设的，可以不用管。

cn目录下的那11个php文件优先翻译。这11个php文件中，`CHANGELOG.php`和`CUSTOM.php`是不需要翻译的，剩下的9个文件特征如下：

### ADMIN.php 管理
文本量非常大，有1093个字符串，格式基本上正常，但是有些语句结束后有注释。

### BANNER.php 横幅 （翻译完成）
文本量很小，总共8个字符串，都是单词，格式基本正常，最后一行结束时没有换行，汉化时应该加上。

### FAQ.php FAQ （剩下BODY块）
文本量小，只有17个值，但其中有8个BODY块（多行HTML格式文本），里面是一段文字，应交给翻译人员。

游戏中似乎没有用到这个页面，所以这个页面的完全翻译等到粗汉化阶段完成之后再回收也不迟。另外，翻译这个页面如果对游戏有足够的了解，会更好一点。

### FLEET.php 舰队
文本量不大，总共181个值。格式正常，不过很多句子是长句。这一部分有OGame和旧版2Moons的翻译可供参考。

### INGAME.php 游戏
文本量非常大，有1019个值。格式基本正常，一些需要注意的地方：

* `$LNG['user_level']` 的值是一个数组，而且内部需要翻译
* `$LNG['ti_create_info']` 这个变量的值有多行，是个长句

### INSTALL.php 安装时 （翻译完成）
文本量不大（87个值），格式正常，里面的句子都不长。

### L18N.php 本地化 （翻译完成）
没什么东西，大部分内容可以拿phpBB里的翻译替代一下。
不过时区那个，翻译了似乎没什么用，因为游戏里面似乎用了另一套机制获取时区-地名对应字符串。

### PUBLIC.php 公共部分 （翻译完成）
文件内容不算太多，有103个字符串，格式基本正常。

### TECH.php 技术
文本量中等，338个字符串，这个文件中大量存在带有键值的多行array。

### 关于安装条款是否翻译的问题

安装界面的步骤1有一个2Moons安装协议，其实就是[GPLv3](http://www.gnu.org/licenses/gpl.html)，由于目前GPLv3并没有没有正式的中文版本（有包括英文版、德文版、日文版、俄文版在内的7种版本，但就是没有中文版），而且使用其他语言（即使是德文）安装，也并不翻译该协议，都是英文表示。因此该协议（暂）不翻译。况且，说实话其实安装的时候没人会去看协议的说……

如果要翻译的话，现在网上有一份非官方的《GNU通用公共许可证 第三版》，翻译后的协议（GPLv3-LICENSE-cn.txt）现在已经放到licenses目录下。

## 五. 关于本地测试
本地测试使用常见的服务端套件都行，Windows下推荐[wampserver](http://www.wampserver.com/)以及[xampp](http://www.apachefriends.org/zh_cn/xampp.html)，不嫌麻烦也可以IIS+mysql+PHP，或者追求速度nginx+mysql+PHP，这个随你喜欢，怎样方便就怎么来；Linux下直接用yum或者apt-get之类的包管理工具安装[apache](http://httpd.apache.org/)+[mysql](http://dev.mysql.com/downloads/)+[php](http://www.php.net/downloads.php)就好，也可以编译安装，不过虚拟主机多是LAMP，最好能模拟生产环境就是了。

2Moons使用的模板引擎是常见的Smarty，Smarty类有些成员变量，用来决定是否启用缓存、是否强制编译等。2Moons在初始化Smarty引擎时，设置了启用缓存，因此默认情况下翻译文本不会及时更新，需要清空缓存重新生成。

至于这些成员变量的初始化，是在include/classes/class.template.php中大约第46行到54行之间的smartySettings函数：

    private function smartySettings()
	{	
		$this->force_compile 			= false;
		$this->caching 					= true; #Set true for production!
		$this->merge_compiled_includes	= true;
		$this->compile_check			= true; #Set false for production!
		$this->php_handling				= Smarty::PHP_REMOVE;

		$this->setCompileDir(is_writable(CACHE_PATH) ? CACHE_PATH : $this->getTempPath());
		$this->setCacheDir($this->getCompileDir().'templates');
		$this->setTemplateDir('styles/templates/');
	}

为了避免每次改变字符串就重置一次缓存，可以在测试环境中把`$this->caching=true;`改为`$this->caching=false;`。

不过，记住该行后面提示的“Set true for production!”，在生产环境，请将该值改回为`true`。

## 七. 中文化计划对2Moons程序部分的改动
（暂未统计）

## 附录A：参考文献
（暂无）

## 附录B：汉化参考
2Moons曾经由晗网(HANHOT)汉化并运营，网上能找到的最后版本是1.1.6，这大约是2011年8月以前的事情了，现在已经停止维护了。而目前（2013年12月）2Moons已经发展到了1.7.3，早就已经面目全非。不过一些基础词汇的翻译还是可以作为参考的。

2Moons本身也是OGame的开源仿制，因此在汉化2Moons之前，可以看看OGame的介绍，或者玩玩官服英文版、台服和国内的改版私服，熟悉一下游戏的内容，在遇到一些歧义的时候能翻译出准确的意思。

另外网上还有流传一份OGameCN 1.4，似乎是xNova的汉化版，结构完全不同，但是还是可以作为参考。

## 附录C：部分常用词汇翻译公约
（暂无）
