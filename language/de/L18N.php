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
 * @info $Id: L18N.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

setlocale(LC_ALL, 'de_DE', 'german'); // http://msdn.microsoft.com/en-us/library/39cwe7zf%28vs.71%29.aspx
setlocale(LC_NUMERIC, 'C');

//SERVER GENERALS
$LNG['dir']         	= 'ltr';
$LNG['week_day']		= array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'); # Start with So!
$LNG['months']			= array('Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');
$LNG['js_tdformat']		= '[M] [D] [d] [H]:[i]:[s]';
$LNG['php_timeformat']	= 'H:i:s';
$LNG['php_dateformat']	= 'd. M Y';
$LNG['php_tdformat']	= 'd. M Y, H:i:s';
$LNG['short_day']		= 'd';
$LNG['short_hour']		= 'h';
$LNG['short_minute']	= 'm';
$LNG['short_second']	= 's';

//Note for the translators, use the phpBB Translation file (LANG/common.php) instead of your own translations

$LNG['timezones']		= array(
	'-12'	=> '[UTC - 12] Baker Island Time',
	'-11'	=> '[UTC - 11] Niue Time, Samoa Standard Time',
	'-10'	=> '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time',
	'-9.5'	=> '[UTC - 9:30] Marquesas Islands Time',
	'-9'	=> '[UTC - 9] Alaska Standard Time, Gambier Island Time',
	'-8'	=> '[UTC - 8] Pacific Standard Time',
	'-7'	=> '[UTC - 7] Mountain Standard Time',
	'-6'	=> '[UTC - 6] Central Standard Time',
	'-5'	=> '[UTC - 5] Eastern Standard Time',
	'-4.5'	=> '[UTC - 4:30] Venezuelan Standard Time',
	'-4'	=> '[UTC - 4] Atlantic Standard Time',
	'-3.5'	=> '[UTC - 3:30] Newfoundland Standard Time',
	'-3'	=> '[UTC - 3] Amazon Standard Time, Central Greenland Time',
	'-2'	=> '[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time',
	'-1'	=> '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time',
	'0'		=> '[UTC] Westeuropäische Zeit, Greenwich Mean Time',
	'1'		=> '[UTC + 1] Mitteleuropäische Zeit, West African Time',
	'2'		=> '[UTC + 2] Osteuropäische Zeit, Central African Time',
	'3'		=> '[UTC + 3] Moscow Standard Time, Eastern African Time',
	'3.5'	=> '[UTC + 3:30] Iran Standard Time',
	'4'		=> '[UTC + 4] Gulf Standard Time, Samara Standard Time',
	'4.5'	=> '[UTC + 4:30] Afghanistan Time',
	'5'		=> '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time',
	'5.5'	=> '[UTC + 5:30] Indian Standard Time, Sri Lanka Time',
	'5.75'	=> '[UTC + 5:45] Nepal Time',
	'6'		=> '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time',
	'6.5'	=> '[UTC + 6:30] Cocos Islands Time, Myanmar Time',
	'7'		=> '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time',
	'8'		=> '[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time',
	'8.75'	=> '[UTC + 8:45] Southeastern Western Australia Standard Time',
	'9'		=> '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time',
	'9.5'	=> '[UTC + 9:30] Australian Central Standard Time',
	'10'	=> '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time',
	'10.5'	=> '[UTC + 10:30] Lord Howe Standard Time',
	'11'	=> '[UTC + 11] Solomon Island Time, Magadan Standard Time',
	'11.5'	=> '[UTC + 11:30] Norfolk Island Time',
	'12'	=> '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time',
	'12.75'	=> '[UTC + 12:45] Chatham Islands Time',
	'13'	=> '[UTC + 13] Tonga Time, Phoenix Islands Time',
	'14'	=> '[UTC + 14] Line Island Time',
);