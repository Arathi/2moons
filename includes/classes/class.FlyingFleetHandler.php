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
 * @version 1.7.3 (2013-05-19)
 * @info $Id: class.FlyingFleetHandler.php 2640 2013-03-23 19:23:26Z slaver7 $
 * @link http://2moons.cc/
 */

class FlyingFleetHandler
{	
	protected $token;
	
	public static $MissionsPattern	= array(
		1	=> 'MissionCaseAttack',
		2	=> 'MissionCaseACS',
		3	=> 'MissionCaseTransport',
		4	=> 'MissionCaseStay',
		5	=> 'MissionCaseStayAlly',
		6	=> 'MissionCaseSpy',
		7	=> 'MissionCaseColonisation',
		8	=> 'MissionCaseRecycling',
		9	=> 'MissionCaseDestruction',
		10	=> 'MissionCaseMIP',
		11	=> 'MissionCaseFoundDM',
		15	=> 'MissionCaseExpedition',
	);
		
	function setToken($token)
	{
		$this->token	= $token;
	}
	
	
	function run()
	{
				
		require_once('includes/classes/class.MissionFunctions.php');
		
		$fleetResult = $GLOBALS['DATABASE']->query("SELECT ".FLEETS.".* 
		FROM ".FLEETS_EVENT." 
		INNER JOIN ".FLEETS." ON fleetID = fleet_id
		WHERE `lock` = '".$this->token."';");
		while ($fleetRow = $GLOBALS['DATABASE']->fetch_array($fleetResult))
		{
			if(!isset(self::$MissionsPattern[$fleetRow['fleet_mission']])) {
				$GLOBALS['DATABASE']->query("DELETE FROM ".FLEETS." WHERE `fleet_id` = '".$fleetRow['fleet_id']."';");
				continue;
			}
			
			#if(!$this->IfFleetBusy($fleetRow['fleet_id'])) continue;
			
			$missionName	= self::$MissionsPattern[$fleetRow['fleet_mission']];
			
			require_once('includes/classes/missions/'.$missionName.'.php');
			$Mission	= new $missionName($fleetRow);
			
			switch($fleetRow['fleet_mess'])
			{
				case 0:
					$Mission->TargetEvent();
				break;
				case 1:
					$Mission->ReturnEvent();
				break;
				case 2:
					$Mission->EndStayEvent();
				break;
			}

			#$GLOBALS['DATABASE']->query("UPDATE ".FLEETS." SET `fleet_busy` = '0' WHERE `fleet_id` = '".$fleetRow['fleet_id']."';");
		}
		$GLOBALS['DATABASE']->free_result($fleetResult);
	}
	
	function IfFleetBusy($FleetID)
	{
		$FleetInfo	= $GLOBALS['DATABASE']->getFirstRow("SELECT fleet_busy FROM ".FLEETS." WHERE `fleet_id` = '".$FleetID."';");
		if($FleetInfo['fleet_busy'] == 1) {
			return false;
		} else {
			$GLOBALS['DATABASE']->query("UPDATE ".FLEETS." SET `fleet_busy` = '1' WHERE `fleet_id` = '".$FleetID."';");
			return true;
		}
	}
}