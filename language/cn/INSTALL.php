<?php

// Translated into Chinese by Arathi and 爱疯的云 . All rights reversed (C) 2013
// 2Moons - Copyright (C) 2010-2012 Slaver


$LNG['back']					= '退后';
$LNG['continue']				= '继续';
$LNG['continueUpgrade']			= '升级!';
$LNG['login']					= '登陆';

$LNG['menu_intro']				= 'Introduction';
$LNG['menu_install']			= '安装';
$LNG['menu_license']			= 'License';
$LNG['menu_upgrade']			= '升级';

$LNG['title_install']			= '安装';

$LNG['intro_lang']				= '语言';
$LNG['intro_install']			= 'To installation';
$LNG['intro_welcome']			= '您好，2Moons的用户！';
$LNG['intro_text']				= '您正在安装的2Moons，是OGame类项目中最好的实现之一。<br>2Moons是也当前仍在开发的XNova中最新最稳定的版本。2Moons的卓越之处在于其稳定、灵活、开发活跃、高质量以及被千锤百炼。我们总是希望着这部作品能够超乎你所期盼。<br><br>本安装系统将会引导你安装，或者从上个版本升级。对于使用本系统中的每一个困惑，以及发生的每一个问题，请不要犹豫，立即向开发及技术支持人员提出！<br><br>2Moons是一个开放源代码的工程，基于GNU GPL v3授权。如对此还有疑异，可以点击下方的链接查看"Lincense"<br><br>在安装开始前，会有一个小测试，用来检测您的空间的方案/域名是否能完全满足2Moons的需要';
$LNG['intro_upgrade_head']		= '已经安装过2Moons？';
$LNG['intro_upgrade_text']		= '<p>You have already installed 2Moons and want easy updating?</p><p>Here you can update your old database with just a few clicks!</p>';


$LNG['upgrade_success']			= 'Update of the database successfully. Database is now available on the revision %s.';
$LNG['upgrade_nothingtodo']		= 'No action is required. Database is already up to revision %s.';
$LNG['upgrade_back']			= 'Back';
$LNG['upgrade_intro_welcome']	= 'Welcome to the database upgrader!';
$LNG['upgrade_available']		= 'Available updates for your database! The database is at the revision %s and can update to revision %s.<br><br>Please choose from the following menu to the first SQL update to install:';
$LNG['upgrade_notavailable']	= 'The used revision %s is the latest for your database.';
$LNG['upgrade_required_rev']	= 'The Updater can work only from revision r2579 (2Moons v1. 7) or later.';


$LNG['licence_head']			= '许可条款';
$LNG['licence_desc']			= '请阅读以下许可条款。拖动滚动条以阅读该文档的全部内容';
$LNG['licence_accept']			= '我接受此协议的条款'; //（在安装2Moons前，您必须同意2Moons的许可条款）
$LNG['licence_need_accept']		= '若要继续安装，您必须接受此协议中的条款';

$LNG['req_head']				= '系统需求';
$LNG['req_desc']				= '在安装开始前，2Moons会做个测试，检验您的服务器是否能支持2Moons，以保证2Moons能够正常安装。建议您仔细阅读测试结果，如果检测中有未通过的项目，请勿继续安装。';
$LNG['reg_yes']					= '是';
$LNG['reg_no']					= '否';
$LNG['reg_found']				= '已找到';
$LNG['reg_not_found']			= '未找到';
$LNG['reg_writable']			= '可写入';
$LNG['reg_not_writable']		= '不可写';
$LNG['reg_file']				= '文件 &raquo;%s&laquo; 可写入？';
$LNG['reg_dir']					= '目录 &raquo;%s&laquo; 可写入？';
$LNG['req_php_need']			= '所安装的&raquo;PHP&laquo;的版本';
$LNG['req_php_need_desc']		= '<strong>必须</strong> — 2Moons基于PHP. 而且需要PHP版本高于5.2.5，这样才能使所有的模块正常工作';
$LNG['reg_gd_need']				= '已安装的GD库版本 &raquo;gdlib&laquo;';
$LNG['reg_gd_desc']				= '<strong>可选</strong> — 图像处理库 &raquo;gdlib&laquo; 用于生成动态图像。 They work without some of the features of the software.';
$LNG['reg_mysqli_active']		= '&raquo;MySQLi&laquo; 扩展支持';
$LNG['reg_mysqli_desc']			= '<strong>必须</strong> — 当前的PHP必须支持MySQLi。如果检测出您的服务器的数据库模块不可用，请与您的服务提供商联系，或者查看一下PHP的文档。';
$LNG['reg_json_need']			= '扩展 &raquo;JSON&laquo; 可用？';
$LNG['reg_iniset_need']			= 'PHP函数 &raquo;ini_set&laquo; 可用？';
$LNG['reg_global_need']			= 'register_globals 已禁用？';
$LNG['reg_global_desc']			= '即使您的服务器已经配置开启"register_globals"，2Moons也能正常工作，但出于安全考虑，还是建议尽可能地去禁用掉改选项。';
$LNG['req_ftp_head']			= '请填写FTP账户信息';
$LNG['req_ftp_desc']			= '填写您的FTP信息，以便2Moons能够自动修复各种问题。或者，您也可以手动去分配写入权限。';
$LNG['req_ftp_host']			= '服务器名';
$LNG['req_ftp_username']		= '用户名';
$LNG['req_ftp_password']		= '密码';
$LNG['req_ftp_dir']				= '2Moons安装目录';
$LNG['req_ftp_send']			= '发送';
$LNG['req_ftp_error_data']		= 'The information provided does not allow you to connect to the FTP server, so this link failed';
$LNG['req_ftp_error_dir']		= 'The story that directory you entered is invalid or not existing';

$LNG['step1_head']				= '配置数据库信息';
$LNG['step1_desc']				= 'Now that it has been determined that 2Moons can be installed on your server, s should provide some information. If you dont know how to run a link database, contact your hosting provider first or with the 2Moons forum for help and support. When you insert the data, checks were introduced properly';
$LNG['step1_mysql_server']		= '数据库服务器或DSN';
$LNG['step1_mysql_port']		= '数据库端口';
$LNG['step1_mysql_dbuser']		= '数据库用户名';
$LNG['step1_mysql_dbpass']		= '数据库密码';
$LNG['step1_mysql_dbname']		= '数据库名称';
$LNG['step1_mysql_prefix']		= '数据表前缀:';

$LNG['step2_prefix_invalid']	= 'The prefix of the database must contain alphanumeric characters and underscore as last character';
$LNG['step2_db_no_dbname']		= 'You dont specified the name for the database';
$LNG['step2_db_too_long']		= 'The table prefix is too long. Must contain at most 36 characters';
$LNG['step2_db_con_fail']		= 'There is an error in the link to database. The details will be displayed below';
$LNG['step2_conf_op_fail']		= "无法写入config.php！";
$LNG['step2_conf_create']		= 'config.php创建成功！';
$LNG['step2_config_exists']		= 'config.php已经存在！';
$LNG['step2_db_done']			= '数据库连接成功！';

$LNG['step3_head']				= '创建数据表';
$LNG['step3_desc']				= '2Moons所需的数据表已经创建成功，并且已经导入了默认值。点击下一步，结束2Moons的安装';
$LNG['step3_db_error']			= '创建数据表失败：';

$LNG['step4_head']				= '管理员账号';
$LNG['step4_desc']				= 'The installation wizard will now create an administrator account for you. Writes the name of use, your password and your email';
$LNG['step4_admin_name']		= '管理员用户名:';
$LNG['step4_admin_name_desc']	= 'Type the name to use with the length of 3 to 20 characters';
$LNG['step4_admin_pass']		= '管理员密码：';
$LNG['step4_admin_pass_desc']	= 'Type a password with a length of 6 to 30 characters';
$LNG['step4_admin_mail']		= 'Contact E-mail:';

$LNG['step6_head']				= '安装完成！';
$LNG['step6_desc']				= '您已经成功安装2Moons';
$LNG['step6_info_head']			= '立即开始2Moons！';
$LNG['step6_info_additional']	= 'If clicking the button below, will s are redirected to the page of administration .AI will be a good advantage to get ares to explore 2Moons administrator tools.<br/><br/><strong>Please delete the &raquo;includes/ENABLE_INSTALL_TOOL&laquo; or modify the filename. With the existence of this file, you can cause your game at risk by allowing someone rewrite the installation!</strong>';

$LNG['sql_close_reason']		= '游戏已关闭';
$LNG['sql_welcome']				= '欢迎来到2Moons v';
