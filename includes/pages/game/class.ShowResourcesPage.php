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
 * @info $Id: class.ShowResourcesPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowResourcesPage extends AbstractPage
{
	public static $requireModule = MODULE_RESSOURCE_LIST;

	function __construct() 
	{
		parent::__construct();
	}
	
	function send()
	{
		global $LNG, $resource, $USER, $PLANET;
		if ($USER['urlaubs_modus'] == 0)
		{
			$updateSQL	= array();
			if(!isset($_POST['prod']))
				$_POST['prod'] = array();
				
				
			foreach($_POST['prod'] as $ressourceID => $Value)
			{
				$FieldName = $resource[$ressourceID].'_porcent';
				if (!isset($PLANET[$FieldName]) || !in_array($Value, range(0, 10)))
					continue;
				
				$updateSQL[]	= $FieldName." = '".((int) $Value)."'";
				
				$PLANET[$FieldName]	= $Value;
				$this->ecoObj->PLANET[$FieldName]	= $Value;
			}

			if(!empty($updateSQL))
			{
				$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET ".implode(", ", $updateSQL)." WHERE `id` = ".$PLANET['id'] .";");
				
				$this->ecoObj->ReBuildCache();
				$this->ecoObj->PLANET['eco_hash'] = $this->ecoObj->CreateHash();
				$PLANET = $this->ecoObj->PLANET;
			}
		}
		$this->save();
		$this->redirectTo('game.php?page=resources');
	}
	function show()
	{
		global $LNG, $ProdGrid, $resource, $reslist, $CONF, $pricelist, $USER, $PLANET;
		
		if($USER['urlaubs_modus'] == 1 || $PLANET['planet_type'] != 1)
		{
			$basicIncome[901]	= 0;
			$basicIncome[902]	= 0;
			$basicIncome[903]	= 0;
			$basicIncome[911]	= 0;
		}
		else
		{		
			$basicIncome[901]	= Config::get($resource[901].'_basic_income');
			$basicIncome[902]	= Config::get($resource[902].'_basic_income');
			$basicIncome[903]	= Config::get($resource[903].'_basic_income');
			$basicIncome[911]	= Config::get($resource[911].'_basic_income');
		}
		
		$temp	= array(
			901	=> array(
				'plus'	=> 0,
				'minus'	=> 0,
			),
			902	=> array(
				'plus'	=> 0,
				'minus'	=> 0,
			),
			903	=> array(
				'plus'	=> 0,
				'minus'	=> 0,
			),
			911	=> array(
				'plus'	=> 0,
				'minus'	=> 0,
			)
		);
		
		$BuildTemp		= $PLANET['temp_max'];
		$BuildEnergy	= $USER[$resource[113]];
		
		$ressIDs		= array_merge(array(), $reslist['resstype'][1], $reslist['resstype'][2]);
		
		if($PLANET['energy_used'] != 0) {
			$prodLevel	= min(1, $PLANET['energy'] / abs($PLANET['energy_used']));
		} else {
			$prodLevel	= 0;
		}
		
		foreach($reslist['prod'] as $ProdID)
		{	
			$BuildLevelFactor	= $PLANET[$resource[$ProdID].'_porcent'];
			$BuildLevel 		= $PLANET[$resource[$ProdID]];
		
			$productionList[$ProdID]	= array(
				'production'	=> array(901 => 0, 902 => 0, 903 => 0, 911 => 0),
				'elementLevel'	=> $PLANET[$resource[$ProdID]],
				'prodLevel'		=> $PLANET[$resource[$ProdID].'_porcent'],
			);
			
			foreach($ressIDs as $ID) 
			{
				if(!isset($ProdGrid[$ProdID]['production'][$ID]))
					continue;
					
				$Production	= eval(ResourceUpdate::getProd($ProdGrid[$ProdID]['production'][$ID]));
				
				if($ID != 911)
				{
					$Production	*= $prodLevel * Config::get('resource_multiplier');
				}
				else
				{
					$Production	*= Config::get('energySpeed');
				}
				
				$productionList[$ProdID]['production'][$ID]	= $Production;
				
				if($Production > 0) {
					if($PLANET[$resource[$ID]] == 0) continue;
					
					$temp[$ID]['plus']	+= $Production;
				} else {
					$temp[$ID]['minus']	+= $Production;
				}
			}
		}
				
		$storage	= array(
			901 => shortly_number($PLANET[$resource[901].'_max']),
			902 => shortly_number($PLANET[$resource[902].'_max']),
			903 => shortly_number($PLANET[$resource[903].'_max']),
		);
		
		$basicProduction	= array(
			901 => $basicIncome[901] * Config::get('resource_multiplier'),
			902 => $basicIncome[902] * Config::get('resource_multiplier'),
			903	=> $basicIncome[903] * Config::get('resource_multiplier'),
			911	=> $basicIncome[911] * Config::get('energySpeed'),
		);
		
		$totalProduction	= array(
			901 => $PLANET[$resource[901].'_perhour'] + $basicProduction[901],
			902 => $PLANET[$resource[902].'_perhour'] + $basicProduction[902],
			903	=> $PLANET[$resource[903].'_perhour'] + $basicProduction[903],
			911	=> $PLANET[$resource[911]] + $basicProduction[911] + $PLANET[$resource[911].'_used'],
		);
		
		$bonusProduction	= array(
			901 => $temp[901]['plus'] * ($USER['factor']['Resource'] + 0.02 * $USER[$resource[131]]),
			902 => $temp[902]['plus'] * ($USER['factor']['Resource'] + 0.02 * $USER[$resource[132]]),
			903	=> $temp[903]['plus'] * ($USER['factor']['Resource'] + 0.02 * $USER[$resource[133]]),
			911	=> $temp[911]['plus'] * $USER['factor']['Energy'],
		);
		
		$dailyProduction	= array(
			901 => $totalProduction[901] * 24,
			902 => $totalProduction[902] * 24,
			903	=> $totalProduction[903] * 24,
			911	=> $totalProduction[911],
		);
		
		$weeklyProduction	= array(
			901 => $totalProduction[901] * 168,
			902 => $totalProduction[902] * 168,
			903	=> $totalProduction[903] * 168,
			911	=> $totalProduction[911],
		);
			
		$prodSelector	= array();
		
		foreach(range(0, 10) as $procent) {
			$prodSelector[$procent]	= ($procent * 10).'%';
		}
		
		$this->tplObj->assign_vars(array(	
			'header'			=> sprintf($LNG['rs_production_on_planet'], $PLANET['name']),
			'prodSelector'		=> $prodSelector,
			'productionList'	=> $productionList,
			'basicProduction'	=> $basicProduction,
			'totalProduction'	=> $totalProduction,
			'bonusProduction'	=> $bonusProduction,
			'dailyProduction'	=> $dailyProduction,
			'weeklyProduction'	=> $weeklyProduction,
			'storage'			=> $storage,
		));
		
		$this->display('page.resources.default.tpl');
	}
}
