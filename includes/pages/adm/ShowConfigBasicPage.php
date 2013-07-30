<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kröpke
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
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2012 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.2 (2013-03-18)
 * @info $Id: ShowConfigBasicPage.php 2746 2013-05-18 11:38:36Z slaver7 $
 * @link http://2moons.cc/
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");

function ShowConfigBasicPage()
{
	global $LNG;
	$config = Config::get(Universe::getEmulated());

	if (!empty($_POST))
	{
		$config_before = array(
			'ttf_file'				=> $config->ttf_file,
			'game_name'				=> $config->game_name,
			'mail_active'			=> $config->mail_active,
			'mail_use'				=> $config->mail_use,
			'smail_path'			=> $config->smail_path,
			'smtp_host'				=> $config->smtp_host,
			'smtp_port'				=> $config->smtp_port,
			'smtp_user'				=> $config->smtp_user,
			'smtp_pass'				=> $config->smtp_pass,
			'smtp_ssl'				=> $config->smtp_ssl,
			'smtp_sendmail'			=> $config->smtp_sendmail,
			'ga_active'				=> $config->ga_active,
			'ga_key'				=> $config->ga_key,
			'capaktiv'				=> $config->capaktiv,
			'cappublic'				=> $config->cappublic,
			'capprivate'			=> $config->capprivate,
			'del_oldstuff'			=> $config->del_oldstuff,
			'del_user_manually'		=> $config->del_user_manually,
			'del_user_automatic'	=> $config->del_user_automatic,
			'del_user_sendmail'		=> $config->del_user_sendmail,
			'sendmail_inactive'		=> $config->sendmail_inactive,
			'timezone'				=> $config->timezone,
			'dst'					=> $config->dst,
			'close_reason'			=> $config->close_reason,
		);
		
		$capaktiv 				= isset($_POST['capaktiv']) && $_POST['capaktiv'] == 'on' ? 1 : 0;
		$ga_active 				= isset($_POST['ga_active']) && $_POST['ga_active'] == 'on' ? 1 : 0;
		$sendmail_inactive 		= isset($_POST['sendmail_inactive']) && $_POST['sendmail_inactive'] == 'on' ? 1 : 0;
		$mail_active 			= isset($_POST['mail_active']) && $_POST['mail_active'] == 'on' ? 1 : 0;
		
		$ttf_file				= HTTP::_GP('ttf_file', '');
		$close_reason			= HTTP::_GP('close_reason', '', true);
		$game_name				= HTTP::_GP('game_name', '', true);
		$capprivate				= HTTP::_GP('capprivate', '');
		$cappublic				= HTTP::_GP('cappublic', '');
		$ga_key					= HTTP::_GP('ga_key', '', true);
		$mail_use				= HTTP::_GP('mail_use', 0);
		$smail_path				= HTTP::_GP('smail_path', '');
		$smtp_host				= HTTP::_GP('smtp_host', '', true);
		$smtp_port				= HTTP::_GP('smtp_port', 0);
		$smtp_user				= HTTP::_GP('smtp_user', '', true);
		$smtp_sendmail			= HTTP::_GP('smtp_sendmail', '', true);
		$smtp_pass				= HTTP::_GP('smtp_pass', '', true);
		$smtp_ssl				= HTTP::_GP('smtp_ssl', '');
		$del_oldstuff			= HTTP::_GP('del_oldstuff', 0);
		$del_user_manually		= HTTP::_GP('del_user_manually', 0);
		$del_user_automatic		= HTTP::_GP('del_user_automatic', 0);
		$del_user_sendmail		= HTTP::_GP('del_user_sendmail', 0);
		$timezone				= HTTP::_GP('timezone', '');
		$dst					= HTTP::_GP('dst', 0);
		
		$config_after = array(
			'ttf_file'				=> $ttf_file,
			'game_name'				=> $game_name,
			'mail_active'			=> $mail_active,
			'mail_use'				=> $mail_use,
			'smail_path'			=> $smail_path,
			'smtp_host'				=> $smtp_host,
			'smtp_port'				=> $smtp_port,
			'smtp_user'				=> $smtp_user,
			'smtp_pass'				=> $smtp_pass,
			'smtp_ssl'				=> $smtp_ssl,
			'smtp_sendmail'			=> $smtp_sendmail,
			'ga_active'				=> $ga_active,
			'ga_key'				=> $ga_key,
			'capaktiv'				=> $capaktiv,
			'cappublic'				=> $cappublic,
			'capprivate'			=> $capprivate,
			'del_oldstuff'			=> $del_oldstuff,
			'del_user_manually'		=> $del_user_manually,
			'del_user_automatic'	=> $del_user_automatic,
			'del_user_sendmail'		=> $del_user_sendmail,
			'sendmail_inactive'		=> $sendmail_inactive,
			'timezone'				=> $timezone,
			'dst'					=> $dst,
			'close_reason'			=> $close_reason
		);

		foreach($config_after as $key => $value)
		{
			$config->$key	= $value;
		}
		$config->save();

		$LOG = new Log(3);
		$LOG->target = 0;
		$LOG->old = $config_before;
		$LOG->new = $config_after;
		$LOG->save();
	}
	
	$TimeZones		= get_timezone_selector();
	
	$template	= new template();
	
	$template->assign_vars(array(
		'del_oldstuff'					=> $config->del_oldstuff,
		'del_user_manually'				=> $config->del_user_manually,
		'del_user_automatic'			=> $config->del_user_automatic,
		'del_user_sendmail'				=> $config->del_user_sendmail,
		'sendmail_inactive'				=> $config->sendmail_inactive,
		'ttf_file'						=> $config->ttf_file,
		'game_name'						=> $config->game_name,
		'mail_active'					=> $config->mail_active,
		'mail_use'						=> $config->mail_use,
		'smail_path'					=> $config->smail_path,
		'smtp_host' 					=> $config->smtp_host,
		'smtp_port' 					=> $config->smtp_port,
		'smtp_user' 					=> $config->smtp_user,
		'smtp_pass' 					=> $config->smtp_pass,
		'smtp_sendmail' 				=> $config->smtp_sendmail,
		'smtp_ssl'						=> $config->smtp_ssl,
		'capprivate' 					=> $config->capprivate,
		'cappublic' 	   				=> $config->cappublic,
		'capaktiv'      	           	=> $config->capaktiv,
        'ga_active'               		=> $config->ga_active,
		'ga_key'           				=> $config->ga_key,
		'timezone'           			=> $config->timezone,
		'dst'           				=> $config->dst,
		'Selector'						=> array('timezone' => $TimeZones, 'mail' => $LNG['se_mail_sel'], 'encry' => array('' => $LNG['se_smtp_ssl_1'], 'ssl' => $LNG['se_smtp_ssl_2'], 'tls' => $LNG['se_smtp_ssl_3'])),
	));
	
	$template->show('ConfigBasicBody.tpl');
}
