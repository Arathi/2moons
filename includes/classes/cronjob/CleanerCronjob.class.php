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
 * @info $Id: CleanerCronjob.class.php 2753 2013-05-25 15:12:17Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class CleanerCronjob implements CronjobTask
{
	function run()
	{
        $config	= Config::get(ROOT_UNI);

		$unis	= Universe::availableUniverses();
	
		//Delete old messages
		$del_before 	= TIMESTAMP - ($config->del_oldstuff * 86400);
		$del_inactive 	= TIMESTAMP - ($config->del_user_automatic * 86400);
		$del_deleted 	= TIMESTAMP - ($config->del_user_manually * 86400);

		if($del_inactive === TIMESTAMP)
		{
			$del_inactive = 2147483647;
		}

		$sql	= 'DELETE FROM %%MESSAGES%% WHERE `message_time` < :time;';
		Database::get()->delete($sql, array(
			':time'	=> $del_before
		));

		$sql	= 'DELETE FROM %%ALLIANCE%% WHERE `ally_members` = 0;';
		Database::get()->delete($sql);

		$sql	= 'DELETE FROM %%PLANETS%% WHERE `destruyed` < :time AND `destruyed` != 0;';
		Database::get()->delete($sql, array(
			':time'	=> TIMESTAMP
		));

		$sql	= 'DELETE FROM %%SESSION%% WHERE `lastonline` < :time;';
		Database::get()->delete($sql, array(
			':time'	=> TIMESTAMP - SESSION_LIFETIME
		));

		$sql	= 'DELETE FROM %%FLEETS_EVENT%% WHERE fleetID NOT IN (SELECT fleet_id FROM %%FLEETS%%);';
		Database::get()->delete($sql);

		$sql	= 'UPDATE %%USERS%% SET `email_2` = `email` WHERE `setmail` < :time;';
		Database::get()->update($sql, array(
			':time'	=> TIMESTAMP
		));

		$sql	= 'SELECT `id` FROM %%USERS%% WHERE `authlevel` = :authlevel
		AND ((`db_deaktjava` != 0 AND `db_deaktjava` < :timeDeleted) OR `onlinetime` < :timeInactive);';

		$deleteUserIds = Database::get()->select($sql, array(
			':authlevel'	=> AUTH_USR,
			':timeDeleted'	=> $del_deleted,
			':timeInactive'	=> $del_inactive
		));

		if(empty($deleteUserIds))
		{
			foreach($deleteUserIds as $dataRow)
			{
				PlayerUtil::deletePlayer($dataRow['id']);
			}	
		}
		
		foreach($unis as $uni)
		{
			$sql	= 'SELECT units FROM %%TOPKB%% WHERE `universe` = :universe ORDER BY units DESC LIMIT 99,1;';

			$battleHallLowest	= Database::get()->selectSingle($sql, array(
				':universe'	=> $uni
			),'units');

			if(!is_null($battleHallLowest))
			{
				$sql	= 'DELETE %%TOPKB%%, %%TOPKB_USERS%%
				FROM %%TOPKB%%
				INNER JOIN %%TOPKB_USERS%% USING (rid)
				WHERE `universe` = :universe AND `units` < :battleHallLowest;';

				Database::get()->delete($sql, array(
					':universe'			=> $uni,
					':battleHallLowest'	=> $battleHallLowest
				));
			}
		}

		$sql	= 'DELETE FROM %%RW%% WHERE `time` < :time AND `rid` NOT IN (SELECT `rid` FROM %%TOPKB%%);';
		Database::get()->delete($sql, array(
			':time'	=> $del_before
		));
	}
}