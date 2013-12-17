<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kr�pke
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
 * @copyright 2012 Jan Kr�pke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.3 (2013-05-19)
 * @info $Id: TeamspeakBuildCache.class.php 2641 2013-03-24 13:43:52Z slaver7 $
 * @link http://2moons.cc/
 */


class TeamspeakBuildCache
{
	function buildCache()
	{		
		$teamspeakData	= array();
		
		switch(Config::get('ts_version'))
		{
			case 2:
				require 'includes/libs/teamspeak/cyts/cyts.class.php';
				$ts = new cyts();
				
				if($ts->connect(Config::get('ts_server'), Config::get('ts_tcpport'), Config::get('ts_udpport'), Config::get('ts_timeout'))) {
					$serverInfo	= $ts->info_serverInfo();
					$teamspeakData	= array(
						'password'	=> '', // NO Server-API avalible.
						'current'	=> $serverInfo["server_currentusers"],
						'maxuser'	=> $serverInfo["server_maxusers"],
					);
					$ts->disconnect();
				} else {
					throw new Exception('Teamspeak-Error: '.$ts->debug());
				}
			break;
			case 3:
				require 'includes/libs/teamspeak/class.teamspeak3.php';
				$tsAdmin 	= new ts3admin(Config::get('ts_server'), Config::get('ts_udpport'), Config::get('ts_timeout'));
				$connected	= $tsAdmin->connect();				
				if(!$connected['success'])
				{
					throw new Exception('Teamspeak-Error: '.implode("<br>\r\n", $connected['errors']));
				}
				
				$selected	= $tsAdmin->selectServer(Config::get('ts_tcpport'), 'port', true);
				if(!$selected['success'])
				{
					throw new Exception('Teamspeak-Error: '.implode("<br>\r\n", $selected['errors']));
				}
					
				$loggedIn	= $tsAdmin->login(Config::get('ts_login'), Config::get('ts_password'));
				if(!$loggedIn['success'])
				{
					throw new Exception('Teamspeak-Error: '.implode("<br>\r\n", $loggedIn['errors']));
				}
				
				$serverInfo = $tsAdmin->serverInfo();
				if(!$serverInfo['success'])
				{
					throw new Exception('Teamspeak-Error: '.implode("<br>\r\n", $serverInfo['errors']));
				}
				
				$teamspeakData	= array(
					'password'	=> $serverInfo['data']['virtualserver_password'],
					'current'	=> $serverInfo['data']['virtualserver_clientsonline'] - 1, 
					'maxuser'	=> $serverInfo['data']['virtualserver_maxclients'],
				);
				
				$tsAdmin->logout();
			break;
		}
		
		return $teamspeakData;
	}
}