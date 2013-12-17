<?php

/**
 *  2Moons
 *  Copyright (C) 2011 Jan Kr�pke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan Kr�pke <info@2moons.cc>
 * @copyright 2009 Lucky
 * @copyright 2011 Jan Kr�pke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.5 (2011-07-31)
 * @info $Id: index.php 2680 2013-05-02 20:38:45Z slaver7 $
 * @link http://2moons.cc/
 */

if(!function_exists('spl_autoload_register')) {
	exit("PHP is missing <a href=\"http://php.net/spl\">Standard PHP Library (SPL)</a> support");
}


$UNI	= 1;

define('MODE', 'INSTALL');
define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))).'/');
chdir(ROOT_PATH);

require('includes/common.php');

$THEME->setUserTheme('gow');

$LNG = new Language;
$LNG->getUserAgentLanguage();
$LNG->includeData(array('L18N', 'INGAME', 'INSTALL'));

$template = new template();
$template->assign(array(
	'lang'			=> $LNG->getLanguage(),
	'Selector'		=> $LNG->getAllowedLangs(false),
	'title'			=> $LNG['title_install'].' &bull; 2Moons',
	'header'		=> $LNG['menu_install'],
	'canUpgrade'	=> file_exists("includes/config.php") && filesize("includes/config.php") !== 0,
));

$enableInstallToolFile	= 'includes/ENABLE_INSTALL_TOOL';
$quickstartFile			= 'includes/FIRST_INSTALL';

// If include/FIRST_INSTALL is present and can be deleted, automatically create include/ENABLE_INSTALL_TOOL
if (is_file($quickstartFile) && is_writeable($quickstartFile) && unlink($quickstartFile)) {
	@touch($enableInstallToolFile);
}

// Only allow Install Tool access if the file "include/ENABLE_INSTALL_TOOL" is found
if (is_file($enableInstallToolFile) && (time() - filemtime($enableInstallToolFile) > 3600)) {
	$content = file_get_contents($enableInstallToolFile);
	$verifyString = 'KEEP_FILE';

	if (trim($content) !== $verifyString) {
		// Delete the file if it is older than 3600s (1 hour)
		unlink($enableInstallToolFile);
	}
}

if (!is_file($enableInstallToolFile)) {
	$template->message($LNG->getTemplate('locked_install'), false, 0, true);
	exit;
}

$language	= HTTP::_GP('lang', '');

if(!empty($language) && in_array($language, $LNG->getAllowedLangs()))
{
	setcookie('lang', $language);
}

$mode	  = HTTP::_GP('mode', '');
switch($mode)
{
	case 'ajax':
		require_once('includes/libs/ftp/ftp.class.php');
		require_once('includes/libs/ftp/ftpexception.class.php');
		$LNG->includeData(array('ADMIN'));
		$CONFIG = array("host" => $_GET['host'], "username" => $_GET['user'], "password" => $_GET['pass'], "port" => 21); 
		try
		{
			$ftp = FTP::getInstance(); 
			$ftp->connect($CONFIG);
		}
		catch (FTPException $error)
		{
			exit($LNG['req_ftp_error_data']);
		}	
					
		if(!$ftp->changeDir($_GET['path']))
			exit($LNG['req_ftp_error_dir']);

		$CHMOD	= (php_sapi_name() == 'apache2handler') ? 0777 : 0755;		
		$ftp->chmod('cache', $CHMOD);
		$ftp->chmod('cache/sessions', $CHMOD);
		$ftp->chmod('includes', $CHMOD);
		$ftp->chmod('install', $CHMOD);
	break;
	case 'upgrade':
		// Willkommen zum Update page. Anzeige, von und zu geupdatet wird. Informationen, dass ein backup erstellt wird.
		require_once('includes/config.php');
		require_once('includes/dbtables.php');
		
		$GLOBALS['DATABASE']	= new Database();
		Config::init();
		
		
		$directoryIterator = new DirectoryIterator('install/updates/');
		try {
			$sqlRevision	= Config::get('sql_revision');
		} catch(Exception $e) {
			$template->message($LNG['upgrade_required_rev'], false, 0, true);
			exit;
		}
		
		$fileList	= array();
		foreach($directoryIterator as $fileInfo)
		{
			if (!$fileInfo->isFile()) continue;
			
			$fileRevision	= substr($fileInfo->getFilename(), 7, -4);
			if ($fileRevision > $sqlRevision)
			{
				$fileList[]	= (int) $fileRevision;
			}
		}
			
		sort($fileList);
		
		$template->assign_vars(array(
			'revisionlist'	=> $fileList,
			'file_revision'	=> empty($fileList) ? $sqlRevision : max($fileList),
			'sql_revision'	=> $sqlRevision,
			'header'		=> $LNG['menu_upgrade'],
		));
		
		$template->show('ins_update.tpl');
	break;
	case 'doupgrade':
		require_once('includes/config.php');
		require_once('includes/dbtables.php');
		
		$startrevision	= HTTP::_GP('startrevision', 0);
		$GLOBALS['DATABASE']		= new Database();
		
		// Create a Backup
		$prefixCounts	= strlen(DB_PREFIX);
		$dbTables		= array();
		$sqlTableRaw	= $GLOBALS['DATABASE']->query("SHOW TABLE STATUS FROM `".DB_NAME."`;");

		while($table = $GLOBALS['DATABASE']->fetchArray($sqlTableRaw))
		{
			if(DB_PREFIX == substr($table['Name'], 0, $prefixCounts))
			{
				$dbTables[]	= $table['Name'];
			}
		}
		
		if(empty($dbTables))
		{
			throw new Exception('No tables found for dump.');
		}
		
		$fileName	= '2MoonsBackup_'.date('d_m_Y_H_i_s', TIMESTAMP).'.sql';
		$filePath	= 'includes/backups/'.$fileName;
		
		require 'includes/classes/SQLDumper.class.php';
		
		Config::init();
		$dump	= new SQLDumper;
		$dump->dumpTablesToFile($dbTables, $filePath);
		@set_time_limit(600);
		$httpRoot	= PROTOCOL.HTTP_HOST.str_replace(array('\\', '//'), '/', dirname(dirname($_SERVER['SCRIPT_NAME'])).'/');
		
		$revision	= $startrevision - 1;
		
		$fileList	= array();
		
		$directoryIterator = new DirectoryIterator('install/updates/');
		foreach($directoryIterator as $fileInfo)
		{
			if (!$fileInfo->isFile()) continue;
			
			$fileRevision	= substr($fileInfo->getFilename(), 7, -4);
	
			if ($fileRevision > $revision)
			{			
				
				$fileExtension	= pathinfo($filePath, PATHINFO_EXTENSION);
				$key			= $fileRevision.((int) $fileExtension === 'php');
				
				$fileList[$key]	= array(
					'fileName'		=> $fileInfo->getFilename(),
					'fileRevision'	=> $fileRevision,
					'fileExtension'	=> $fileExtension,
				);
			}
		}
		
		ksort($fileList);
		
		if (!empty($fileList) && !empty($revision))
		{
			foreach($fileList as $fileInfo)
			{
				switch($fileInfo['fileExtension'])
				{
					case 'php':
						copy(ROOT_PATCH.'install/updates/'.$fileInfo['fileName'], $fileInfo['fileName']);
						$ch = curl_init($httpRoot.$fileInfo['fileName']);
						curl_setopt($ch, CURLOPT_HEADER, false);
						curl_setopt($ch, CURLOPT_NOBODY, true);
						curl_setopt($ch, CURLOPT_MUTE, true);
						curl_exec($ch);
						if(curl_errno($ch))
						{
							$errorMessage = 'CURL-Error on update '.basename($fileInfo['filePath']).':'.curl_error($ch);
							try {
								$dump->restoreDatabase($filePath);
								$message	= 'Update error.<br><br>'.$errorMessage.'<br><br><b><i>Backup restored.</i></b>';
							} catch(Exception $e) {
								$message	= 'Update error.<br><br>'.$errorMessage.'<br><br><b><i>Can not restore backup. Your game is maybe broken right now.</i></b><br><br>Restore error:<br>'.$e->getMessage();
							}
							throw new Exception($message);
						}
						curl_close($ch);
						unlink(ROOT_PATCH.$file);
					break;
					case 'sql';
						$data = file_get_contents('install/updates/'.$fileInfo['fileName']);
						try {
							$GLOBALS['DATABASE']->multi_query(str_replace("prefix_", DB_PREFIX, $data));
						} catch (Exception $e) {
							$errorMessage = $e->getMessage();
							try {
								$dump->restoreDatabase($filePath);
								$message	= 'Update error.<br><br>'.$errorMessage.'<br><br><b><i>Backup restored.</i></b>';
							} catch(Exception $e) {
								$message	= 'Update error.<br><br>'.$errorMessage.'<br><br><b><i>Can not restore backup. Your game is maybe broken right now.</i></b><br><br>Restore error:<br>'.$e->getMessage();
							}
							throw new Exception($message);
						}
					break;
				}
			}
			
			$revision	= end($fileList);
			$revision	= $revision['fileRevision'];
		}
		
		$gameVersion	= explode('.', Config::get('VERSION'));
		$gameVersion[2]	= $revision;
		
		$GLOBALS['DATABASE']->query("UPDATE ".CONFIG." SET VERSION = '".implode('.', $gameVersion)."', sql_revision = ".$revision.";");
		ClearCache();
		$template->assign_vars(array(
			'update'		=> !empty($fileList),
			'revision'		=> $revision,
			'header'		=> $LNG['menu_upgrade'],
		));
		$template->show('ins_doupdate.tpl');
	break;
	case 'install':
		$step	  = HTTP::_GP('step', 0);
		switch ($step) {
			case 1:
				if(isset($_POST['post'])) {
					if(isset($_POST['accept'])) {
						HTTP::redirectTo('index.php?mode=install&step=2');
					} else {
						$template->assign(array(
							'accept'	=> false,
						));
					}
				}
				$template->show('ins_license.tpl');
			break;
			case 2:
				$error 	= false;
				$ftp 	= false;
				if(version_compare(PHP_VERSION, "5.2.5", ">=")){
					$PHP = "<span class=\"yes\">".$LNG['reg_yes'].", v".PHP_VERSION."</span>";
				} else {
					$PHP = "<span class=\"no\">".$LNG['reg_no'].", v".PHP_VERSION."</span>";
					$error	= true;
				}
				
				if(class_exists('mysqli')){
					$mysqli = "<span class=\"yes\">".$LNG['reg_yes']."</span>";
				} else {
					$mysqli = "<span class=\"no\">".$LNG['reg_no']."</span>";
					$error	= true;
				}
						
				if(function_exists('json_encode')){
					$json = "<span class=\"yes\">".$LNG['reg_yes']."</span>";
				} else {
					$json = "<span class=\"no\">".$LNG['reg_no']."</span>";
					$error	= true;
				}
				
				if(function_exists('ini_set')){
					$iniset = "<span class=\"yes\">".$LNG['reg_yes']."</span>";
				} else {
					$iniset = "<span class=\"no\">".$LNG['reg_no']."</span>";
					$error	= true;
				}
			
				if(!ini_get('register_globals')){
					$global = "<span class=\"yes\">".$LNG['reg_yes']."</span>";
				} else {
					$global = "<span class=\"no\">".$LNG['reg_no']."</span>";
					$error	= true;
				}

				if(!extension_loaded('gd')) {
					$gdlib = "<span class=\"no\">".$LNG['reg_no']."</span>";
				} else {
					$gdVerion = '0.0.0';
					if (function_exists('gd_info')) {
						$temp = gd_info();
						$match = array();
						if (preg_match('!([0-9]+\.[0-9]+(?:\.[0-9]+)?)!', $temp['GD Version'], $match)) {
							if (preg_match('/^[0-9]+\.[0-9]+$/', $match[1])) $match[1] .= '.0';
								$gdVerion = $match[1];
						}
					}
					$gdlib = "<span class=\"yes\">".$LNG['reg_yes'].", v".$gdVerion."</span>";
				}
				
				clearstatcache();
				
				if(file_exists("includes/config.php") || @touch("includes/config.php")){
					if(is_writable("includes/config.php")){
						$chmod = "<span class=\"yes\"> - ".$LNG['reg_writable']."</span>";
					} else {
						$chmod = " - <span class=\"no\">".$LNG['reg_not_writable']."</span>";
						$error	= true;
						$ftp	= true;
					}
					$config = "<tr><td class=\"transparent left\"><p>".sprintf($LNG['reg_file'], 'includes/config.php')."</p></td><td class=\"transparent\"><span class=\"yes\">".$LNG['reg_found']."</span>".$chmod."</td></tr>";
				} else {
					$config = "<tr><td class=\"transparent left\"><p>".sprintf($LNG['reg_file'], 'includes/config.php')."</p></td><td class=\"transparent\"><span class=\"no\">".$LNG['reg_not_found']."</span></td></tr>";
					$error	= true;
					$ftp	= true;
				}
				
				$directories = array('cache/', 'cache/templates/', 'includes/');
				$dirs = "";
				foreach ($directories as $dir)
				{
					if(is_writable(ROOT_PATH . $dir)) {
							$chmod = "<span class=\"yes\"> - ".$LNG['reg_writable']."</span>";
						} else {
							$chmod = " - <span class=\"no\">".$LNG['reg_not_writable']."</span>";
							$error	= true;
							$ftp	= true;
						}
					$dirs .= "<tr><td class=\"transparent left\"><p>".sprintf($LNG['reg_dir'], $dir)."</p></td><td class=\"transparent\"><span class=\"yes\">".$LNG['reg_found']."</span>".$chmod."</td></tr>";
				}

				if($error == false){
					$done = '<tr class="noborder"><td colspan="2" class="transparent"><a href="index.php?mode=install&step=3"><button style="cursor: pointer;">'.$LNG['continue'].'</button></a></td></tr>';
				} else {
					$done = '';
				}
				
				$template->assign(array(
					'dir'					=> $dirs,
					'json'					=> $json,
					'done'					=> $done,
					'config'				=> $config,
					'gdlib'					=> $gdlib,
					'PHP'					=> $PHP,
					'mysqli'				=> $mysqli,
					'ftp'					=> $ftp,
					'iniset'				=> $iniset,
					'global'				=> $global
				));
				$template->show('ins_req.tpl');
			break;
			case 3:
				$template->show('ins_form.tpl');
			break;
			case 4:
				$host	= HTTP::_GP('host', '');
				$port	= HTTP::_GP('port', 3306);
				$user	= HTTP::_GP('user', '', true);
				$userpw	= HTTP::_GP('passwort', '', true);
				$dbname	= HTTP::_GP('dbname', '', true);
				$prefix	= HTTP::_GP('prefix', 'uni1_');
				
				$template->assign(array(
					'host'		=> $host,
					'port'		=> $port,
					'user'		=> $user,
					'dbname'	=> $dbname,
					'prefix'	=> $prefix,
				));
				
				if (empty($dbname)) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_db_no_dbname'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				if (strlen($prefix) > 36) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_db_too_long'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				if (strspn($prefix, '-./\\') !== 0) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_prefix_invalid'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				if (preg_match('!^[0-9]!', $prefix) !== 0) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_prefix_invalid'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				if (is_file("includes/config.php") && filesize("includes/config.php") != 0) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_config_exists'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}

				@touch("includes/config.php");
				if (!is_writable("includes/config.php")) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_conf_op_fail'],
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				$database					= array();
				$database['host']			= $host;
				$database['port']			= $port;
				$database['user']			= $user;
				$database['userpw']			= $userpw;
				$database['databasename']	= $dbname;
				$database['tableprefix']	= $prefix;
				
				require_once('includes/classes/class.Database.php');
				
				try {
					$GLOBALS['DATABASE'] = new Database();
				} catch (Exception $e) {
					$template->assign(array(
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step2_db_con_fail'].'</p><p>'.$e->getMessage(),
					));
					$template->show('ins_step4.tpl');
					exit;
				}
				
				@touch("includes/error.log");
				
				$blowfish	= substr(str_shuffle('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 22);
				
				file_put_contents("includes/config.php", sprintf(file_get_contents("includes/config.sample.php"), $host, $port, $user, $userpw, $dbname, $prefix, $blowfish));
				$template->assign(array(
					'class'		=> 'noerror',
					'message'	=> $LNG['step2_db_done'],
				));
				$template->show('ins_step4.tpl');
				exit;
			break;
			case 5:
				$template->show('ins_step5.tpl');
			break;
			case 6:
				require_once('includes/config.php');
				require_once('includes/dbtables.php');	
				require_once('includes/classes/class.Database.php');
				
				$GLOBALS['DATABASE']	= new Database();
				
				$installSQL				= file_get_contents('install/install.sql');
				$installVersion			= file_get_contents('install/VERSION');
				$installRevision		= 0;
				
				preg_match('!\$'.'Id: install.sql ([0-9]+)!', $installSQL, $match); 
				
				$installVersion		= explode('.', $installVersion);
				if(isset($match[1]))
				{
					$installRevision	= (int) $match[1];
					$installVersion[2]	= $installRevision;
				}
				else
				{
					$installRevision	= (int) $installVersion[2];
				}
				
				$installVersion		= implode('.', $installVersion);
				
 				try {
 					$GLOBALS['DATABASE']->multi_query(str_replace(
 					array(
 						'%PREFIX%',
 						'%VERSION%',
						'%REVISION%',
 					), array(
 						$database['tableprefix'],
						$installVersion,
						$installRevision,
					), $installSQL));
 					
					unset($installSQL, $installRevision, $installVersion);
					
 					Config::init();
					Config::update(array(
						'timezone'			=> @date_default_timezone_get(),
						'lang'				=> $LNG->getLanguage(),
						'OverviewNewsText'	=> $LNG['sql_welcome'].'1.7',
						'uni_name'			=> $LNG['fcm_universe'].' 1',
						'close_reason'		=> $LNG['sql_close_reason'],
						'moduls'			=> implode(';', array_fill(0, MODULE_AMOUNT - 1, 1))
					), 1);
					
					HTTP::redirectTo('index.php?mode=install&step=7');
				} catch (Exception $e) {
					unlink("includes/config.php");
					$error	= $GLOBALS['DATABASE']->error;
					if(empty($error))
					{
						$error	= $e->getMessage();
					}
					$template->assign(array(
						'host'		=> $database['host'],
						'port'		=> $database['port'],
						'user'		=> $database['user'],
						'dbname'	=> $database['databasename'],
						'prefix'	=> $database['tableprefix'],
						'class'		=> 'fatalerror',
						'message'	=> $LNG['step3_db_error'].'</p><p>'.$error,
					));
					$template->show('ins_step4.tpl');
					exit;
				}
			break;
			case 7:
				$template->show('ins_acc.tpl');
			break;
			case 8:
				$AdminUsername	= HTTP::_GP('username', '', UTF8_SUPPORT);
				$AdminPassword	= HTTP::_GP('password', '', UTF8_SUPPORT);
				$AdminMail		= HTTP::_GP('email', '');
				
				// Get Salt.
				require_once('includes/config.php');

				$hashPassword	= cryptPassword($AdminPassword);
				
				$template->assign(array(
					'username'	=> $AdminUsername,
					'email'		=> $AdminMail,
				));
				
				if (empty($AdminUsername) && empty($AdminPassword) && empty($AdminMail)) {
					$template->assign(array(
						'message'	=> $LNG['step4_need_fields'],
					));
					$template->show('ins_step8error.tpl');
					exit;
				}
					
				require_once('includes/dbtables.php');
				$GLOBALS['DATABASE']	= new Database();
				Config::init();
								
				$SQL  = "INSERT INTO ".USERS." SET ";
				$SQL .= "username		= '".$GLOBALS['DATABASE']->sql_escape($AdminUsername)."', ";
				$SQL .= "password		= '".$GLOBALS['DATABASE']->sql_escape($hashPassword)."', ";
				$SQL .= "email			= '".$GLOBALS['DATABASE']->sql_escape($AdminMail)."', ";
				$SQL .= "email_2		= '".$GLOBALS['DATABASE']->sql_escape($AdminMail)."', ";
				$SQL .= "ip_at_reg		= '".$_SERVER['REMOTE_ADDR']."', ";
				$SQL .= "lang			= '".$LNG->getLanguage(). "', ";
				$SQL .= "authlevel		= ".AUTH_ADM.", ";
				$SQL .= "dpath 			= '".DEFAULT_THEME."', ";
				$SQL .= "rights			= '', ";
				$SQL .= "id_planet		= 1, ";
				$SQL .= "universe		= 1, ";
				$SQL .= "galaxy			= 1, ";
				$SQL .= "system			= 1, ";
				$SQL .= "planet			= 2, ";
				$SQL .= "register_time	= ".TIMESTAMP.";";
				$GLOBALS['DATABASE']->query($SQL);
						
				require_once('includes/functions/CreateOnePlanetRecord.php');
				
				$PlanetID		= CreateOnePlanetRecord(1, 1, 1, 1, 1, '', true, AUTH_ADM);
				$SESSION       	= new Session();
				$SESSION->DestroySession();
				$SESSION->CreateSession(1, $AdminUsername, $PlanetID, 1, 3, DEFAULT_THEME);
				$_SESSION['admin_login']	= $hashPassword;
				
				@unlink($enableInstallToolFile);
				$template->show('ins_step8.tpl');
			break;
		}
	break;
	default:
		$template->assign(array(
			'intro_text'	=> $LNG['intro_text'],
			'intro_welcome'	=> $LNG['intro_welcome'],
			'intro_install'	=> $LNG['intro_install'],
		));
		$template->show('ins_intro.tpl');
	break;
}
