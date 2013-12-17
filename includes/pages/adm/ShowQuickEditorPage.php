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
 * @info $Id: ShowQuickEditorPage.php 2632 2013-03-18 19:05:14Z slaver7 $
 * @link http://2moons.cc/
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__))) throw new Exception("Permission error!");

function ShowQuickEditorPage()
{
	global $USER, $LNG, $reslist, $resource, $pricelist;
	$action	= HTTP::_GP('action', '');
	$edit	= HTTP::_GP('edit', '');
	$id 	= HTTP::_GP('id', 0);

	switch($edit)
	{
		case 'planet':
			$DataIDs	= array_merge($reslist['fleet'], $reslist['build'], $reslist['defense']);
			foreach($DataIDs as $ID)
			{
				$SpecifyItemsPQ	.= "`".$resource[$ID]."`,";
			}
			$PlanetData	= $GLOBALS['DATABASE']->getFirstRow("SELECT ".$SpecifyItemsPQ." `name`, `id_owner`, `planet_type`, `galaxy`, `system`, `planet`, `destruyed`, `diameter`, `field_current`, `field_max`, `temp_min`, `temp_max`, `metal`, `crystal`, `deuterium` FROM ".PLANETS." WHERE `id` = '".$id."';");
						
			if($action == 'send'){
				$SQL	= "UPDATE ".PLANETS." SET ";
				$Fields	= $PlanetData['field_current'];
				foreach($DataIDs as $ID)
				{
					$level	= min(max(0, round(HTTP::_GP($resource[$ID], 0.0))), (in_array($ID, $reslist['build']) ? 255: 18446744073709551615));
				
					if(in_array($ID, $reslist['allow'][$PlanetData['planet_type']]))
					{
						$Fields	+= $level - $PlanetData[$resource[$ID]];
					}
					
					$SQL	.= "`".$resource[$ID]."` = ".$level.", ";
				}
				
				$SQL	.= "`metal` = ".max(0, round(HTTP::_GP('metal', 0.0))).", ";
				$SQL	.= "`crystal` = ".max(0, round(HTTP::_GP('crystal', 0.0))).", ";
				$SQL	.= "`deuterium` = ".max(0, round(HTTP::_GP('deuterium', 0.0))).", ";
				$SQL	.= "`field_current` = '".$Fields."', ";
				$SQL	.= "`field_max` = '".HTTP::_GP('field_max', 0)."', ";
				$SQL	.= "`name` = '".$GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT))."', ";
				$SQL	.= "`eco_hash` = '' ";
				$SQL	.= "WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';";
					
				$GLOBALS['DATABASE']->query($SQL);
				
				$old = array();
				$new = array();
                foreach(array_merge($DataIDs,$reslist['resstype'][1]) as $IDs)
                {
                    $old[$IDs]    = $PlanetData[$resource[$IDs]];
					$new[$IDs]    = max(0, round(HTTP::_GP($resource[$IDs], 0.0)));
                }
				$old['field_max'] = $PlanetData['field_max'];
				$new['field_max'] = HTTP::_GP('field_max', 0);
				$LOG = new Log(2);
				$LOG->target = $id;
				$LOG->old = $old;
				$LOG->new = $new;
				$LOG->save();
		
				exit(sprintf($LNG['qe_edit_planet_sucess'], $PlanetData['name'], $PlanetData['galaxy'], $PlanetData['system'], $PlanetData['planet']));
			}
			$UserInfo				= $GLOBALS['DATABASE']->getFirstRow("SELECT `username` FROM ".USERS." WHERE `id` = '".$PlanetData['id_owner']."' AND `universe` = '".$_SESSION['adminuni']."';");

			$build = $defense = $fleet	= array();
			
			foreach($reslist['allow'][$PlanetData['planet_type']] as $ID)
			{
				$build[]	= array(
					'type'	=> $resource[$ID],
					'name'	=> $LNG['tech'][$ID],
					'count'	=> pretty_number($PlanetData[$resource[$ID]]),
					'input'	=> $PlanetData[$resource[$ID]]
				);
			}
			
			foreach($reslist['fleet'] as $ID)
			{
				$fleet[]	= array(
					'type'	=> $resource[$ID],
					'name'	=> $LNG['tech'][$ID],
					'count'	=> pretty_number($PlanetData[$resource[$ID]]),
					'input'	=> $PlanetData[$resource[$ID]]
				);
			}
			
			foreach($reslist['defense'] as $ID)
			{
				$defense[]	= array(
					'type'	=> $resource[$ID],
					'name'	=> $LNG['tech'][$ID],
					'count'	=> pretty_number($PlanetData[$resource[$ID]]),
					'input'	=> $PlanetData[$resource[$ID]]
				);
			}

			$template	= new template();
			$template->assign_vars(array(	
				'build'			=> $build,
				'fleet'			=> $fleet,
				'defense'		=> $defense,
				'id'			=> $id,
				'ownerid'		=> $PlanetData['id_owner'],
				'ownername'		=> $UserInfo['username'],
				'name'			=> $PlanetData['name'],
				'galaxy'		=> $PlanetData['galaxy'],
				'system'		=> $PlanetData['system'],
				'planet'		=> $PlanetData['planet'],
				'field_min'		=> $PlanetData['field_current'],
				'field_max'		=> $PlanetData['field_max'],
				'temp_min'		=> $PlanetData['temp_min'],
				'temp_max'		=> $PlanetData['temp_max'],
				'metal'			=> floattostring($PlanetData['metal']),
				'crystal'		=> floattostring($PlanetData['crystal']),
				'deuterium'		=> floattostring($PlanetData['deuterium']),
				'metal_c'		=> pretty_number($PlanetData['metal']),
				'crystal_c'		=> pretty_number($PlanetData['crystal']),
				'deuterium_c'	=> pretty_number($PlanetData['deuterium']),
			));
			$template->show('QuickEditorPlanet.tpl');
		break;
		case 'player':
			$DataIDs	= array_merge($reslist['tech'], $reslist['officier']);
			foreach($DataIDs as $ID)
			{
				$SpecifyItemsPQ	.= "`".$resource[$ID]."`,";
			}
			$UserData	= $GLOBALS['DATABASE']->getFirstRow("SELECT ".$SpecifyItemsPQ." `username`, `authlevel`, `galaxy`, `system`, `planet`, `id_planet`, `darkmatter`, `authattack`, `authlevel` FROM ".USERS." WHERE `id` = '".$id."';");
			$ChangePW	= $USER['id'] == ROOT_USER || ($id != ROOT_USER && $USER['authlevel'] > $UserData['authlevel']);
		
			if($action == 'send'){
				$SQL	= "UPDATE ".USERS." SET ";
				foreach($DataIDs as $ID)
				{
					$SQL	.= "`".$resource[$ID]."` = ".min(abs(HTTP::_GP($resource[$ID], 0)), 255).", ";
				}
				$SQL	.= "`darkmatter` = '".max(HTTP::_GP('darkmatter', 0), 0)."', ";
				if(!empty($_POST['password']) && $ChangePW)
					$SQL	.= "`password` = '".cryptPassword(HTTP::_GP('password', '', true))."', ";
				$SQL	.= "`username` = '".$GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT))."', ";
				$SQL	.= "`authattack` = '".($UserData['authlevel'] != AUTH_USR && HTTP::_GP('authattack', '') == 'on' ? $UserData['authlevel'] : 0)."' ";
				$SQL	.= "WHERE `id` = '".$id."' AND `universe` = '".$_SESSION['adminuni']."';";
				$GLOBALS['DATABASE']->query($SQL);
				
				$old = array();
				$new = array();
				$multi	=  HTTP::_GP('multi', 0);
				foreach($DataIDs as $IDs)
                {
                    $old[$IDs]    = $UserData[$resource[$IDs]];
                    $new[$IDs]    = abs(HTTP::_GP($resource[$IDs], 0));
                }
				$old[921]			= $UserData[$resource[921]];
				$new[921]			= abs(HTTP::_GP($resource[921], 0));
				$old['username']	= $UserData['username'];
				$new['username']	= $GLOBALS['DATABASE']->sql_escape(HTTP::_GP('name', '', UTF8_SUPPORT));
				$old['authattack']	= $UserData['authattack'];
				$new['authattack']	= ($UserData['authlevel'] != AUTH_USR && HTTP::_GP('authattack', '') == 'on' ? $UserData['authlevel'] : 0);
				$old['multi']		= $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".MULTI." WHERE userID = ".$id.";");
				$new['authattack']	= $multi;
			
				if($old['multi'] != $multi)
				{
					if($multi == 0)
					{
						$GLOBALS['DATABASE']->query("DELETE FROM ".MULTI." WHERE userID = ".((int) $id).";");
					}
					elseif($multi == 1)
					{
						$GLOBALS['DATABASE']->query("INSERT INTO ".MULTI." SET userID = ".((int) $id).";");
					}
				}
				
				$LOG = new Log(1);
				$LOG->target = $id;
				$LOG->old = $old;
				$LOG->new = $new;
				$LOG->save();
				
				exit(sprintf($LNG['qe_edit_player_sucess'], $UserData['username'], $id));
			}
			$PlanetInfo				= $GLOBALS['DATABASE']->getFirstRow("SELECT `name` FROM ".PLANETS." WHERE `id` = '".$UserData['id_planet']."' AND `universe` = '".$_SESSION['adminuni']."';");

			$tech		= array();
			$officier	= array();
			
			foreach($reslist['tech'] as $ID)
			{
				$tech[]	= array(
					'type'	=> $resource[$ID],
					'name'	=> $LNG['tech'][$ID],
					'count'	=> pretty_number($UserData[$resource[$ID]]),
					'input'	=> $UserData[$resource[$ID]]
				);
			}
			foreach($reslist['officier'] as $ID)
			{
				$officier[]	= array(
					'type'	=> $resource[$ID],
					'name'	=> $LNG['tech'][$ID],
					'count'	=> pretty_number($UserData[$resource[$ID]]),
					'input'	=> $UserData[$resource[$ID]]
				);
			}

			$template	= new template();
			$template->assign_vars(array(	
				'tech'			=> $tech,
				'officier'		=> $officier,
				'id'			=> $id,
				'planetid'		=> $UserData['id_planet'],
				'planetname'	=> $PlanetInfo['name'],
				'name'			=> $UserData['username'],
				'galaxy'		=> $UserData['galaxy'],
				'system'		=> $UserData['system'],
				'planet'		=> $UserData['planet'],
				'authlevel'		=> $UserData['authlevel'],
				'authattack'	=> $UserData['authattack'],
				'multi'			=> $GLOBALS['DATABASE']->getFirstCell("SELECT COUNT(*) FROM ".MULTI." WHERE userID = ".$id.";"),
				'ChangePW'		=> $ChangePW,
				'darkmatter'	=> floattostring($UserData['darkmatter']),
				'darkmatter_c'	=> pretty_number($UserData['darkmatter']),
			));
			$template->show('QuickEditorUser.tpl');
		break;
	}
}