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
 * @info $Id: ReferralCronjob.class.php 2747 2013-05-18 16:55:49Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class ReferralCronJob implements CronjobTask
{
	function run()
	{		
		if(Config::get(ROOT_UNI)->ref_active != 1)
		{
			return null;
		}
		/** @var $langObjects Language[] */
		$langObjects	= array();

		$db	= Database::get();

		$sql	= 'SELECT `username`, `ref_id`, `id`, `lang`, user.`universe`
		FROM %%USERS%% user
		INNER JOIN %%STATPOINTS%% as stats
		ON stats.`id_owner` = user.`id` AND stats.`stat_type` = :type AND stats.`total_points` >= :points
		WHERE user.`ref_bonus` = 1;';

		$userArray	= $db->select($sql, array(
			':points'	=> Config::get(ROOT_UNI)->ref_minpoints
		));

		foreach($userArray as $user)
		{
			if(!isset($langObjects[$user['lang']]))
			{
				$langObjects[$user['lang']]	= new Language($user['lang']);
				$langObjects[$user['lang']]->includeData(array('L18N', 'INGAME', 'TECH', 'CUSTOM'));
			}

			$userConfig	= Config::get($user['universe']);
			
			$LNG	= $langObjects[$user['lang']];
			$sql	= 'UPDATE %%USERS%% SET `darkmatter` = `darkmatter` + :bonus WHERE `id` = :userId;';

			$db->update($sql, array(
				':bonus'	=> $userConfig->ref_bonus,
				':userId'	=> $user['ref_id']
			));

			$sql	= 'UPDATE %%USERS%% SET `ref_bonus` = 0 WHERE `id` = :userId;';

			$db->update($sql, array(
				':userId'	=> $user['id']
			));

			$Message	= sprintf($LNG['sys_refferal_text'], $user['username'], pretty_number($userConfig->ref_minpoints), pretty_number($userConfig->ref_bonus), $LNG['tech'][921]);
			PlayerUtil::sendMessage($user['ref_id'], '', TIMESTAMP, 4, $LNG['sys_refferal_from'], sprintf($LNG['sys_refferal_title'], $user['username']), $Message);
		}

		return true;
	}
}