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
 * @info $Id: GenerateReport.php 2747 2013-05-18 16:55:49Z slaver7 $
 * @link http://2moons.cc/
 */

function GenerateReport($combatResult, $reportInfo)
{
	$Destroy	= array('att' => 0, 'def' => 0);
	$DATA		= array();
	$DATA['mode']	= (int) $reportInfo['moonDestroy'];
	$DATA['time']	= $reportInfo['thisFleet']['fleet_start_time'];
	$DATA['start']	= array($reportInfo['thisFleet']['fleet_start_galaxy'], $reportInfo['thisFleet']['fleet_start_system'], $reportInfo['thisFleet']['fleet_start_planet'], $reportInfo['thisFleet']['fleet_start_type']);
	$DATA['koords']	= array($reportInfo['thisFleet']['fleet_end_galaxy'], $reportInfo['thisFleet']['fleet_end_system'], $reportInfo['thisFleet']['fleet_end_planet'], $reportInfo['thisFleet']['fleet_end_type']);
	$DATA['units']	= array($combatResult['unitLost']['attacker'], $combatResult['unitLost']['defender']);
	$DATA['debris']	= $reportInfo['debris'];
	$DATA['steal']	= $reportInfo['stealResource'];
	$DATA['result']	= $combatResult['won'];
	$DATA['moon']	= array(
		'moonName'				=> $reportInfo['moonName'],
		'moonChance'			=> (int) $reportInfo['moonChance'],
		'moonDestroyChance'		=> (int) $reportInfo['moonDestroyChance'],
		'moonDestroySuccess'	=> (int) $reportInfo['moonDestroySuccess'],
		'fleetDestroyChance'	=> (int) $reportInfo['fleetDestroyChance'],
		'fleetDestroySuccess'	=> (int) $reportInfo['fleetDestroySuccess']
	);
	
	if(isset($reportInfo['additionalInfo']))
	{
		$DATA['additionalInfo'] = $reportInfo['additionalInfo'];
	}
	else
	{
		$DATA['additionalInfo']	= "";
	}
	
	foreach($combatResult['rw'][0]['attackers'] as $player)
	{
		$DATA['players'][$player['player']['id']]	= array(
			'name'		=> $player['player']['username'],
			'koords'	=> array($player['fleetDetail']['fleet_start_galaxy'], $player['fleetDetail']['fleet_start_system'], $player['fleetDetail']['fleet_start_planet'], $player['fleetDetail']['fleet_start_type']),
			'tech'		=> array($player['techs'][0] * 100, $player['techs'][1] * 100, $player['techs'][2] * 100),
		);
	}
	foreach($combatResult['rw'][0]['defenders'] as $player)
	{
		$DATA['players'][$player['player']['id']]	= array(
			'name'		=> $player['player']['username'],
			'koords'	=> array($player['fleetDetail']['fleet_start_galaxy'], $player['fleetDetail']['fleet_start_system'], $player['fleetDetail']['fleet_start_planet'], $player['fleetDetail']['fleet_start_type']),
			'tech'		=> array($player['techs'][0] * 100, $player['techs'][1] * 100, $player['techs'][2] * 100),
		);
	}
	
	foreach($combatResult['rw'] as $Round => $RoundInfo)
	{
		foreach($RoundInfo['attackers'] as $FleetID => $player)
		{	
			$playerData	= array('userID' => $player['player']['id'], 'ships' => array());
			
			if(array_sum($player['unit']) == 0) {
				$DATA['rounds'][$Round]['attacker'][] = $playerData;
				$Destroy['att']++;
				continue;
			}
			
			foreach($player['unit'] as $ShipID => $Amount)
			{
				if ($Amount <= 0)
					continue;
					
				$ShipInfo	= $RoundInfo['infoA'][$FleetID][$ShipID];
				$playerData['ships'][$ShipID]	= array(
					$Amount, $ShipInfo['att'], $ShipInfo['def'], $ShipInfo['shield']
				);
			}
			
			$DATA['rounds'][$Round]['attacker'][] = $playerData;
		}
		
		foreach($RoundInfo['defenders'] as $FleetID => $player)
		{	
			$playerData	= array('userID' => $player['player']['id'], 'ships' => array());
			if(array_sum($player['unit']) == 0) {
				$DATA['rounds'][$Round]['defender'][] = $playerData;
				$Destroy['def']++;
				continue;
			}
				
			foreach($player['unit'] as $ShipID => $Amount)
			{
				if ($Amount <= 0) {
					$Destroy['def']++;
					continue;
				}
					
				$ShipInfo	= $RoundInfo['infoD'][$FleetID][$ShipID];
				$playerData['ships'][$ShipID]	= array(
					$Amount, $ShipInfo['att'], $ShipInfo['def'], $ShipInfo['shield']
				);
			}
			$DATA['rounds'][$Round]['defender'][] = $playerData;
		}
		
		if ($Round >= MAX_ATTACK_ROUNDS || $Destroy['att'] == count($RoundInfo['attackers']) || $Destroy['def'] == count($RoundInfo['defenders']))
			break;
		
		if(isset($RoundInfo['attack'], $RoundInfo['attackShield'], $RoundInfo['defense'], $RoundInfo['defShield']))
			$DATA['rounds'][$Round]['info']	= array($RoundInfo['attack'], $RoundInfo['attackShield'], $RoundInfo['defense'], $RoundInfo['defShield']);
		else
			$DATA['rounds'][$Round]['info']	= array(NULL, NULL, NULL, NULL);
	}
	return $DATA;
}
	