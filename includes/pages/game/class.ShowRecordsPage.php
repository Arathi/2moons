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
 * @info $Id: class.ShowRecordsPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */


class ShowRecordsPage extends AbstractPage
{
    public static $requireModule = MODULE_RECORDS;

	function __construct() 
	{
		parent::__construct();
	}
	
	function show()
	{
		global $USER, $PLANET, $LNG, $resource, $CONF, $UNI, $reslist;

		$recordResult	= $GLOBALS['DATABASE']->query("SELECT elementID, level, userID, username FROM ".USERS." INNER JOIN ".RECORDS." ON userID = id WHERE universe = ".$UNI.";");
		
		$defenseList	= array_fill_keys($reslist['defense'], array());
		$fleetList		= array_fill_keys($reslist['fleet'], array());
		$researchList	= array_fill_keys($reslist['tech'], array());
		$buildList		= array_fill_keys($reslist['build'], array());
		
		while($recordRow = $GLOBALS['DATABASE']->fetch_array($recordResult)) {
			if (in_array($recordRow['elementID'], $reslist['defense'])) {
				$defenseList[$recordRow['elementID']][]		= $recordRow;
			} elseif (in_array($recordRow['elementID'], $reslist['fleet'])) {
				$fleetList[$recordRow['elementID']][]		= $recordRow;
			} elseif (in_array($recordRow['elementID'], $reslist['tech'])) {
				$researchList[$recordRow['elementID']][]	= $recordRow;
			} elseif (in_array($recordRow['elementID'], $reslist['build'])) {
				$buildList[$recordRow['elementID']][]		= $recordRow;
			}
		}
		
		$this->tplObj->assign_vars(array(	
			'defenseList'	=> $defenseList,
			'fleetList'		=> $fleetList,
			'researchList'	=> $researchList,
			'buildList'		=> $buildList,
			'update'		=> _date($LNG['php_tdformat'], Config::get('stat_last_update'), $USER['timezone']),
		));
		
		$this->display('page.records.default.tpl');
	}
}
 