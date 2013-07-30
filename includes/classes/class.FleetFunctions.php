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
 * @info $Id: class.FleetFunctions.php 2753 2013-05-25 15:12:17Z slaver7 $
 * @link http://2moons.cc/
 */

class FleetFunctions 
{
	static $allowedSpeed	= array(10 => 100, 9 => 90, 8 => 80, 7 => 70, 6 => 60, 5 => 50, 4 => 40, 3 => 30, 2 => 20, 1 => 10);
	
	private static function GetShipConsumption($Ship, $Player)
	{
		global $pricelist;

		return (($Player['impulse_motor_tech'] >= 5 && $Ship == 202) || ($Player['hyperspace_motor_tech'] >= 8 && $Ship == 211)) ? $pricelist[$Ship]['consumption2'] : $pricelist[$Ship]['consumption'];
	}

	private static function OnlyShipByID($Ships, $ShipID)
	{
		return isset($Ships[$ShipID]) && count($Ships) === 1;
	}

	private static function GetShipSpeed($Ship, $Player)
	{
		global $pricelist;
		
		$techSpeed	= $pricelist[$Ship]['tech'];
		
		if($techSpeed == 4) {
			$techSpeed = $Player['impulse_motor_tech'] >= 5 ? 2 : 1;
		}
		if($techSpeed == 5) {
			$techSpeed = $Player['hyperspace_motor_tech'] >= 8 ? 3 : 2;
		}
			
		
		switch($techSpeed)
		{
			case 1:
				$speed	= $pricelist[$Ship]['speed'] * (1 + (0.1 * $Player['combustion_tech']));
			break;
			case 2:
				$speed	= $pricelist[$Ship]['speed'] * (1 + (0.2 * $Player['impulse_motor_tech']));
			break;
			case 3:
				$speed	= $pricelist[$Ship]['speed'] * (1 + (0.3 * $Player['hyperspace_motor_tech']));
			break;
			default:
				$speed	= 0;
			break;
		}

		return $speed;
	}
	
	public static function getExpeditionLimit($USER)
	{
		return floor(sqrt($USER[$GLOBALS['resource'][124]]));
	}
	
	public static function getDMMissionLimit($USER)
	{
		return Config::get($USER['universe'])->max_dm_missions;
	}
	
	public static function getMissileRange($Level)
	{
		return max(($Level * 5) - 1, 0);
	}
	
	public static function CheckUserSpeed($speed)
	{
		return isset(self::$allowedSpeed[$speed]);
	}

	public static function GetTargetDistance($start, $target)
	{
		if ($start[0] != $target[0])
			return abs($start[0] - $target[0]) * 20000;
		
		if ($start[1] != $target[1])
			return abs($start[1] - $target[1]) * 95 + 2700;
		
		if ($start[2] != $target[2])
			return abs($start[2] - $target[2]) * 5 + 1000;

		return 5;
	}

	public static function GetMissionDuration($SpeedFactor, $MaxFleetSpeed, $Distance, $GameSpeed, $USER)
	{
		$SpeedFactor	= (3500 / ($SpeedFactor * 0.1));
		$SpeedFactor	*= pow($Distance * 10 / $MaxFleetSpeed, 0.5);
		$SpeedFactor	+= 10;
		$SpeedFactor	/= $GameSpeed;
		
		if(isset($USER['factor']['FlyTime']))
		{
			$SpeedFactor	*= max(0, 1 + $USER['factor']['FlyTime']);
		}
		
		return max($SpeedFactor, MIN_FLEET_TIME);
	}
 
	public static function GetMIPDuration($startSystem, $targetSystem)
	{
		$Distance = abs($startSystem - $targetSystem);
		$Duration = max(round((30 + 60 * $Distance) / self::GetGameSpeedFactor()), MIN_FLEET_TIME);
		
		return $Duration;
	}

	public static function GetGameSpeedFactor()
	{
		return Config::get()->fleet_speed / 2500;
	}
	
	public static function GetMaxFleetSlots($USER)
	{
		global $resource;
		return 1 + $USER[$resource[108]] + $USER['factor']['FleetSlots'];
	}

	public static function GetFleetRoom($Fleet)
	{
		global $pricelist;
		$FleetRoom 				= 0;
		foreach ($Fleet as $ShipID => $amount)
		{
			$FleetRoom		   += $pricelist[$ShipID]['capacity'] * $amount;
		}
		return $FleetRoom;
	}
	
	public static function GetFleetMaxSpeed ($Fleets, $Player)
	{
		$FleetArray = (!is_array($Fleets)) ? array($Fleets => 1) : $Fleets;
		$speedalls 	= array();
		
		foreach ($FleetArray as $Ship => $Count) {
			$speedalls[$Ship] = self::GetShipSpeed($Ship, $Player);
		}
		
		return min($speedalls);
	}

	public static function GetFleetConsumption($FleetArray, $MissionDuration, $MissionDistance, $Player, $GameSpeed)
	{
		$consumption = 0;

		foreach ($FleetArray as $Ship => $Count)
		{
			$ShipSpeed          = self::GetShipSpeed($Ship, $Player);
			$ShipConsumption    = self::GetShipConsumption($Ship, $Player);
			
			$spd                = 35000 / (round($MissionDuration, 0) * $GameSpeed - 10) * sqrt($MissionDistance * 10 / $ShipSpeed);
			$basicConsumption   = $ShipConsumption * $Count;
			$consumption        += $basicConsumption * $MissionDistance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
		}
		return (round($consumption) + 1);
	}

	public static function GetFleetMissions($USER, $MisInfo, $Planet)
	{
		global $resource;
		$Missions	= self::GetAvailableMissions($USER, $MisInfo, $Planet);
		$stayBlock	= array();

		$haltSpeed	= Config::get($USER['universe'])->halt_speed;

		if (in_array(15, $Missions)) {
			for($i = 1;$i <= $USER[$resource[124]];$i++)
			{
				$stayBlock[$i]	= round($i / $haltSpeed, 2);
			}
		}
		elseif(in_array(11, $Missions)) 
		{
			$stayBlock = array(1 => 1);
		}
		elseif(in_array(5, $Missions)) 
		{
			$stayBlock = array(1 => 1, 2 => 2, 4 => 4, 8 => 8, 12 => 12, 16 => 16, 32 => 32);
		}
		
		return array('MissionSelector' => $Missions, 'StayBlock' => $stayBlock);
	}

	/*
	 *
	 * Unserialize an Fleetstring to an array
	 *
	 * @param string
	 *
	 * @return array
	 *
	 */

	public static function unserialize($fleetAmount)
	{
		$fleetTyps		= explode(';', $fleetAmount);

		$fleetAmount	= array();

		foreach ($fleetTyps as $fleetTyp)
		{
			$temp = explode(',', $fleetTyp);

			if (empty($temp[0])) continue;

			if (!isset($fleetAmount[$temp[0]]))
			{
				$fleetAmount[$temp[0]] = 0;
			}

			$fleetAmount[$temp[0]] += $temp[1];
		}

		return $fleetAmount;
	}

	public static function GetACSDuration($acsId)
	{
		if(empty($acsId))
		{
			return 0;
		}

		$sql			= 'SELECT ankunft FROM %%AKS%% WHERE id = :acsId;';
		$acsEndTime 	= Database::get()->selectSingle($sql, array(
			':acsId'	=> $acsId
		), 'ankunft');
		
		return empty($acsEndTime) ? $acsEndTime - TIMESTAMP : 0;
	}
	
	public static function setACSTime($timeDifference, $acsId)
	{
		if(empty($acsId))
		{
			throw new InvalidArgumentException('Missing acsId on '.__CLASS__.'::'.__METHOD__);
		}

		$db		= Database::get();

		$sql	= 'UPDATE %%AKS%% SET ankunft = ankunft + :time WHERE id = :acsId;';
		$db->update($sql, array(
			':time'		=> $timeDifference,
			':acsId'	=> $acsId,
		));

		$sql	= 'UPDATE %%FLEETS%%, %%FLEETS_EVENT%% SET
		fleet_start_time = fleet_start_time + :time,
		fleet_end_stay   = fleet_end_stay + :time,
		fleet_end_time   = fleet_end_time + :time,
		time             = time + :time
		WHERE fleet_group = :acsId AND fleet_id = fleetID;';

		$db->update($sql, array(
			':time'		=> $timeDifference,
			':acsId'	=> $acsId,
		));

        return true;
	}

	public static function GetCurrentFleets($userId, $fleetMission = 10, $thisMission = false)
	{
		if($thisMission)
		{
			$sql = 'SELECT COUNT(*) as state
			FROM %%FLEETS%%
			WHERE fleet_owner = :userId
			AND fleet_mission = :fleetMission;';
		}
		else
		{
			$sql = 'SELECT COUNT(*) as state
			FROM %%FLEETS%%
			WHERE fleet_owner = :userId
			AND fleet_mission != :fleetMission;';
		}

		$ActualFleets = Database::get()->selectSingle($sql, array(
			':userId'		=> $userId,
			':fleetMission'	=> $fleetMission,
		));
		return $ActualFleets['state'];
	}	
	
	public static function SendFleetBack($USER, $FleetID)
	{
		$db				= Database::get();

		$sql			= 'SELECT start_time, fleet_mission, fleet_group, fleet_owner, fleet_mess FROM %%FLEETS%% WHERE fleet_id = :fleetId;';
		$fleetResult	= $db->selectSingle($sql, array(
			':fleetId'	=> $FleetID,
		));

		if ($fleetResult['fleet_owner'] != $USER['id'] || $fleetResult['fleet_mess'] == 1)
		{
			return false;
		}

		$sqlWhere	= 'fleet_id';

		if($fleetResult['fleet_mission'] == 1 && $fleetResult['fleet_group'] != 0)
		{
			$sql		= 'SELECT COUNT(*) as state FROM %%USERS_ACS%% WHERE acsID = :acsId;';
			$isInGroup	= $db->selectSingle($sql, array(
				':acsId'	=> $fleetResult['fleet_group'],
			), 'state');

			if($isInGroup)
			{
				$sql = 'DELETE %%AKS%%, %%USERS_ACS%%
				FROM %%AKS%%
				LEFT JOIN %%USERS_ACS%% ON acsID = %%AKS%%.id
				WHERE %%AKS%%.id = :acsId;';

				$db->delete($sql, array(
					':acsId'	=> $fleetResult['fleet_group']
			  	));
				
				$FleetID	= $fleetResult['fleet_group'];
				$sqlWhere	= 'fleet_group';
			}
		}
		
		$fleetEndTime	= (TIMESTAMP - $fleetResult['start_time']) + TIMESTAMP;
		
		$sql	= 'UPDATE %%FLEETS%%, %%FLEETS_EVENT%% SET
		fleet_group			= :fleetGroup,
		fleet_end_stay		= :endStayTime,
		fleet_end_time		= :endTime,
		fleet_mess			= :fleetState,
		hasCanceled			= :hasCanceled,
		time				= :endTime
		WHERE '.$sqlWhere.' = :id AND fleet_id = fleetID;';

		$db->update($sql, array(
			':id'			=> $FleetID,
			':endStayTime'	=> TIMESTAMP,
			':endTime'		=> $fleetEndTime,
			':fleetGroup'	=> 0,
			':hasCanceled'	=> 1,
			':fleetState'	=> FLEET_RETURN
		));

		$sql	= 'UPDATE %%LOG_FLEETS%% SET
		fleet_end_stay	= :endStayTime,
		fleet_end_time	= :endTime,
		fleet_mess		= :fleetState,
		fleet_state		= 2
		WHERE '.$sqlWhere.' = :id;';

		$db->update($sql, array(
			':id'			=> $FleetID,
			':endStayTime'	=> TIMESTAMP,
			':endTime'		=> $fleetEndTime,
			':fleetState'	=> FLEET_RETURN
		));

		return true;
	}
	
	public static function GetFleetShipInfo($FleetArray, $Player)
	{
		$FleetInfo	= array();
		foreach ($FleetArray as $ShipID => $Amount) {
			$FleetInfo[$ShipID]	= array('consumption' => self::GetShipConsumption($ShipID, $Player), 'speed' => self::GetFleetMaxSpeed($ShipID, $Player), 'amount' => floattostring($Amount));
		}
		return $FleetInfo;
	}
	
	public static function GotoFleetPage($Code = 0)
	{	
		global $LNG;
		if(Config::get()->debug == 1)
		{
			$temp = debug_backtrace();
			echo str_replace($_SERVER["DOCUMENT_ROOT"],'.',$temp[0]['file'])." on ".$temp[0]['line']. " | Code: ".$Code." | Error: ".(isset($LNG['fl_send_error'][$Code]) ? $LNG['fl_send_error'][$Code] : '');
			exit;
		}
		
		HTTP::redirectTo('game.php?page=fleetTable&code='.$Code);
	}
	
	public static function GetAvailableMissions($USER, $MissionInfo, $GetInfoPlanet)
	{	
		$YourPlanet				= (!empty($GetInfoPlanet['id_owner']) && $GetInfoPlanet['id_owner'] == $USER['id']) ? true : false;
		$UsedPlanet				= (!empty($GetInfoPlanet['id_owner'])) ? true : false;
		$availableMissions		= array();
		
		if ($MissionInfo['planet'] == (Config::get($USER['universe'])->max_planets + 1) && isModulAvalible(MODULE_MISSION_EXPEDITION))
			$availableMissions[]	= 15;	
		elseif ($MissionInfo['planettype'] == 2) {
			if ((isset($MissionInfo['Ship'][209]) || isset($MissionInfo['Ship'][219])) && isModulAvalible(MODULE_MISSION_RECYCLE) && !($GetInfoPlanet['der_metal'] == 0 && $GetInfoPlanet['der_crystal'] == 0))
				$availableMissions[]	= 8;
		} else {
			if (!$UsedPlanet) {
				if (isset($MissionInfo['Ship'][208]) && $MissionInfo['planettype'] == 1 && isModulAvalible(MODULE_MISSION_COLONY))
					$availableMissions[]	= 7;
			} else {
				if(isModulAvalible(MODULE_MISSION_TRANSPORT))
					$availableMissions[]	= 3;
					
				if (!$YourPlanet && self::OnlyShipByID($MissionInfo['Ship'], 210) && isModulAvalible(MODULE_MISSION_SPY))
					$availableMissions[]	= 6;

				if (!$YourPlanet) {
					if(isModulAvalible(MODULE_MISSION_ATTACK))
						$availableMissions[]	= 1;
					if(isModulAvalible(MODULE_MISSION_HOLD))
						$availableMissions[]	= 5;}
						
				elseif(isModulAvalible(MODULE_MISSION_STATION)) {
					$availableMissions[]	= 4;}
					
				if (!empty($MissionInfo['IsAKS']) && !$YourPlanet && isModulAvalible(MODULE_MISSION_ATTACK) && isModulAvalible(MODULE_MISSION_ACS))
					$availableMissions[]	= 2;

				if (!$YourPlanet && $MissionInfo['planettype'] == 3 && isset($MissionInfo['Ship'][214]) && isModulAvalible(MODULE_MISSION_DESTROY))
					$availableMissions[]	= 9;

				if ($YourPlanet && $MissionInfo['planettype'] == 3 && self::OnlyShipByID($MissionInfo['Ship'], 220) && isModulAvalible(MODULE_MISSION_DARKMATTER))
					$availableMissions[]	= 11;
			}
		}
		
		return $availableMissions;
	}
	
	public static function CheckBash($Target)
	{
		global $USER;

		if(!BASH_ON)
		{
			return false;
		}

		$sql	= 'SELECT COUNT(*) as state
		FROM %%LOG_FLEETS%%
		WHERE fleet_owner = :fleetOwner,
		AND fleet_end_id = :fleetEndId,
		AND fleet_state != :fleetState,
		AND fleet_start_time > :fleetStartTime,
		AND fleet_mission IN (1,2,9);';

		$Count	= Database::get()->selectSingle($sql, array(
			':fleetOwner'		=> $USER['id'],
			':fleetEndId'		=> $Target,
			':fleetState'		=> 2,
			':fleetStartTime'	=> (TIMESTAMP - BASH_TIME),
		));

		return $Count >= BASH_COUNT;
	}
	
	public static function sendFleet($fleetArray, $fleetMission, $fleetStartOwner, $fleetStartPlanetID,
		$fleetStartPlanetGalaxy, $fleetStartPlanetSystem, $fleetStartPlanetPlanet, $fleetStartPlanetType,
		$fleetTargetOwner, $fleetTargetPlanetID, $fleetTargetPlanetGalaxy, $fleetTargetPlanetSystem,
		$fleetTargetPlanetPlanet, $fleetTargetPlanetType, $fleetResource, $fleetStartTime, $fleetStayTime,
		$fleetEndTime, $fleetGroup = 0, $missileTarget = 0)
	{
		global $resource;
		$fleetShipCount	= array_sum($fleetArray);
		$fleetData		= array();

		$db				= Database::get();

		$params			= array(':planetId'	=> $fleetStartPlanetID);

		$planetQuery	= "";
		foreach($fleetArray as $ShipID => $ShipCount) {
			$fleetData[]	= $ShipID.','.floattostring($ShipCount);
			$planetQuery[]	= $resource[$ShipID]." = ".$resource[$ShipID]." - :".$resource[$ShipID];

			$params[':'.$resource[$ShipID]]	= floattostring($ShipCount);
		}

		$sql	= 'UPDATE %%PLANETS%% SET '.implode(', ', $planetQuery).' WHERE id = :planetId;';

		$db->update($sql, $params);

		$sql	= 'INSERT INTO %%FLEETS%% SET
		fleet_owner					= :fleetStartOwner,
		fleet_target_owner			= :fleetTargetOwner,
		fleet_mission				= :fleetMission,
		fleet_amount				= :fleetShipCount,
		fleet_array					= :fleetData,
		fleet_universe				= :universe,
		fleet_start_time			= :fleetStartTime,
		fleet_end_stay				= :fleetStayTime,
		fleet_end_time				= :fleetEndTime,
		fleet_start_id				= :fleetStartPlanetID,
		fleet_start_galaxy			= :fleetStartPlanetGalaxy,
		fleet_start_system			= :fleetStartPlanetSystem,
		fleet_start_planet			= :fleetStartPlanetPlanet,
		fleet_start_type			= :fleetStartPlanetType,
		fleet_end_id				= :fleetTargetPlanetID,
		fleet_end_galaxy			= :fleetTargetPlanetGalaxy,
		fleet_end_system			= :fleetTargetPlanetSystem,
		fleet_end_planet			= :fleetTargetPlanetPlanet,
		fleet_end_type				= :fleetTargetPlanetType,
		fleet_resource_metal		= :fleetResource901,
		fleet_resource_crystal		= :fleetResource902,
		fleet_resource_deuterium	= :fleetResource903,
		fleet_group					= :fleetGroup,
		fleet_target_obj			= :missileTarget,
		start_time					= :timestamp;';

		$db->insert($sql, array(
			':fleetStartOwner'			=> $fleetStartOwner,
			':fleetTargetOwner'			=> $fleetTargetOwner,
			':fleetMission'				=> $fleetMission,
			':fleetShipCount'			=> $fleetShipCount,
			':fleetData'				=> implode(';', $fleetData),
			':fleetStartTime'			=> $fleetStartTime,
			':fleetStayTime'			=> $fleetStayTime,
			':fleetEndTime'				=> $fleetEndTime,
			':fleetStartPlanetID'		=> $fleetStartPlanetID,
			':fleetStartPlanetGalaxy'	=> $fleetStartPlanetGalaxy,
			':fleetStartPlanetSystem'	=> $fleetStartPlanetSystem,
			':fleetStartPlanetPlanet'	=> $fleetStartPlanetPlanet,
			':fleetStartPlanetType'		=> $fleetStartPlanetType,
			':fleetTargetPlanetID'		=> $fleetTargetPlanetID,
			':fleetTargetPlanetGalaxy'	=> $fleetTargetPlanetGalaxy,
			':fleetTargetPlanetSystem'	=> $fleetTargetPlanetSystem,
			':fleetTargetPlanetPlanet'	=> $fleetTargetPlanetPlanet,
			':fleetTargetPlanetType'	=> $fleetTargetPlanetType,
			':fleetResource901'			=> $fleetResource[901],
			':fleetResource902'			=> $fleetResource[902],
			':fleetResource903'			=> $fleetResource[903],
			':fleetGroup'				=> $fleetGroup,
			':missileTarget'			=> $missileTarget,
			':timestamp'				=> TIMESTAMP,
			':universe'	   				=> Universe::current(),
		));

		$fleetId	= $db->lastInsertId();

		$sql	= 'INSERT INTO %%FLEETS_EVENT%% SET fleetID	= :fleetId, `time` = :endTime;';
		$db->insert($sql, array(
			':fleetId'	=> $fleetId,
			':endTime'	=> $fleetStartTime
		));

		$sql	= 'INSERT INTO %%LOG_FLEETS%% SET
		fleet_id					= :fleetId,
		fleet_owner					= :fleetStartOwner,
		fleet_target_owner			= :fleetTargetOwner,
		fleet_mission				= :fleetMission,
		fleet_amount				= :fleetShipCount,
		fleet_array					= :fleetData,
		fleet_universe				= :universe,
		fleet_start_time			= :fleetStartTime,
		fleet_end_stay				= :fleetStayTime,
		fleet_end_time				= :fleetEndTime,
		fleet_start_id				= :fleetStartPlanetID,
		fleet_start_galaxy			= :fleetStartPlanetGalaxy,
		fleet_start_system			= :fleetStartPlanetSystem,
		fleet_start_planet			= :fleetStartPlanetPlanet,
		fleet_start_type			= :fleetStartPlanetType,
		fleet_end_id				= :fleetTargetPlanetID,
		fleet_end_galaxy			= :fleetTargetPlanetGalaxy,
		fleet_end_system			= :fleetTargetPlanetSystem,
		fleet_end_planet			= :fleetTargetPlanetPlanet,
		fleet_end_type				= :fleetTargetPlanetType,
		fleet_resource_metal		= :fleetResource901,
		fleet_resource_crystal		= :fleetResource902,
		fleet_resource_deuterium	= :fleetResource903,
		fleet_group					= :fleetGroup,
		fleet_target_obj			= :missileTarget,
		start_time					= :timestamp;';

		$db->insert($sql, array(
			':fleetId'					=> $fleetId,
			':fleetStartOwner'			=> $fleetStartOwner,
			':fleetTargetOwner'			=> $fleetTargetOwner,
			':fleetMission'				=> $fleetMission,
			':fleetShipCount'			=> $fleetShipCount,
			':fleetData'				=> implode(';', $fleetData),
			':fleetStartTime'			=> $fleetStartTime,
			':fleetStayTime'			=> $fleetStayTime,
			':fleetEndTime'				=> $fleetEndTime,
			':fleetStartPlanetID'		=> $fleetStartPlanetID,
			':fleetStartPlanetGalaxy'	=> $fleetStartPlanetGalaxy,
			':fleetStartPlanetSystem'	=> $fleetStartPlanetSystem,
			':fleetStartPlanetPlanet'	=> $fleetStartPlanetPlanet,
			':fleetStartPlanetType'		=> $fleetStartPlanetType,
			':fleetTargetPlanetID'		=> $fleetTargetPlanetID,
			':fleetTargetPlanetGalaxy'	=> $fleetTargetPlanetGalaxy,
			':fleetTargetPlanetSystem'	=> $fleetTargetPlanetSystem,
			':fleetTargetPlanetPlanet'	=> $fleetTargetPlanetPlanet,
			':fleetTargetPlanetType'	=> $fleetTargetPlanetType,
			':fleetResource901'			=> $fleetResource[901],
			':fleetResource902'			=> $fleetResource[902],
			':fleetResource903'			=> $fleetResource[903],
			':fleetGroup'				=> $fleetGroup,
			':missileTarget'			=> $missileTarget,
			':timestamp'				=> TIMESTAMP,
			':universe'	   				=> Universe::current(),
		));
	}
}