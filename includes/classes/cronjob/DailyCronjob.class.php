<?php

/**
 *  2Moons
 *  Copyright (C) 2011 Jan Kröpke
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
 * @copyright 2009 Lucky
 * @copyright 2011 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.0 (2011-12-10)
 * @info $Id: DailyCronjob.class.php 2747 2013-05-18 16:55:49Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class DailyCronJob implements CronjobTask
{
	function run()
	{
		$this->optimizeTables();
		$this->clearCache();
		$this->reCalculateCronjobs();
		$this->clearEcoCache();
	}
	
	function optimizeTables()
	{
		$sql			= "SHOW TABLE STATUS FROM `".DB_NAME."`;";
		$sqlTableRaw	= Database::get()->nativeQuery($sql);

		$prefixCounts	= strlen(DB_PREFIX);
		$dbTables		= array();

		foreach($sqlTableRaw as $table)
		{
			if (DB_PREFIX == substr($table['Name'], 0, $prefixCounts)) {
				$dbTables[] = $table['Name'];
			}
		}

		if(!empty($dbTables))
		{
			Database::get()->nativeQuery("OPTIMIZE TABLE ".implode(', ', $dbTables).";");
		}
	}

	function clearCache()
	{
		ClearCache();
	}
	
	function reCalculateCronjobs()
	{
		Cronjob::reCalculateCronjobs();
	}
	
	function clearEcoCache()
	{
		$sql	= "UPDATE %%PLANETS%% SET eco_hash = '';";
		Database::get()->update($sql);
	}
}
