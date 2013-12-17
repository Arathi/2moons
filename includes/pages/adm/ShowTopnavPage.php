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
 * @info $Id: ShowTopnavPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

function ShowTopnavPage()
{
	global $LNG, $USER, $UNI, $CONF;
	$template	= new template();

	$AvailableUnis[Config::get('uni')]	= Config::get('uni_name').' (ID: '.Config::get('uni').')';
	$Query	= $GLOBALS['DATABASE']->query("SELECT `uni`, `uni_name` FROM ".CONFIG." WHERE `uni` != '".$UNI."' ORDER BY `uni` DESC;");
	while($Unis	= $GLOBALS['DATABASE']->fetch_array($Query)) {
		$AvailableUnis[$Unis['uni']]	= $Unis['uni_name'].' (ID: '.$Unis['uni'].')';
	}
	ksort($AvailableUnis);
	$template->assign_vars(array(	
		'ad_authlevel_title'	=> $LNG['ad_authlevel_title'],
		're_reset_universe'		=> $LNG['re_reset_universe'],
		'mu_universe'			=> $LNG['mu_universe'],
		'mu_moderation_page'	=> $LNG['mu_moderation_page'],
		'adm_cp_title'			=> $LNG['adm_cp_title'],
		'adm_cp_index'			=> $LNG['adm_cp_index'],
		'adm_cp_logout'			=> $LNG['adm_cp_logout'],
		'sid'					=> session_id(),
		'id'					=> $USER['id'],
		'authlevel'				=> $USER['authlevel'],
		'AvailableUnis'			=> $AvailableUnis,
		'UNI'					=> $_SESSION['adminuni'],
	));
	
	$template->show('ShowTopnavPage.tpl');
}