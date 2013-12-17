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
 * @info $Id: class.ShowTraderPage.php 2644 2013-03-26 18:23:11Z slaver7 $
 * @link http://2moons.cc/
 */


class ShowTraderPage extends AbstractPage
{
	public static $requireModule = MODULE_TRADER;

	function __construct() 
	{
		parent::__construct();
	}
	
	public static $Charge = array(
		901	=> array(901 => 1, 902 => 2, 903 => 4),
		902	=> array(901 => 0.5, 902 => 1, 903 => 2),
		903	=> array(901 => 0.25, 902 => 0.5, 903 => 1),
	);
	
	public function show() 
	{
		global $LNG, $CONF, $USER, $resource;
		
		$this->tplObj->assign_vars(array(
			'tr_cost_dm_trader'		=> sprintf($LNG['tr_cost_dm_trader'], pretty_number(Config::get('darkmatter_cost_trader')), $LNG['tech'][921]),
			'charge'				=> self::$Charge,
			'resource'				=> $resource,
			'requiredDarkMatter'	=> $USER['darkmatter'] < Config::get('darkmatter_cost_trader') ? sprintf($LNG['tr_not_enought'], $LNG['tech'][921]) : false,
		));
		
		$this->display("page.trader.default.tpl");
	}
		
	function trade()
	{
		global $USER, $LNG, $CONF, $reslist;
		
		if ($USER['darkmatter'] < Config::get('darkmatter_cost_trader')) {
			$this->redirectTo('game.php?page=trader');
		}
		
		$resourceID	= HTTP::_GP('resource', 0);
		
		if(!in_array($resourceID, array_keys(self::$Charge))) {
			$this->printMessage($LNG['invalid_action']);
		}
		
		$tradeResources	= array_values(array_diff(array_keys(self::$Charge[$resourceID]), array($resourceID)));
		$this->tplObj->loadscript("trader.js");
		$this->tplObj->assign_vars(array(
			'tradeResourceID'	=> $resourceID,
			'tradeResources'	=> $tradeResources,
			'charge' 			=> self::$Charge[$resourceID],
		));

		$this->display('page.trader.trade.tpl');
	}
	
	function send()
	{
		global $USER, $PLANET, $LNG, $CONF, $reslist, $resource;
		
		if ($USER['darkmatter'] < Config::get('darkmatter_cost_trader')) {
			$this->redirectTo('game.php?page=trader');
		}
		
		$resourceID	= HTTP::_GP('resource', 0);
		
		if(!in_array($resourceID, array_keys(self::$Charge))) {
			$this->printMessage($LNG['invalid_action']);
		}

		$getTradeResources	= HTTP::_GP('trade', array());
		
		$tradeResources		= array_values(array_diff(array_keys(self::$Charge[$resourceID]), array($resourceID)));
		$tradeSum 			= 0;
		
		foreach($tradeResources as $tradeRessID) {
			if(!isset($getTradeResources[$tradeRessID]))
			{
				continue;
			}
			$tradeAmount	= max(0, round((float) $getTradeResources[$tradeRessID]));
			
			if(empty($tradeAmount) || !isset(self::$Charge[$resourceID][$tradeRessID]))
			{
				continue;  
			}
			
			if(isset($PLANET[$resource[$resourceID]]))
			{
				$usedResources	= $tradeAmount * self::$Charge[$resourceID][$tradeRessID];
				
				if($usedResources > $PLANET[$resource[$resourceID]])
				{
					$this->printMessage(sprintf($LNG['tr_not_enought'], $LNG['tech'][$resourceID]), array("game.php?page=trader", 3));
				}
				
				$tradeSum	  						+= $tradeAmount;
				$PLANET[$resource[$resourceID]]		-= $usedResources;
			}
			elseif(isset($USER[$resource[$resourceID]]))
			{
				if($resourceID == 291)
				{
					$USER[$resource[$resourceID]]	-= Config::get('darkmatter_cost_trader');
				}
				
				$usedResources	= $tradeAmount * self::$Charge[$resourceID][$tradeRessID];
				
				if($usedResources > $USER[$resource[$resourceID]])
				{
					$this->printMessage(sprintf($LNG['tr_not_enought'], $LNG['tech'][$resourceID]), array("game.php?page=trader", 3));
				}
				
				$tradeSum	  						+= $tradeAmount;
				$USER[$resource[$resourceID]]		-= $usedResources;
				
				if($resourceID == 291)
				{
					$USER[$resource[$resourceID]]	+= Config::get('darkmatter_cost_trader');
				}
			}
			else
			{
				throw new Exception('Unknow resource ID #'.$resourceID);
			}
			
			if(isset($PLANET[$resource[$tradeRessID]]))
			{
				$PLANET[$resource[$tradeRessID]]	+= $tradeAmount;
			}
			elseif(isset($USER[$resource[$tradeRessID]]))
			{
				$USER[$resource[$tradeRessID]]		+= $tradeAmount;
			}
			else
			{
				throw new Exception('Unknow resource ID #'.$tradeRessID);
			}
		}
		
		if ($tradeSum > 0)
		{
			$USER[$resource[921]]	-= Config::get('darkmatter_cost_trader');
		}
		
		$this->printMessage($LNG['tr_exchange_done'], array("game.php?page=trader", 3));
	}
}