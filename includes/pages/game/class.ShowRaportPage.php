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
 * @info $Id: class.ShowRaportPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

class ShowRaportPage extends AbstractPage
{
	public static $requireModule = 0;
	
	protected $disableEcoSystem = true;

	function __construct() 
	{
		parent::__construct();
	}
	
	private function BCWrapperPreRev2321($CombatRaport)
	{
		if(isset($CombatRaport['moon']['desfail']))
		{
			$CombatRaport['moon']	= array(
				'moonName'				=> $CombatRaport['moon']['name'],
				'moonChance'			=> $CombatRaport['moon']['chance'],
				'moonDestroySuccess'	=> !$CombatRaport['moon']['desfail'],
				'fleetDestroyChance'	=> $CombatRaport['moon']['chance2'],
				'fleetDestroySuccess'	=> !$CombatRaport['moon']['fleetfail']
			);			
		}
		elseif(isset($CombatRaport['moon'][0]))
		{
			$CombatRaport['moon']	= array(
				'moonName'				=> $CombatRaport['moon'][1],
				'moonChance'			=> $CombatRaport['moon'][0],
				'moonDestroySuccess'	=> !$CombatRaport['moon'][2],
				'fleetDestroyChance'	=> $CombatRaport['moon'][3],
				'fleetDestroySuccess'	=> !$CombatRaport['moon'][4]
			);			
		}
		
		if(isset($CombatRaport['simu']))
		{
			$CombatRaport['additionalInfo'] = $CombatRaport['simu'];
		}
		
		if(isset($CombatRaport['debris'][0]))
		{
            $CombatRaport['debris'] = array(
                901	=> $CombatRaport['debris'][0],
                902	=> $CombatRaport['debris'][1]
            );
		}
		
		if (!empty($CombatRaport['steal']['metal']))
		{
			$CombatRaport['steal'] = array(
				901	=> $CombatRaport['steal']['metal'],
				902	=> $CombatRaport['steal']['crystal'],
				903	=> $CombatRaport['steal']['deuterium']
			);
		}
		
		return $CombatRaport;
	}
	
	function battlehall() 
	{
		global $LNG, $USER;
		
		$LNG->includeData(array('FLEET'));
		$this->setWindow('popup');
		
		$RID		= HTTP::_GP('raport', '');
		
		$Raport		= $GLOBALS['DATABASE']->getFirstRow("SELECT 
		raport, time,
		(
			SELECT 
			GROUP_CONCAT(username SEPARATOR ' & ') as attacker
			FROM ".USERS." 
			WHERE id IN (SELECT uid FROM ".TOPKB_USERS." WHERE ".TOPKB_USERS.".rid = ".RW.".rid AND role = 1)
		) as attacker,
		(
			SELECT 
			GROUP_CONCAT(username SEPARATOR ' & ') as defender
			FROM ".USERS." 
			WHERE id IN (SELECT uid FROM ".TOPKB_USERS." WHERE ".TOPKB_USERS.".rid = ".RW.".rid AND role = 2)
		) as defender
		FROM ".RW."
		WHERE rid = '".$GLOBALS['DATABASE']->escape($RID)."';");
		
		$Info		= array($Raport["attacker"], $Raport["defender"]);
		
		if(!isset($Raport)) {
			$this->printMessage($LNG['sys_raport_not_found']);
		}
		
		$CombatRaport			= unserialize($Raport['raport']);
		$CombatRaport['time']	= _date($LNG['php_tdformat'], $CombatRaport['time'], $USER['timezone']);
		$CombatRaport			= $this->BCWrapperPreRev2321($CombatRaport);
		
		$this->tplObj->assign_vars(array(
			'Raport'	=> $CombatRaport,
			'Info'		=> $Info,
			'pageTitle'	=> $LNG['lm_topkb']
		));
		
		$this->display('shared.mission.raport.tpl');
	}
	
	function show() 
	{
		global $LNG, $USER;
		
		$LNG->includeData(array('FLEET'));		
		$this->setWindow('popup');
		
		$RID		= HTTP::_GP('raport', '');
		
		$raportData		= $GLOBALS['DATABASE']->getFirstRow("SELECT raport,attacker,defender FROM ".RW." WHERE rid = '".$GLOBALS['DATABASE']->escape($RID)."';");

		if(empty($raportData)) {
			$this->printMessage($LNG['sys_raport_not_found']);
		}
		
		// empty is BC for pre r2484
		$isAttacker = empty($raportData['attacker']) || in_array($USER['id'], explode(",", $raportData['attacker']));
		$isDefender = empty($raportData['defender']) || in_array($USER['id'], explode(",", $raportData['defender']));
		
		if(empty($raportData) || (!$isAttacker && !$isDefender)) {
			$this->printMessage($LNG['sys_raport_not_found']);
		}

		$CombatRaport			= unserialize($raportData['raport']);
		if($isAttacker && !$isDefender && $CombatRaport['result'] == 'r' && count($CombatRaport['rounds']) <= 2) {
			$this->printMessage($LNG['sys_raport_lost_contact']);
		}
		
		$CombatRaport['time']	= _date($LNG['php_tdformat'], $CombatRaport['time'], (isset($USER['timezone']) ? $USER['timezone'] : Config::get('timezone')));
		
		$CombatRaport			= $this->BCWrapperPreRev2321($CombatRaport);
		
		$this->tplObj->assign_vars(array(
			'Raport'	=> $CombatRaport,
			'pageTitle'	=> $LNG['sys_mess_attack_report']
		));
		
		$this->display('shared.mission.raport.tpl');
	}
}