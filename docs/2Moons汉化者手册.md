#2Moons汉化者手册

## 需要的工具
* SVN客户端

    下文会提到，官方版本的源码仓库在Google Code的svn上，因此获取官方版本需要SVN客户端。Windows下推荐使用TortoiseSVN。

* git客户端

    汉化项目托管在git@osc上，因此提交代码以及下载新的开发中的汉化版本都要用git客户端。Windows下推荐使用Msysgit，如果以前用习惯了TortoiseSVN，也可以用TortoiseGit。

* 文本编辑器

    既然是汉化一个PHP的项目，文本编辑器就不能太水，该有的功能还是得有的。其他不多说，推荐使用以下编辑器（按字母顺序排序）：

    - [Emacs](http://www.gnu.org/software/emacs/)
    - [Notepad++](http://notepad-plus-plus.org/)
    - [Sublime Text](http://www.sublimetext.com/2)
    - [UltraEdit](http://www.ultraedit.cn/)
    - [Vim](http://www.vim.org/)

* 比较与合并工具

    推荐使用Beyond Compare，不过这是款商业软件。

## 官方版本获取
2Moons是个仍在开发中的项目，因此原始的德文版会经常更新，英文版是官方团队维护的，因此更新也较为及时。

2Moons项目建立在[Google Code](https://code.google.com/p/2moons/)上，使用Subversion对源码进行版本控制，SVN检出地址为：`http://2moons.googlecode.com/svn/trunk/`

通过及时更新SVN本地工作副本，我们可以获取最新官方版本的源代码。

## 需要汉化的文件
目前已经在language下添加了cn目录，程序中有自动获取语言包信息的机制，因此不需要修改程序文件就能使程序支持中文（以后有使程序默认使用cn语言包的改造计划，不过貌似改造量并不大）。

cn目录是从en目录复制出来的，因为其他语言的翻译不好招……简短的词翻译还是没问题的，现在有问题的是长句，其实长句也没什么，最有问题的……嗯，长难句。

cn目录下还有一个template目录，里面存放着邮件模板、已安装提示信息、游戏规则等，这些文件的翻译后面再说，cn目录下的那11个php文件优先翻译。这11个php文件中，`CHANGELOG.php`和`CUSTOM.php`是不需要翻译的，剩下的9个文件特征如下：

* ADMIN.php __管理__

    文本量非常大，有1093个字符串，格式基本上正常，但是有些语句结束后有注释。

* BANNER.php __横幅__ （翻译完成）

    文本量很小，总共8个字符串，都是单词，格式基本正常，最后一行结束时没有换行，汉化时应该加上。

* FAQ.php __FAQ__

    文本量小，只有17个值，但其中有8个BODY块（多行HTML格式文本）。

* FLEET.php __舰队__

    文本量不大，总共181个值。格式正常，不过很多句子是长句。

* INGAME.php __游戏__

    文本量非常大，有1019个值。格式基本正常，一些需要注意的地方：

    - `$LNG['user_level']` 的值是一个数组，而且内部需要翻译<br/>
    - `$LNG['ti_create_info']` 这个变量的值有多行，是个长句

* INSTALL.php __安装时__

    文本量不大（87个值），格式正常，里面的句子都不长，现在翻译得差不多了。

* L18N.php __本地化__ （翻译完成）

    没什么东西，可以拿phpBB的翻译替代一下。

* PUBLIC.php __公共部分__

    文件内容不算太多，有103个字符串，格式基本正常。

* TECH.php __技术__

    文本量中等，338个字符串，这个文件中大量存在带有键值的多行array。

## 关于本地测试
本地测试使用常见的AMP服务器套件都行，Windows下推荐wampserver以及xampp，Linux下手动安装apache+mysql+php就好。

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

## 部分常用词汇翻译公约
（暂无）

## 注意事项

* 关于安装条款是否翻译的问题

    安装界面的步骤1有一个2Moons安装协议，其实就是[GPLv3（GNU通用公共许可证 第三版）](http://www.gnu.org/licenses/gpl.html)，由于目前GPLv3并没有没有正式的中文版本（有包括英文版、德文版、日文版、俄文版在内的7种版本，但就是没有中文版），而且使用其他语言（即使是德文）安装，也并不翻译该协议，都是英文表示。因此该协议（暂）不翻译。况且，说实话其实安装的时候没人会去看协议的说……

* 汉化参考

    2Moons曾经由晗网(HANHOT)汉化并运营，网上能找到的最后版本是1.1.6，这大约是2011年8月以前的事情了，现在已经停止维护了。而目前（2013年12月）2Moons已经发展到了1.7.3，早就已经面目全非。不过一些基础词汇的翻译还是可以作为参考的。

    2Moons本身也是OGame的开源仿制，因此在汉化2Moons之前，可以看看OGame的介绍，或者玩玩英文版，熟悉一下游戏的内容，在遇到一些歧义的时候能翻译出准确的意思。

