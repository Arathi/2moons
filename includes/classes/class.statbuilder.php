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
 * @info $Id: class.statbuilder.php 2641 2013-03-24 13:43:52Z slaver7 $
 * @link http://2moons.cc/
 */

class statbuilder
{
	function __construct()
	{
		$this->starttime   	= microtime(true);
		$this->memory		= array(round(memory_get_usage() / 1024,1),round(memory_get_usage(1) / 1024,1));
		$this->time   		= TIMESTAMP;

		$this->recordData  	= array();
		
		$this->Unis			= array();
		$Query				= $GLOBALS['DATABASE']->query("SELECT uni FROM ".CONFIG." ORDER BY uni ASC;");
		while($Uni	= $GLOBALS['DATABASE']->fetch_array($Query)) {
			$this->Unis[]	= $Uni['uni'];
		}
	}

	private function SomeStatsInfos()
	{
		return array(
			'stats_time'		=> $this->time,
			'totaltime'    		=> round(microtime(true) - $this->starttime, 7),
			'memory_peak'		=> array(round(memory_get_peak_usage() / 1024,1), round(memory_get_peak_usage(1) / 1024,1)),
			'initial_memory'	=> $this->memory,
			'end_memory'		=> array(round(memory_get_usage() / 1024,1), round(memory_get_usage(1) / 1024,1)),
			'sql_count'			=> $GLOBALS['DATABASE']->get_sql(),
		);
	}
	
	private function CheckUniverseAccounts($UniData)
	{
		$UniData	= $UniData + array_combine($this->Unis, array_fill(1, count($this->Unis), 0));
		foreach($UniData as $Uni => $Amount) {
			Config::update(array('users_amount' => $Amount), $Uni);
		}
	}
	
	private function GetUsersInfosFromDB()
	{
		global $resource, $reslist;
		$select_defenses	=	'';
		$select_buildings	=	'';
		$selected_tech		=	'';
		$select_fleets		=	'';
				
		foreach($reslist['build'] as $Building){
			$select_buildings	.= " p.".$resource[$Building].",";
		}
		
		foreach($reslist['tech'] as $Techno){
			$selected_tech		.= " u.".$resource[$Techno].",";
		}	
		
		foreach($reslist['fleet'] as $Fleet){
			$select_fleets		.= " SUM(p.".$resource[$Fleet].") as ".$resource[$Fleet].",";
		}	
		
		foreach($reslist['defense'] as $Defense){
			$select_defenses	.= " SUM(p.".$resource[$Defense].") as ".$resource[$Defense].",";
		}
		
		foreach($reslist['missile'] as $Defense){
			$select_defenses	.= " SUM(p.".$resource[$Defense].") as ".$resource[$Defense].",";
		}
		
		$FlyingFleets	= array();
		$SQLFleets		= $GLOBALS['DATABASE']->query('SELECT fleet_array, fleet_owner FROM '.FLEETS.';');
		while ($CurFleets = $GLOBALS['DATABASE']->fetch_array($SQLFleets))
		{
			$FleetRec   	= explode(";", $CurFleets['fleet_array']);
			
			if(!is_array($FleetRec)) continue;
				
			foreach($FleetRec as $Item => $Group) {
				if (empty($Group)) continue;
				
				$Ship    	   = explode(",", $Group);
				if(!isset($FlyingFleets[$CurFleets['fleet_owner']][$Ship[0]]))
					$FlyingFleets[$CurFleets['fleet_owner']][$Ship[0]]	= $Ship[1];
				else
					$FlyingFleets[$CurFleets['fleet_owner']][$Ship[0]]	+= $Ship[1];
			}
		}
		
		$GLOBALS['DATABASE']->free_result($SQLFleets);
		
		$Return['Fleets'] 	= $FlyingFleets;		
		$Return['Planets']	= $GLOBALS['DATABASE']->query('SELECT SQL_BIG_RESULT DISTINCT '.$select_buildings.' p.id, p.universe, p.id_owner, u.authlevel, u.bana, u.username FROM '.PLANETS.' as p LEFT JOIN '.USERS.' as u ON u.id = p.id_owner;');
		$Return['Users']	= $GLOBALS['DATABASE']->query('SELECT SQL_BIG_RESULT DISTINCT '.$selected_tech.$select_fleets.$select_defenses.' u.id, u.ally_id, u.authlevel, u.bana, u.universe, u.username, s.tech_rank AS old_tech_rank, s.build_rank AS old_build_rank, s.defs_rank AS old_defs_rank, s.fleet_rank AS old_fleet_rank, s.total_rank AS old_total_rank FROM '.USERS.' as u LEFT JOIN '.STATPOINTS.' as s ON s.stat_type = 1 AND s.id_owner = u.id LEFT JOIN '.PLANETS.' as p ON u.id = p.id_owner GROUP BY s.id_owner, u.id, u.authlevel;');
		$Return['Alliance']	= $GLOBALS['DATABASE']->query('SELECT SQL_BIG_RESULT DISTINCT a.id, a.ally_universe, s.tech_rank AS old_tech_rank, s.build_rank AS old_build_rank, s.defs_rank AS old_defs_rank, s.fleet_rank AS old_fleet_rank, s.total_rank AS old_total_rank FROM '.ALLIANCE.' as a LEFT JOIN '.STATPOINTS.' as s ON s.stat_type = 2 AND s.id_owner = a.id;');
	
		return $Return;
	}
	
	private function setRecords($userID, $elementID, $amount)
	{
		$this->recordData[$elementID][$amount][]	= $userID;
	}
	
	private function writeRecordData()
	{
		$QueryData	= array();
		foreach($this->recordData as $elementID => $elementArray) {
			krsort($elementArray, SORT_NUMERIC);
			$userWinner		= reset($elementArray);
			$maxAmount		= key($elementArray);
			$userWinner		= array_unique($userWinner);
			foreach($userWinner as $userID) {
				$QueryData[]	= "(".$userID.",".$elementID.",".$maxAmount.")";
			}
		}
		
		if(!empty($QueryData)) {
			$SQL	= "TRUNCATE TABLE ".RECORDS.";";
			$SQL	.= "INSERT INTO ".RECORDS." (userID, elementID, level) VALUES ".implode(', ', $QueryData).";";
			$this->SaveDataIntoDB($SQL);
		}
	}
	
	private function SaveDataIntoDB($Data)
	{
		if(!empty($Data))
			$GLOBALS['DATABASE']->multi_query($Data);
	}

	private function GetTechnoPoints($USER) 
	{
		global $resource, $reslist, $pricelist;
		$TechCounts = 0;
		$TechPoints = 0;

		foreach($reslist['tech'] as $Techno) 
		{
			if($USER[$resource[$Techno]] == 0) continue;

			$Units	= $pricelist[$Techno]['cost'][901] + $pricelist[$Techno]['cost'][902] + $pricelist[$Techno]['cost'][903];
			for($Level = 1; $Level <= $USER[$resource[$Techno]]; $Level++)
			{
				$TechPoints	+= $Units * pow($pricelist[$Techno]['factor'], $Level);
			}
			
			$TechCounts		+= $USER[$resource[$Techno]];
			
			$this->setRecords($USER['id'], $Techno, $USER[$resource[$Techno]]);
		}
		
		return array('count' => $TechCounts, 'points' => ($TechPoints / Config::get('stat_settings')));
	}

	private function GetBuildPoints($PLANET) 
	{
		global $resource, $reslist, $pricelist;
		$BuildCounts = 0;
		$BuildPoints = 0;
		
		foreach($reslist['build'] as $Build)
		{
			if($PLANET[$resource[$Build]] == 0) continue;
			
			$Units			 = $pricelist[$Build]['cost'][901] + $pricelist[$Build]['cost'][902] + $pricelist[$Build]['cost'][903];
			for($Level = 1; $Level <= $PLANET[$resource[$Build]]; $Level++)
			{
				$BuildPoints	+= $Units * pow($pricelist[$Build]['factor'], $Level);
			}
			
			$BuildCounts	+= $PLANET[$resource[$Build]];
			
			$this->setRecords($PLANET['id_owner'], $Build, $PLANET[$resource[$Build]]);
		}
		return array('count' => $BuildCounts, 'points' => ($BuildPoints / Config::get('stat_settings')));
	}

	private function GetDefensePoints($USER) 
	{
		global $resource, $reslist, $pricelist;
		$DefenseCounts = 0;
		$DefensePoints = 0;
				
		foreach(array_merge($reslist['defense'], $reslist['missile']) as $Defense) {
			if($USER[$resource[$Defense]] == 0) continue;
			
			$Units			= $pricelist[$Defense]['cost'][901] + $pricelist[$Defense]['cost'][902] + $pricelist[$Defense]['cost'][903];
			$DefensePoints += $Units * $USER[$resource[$Defense]];
			$DefenseCounts += $USER[$resource[$Defense]];
		
			$this->setRecords($USER['id'], $Defense, $USER[$resource[$Defense]]);
		}
		
		return array('count' => $DefenseCounts, 'points' => ($DefensePoints / Config::get('stat_settings')));
	}

	private function GetFleetPoints($USER) 
	{
		global $resource, $reslist, $pricelist;
		$FleetCounts = 0;
		$FleetPoints = 0;
	
		foreach($reslist['fleet'] as $Fleet) {	
			if($USER[$resource[$Fleet]] == 0) continue;
			
			$Units			= $pricelist[$Fleet]['cost'][901] + $pricelist[$Fleet]['cost'][902] + $pricelist[$Fleet]['cost'][903];
			$FleetPoints   += $Units * $USER[$resource[$Fleet]];
			$FleetCounts   += $USER[$resource[$Fleet]];
			
			$this->setRecords($USER['id'], $Fleet, $USER[$resource[$Fleet]]);
		}
		
		return array('count' => $FleetCounts, 'points' => ($FleetPoints / Config::get('stat_settings')));
	}
	
	private function SetNewRanks()
	{
		$QryUpdateStats = "";
		foreach($this->Unis as $Uni)
		{
			$tech			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s, ".USERS." as u WHERE s.universe = '".$Uni."' AND s.stat_type = '1' AND s.id_owner = u.id ".((Config::get('stat') == 2)?'AND u.authlevel < '.Config::get('stat_level').' ':'')." ORDER BY tech_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$tech[$CurUser['id_owner']]	= $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);

			$build			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s, ".USERS." as u WHERE s.universe = '".$Uni."' AND  s.stat_type = '1' AND s.id_owner = u.id ".((Config::get('stat') == 2)?'AND u.authlevel < '.Config::get('stat_level').' ':'')." ORDER BY build_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$build[$CurUser['id_owner']] = $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
			$defs			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s, ".USERS." as u WHERE s.universe = '".$Uni."' AND  s.stat_type = '1' AND s.id_owner = u.id ".((Config::get('stat') == 2)?'AND u.authlevel < '.Config::get('stat_level').' ':'')." ORDER BY defs_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$defs[$CurUser['id_owner']]	= $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
			$fleet			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s, ".USERS." as u WHERE s.universe = '".$Uni."' AND  s.stat_type = '1' AND s.id_owner = u.id ".((Config::get('stat') == 2)?'AND u.authlevel < '.Config::get('stat_level').' ':'')." ORDER BY fleet_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$fleet[$CurUser['id_owner']] = $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s, ".USERS." as u WHERE s.universe = '".$Uni."' AND  s.stat_type = '1' AND s.id_owner = u.id ".((Config::get('stat') == 2)?'AND u.authlevel < '.Config::get('stat_level').' ':'')." ORDER BY total_points DESC;");

			while($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$QryUpdateStats .= "UPDATE ".STATPOINTS." SET tech_rank = '". $tech[$CurUser['id_owner']] ."', build_rank = '". $build[$CurUser['id_owner']] ."', defs_rank = '". $defs[$CurUser['id_owner']] ."', fleet_rank = '". $fleet[$CurUser['id_owner']] ."', total_rank = '". $Rank ."' WHERE stat_type = '1' AND id_owner = '". $CurUser['id_owner'] ."';";
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
				
			$tech			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s WHERE s.universe = '".$Uni."' AND  s.stat_type = '2' ORDER BY tech_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$tech[$CurUser['id_owner']]	= $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
			
			$build			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s WHERE s.universe = '".$Uni."' AND  s.stat_type = '2' ORDER BY build_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$build[$CurUser['id_owner']] = $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
			$defs			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s WHERE s.universe = '".$Uni."' AND  s.stat_type = '2' ORDER BY defs_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$defs[$CurUser['id_owner']]	= $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
			
			$fleet			= array();
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s WHERE s.universe = '".$Uni."' AND  s.stat_type = '2' ORDER BY fleet_points DESC;");
			while ($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$fleet[$CurUser['id_owner']] = $Rank;
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
				
			$Rank           = 1;
			$RankQry        = $GLOBALS['DATABASE']->query("SELECT s.id_owner FROM ".STATPOINTS." as s WHERE s.universe = '".$Uni."' AND s.stat_type = '2' ORDER BY total_points DESC;");

			while($CurUser = $GLOBALS['DATABASE']->fetch_array($RankQry))
			{
				$QryUpdateStats .= "UPDATE ".STATPOINTS." SET tech_rank = '". $tech[$CurUser['id_owner']] ."', build_rank = '". $build[$CurUser['id_owner']] ."', defs_rank = '". $defs[$CurUser['id_owner']] ."', fleet_rank = '". $fleet[$CurUser['id_owner']] ."', total_rank = '". $Rank ."' WHERE stat_type = '2' AND id_owner = '". $CurUser['id_owner'] ."';";
				$Rank++;
			}

			$GLOBALS['DATABASE']->free_result($RankQry);
		}
		return $QryUpdateStats;
	}
	
	final public function MakeStats()
	{
		global $resource;
		$AllyPoints	= array();
		$UserPoints	= array();
		$TotalData	= $this->GetUsersInfosFromDB();
		$FinalSQL	= 'TRUNCATE TABLE '.STATPOINTS.';';
		$FinalSQL	.= "INSERT INTO ".STATPOINTS." (id_owner, id_ally, stat_type, universe, tech_old_rank, tech_points, tech_count, build_old_rank, build_points, build_count, defs_old_rank, defs_points, defs_count, fleet_old_rank, fleet_points, fleet_count, total_old_rank, total_points, total_count) VALUES ";
		
		while($PlanetData = $GLOBALS['DATABASE']->fetch_array($TotalData['Planets']))
		{		
			if((in_array(Config::get('stat'), array(1, 2)) && $PlanetData['authlevel'] >= Config::get('stat_level')) || !empty($PlanetData['bana'])) continue;
			
 			if(!isset($UserPoints[$PlanetData['id_owner']])) {
				$UserPoints[$PlanetData['id_owner']]['build']['count'] = $UserPoints[$PlanetData['id_owner']]['build']['points'] = 0;
			}
			
			$BuildPoints												= $this->GetBuildPoints($PlanetData);
			$UserPoints[$PlanetData['id_owner']]['build']['count'] 		+= $BuildPoints['count'];
			$UserPoints[$PlanetData['id_owner']]['build']['points'] 	+= $BuildPoints['points'];
		}
		
		$GLOBALS['DATABASE']->free_result($TotalData['Planets']);
		
		$UniData	= array();
		
		while($UserData	= $GLOBALS['DATABASE']->fetch_array($TotalData['Users']))
		{
			if(!isset($UniData[$UserData['universe']]))
				$UniData[$UserData['universe']] = 0;
			
			$UniData[$UserData['universe']]++;
				
			if ((in_array(Config::get('stat'), array(1, 2)) && $UserData['authlevel'] >= Config::get('stat_level')) || !empty($UserData['bana']))
			{	
				$FinalSQL  .= "(".$UserData['id'].",".$UserData['ally_id'].",1,".$UserData['universe'].",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0), ";
				continue;
			}

			if(isset($TotalData['Fleets'][$UserData['id']])) {
				foreach($TotalData['Fleets'][$UserData['id']] as $ID => $Amount)
					$UserData[$resource[$ID]]	+= $Amount;
			}
			
			$TechnoPoints		= $this->GetTechnoPoints($UserData);
			$FleetPoints		= $this->GetFleetPoints($UserData);
			$DefensePoints		= $this->GetDefensePoints($UserData);
			
			$UserPoints[$UserData['id']]['fleet']['count'] 		= $FleetPoints['count'];
			$UserPoints[$UserData['id']]['fleet']['points'] 	= $FleetPoints['points'];
			$UserPoints[$UserData['id']]['defense']['count'] 	= $DefensePoints['count'];
			$UserPoints[$UserData['id']]['defense']['points']	= $DefensePoints['points'];
			$UserPoints[$UserData['id']]['techno']['count'] 	= $TechnoPoints['count'];
			$UserPoints[$UserData['id']]['techno']['points'] 	= $TechnoPoints['points'];
			
			$UserPoints[$UserData['id']]['total']['count'] 		= $UserPoints[$UserData['id']]['techno']['count']
																+ $UserPoints[$UserData['id']]['build']['count']
																+ $UserPoints[$UserData['id']]['defense']['count']
																+ $UserPoints[$UserData['id']]['fleet']['count'];
																
			$UserPoints[$UserData['id']]['total']['points'] 	= $UserPoints[$UserData['id']]['techno']['points']
																+ $UserPoints[$UserData['id']]['build']['points']
																+ $UserPoints[$UserData['id']]['defense']['points'] 
																+ $UserPoints[$UserData['id']]['fleet']['points'];

			if($UserData['ally_id'] != 0)
			{
				if(!isset($AllyPoints[$UserData['ally_id']]))
				{
					$AllyPoints[$UserData['ally_id']]['build']['count']		= 0;
					$AllyPoints[$UserData['ally_id']]['build']['points']	= 0;
					$AllyPoints[$UserData['ally_id']]['fleet']['count']		= 0;
					$AllyPoints[$UserData['ally_id']]['fleet']['points']	= 0;
					$AllyPoints[$UserData['ally_id']]['defense']['count']	= 0;
					$AllyPoints[$UserData['ally_id']]['defense']['points']	= 0;
					$AllyPoints[$UserData['ally_id']]['techno']['count']	= 0;
					$AllyPoints[$UserData['ally_id']]['techno']['points']	= 0;
					$AllyPoints[$UserData['ally_id']]['total']['count']		= 0;
					$AllyPoints[$UserData['ally_id']]['total']['points']	= 0;				
				}
			
				$AllyPoints[$UserData['ally_id']]['build']['count']		+= $UserPoints[$UserData['id']]['build']['count'];
				$AllyPoints[$UserData['ally_id']]['build']['points']	+= $UserPoints[$UserData['id']]['build']['points'];
				$AllyPoints[$UserData['ally_id']]['fleet']['count']		+= $UserPoints[$UserData['id']]['fleet']['count'];
				$AllyPoints[$UserData['ally_id']]['fleet']['points']	+= $UserPoints[$UserData['id']]['fleet']['points'];
				$AllyPoints[$UserData['ally_id']]['defense']['count']	+= $UserPoints[$UserData['id']]['defense']['count'];
				$AllyPoints[$UserData['ally_id']]['defense']['points']	+= $UserPoints[$UserData['id']]['defense']['points'];
				$AllyPoints[$UserData['ally_id']]['techno']['count']	+= $UserPoints[$UserData['id']]['techno']['count'];
				$AllyPoints[$UserData['ally_id']]['techno']['points']	+= $UserPoints[$UserData['id']]['techno']['points'];
				$AllyPoints[$UserData['ally_id']]['total']['count']		+= $UserPoints[$UserData['id']]['total']['count'];
				$AllyPoints[$UserData['ally_id']]['total']['points']	+= $UserPoints[$UserData['id']]['total']['points'];
			}
			
			$FinalSQL  .= "(".
			$UserData['id'].", ".
			$UserData['ally_id'].", 1, ".
			$UserData['universe'].", ".
			(isset($UserData['old_tech_rank']) ? $UserData['old_tech_rank'] : 0).", ".
			(isset($UserPoints[$UserData['id']]['techno']['points']) ? min($UserPoints[$UserData['id']]['techno']['points'], 1E50) : 0).", ".
			(isset($UserPoints[$UserData['id']]['techno']['count']) ? $UserPoints[$UserData['id']]['techno']['count'] : 0).", ".
			(isset($UserData['old_build_rank']) ? $UserData['old_build_rank'] : 0).", ".
			(isset($UserPoints[$UserData['id']]['build']['points']) ? min($UserPoints[$UserData['id']]['build']['points'], 1E50) : 0).", ".
			(isset($UserPoints[$UserData['id']]['build']['count']) ? $UserPoints[$UserData['id']]['build']['count'] : 0).", ".
			(isset($UserData['old_defs_rank']) ? $UserData['old_defs_rank'] : 0).", ".
			(isset($UserPoints[$UserData['id']]['defense']['points']) ? min($UserPoints[$UserData['id']]['defense']['points'], 1E50) : 0).", ".
			(isset($UserPoints[$UserData['id']]['defense']['count']) ? $UserPoints[$UserData['id']]['defense']['count'] : 0).", ".
			(isset($UserData['old_fleet_rank']) ? $UserData['old_fleet_rank'] : 0).", ".
			(isset($UserPoints[$UserData['id']]['fleet']['points']) ? min($UserPoints[$UserData['id']]['fleet']['points'], 1E50) : 0).", ".
			(isset($UserPoints[$UserData['id']]['fleet']['count']) ? $UserPoints[$UserData['id']]['fleet']['count'] : 0).", ".
			(isset($UserData['old_total_rank']) ? $UserData['old_total_rank'] : 0).", ".
			(isset($UserPoints[$UserData['id']]['total']['points']) ? min($UserPoints[$UserData['id']]['total']['points'], 1E50) : 0).", ".
			(isset($UserPoints[$UserData['id']]['total']['count']) ? $UserPoints[$UserData['id']]['total']['count'] : 0)."), ";
		}
		
		$GLOBALS['DATABASE']->free_result($TotalData['Users']);
		
		$FinalSQL	= substr($FinalSQL, 0, -2).';';
		
		$this->SaveDataIntoDB($FinalSQL);
		unset($UserPoints);
		
		if(count($AllyPoints) != 0)
		{
			$AllySQL = "INSERT INTO ".STATPOINTS." (id_owner, id_ally, stat_type, universe, tech_old_rank, tech_points, tech_count, build_old_rank, build_points, build_count, defs_old_rank, defs_points, defs_count, fleet_old_rank, fleet_points, fleet_count, total_old_rank, total_points, total_count) VALUES ";
			while($AllianceData	= $GLOBALS['DATABASE']->fetch_array($TotalData['Alliance']))
			{
				$AllySQL  .= "(".
				$AllianceData['id'].", 0, 2, ".
				$AllianceData['ally_universe'].", ".
				(isset($AllyPoints['old_tech_rank']) ? $AllyPoints['old_tech_rank'] : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['techno']['points']) ? min($AllyPoints[$AllianceData['id']]['techno']['points'], 1E50) : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['techno']['count']) ? $AllyPoints[$AllianceData['id']]['techno']['count'] : 0).", ".
				(isset($AllianceData['old_build_rank']) ? $AllianceData['old_build_rank'] : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['build']['points']) ? min($AllyPoints[$AllianceData['id']]['build']['points'], 1E50) : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['build']['count']) ? $AllyPoints[$AllianceData['id']]['build']['count'] : 0).", ".
				(isset($AllianceData['old_defs_rank']) ? $AllianceData['old_defs_rank'] : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['defense']['points']) ? min($AllyPoints[$AllianceData['id']]['defense']['points'], 1E50) : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['defense']['count']) ? $AllyPoints[$AllianceData['id']]['defense']['count'] : 0).", ".
				(isset($AllianceData['old_fleet_rank']) ? $AllianceData['old_fleet_rank'] : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['fleet']['points']) ? min($AllyPoints[$AllianceData['id']]['fleet']['points'], 1E50) : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['fleet']['count']) ? $AllyPoints[$AllianceData['id']]['fleet']['count'] : 0).", ".
				(isset($AllianceData['old_total_rank']) ? $AllianceData['old_total_rank'] : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['total']['points']) ? min($AllyPoints[$AllianceData['id']]['total']['points'], 1E50) : 0).", ".
				(isset($AllyPoints[$AllianceData['id']]['total']['count']) ? $AllyPoints[$AllianceData['id']]['total']['count'] : 0)."), ";
			}
			unset($AllyPoints);
			$AllySQL	= substr($AllySQL, 0, -2).';';
			$this->SaveDataIntoDB($AllySQL);
		}	
			
		$GLOBALS['DATABASE']->free_result($TotalData['Alliance']);
		
		$RankSQL    = $this->SetNewRanks();

		$this->SaveDataIntoDB($RankSQL);
		$this->CheckUniverseAccounts($UniData);		
		$this->writeRecordData();	
		return $this->SomeStatsInfos();
	}
}
