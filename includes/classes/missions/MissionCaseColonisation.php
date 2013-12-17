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
 * @info $Id: MissionCaseColonisation.php 2640 2013-03-23 19:23:26Z slaver7 $
 * @link http://2moons.cc/
 */

class MissionCaseColonisation extends MissionFunctions
{
	function __construct($Fleet)
	{
		$this->_fleet	= $Fleet;
	}
	
	function TargetEvent()
	{	
		global $resource;
		$iPlanetCount 	= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".PLANETS." WHERE `id_owner` = '". $this->_fleet['fleet_owner'] ."' AND `planet_type` = '1' AND `destruyed` = '0';");
		$iGalaxyPlace 	= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".PLANETS." WHERE `id` = '".$this->_fleet['fleet_end_id']."';");
		$senderUser		= $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".USERS." WHERE `id` = '".$this->_fleet['fleet_owner']."';");
		$senderPlanet	= $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".PLANETS." WHERE `id` = '".$this->_fleet['fleet_start_id']."';");
		$senderUser['factor']	= getFactors($senderUser, 'basic', $this->_fleet['fleet_start_time']);
		$LNG			= $this->getLanguage($senderUser['lang']);
		
		$MaxPlanets		= PlayerUtil::maxPlanetCount($senderUser);
		
		if ($iGalaxyPlace != 0)
		{
			$TheMessage = sprintf($LNG['sys_colo_notfree'], GetTargetAdressLink($this->_fleet, ''));
			$this->setState(FLEET_RETURN);
		}
		elseif($iPlanetCount >= $MaxPlanets)
		{
			$TheMessage = sprintf($LNG['sys_colo_maxcolo'] , GetTargetAdressLink($this->_fleet, ''), $MaxPlanets);
			$this->setState(FLEET_RETURN);
		}
		elseif(PlayerUtil::allowPlanetPosition($this->_fleet['fleet_end_planet'],$senderUser) == false)
		{
			$TheMessage = sprintf($LNG['sys_colo_notech'] , GetTargetAdressLink($this->_fleet, ''), $MaxPlanets);
			$this->setState(FLEET_RETURN);
		}		
		else
		{
			require_once('includes/functions/CreateOnePlanetRecord.php');
			$NewOwnerPlanet = CreateOnePlanetRecord($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet'], $this->_fleet['fleet_universe'], $this->_fleet['fleet_owner'], $LNG['fcp_colony'], false, $senderUser['authlevel']);
			if($NewOwnerPlanet === false)
			{
				$TheMessage = sprintf($LNG['sys_colo_badpos'], GetTargetAdressLink($this->_fleet, ''));
					$this->setState(FLEET_RETURN);
			}
			else
			{
				$this->_fleet['fleet_end_id']	= $NewOwnerPlanet;
				$TheMessage = sprintf($LNG['sys_colo_allisok'], GetTargetAdressLink($this->_fleet, ''));
				$this->StoreGoodsToPlanet();
				if ($this->_fleet['fleet_amount'] == 1) {
					$this->KillFleet();
				} else {
					$CurrentFleet = explode(";", $this->_fleet['fleet_array']);
					$NewFleet     = '';
					foreach ($CurrentFleet as $Item => $Group)
					{
						if (empty($Group)) continue;

						$Class = explode (",", $Group);
						if ($Class[0] == 208 && $Class[1] > 1)
							$NewFleet  .= $Class[0].",".($Class[1] - 1).";";
						elseif ($Class[0] != 208 && $Class[1] > 0)
							$NewFleet  .= $Class[0].",".$Class[1].";";
					}
					$this->UpdateFleet('fleet_array', $NewFleet);
					$this->UpdateFleet('fleet_amount', ($this->_fleet['fleet_amount'] - 1));
					$this->UpdateFleet('fleet_resource_metal', 0);
					$this->UpdateFleet('fleet_resource_crystal', 0);
					$this->UpdateFleet('fleet_resource_deuterium', 0);
					$this->setState(FLEET_RETURN);
				}
			}
		}
		SendSimpleMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 4, $LNG['sys_colo_mess_from'], $LNG['sys_colo_mess_report'], $TheMessage);
		$this->SaveFleet();
	}
	
	function EndStayEvent()
	{
		return;
	}
	
	function ReturnEvent()
	{
		$this->RestoreFleet();
	}
}