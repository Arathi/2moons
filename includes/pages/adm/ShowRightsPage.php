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
 * @info $Id: ShowRightsPage.php 2640 2013-03-23 19:23:26Z slaver7 $
 * @link http://2moons.cc/
 */

if (!allowedTo(str_replace(array(dirname(__FILE__), '\\', '/', '.php'), '', __FILE__)) || $_GET['sid'] != session_id()) exit;
function ShowRightsPage()
{
	global $LNG, $CONF, $USER;
	$mode	= HTTP::_GP('mode', '');
	switch($mode)
	{
		case 'rights':

			$template	= new template();
			$template->loadscript('filterlist.js');
			
			if ($_POST)
			{
				$id			= HTTP::_GP('id_1', 0);
				
				if($USER['id'] != ROOT_USER && $id == ROOT_USER) {
					$template->message($LNG['ad_authlevel_error_3'], '?page=rights&mode=rights&sid='.session_id());
					exit;
				}
				
				if(!isset($_POST['rights'])) {
					$_POST['rights']	= array();
				}
				
				if($_POST['action'] == 'send') {
					$GLOBALS['DATABASE']->query("UPDATE ".USERS." SET `rights` = '".serialize(array_map('intval', $_POST['rights']))."' WHERE `id` = '".$id."';");
				}
				
				$Rights	= $GLOBALS['DATABASE']->getFirstRow("SELECT rights FROM ".USERS." WHERE `id` = '".$id."';");
				if(($Rights['rights'] = unserialize($Rights['rights'])) === false) {
					$Rights['rights']	= array();
				}
				
				$Files	= array_map('prepare', array_diff(scandir('includes/pages/adm/'), array('.', '..', '.svn', 'index.html', '.htaccess', 'ShowIndexPage.php', 'ShowOverviewPage.php', 'ShowMenuPage.php', 'ShowTopnavPage.php')));
				
				$template->assign_vars(array(	
					'Files'						=> $Files, 
					'Rights'					=> $Rights['rights'], 
					'id'						=> $id, 
					'yesorno'					=> $LNG['one_is_yes'], 
					'ad_authlevel_title'		=> $LNG['ad_authlevel_title'], 
					'button_submit'				=> $LNG['button_submit'],  
					'sid'						=> session_id(), 
				));
				
				$template->show('ModerrationRightsPostPage.tpl');		
				exit;
			}
							
			if ($_GET['get'] == 'adm')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_ADM."'";
			elseif ($_GET['get'] == 'ope')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_OPS."'";
			elseif ($_GET['get'] == 'mod')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_MOD."'";
			elseif ($_GET['get'] == 'pla')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_USR."'";			
				
				
			$QueryUsers	=	$GLOBALS['DATABASE']->query("SELECT `id`, `username`, `authlevel` FROM ".USERS." WHERE `universe` = '".$_SESSION['adminuni']."'".$WHEREUSERS.";");
				
			$UserList	= "";
			while ($List = $GLOBALS['DATABASE']->fetch_array($QueryUsers)) {
				$UserList	.=	'<option value="'.$List['id'].'">'.$List['username'].'&nbsp;&nbsp;('.$LNG['rank'][$List['authlevel']].')</option>';
			}	

			$template->assign_vars(array(	
				'Selector'					=> array(0 => $LNG['rank'][0], 1 => $LNG['rank'][1], 2 => $LNG['rank'][2], 3 => $LNG['rank'][3]), 
				'UserList'					=> $UserList, 
				'ad_authlevel_title'		=> $LNG['ad_authlevel_title'], 
				'bo_select_title'			=> $LNG['bo_select_title'], 
				'button_submit'				=> $LNG['button_submit'], 
				'button_deselect'			=> $LNG['button_deselect'], 
				'button_filter'				=> $LNG['button_filter'], 
				'ad_authlevel_insert_id'	=> $LNG['ad_authlevel_insert_id'], 
				'ad_authlevel_auth'			=> $LNG['ad_authlevel_auth'], 
				'ad_authlevel_aa'			=> $LNG['ad_authlevel_aa'], 
				'ad_authlevel_oo'			=> $LNG['ad_authlevel_oo'], 
				'ad_authlevel_mm'			=> $LNG['ad_authlevel_mm'], 
				'ad_authlevel_jj'			=> $LNG['ad_authlevel_jj'], 
				'ad_authlevel_tt'			=> $LNG['ad_authlevel_tt'], 
				'sid'						=> session_id(), 
			));
	
			$template->show('ModerrationRightsPage.tpl');
		break;
		case 'users':
			$template	= new template();
			$template->loadscript('filterlist.js');
			
			if ($_POST)
			{
				$id			= HTTP::_GP('id_1', 0);
				$authlevel	= HTTP::_GP('authlevel', 0);
				
				if($id == 0)
					$id	= HTTP::_GP('id_2', 0);
					
				if($USER['id'] != ROOT_USER && $id == ROOT_USER) {
					$template->message($LNG['ad_authlevel_error_3'], '?page=rights&mode=users&sid='.session_id());
					exit;
				}	
				
				$GLOBALS['DATABASE']->multi_query("UPDATE ".USERS." SET `authlevel` = '".HTTP::_GP('authlevel', 0)."' WHERE `id` = '".$id."';");
				$template->message($LNG['ad_authlevel_succes'], '?page=rights&mode=users&sid='.session_id());
				exit;
			}
							
			if ($_GET['get'] == 'adm')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_ADM."'";
			elseif ($_GET['get'] == 'ope')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_OPS."'";
			elseif ($_GET['get'] == 'mod')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_MOD."'";
			elseif ($_GET['get'] == 'pla')
				$WHEREUSERS	=	"AND `authlevel` = '".AUTH_USR."'";	
				
			$QueryUsers	=	$GLOBALS['DATABASE']->query("SELECT `id`, `username`, `authlevel` FROM ".USERS." WHERE `universe` = '".$_SESSION['adminuni']."'".$WHEREUSERS.";");
				
			$UserList	= "";
			while ($List = $GLOBALS['DATABASE']->fetch_array($QueryUsers)) {
				$UserList	.=	'<option value="'.$List['id'].'">'.$List['username'].'&nbsp;&nbsp;('.$LNG['rank'][$List['authlevel']].')</option>';
			}	

			$template->assign_vars(array(	
				'Selector'					=> array(0 => $LNG['rank'][0], 1 => $LNG['rank'][1], 2 => $LNG['rank'][2], 3 => $LNG['rank'][3]), 
				'UserList'					=> $UserList, 
				'ad_authlevel_title'		=> $LNG['ad_authlevel_title'], 
				'bo_select_title'			=> $LNG['bo_select_title'], 
				'button_submit'				=> $LNG['button_submit'], 
				'button_deselect'			=> $LNG['button_deselect'], 
				'button_filter'				=> $LNG['button_filter'], 
				'ad_authlevel_insert_id'	=> $LNG['ad_authlevel_insert_id'], 
				'ad_authlevel_auth'			=> $LNG['ad_authlevel_auth'], 
				'ad_authlevel_aa'			=> $LNG['ad_authlevel_aa'], 
				'ad_authlevel_oo'			=> $LNG['ad_authlevel_oo'], 
				'ad_authlevel_mm'			=> $LNG['ad_authlevel_mm'], 
				'ad_authlevel_jj'			=> $LNG['ad_authlevel_jj'], 
				'ad_authlevel_tt'			=> $LNG['ad_authlevel_tt'], 
				'sid'						=> session_id(), 
			));
	
			$template->show('ModerrationUsersPage.tpl');
		break;
	}
}

function prepare($val)
{
	return str_replace('.php', '', $val);
}