<?php

// Translated into English by QwataKayean . All rights reversed (C) 2012
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


$LNG['licence_head']			= 'License terms';
$LNG['licence_desc']			= 'Please read the license terms below. Use the scroll bar to see all the contents of the document';
$LNG['licence_accept']			= 'To continue the installation of 2Moons, you need to agree to the terms and conditions of lincense of 2Moons';
$LNG['licence_need_accept']		= 'If you want to continue with the installation, will s that accept the terms of license';

$LNG['req_head']				= '系统需求';
$LNG['req_desc']				= 'Before the installation proceed, 2Moons will be some tests to verify that your server supports the 2Moons, so ensure that the 2Moons can be installed. Its suggested that you read carefully the results, and do not proceed until all these be checked.';
$LNG['reg_yes']					= 'Yes';
$LNG['reg_no']					= 'No';
$LNG['reg_found']				= '已找到';
$LNG['reg_not_found']			= '未找到';
$LNG['reg_writable']			= '可写入';
$LNG['reg_not_writable']		= '不可写';
$LNG['reg_file']				= 'The file &raquo;%s&laquo; Is recordable?';
$LNG['reg_dir']					= 'The folder &raquo;%s&laquo; Is recordable?';
$LNG['req_php_need']			= 'Installed version of the scripting language &raquo;PHP&laquo;';
$LNG['req_php_need_desc']		= '<strong>Required</strong> — PHP is the language code base of 2Moons. This is the required PHP version 5.2.5 or higher so that all modules work correctly';
$LNG['reg_gd_need']				= 'Installed version of the GD PHP Script &raquo;gdlib&laquo;';
$LNG['reg_gd_desc']				= '<strong>Optional</strong> — Graphic processing library &raquo;gdlib&laquo; Is responsible for the generation of dynamic images. They work without some of the features of the software.';
$LNG['reg_mysqli_active']		= 'Extension support &raquo;MySQLi&laquo;';
$LNG['reg_mysqli_desc']			= '<strong>Required</strong> — Need to have support for MySQLi in PHP. If no module of the database are shown as being available, you should contact your hosting provider (Domain Hoster) or review the documentation of PHP.';
$LNG['reg_json_need']			= 'Extension &raquo;JSON&laquo; avaliable?';
$LNG['reg_iniset_need']			= 'PHP function &raquo;ini_set&laquo; avaliable?';
$LNG['reg_global_need']			= 'register_globals disabled?';
$LNG['reg_global_desc']			= '2Moons will also work, if this configuration is installed on your server. However, it is recommended for security reasons, disable "register_globals" in PHP installation, if that is possible.';
$LNG['req_ftp_head']			= 'Insert information of FTP';
$LNG['req_ftp_desc']			= 'Write your information from FTP so 2Moons automatically fix problems. Alternatively, you can also manually assign permissions to write.';
$LNG['req_ftp_host']			= '服务器名';
$LNG['req_ftp_username']		= '用户名';
$LNG['req_ftp_password']		= '密码';
$LNG['req_ftp_dir']				= '2Moons安装目录';
$LNG['req_ftp_send']			= '发送';
$LNG['req_ftp_error_data']		= 'The information provided does not allow you to connect to the FTP server, so this link failed';
$LNG['req_ftp_error_dir']		= 'The story that directory you entered is invalid or not existing';

$LNG['step1_head']				= 'Configure the installation database';
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
$LNG['step3_desc']				= 'The tables needed for the 2Moons database already have been created and populated with default values. To go to the next step, conclude the installation of 2Moons';
$LNG['step3_db_error']			= 'Failed to create the database tables:';

$LNG['step4_head']				= '管理员账号';
$LNG['step4_desc']				= 'The installation wizard will now create an administrator account for you. Writes the name of use, your password and your email';
$LNG['step4_admin_name']		= '管理员用户名:';
$LNG['step4_admin_name_desc']	= 'Type the name to use with the length of 3 to 20 characters';
$LNG['step4_admin_pass']		= '管理员密码：';
$LNG['step4_admin_pass_desc']	= 'Type a password with a length of 6 to 30 characters';
$LNG['step4_admin_mail']		= 'Contact E-mail:';

$LNG['step6_head']				= '安装完成！';
$LNG['step6_desc']				= 'You installed with success the 2Moons system';
$LNG['step6_info_head']			= 'Getting and using the 2Moons now!';
$LNG['step6_info_additional']	= 'If clicking the button below, will s are redirected to the page of administration .AI will be a good advantage to get ares to explore 2Moons administrator tools.<br/><br/><strong>Please delete the &raquo;includes/ENABLE_INSTALL_TOOL&laquo; or modify the filename. With the existence of this file, you can cause your game at risk by allowing someone rewrite the installation!</strong>';

$LNG['sql_close_reason']		= '游戏已关闭';
$LNG['sql_welcome']				= '欢迎来到2Moons v';
