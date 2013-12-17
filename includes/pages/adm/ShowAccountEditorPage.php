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
 * @info $Id: ShowAccountEditorPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

# Actions not logged: Planet-Edit, Alliance-Edit 

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");

function ShowAccountEditorPage() 
{
	global $USER, $LNG, $reslist, $resource, $UNI;
	$template 	= new template();

	switch($_GET['edit'])
	{
		case 'resources':
			$id         = HTTP::_GP('id', 0);
			$id_dark    = HTTP::_GP('id_dark', 0);
			$metal      = max(0, round(HTTP::_GP('metal', 0.0)));
			$cristal    = max(0, round(HTTP::_GP('cristal', 0.0)));
			$deut       = max(0, round(HTTP::_GP('deut', 0.0)));
			$dark		= HTTP::_GP('dark', 0);

			if ($_POST)
			{
				if (!empty($id))
					$before = $GLOBALS['DATABASE']->getFirstRow("SELECT `metal`,`crystal`,`deuterium`,`universe`  FROM ".PLANETS." WHERE `id` = '". $id ."';");
				if (!empty($id_dark))
					$before_dm = $GLOBALS['DATABASE']->getFirstRow("SELECT `darkmatter` FROM ".USERS." WHERE `id` = '". $id_dark ."';");
				if ($_POST['add'])
				{
					if (!empty($id)) {
						$SQL  = "UPDATE ".PLANETS." SET ";
						$SQL .= "`metal` = `metal` + '".$metal."', ";
						$SQL .= "`crystal` = `crystal` + '".$cristal."', ";
						$SQL .= "`deuterium` = `deuterium` + '".$deut ."' ";
						$SQL .= "WHERE ";
						$SQL .= "`id` = '". $id ."' AND `universe` = '".$_SESSION['adminuni']."';";
						$GLOBALS['DATABASE']->query($SQL);
						$after 		= array('metal' => ($before['metal'] + $metal), 'crystal' => ($before['crystal'] + $cristal), 'deuterium' => ($before['deuterium'] + $deut));
					}
					
					if (!empty($id_dark)) {
						$SQL  = "UPDATE ".USERS." SET ";
						$SQL .= "`darkmatter` = `darkmatter` + '". $dark ."' ";
						$SQL .= "WHERE ";
						$SQL .= "`id` = '". $id_dark ."' AND `universe` = '".$_SESSION['adminuni']."' ";
						$GLOBALS['DATABASE']->query($SQL);
						$after_dm 	= array('darkmatter' => ($before_dm['darkmatter'] + $dark));
					}
				}
				elseif ($_POST['delete'])
				{
					if (!empty($id)) {
						$SQL  = "UPDATE ".PLANETS." SET ";
						$SQL .= "`metal` = `metal` - '". $metal ."', ";
						$SQL .= "`crystal` = `crystal` - '". $cristal ."', ";
						$SQL .= "`deuterium` = `deuterium` - '". $deut ."' ";
						$SQL .= "WHERE ";
						$SQL .= "`id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';";
						$GLOBALS['DATABASE']->query($SQL);
						$after 		= array('metal' => ($before['metal'] - $metal), 'crystal' => ($before['crystal'] - $cristal), 'deuterium' => ($before['deuterium'] - $deut));
					}

					if (!empty($id_dark)) {
						$SQL  = "UPDATE ".USERS." SET ";
						$SQL .= "`darkmatter` = `darkmatter` - '". $dark ."' ";
						$SQL .= "WHERE ";
						$SQL .= "`id` = '". $id_dark ."';";
						$GLOBALS['DATABASE']->query($SQL);
						$after_dm 	= array('darkmatter' => ($before_dm['darkmatter'] - $dark));
					}
				}
								
				if (!empty($id)) {
					$LOG = new Log(2);
					$LOG->target = $id;
					$LOG->universe = $before_dm['universe'];
					$LOG->old = $before;
					$LOG->new = $after;
					$LOG->save();
				}
				
				if (!empty($id_dark)) {
					$LOG = new Log(1);
					$LOG->target = $id_dark;
					$LOG->universe = $before_dm['universe'];
					$LOG->old = $before_dm;
					$LOG->new = $after_dm;
					$LOG->save();
				}

				if ($_POST['add']) {
					$template->message($LNG['ad_add_res_sucess'], '?page=accounteditor&edit=resources');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_res_sucess'], '?page=accounteditor&edit=resources');
				}
				exit;
			}
			
			
			$template->show('AccountEditorPageResources.tpl');
		break;
		case 'ships':
			if($_POST)
			{
				$before1 = $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".PLANETS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				$before = array();
				$after = array();
				foreach($reslist['fleet'] as $ID)
				{
					$before[$ID] = $before1[$resource[$ID]];
				}
				if ($_POST['add'])
				{
					$SQL  = "UPDATE ".PLANETS." SET ";
					$SQL .= "`eco_hash` = '', ";
					foreach($reslist['fleet'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` + '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = $before[$ID] + max(0, round(HTTP::_GP($resource[$ID], 0.0)));
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				elseif ($_POST['delete'])
				{
					$SQL  = "UPDATE ".PLANETS." SET ";
					$SQL .= "`eco_hash` = '', ";
					foreach($reslist['fleet'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` - '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = max($before[$ID] - max(0, round(HTTP::_GP($resource[$ID], 0.0))),0);
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				
				$LOG = new Log(2);
				$LOG->target = HTTP::_GP('id', 0);
				$LOG->universe = $before1['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();

				if ($_POST['add']) {
					$template->message($LNG['ad_add_ships_sucess'], '?page=accounteditor&edit=ships');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_ships_sucess'], '?page=accounteditor&edit=ships');
				}
				exit;
			}

			$parse['ships']	= "";
			foreach($reslist['fleet'] as $ID)
			{
				$INPUT[$ID]	= array(
					'type'	=> $resource[$ID],
				);
			}

			$template->assign_vars(array(
				'inputlist'			=> $INPUT,
			));
						
			$template->show('AccountEditorPageShips.tpl');
		break;

		case 'defenses':
			if($_POST)
			{
				$before1 = $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".PLANETS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				$before = array();
				$after = array();
				foreach($reslist['defense'] as $ID)
				{
					$before[$ID] = $before1[$resource[$ID]];
				}
				if ($_POST['add'])
				{
					$SQL  = "UPDATE ".PLANETS." SET ";
					foreach($reslist['defense'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` + '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = $before[$ID] + max(0, round(HTTP::_GP($resource[$ID], 0.0)));
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				elseif ($_POST['delete'])
				{
					$SQL  = "UPDATE ".PLANETS." SET ";
					foreach($reslist['defense'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` - '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = max($before[$ID] - max(0, round(HTTP::_GP($resource[$ID], 0.0))),0);
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query( $SQL);
					$Name	=	$LNG['log_nomoree'];
				}
				
				$LOG = new Log(2);
				$LOG->target = HTTP::_GP('id', 0);
				$LOG->universe = $before1['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();

				if ($_POST['add']) {
					$template->message($LNG['ad_add_defenses_success'], '?page=accounteditor&edit=defenses');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_defenses_success'], '?page=accounteditor&edit=defenses');
				}
				exit;
			}
			
			foreach($reslist['defense'] as $ID)
			{
				$INPUT[$ID]	= array(
					'type'	=> $resource[$ID],
				);
			}

			$template->assign_vars(array(
				'inputlist'			=> $INPUT,
			));
						
			$template->show('AccountEditorPageDefenses.tpl');
		break;
		break;

		case 'buildings':
			if($_POST)
			{
				$PlanetData = $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".PLANETS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				if(!isset($PlanetData))
				{
					$template->message($LNG['ad_add_not_exist'], '?page=accounteditor&edit=buildings');
				}
				$before = array();
				$after = array();
				foreach($reslist['allow'][$PlanetData['planet_type']] as $ID)
				{
					$before[$ID] = $PlanetData[$resource[$ID]];
				}
				if ($_POST['add'])
				{
					$Fields	= 0;
					$SQL  = "UPDATE ".PLANETS." SET ";
					$SQL .= "`eco_hash` = '', ";
					foreach($reslist['allow'][$PlanetData['planet_type']] as $ID)
					{
						$Count			= max(0, round(HTTP::_GP($resource[$ID], 0.0)));
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` + '".$Count."'";
						$after[$ID] 	= $before[$ID] + $Count;
						$Fields			+= $Count;
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= ", `field_current` = `field_current` + '".$Fields."'";
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				elseif ($_POST['delete'])
				{
					$Fields	= 0;
					$SQL  = "UPDATE ".PLANETS." SET ";
					$SQL .= "`eco_hash` = '', ";
					foreach($reslist['allow'][$PlanetData['planet_type']] as $ID)
					{
						$Count			= max(0, round(HTTP::_GP($resource[$ID], 0.0)));
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` - '".$Count."'";
						$after[$ID]		= max($before[$ID] - $Count,0);
						$Fields			+= $Count;
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= ", `field_current` = `field_current` - '".$Fields."'";
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				
				$LOG = new Log(2);
				$LOG->target = HTTP::_GP('id', 0);
				$LOG->universe = $before1['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();

				if ($_POST['add']) {
					$template->message($LNG['ad_add_build_success'], '?page=accounteditor&edit=buildings');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_build_success'], '?page=accounteditor&edit=buildings');
				}
				exit;
			}
			
			foreach($reslist['build'] as $ID)
			{
				$INPUT[$ID]	= array(
					'type'	=> $resource[$ID],
				);
			}

			$template->assign_vars(array(
				'inputlist'			=> $INPUT,
			));
						
			$template->show('AccountEditorPageBuilds.tpl');
		break;

		case 'researchs':
			if($_POST)
			{
				$before1 = $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".USERS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				$before = array();
				$after = array();
				foreach($reslist['tech'] as $ID)
				{
					$before[$ID] = $before1[$resource[$ID]];
				}
				if ($_POST['add'])
				{
					$SQL  = "UPDATE ".USERS." SET ";
					foreach($reslist['tech'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` + '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = $before[$ID] + max(0, round(HTTP::_GP($resource[$ID], 0.0)));
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				elseif ($_POST['delete'])
				{
					$SQL  = "UPDATE ".USERS." SET ";
					foreach($reslist['tech'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` - '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = max($before[$ID] - max(0, round(HTTP::_GP($resource[$ID], 0.0))),0);
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				
				$LOG = new Log(1);
				$LOG->target = HTTP::_GP('id', 0);
				$LOG->universe = $before1['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();
				
				if ($_POST['add']) {
					$template->message($LNG['ad_add_tech_success'], '?page=accounteditor&edit=researchs');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_tech_success'], '?page=accounteditor&edit=researchs');
				}
				exit;
			}
			
			foreach($reslist['tech'] as $ID)
			{
				$INPUT[$ID]	= array(
					'type'	=> $resource[$ID],
				);
			}

			$template->assign_vars(array(
				'inputlist'			=> $INPUT,
			));
						
			$template->show('AccountEditorPageResearch.tpl');
		break;
		case 'personal':
			if ($_POST)
			{
				$id			= HTTP::_GP('id', 0);				
				$username	= HTTP::_GP('username', '', UTF8_SUPPORT);				
				$password	= HTTP::_GP('password', '', true);				
				$email		= HTTP::_GP('email', '');				
				$email_2	= HTTP::_GP('email_2', '');				
				$vacation	= HTTP::_GP('vacation', '');				
				
				$before = $GLOBALS['DATABASE']->getFirstRow("SELECT `username`,`email`,`email_2`,`password`,`urlaubs_modus`,`urlaubs_until` FROM ".USERS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				$after = array();
				
				$PersonalQuery    =    "UPDATE ".USERS." SET ";

				if(!empty($username) && $id != ROOT_USER) {
					$PersonalQuery    .= "`username` = '".$GLOBALS['DATABASE']->sql_escape($username)."', ";
					$after['username'] = $username;
				}
				
				if(!empty($email) && $id != ROOT_USER) {
					$PersonalQuery    .= "`email` = '".$GLOBALS['DATABASE']->sql_escape($email)."', ";
					$after['email'] = $email;
				}
				
				if(!empty($email_2) && $id != ROOT_USER) {
					$PersonalQuery    .= "`email_2` = '".$GLOBALS['DATABASE']->sql_escape($email_2)."', ";
					$after['email_2'] = $email_2;
				}

				if(!empty($password) && $id != ROOT_USER) {
					$PersonalQuery    .= "`password` = '".$GLOBALS['DATABASE']->sql_escape(cryptPassword($password))."', ";
					$after['password'] = (cryptPassword($password) != $before['password']) ? 'CHANGED' : '';
				}
				$before['password'] = '';

				$Answer		= 0;
				$TimeAns	= 0;
				
				if ($vacation == 'yes') {
					$Answer		= 1;
					$after['urlaubs_modus'] = 1;
					$TimeAns    = TIMESTAMP + $_POST['d'] * 86400 + $_POST['h'] * 3600 + $_POST['m'] * 60 + $_POST['s'];
					$after['urlaubs_until'] = $TimeAns;
				}
				
				$PersonalQuery    .=  "`urlaubs_modus` = '".$Answer."', `urlaubs_until` = '".$TimeAns."' ";			
				$PersonalQuery    .= "WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."'";
				$GLOBALS['DATABASE']->query($PersonalQuery);
				
				$LOG = new Log(1);
				$LOG->target = $id;
				$LOG->universe = $before['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();
				
				$template->message($LNG['ad_personal_succes'], '?page=accounteditor&edit=personal');
				exit;
			}
			
			$template->assign_vars(array(
				'Selector'				=> array(''	=> $LNG['select_option'], 'yes' => $LNG['one_is_yes'][1], 'no' => $LNG['one_is_yes'][0]),
			));
						
			$template->show('AccountEditorPagePersonal.tpl');
		break;

		case 'officiers':
			if($_POST)
			{
				$before1 = $GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".USERS." WHERE `id` = '". HTTP::_GP('id', 0) ."';");
				$before = array();
				$after = array();
				foreach($reslist['officier'] as $ID)
				{
					$before[$ID] = $before1[$resource[$ID]];
				}
				if ($_POST['add'])
				{
					$SQL  = "UPDATE ".USERS." SET ";
					foreach($reslist['officier'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` + '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = $before[$ID] + max(0, round(HTTP::_GP($resource[$ID], 0.0)));
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				elseif ($_POST['delete'])
				{
					$SQL  = "UPDATE ".USERS." SET ";
					foreach($reslist['officier'] as $ID)
					{
						$QryUpdate[]	= "`".$resource[$ID]."` = `".$resource[$ID]."` - '".max(0, round(HTTP::_GP($resource[$ID], 0.0)))."'";
						$after[$ID] = max($before[$ID] - max(0, round(HTTP::_GP($resource[$ID], 0.0))),0);
					}
					$SQL .= implode(", ", $QryUpdate);
					$SQL .= "WHERE ";
					$SQL .= "`id` = '".HTTP::_GP('id', 0)."' AND `universe` = '".$_SESSION['adminuni']."';";
					$GLOBALS['DATABASE']->query($SQL);
				}
				
				$LOG = new Log(1);
				$LOG->target = HTTP::_GP('id', 0);
				$LOG->universe = $before1['universe'];
				$LOG->old = $before;
				$LOG->new = $after;
				$LOG->save();
				
				if ($_POST['add']) {
					$template->message($LNG['ad_add_offi_success'], '?page=accounteditor&edit=officiers');
				} else if ($_POST['delete']) {
					$template->message($LNG['ad_delete_offi_success'], '?page=accounteditor&edit=officiers');
				}
				exit;
			}
			
			foreach($reslist['officier'] as $ID)
			{
				$INPUT[$ID]	= array(
					'type'	=> $resource[$ID],
				);
			}

			$template->assign_vars(array(
				'inputlist'			=> $INPUT,
			));
						
			$template->show('AccountEditorPageOfficiers.tpl');
		break;

		case 'planets':
			if ($_POST)
			{
				$id				= HTTP::_GP('id', 0);
				$name			= HTTP::_GP('name', '', UTF8_SUPPORT);
				$diameter		= HTTP::_GP('diameter', 0);
				$fields			= HTTP::_GP('fields', 0);
				$buildings		= HTTP::_GP('0_buildings', '');
				$ships			= HTTP::_GP('0_ships', '');
				$defenses		= HTTP::_GP('0_defenses', '');
				$c_hangar		= HTTP::_GP('0_c_hangar', '');
				$c_buildings	= HTTP::_GP('0_c_buildings', '');
				$change_pos		= HTTP::_GP('change_position', '');
				$galaxy			= HTTP::_GP('g', 0);
				$system			= HTTP::_GP('s', 0);
				$planet			= HTTP::_GP('p', 0);

				if (!empty($name))
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `name` = '".$GLOBALS['DATABASE']->sql_escape($name)."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
						
				if ($buildings == 'on')
				{
					foreach($reslist['build'] as $ID) {
						$BUILD[]	= "`".$resource[$ID]."` = '0'";
					}
						
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET ".implode(', ',$BUILD)." WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
				}
					
				if ($ships == 'on')
				{
					foreach($reslist['fleet'] as $ID) {
						$SHIPS[]	= "`".$resource[$ID]."` = '0'";
					}
					
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET ".implode(', ',$SHIPS)." WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
				}
						
				if ($defenses == 'on')
				{
					foreach($reslist['defense'] as $ID) {
						$DEFS[]	= "`".$resource[$ID]."` = '0'";
					}
				
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET ".implode(', ',$DEFS)." WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
				}

				if ($c_hangar == 'on')
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `b_hangar` = '0', `b_hangar_plus` = '0', `b_hangar_id` = '' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");

				if ($c_buildings == 'on')
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `b_building` = '0', `b_building_id` = '' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");

				if (!empty($diameter))
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `diameter` = '".$diameter."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");

				if (!empty($fields))
					$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `field_max` = '".$fields."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
						
				if ($change_pos == 'on' && $galaxy > 0 && $system > 0 && $planet > 0 && $galaxy <= $GLOBALS['CONFIG'][$_SESSION['adminuni']]['max_galaxy'] && $system <= $GLOBALS['CONFIG'][$_SESSION['adminuni']]['max_system'] && $planet <= $GLOBALS['CONFIG'][$_SESSION['adminuni']]['max_planets'])
				{
					$P	=	$GLOBALS['DATABASE']->getFirstRow("SELECT galaxy,system,planet,planet_type FROM ".PLANETS." WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
					if ($P['planet_type'] == '1')
					{
						if (CheckPlanetIfExist($galaxy, $system, $planet, $UNI, $P['planet_type']))
						{
							$template->message($LNG['ad_pla_error_planets3'], '?page=accounteditor&edit=planets');
							exit;
						}

						$GLOBALS['DATABASE']->query ("UPDATE ".PLANETS." SET `galaxy` = '".$galaxy."', `system` = '".$system."', `planet` = '".$planet."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");

					} else {
						if(CheckPlanetIfExist($galaxy, $system, $planet, $UNI, $P['planet_type']))
						{
							$template->message($LNG['ad_pla_error_planets5'], '?page=accounteditor&edit=planets');
							exit;
						}
						
						$Target	= $GLOBALS['DATABASE']->getFirstRow("SELECT id_luna FROM ".PLANETS." WHERE `galaxy` = '".$galaxy."' AND `system` = '".$system."' AND `planet` = '".$planet."' AND `planet_type` = '1';");
								
						if ($Target['id_luna'] != '0')
						{
							$template->message($LNG['ad_pla_error_planets4'], '?page=accounteditor&edit=planets');
							exit;
						}
							
						$GLOBALS['DATABASE']->multi_query("UPDATE ".PLANETS." SET `id_luna` = '0' WHERE `galaxy` = '".$P['galaxy']."' AND `system` = '".$P['system']."' AND `planet` = '".$P['planet']."' AND `planet_type` = '1';UPDATE ".PLANETS." SET `id_luna` = '".$id."'  WHERE `galaxy` = '".$galaxy."' AND `system` = '".$system."' AND `planet` = '".$planet."' AND planet_type = '1';UPDATE ".PLANETS." SET `galaxy` = '".$galaxy."', `system` = '".$system."', `planet` = '".$planet."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';");
						
						$QMOON2	=	$GLOBALS['DATABASE']->getFirstRow("SELECT id_owner FROM ".PLANETS." WHERE `galaxy` = '".$galaxy."' AND `system` = '".$system."' AND `planet` = '".$planet."';");
						$GLOBALS['DATABASE']->query("UPDATE ".PLANETS." SET `galaxy` = '".$galaxy."', `system` = '".$system."', `planet` = '".$planet."', `id_owner` = '".$QMOON2['id_owner']."' WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."' AND `planet_type` = '3';");
					}
				}

				$template->message($LNG['ad_pla_succes'], '?page=accounteditor&edit=planets');
				exit;
			}
			
			$template->show('AccountEditorPagePlanets.tpl');
		break;

		case 'alliances':
			if ($_POST)
			{
				$id				=	HTTP::_GP('id', 0);
				$name			=	HTTP::_GP('name', '', UTF8_SUPPORT);
				$changeleader	=	HTTP::_GP('changeleader', 0);
				$tag			=	HTTP::_GP('tag', '', UTF8_SUPPORT);
				$externo		=	HTTP::_GP('externo', '', true);
				$interno		=	HTTP::_GP('interno', '', true);
				$solicitud		=	HTTP::_GP('solicitud', '', true);
				$delete			=	HTTP::_GP('delete', '');
				$delete_u		=	HTTP::_GP('delete_u', '');

				$QueryF	=	$GLOBALS['DATABASE']->getFirstRow("SELECT * FROM ".ALLIANCE." WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");

				if (!empty($name))
					$GLOBALS['DATABASE']->query("UPDATE ".ALLIANCE." SET `ally_name` = '".$name."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");

				if (!empty($tag))
					$GLOBALS['DATABASE']->query("UPDATE ".ALLIANCE." SET `ally_tag` = '".$tag."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");

				$QueryF2	=	$GLOBALS['DATABASE']->getFirstRow("SELECT ally_id FROM ".USERS." WHERE `id` = '".$changeleader."';");
				$GLOBALS['DATABASE']->multi_query("UPDATE ".ALLIANCE." SET `ally_owner` = '".$changeleader."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';UPDATE ".USERS." SET `ally_rank_id` = '0' WHERE `id` = '".$changeleader."';");
						
				if (!empty($externo))
					$GLOBALS['DATABASE']->query("UPDATE ".ALLIANCE." SET `ally_description` = '".$externo."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");
				
				if (!empty($interno))
					$GLOBALS['DATABASE']->query("UPDATE ".ALLIANCE." SET `ally_text` = '".$interno."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");
					
				if (!empty($solicitud))
					$GLOBALS['DATABASE']->query("UPDATE ".ALLIANCE." SET `ally_request` = '".$solicitud."' WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';");
				
				if ($delete == 'on')
				{
					$GLOBALS['DATABASE']->multi_query("DELETE FROM ".ALLIANCE." WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';UPDATE ".USERS." SET `ally_id` = '0', `ally_rank_id` = '0', `ally_register_time` = '0' WHERE `ally_id` = '".$id."';");
				}

				if (!empty($delete_u))
				{
					$GLOBALS['DATABASE']->multi_query("UPDATE ".ALLIANCE." SET `ally_members` = ally_members - 1 WHERE `id` = '".$id."' AND `ally_universe` = '".$_SESSION['adminuni']."';UPDATE ".USERS." SET `ally_id` = '0', `ally_rank_id` = '0', `ally_register_time` = '0' WHERE `id` = '".$delete_u."' AND `ally_id` = '".$id."';");
				}


				$template->message($LNG['ad_ally_succes'], '?page=accounteditor&edit=alliances');
				exit;
			}
			
			$template->show('AccountEditorPageAlliance.tpl');
		break;

		default:
			$template->show('AccountEditorPageMenu.tpl');
		break;
	}
}