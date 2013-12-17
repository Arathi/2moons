<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan
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
 * @author Jan <info@2moons.cc>
 * @copyright 2006 Perberos <ugamela@perberos.com.ar> (UGamela)
 * @copyright 2008 Chlorel (XNova)
 * @copyright 2012 Jan <info@2moons.cc> (2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0 (2012-11-31)
 * @info $Id: class.Session.php 2618 2013-03-11 19:36:08Z slaver7 $
 * @link http://2moons.cc/
 */

class Session
{
	private static $obj;
	private static $isInit = false;
	
	static function init() {
		ini_set('session.use_cookies', '1');
		ini_set('session.use_only_cookies', '1');
		ini_set('session.use_trans_sid', 0);
		ini_set('session.auto_start', '0');
		ini_set('session.serialize_handler', 'php');  
		ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
		ini_set('session.gc_probability', '1');
		ini_set('session.gc_divisor', '1000');
		ini_set('session.bug_compat_warn', '0');
		ini_set('session.bug_compat_42', '0');
		ini_set('session.cookie_httponly', true);
		
		$HTTP_ROOT = MODE === 'INSTALL' ? dirname(HTTP_ROOT) : HTTP_ROOT;
		
		session_set_cookie_params(SESSION_LIFETIME, $HTTP_ROOT, NULL, HTTPS, true);
		session_cache_limiter('nocache');
		session_name('2Moons');
		self::$isInit	= true;
	}
	
	function __construct()
	{
		if(self::$isInit === false)
		{
			self::init();
		}
	}
	
	static function redirectCode($Code)
	{
		HTTP::redirectTo('index.php?code='.$Code);
	}
	
	static function create($userID, $planetID = 0)
	{
		self::$obj	= new self;
		
		if(!isset($_SESSION)) {
			session_start();
		}
		
		$GLOBALS['DATABASE']->query("REPLACE INTO ".SESSION." SET
		sessionID = '".session_id()."',
		userID = ".$userID.",
		lastonline = ".TIMESTAMP.",
		userIP = '".$_SERVER['REMOTE_ADDR']."';");
		
		$_SESSION['id']			= $userID;
		$_SESSION['agent']		= $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['planet']		= $planetID;
		
		return self::$obj;
	}
	
	function CreateSession($userID, $userName, $planetID = 0, $uni, $authlevel, $dpath)
	{
		self::create($userID, $planetID = 0);
		$_SESSIOn['authlevel']	= $authlevel;
		$_SESSIOn['uni']		= $uni;
		$_SESSIOn['dpath']		= $dpath;
	}
	
	function IsUserLogin()
	{
		return $this->isActiveSession();
	}
		
	function isActiveSession()
	{
		if(!isset($_SESSION)) {
			session_start();
		}
		
		return !empty($_SESSION['id']);
	}
	
	function GetSessionFromDB()
	{
		return $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".SESSION." WHERE sessionID = '".session_id()."' AND userID = ".$_SESSION['id'].";");
	}
	
	function GetPath()
	{
		return basename($_SERVER['SCRIPT_NAME']).(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
	}
	
	function updateSession()
	{		
		$_SESSION['last']	= $this->GetSessionFromDB();
		if(empty($_SESSION['last']) || !$this->CompareIPs($_SESSION['last']['userIP']))
		{
			$this->DestroySession();
			self::redirectCode(2);
		}
		
		$SelectPlanet  		= HTTP::_GP('cp',0);
		
		if(!empty($SelectPlanet))
		{
			$IsPlanetMine 	=	$GLOBALS['DATABASE']->getFirstRow("SELECT id FROM ".PLANETS." WHERE id = '".$SelectPlanet."' AND id_owner = '".$_SESSION['id']."';");
		}	
		$_SESSION['path']		= $this->GetPath();
		$_SESSION['planet']		= !empty($IsPlanetMine['id']) ? $IsPlanetMine['id'] : $_SESSION['planet'];

		$GLOBALS['DATABASE']->query("UPDATE ".USERS." u, ".SESSION." s SET 
		u.onlinetime = ".TIMESTAMP.",
		s.lastonline = ".TIMESTAMP.",
		u.user_lastip = '".$_SERVER['REMOTE_ADDR']."',
		s.userIP = '".$_SERVER['REMOTE_ADDR']."'
		WHERE
		sessionID = '".session_id()."' AND u.id = s.userID;");
		return true;
	}
	
	function CompareIPs($IP)
	{
		if (strpos($_SERVER['REMOTE_ADDR'], ':') !== false && strpos($IP, ':') !== false)
		{
			$s_ip = $this->short_ipv6($IP, COMPARE_IP_BLOCKS);
			$u_ip = $this->short_ipv6($_SERVER['REMOTE_ADDR'], COMPARE_IP_BLOCKS);
		}
		else
		{
			$s_ip = implode('.', array_slice(explode('.', $IP), 0, COMPARE_IP_BLOCKS));
			$u_ip = implode('.', array_slice(explode('.', $_SERVER['REMOTE_ADDR']), 0, COMPARE_IP_BLOCKS));
		}
		
		return ($s_ip == $u_ip);
	}

	function short_ipv6($ip, $length)
	{
		if ($length < 1)
		{
			return '';
		}

		$blocks = substr_count($ip, ':') + 1;
		if ($blocks < 9)
		{
			$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
		}
		if ($ip[0] == ':')
		{
			$ip = '0000' . $ip;
		}
		if ($length < 4)
		{
			$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
		}

		return $ip;
	}
	
	function DestroySession()
	{
		$GLOBALS['DATABASE']->query("DELETE FROM ".SESSION." WHERE sessionID = '".session_id()."';"); 
		@session_destroy();
	}
	
	
}