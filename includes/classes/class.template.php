<?php

/**
 *  2Moons
 *  Copyright (C) 2011  Slaver
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
 * @author Slaver <slaver7@gmail.com>
 * @copyright 2009 Lucky <lucky@xgproyect.net> (XGProyecto)
 * @copyright 2011 Slaver <slaver7@gmail.com> (Fork/2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.6.1 (2011-11-19)
 * @info $Id: class.template.php 2641 2013-03-24 13:43:52Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

require('includes/libs/Smarty/Smarty.class.php');
		
class template extends Smarty
{
	protected $window	= 'full';
	protected $jsscript	= array();
	protected $script	= array();
	
	function __construct()
	{	
		parent::__construct();
		$this->smartySettings();
	}
	
	function smartySettings()
	{	
		$this->force_compile 			= false;
		$this->caching 					= true; #Set true for production!
		$this->merge_compiled_includes	= true;
		$this->compile_check			= true; #Set false for production!
		$this->php_handling				= Smarty::PHP_REMOVE;
		
		$this->setCompileDir(is_writable(ROOT_PATH.'cache/') ? ROOT_PATH.'cache/' : $this->getTempPath());
		$this->setCacheDir(ROOT_PATH.'cache/templates');
		$this->setTemplateDir(ROOT_PATH.'styles/templates/');
	}
	
	public function loadscript($script)
	{
		$this->jsscript[]			= substr($script, 0, -3);
	}
	
	public function execscript($script)
	{
		$this->script[]				= $script;
	}
	
	public function getTempPath()
	{
		$this->force_compile 		= true;
		include 'includes/libs/wcf/BasicFileUtil.class.php';
		return BasicFileUtil::getTempFolder();
	}
		
	public function assign_vars($var, $nocache = true) 
	{		
		parent::assign($var, NULL, $nocache);
	}
	
	private function adm_main()
	{
		global $LNG, $USER;
		
		$dateTimeServer		= new DateTime("now");
		if(isset($USER['timezone'])) {
			try {
				$dateTimeUser	= new DateTime("now", new DateTimeZone($USER['timezone']));
			} catch (Exception $e) {
				$dateTimeUser	= $dateTimeServer;
			}
		} else {
			$dateTimeUser	= $dateTimeServer;
		}
		
		$this->assign_vars(array(
			'scripts'			=> $this->script,
			'title'				=> Config::get('game_name').' - '.$LNG['adm_cp_title'],
			'fcm_info'			=> $LNG['fcm_info'],
            'lang'    			=> $LNG->getLanguage(),
			'REV'				=> substr(Config::get('VERSION'), -4),
			'date'				=> explode("|", date('Y\|n\|j\|G\|i\|s\|Z', TIMESTAMP)),
			'Offset'			=> $dateTimeUser->getOffset() - $dateTimeServer->getOffset(),
			'VERSION'			=> Config::get('VERSION'),
			'dpath'				=> 'styles/theme/gow/',
			'bodyclass'			=> 'full'
		));
	}
	
	public function show($file)
	{		
		global $USER, $PLANET, $LNG, $THEME;

		if($THEME->isCustomTPL($file))
			$this->setTemplateDir($THEME->getTemplatePath());
			
		$tplDir	= $this->getTemplateDir();
			
		if(MODE === 'INSTALL') {
			$this->setTemplateDir($tplDir[0].'install/');
		} elseif(MODE === 'ADMIN') {
			$this->setTemplateDir($tplDir[0].'adm/');
			$this->adm_main();
		}

		$this->assign_vars(array(
			'scripts'		=> $this->jsscript,
			'execscript'	=> implode("\n", $this->script),
		));

		$this->assign_vars(array(
			'LNG'			=> $LNG,
		), false);
		
		$this->compile_id	= $LNG->getLanguage();
		
		parent::display($file);
	}
	
	public function display($file)
	{
		global $LNG;
		$this->compile_id	= $LNG->getLanguage();
		parent::display($file);
	}
	
	public function gotoside($dest, $time = 3)
	{
		$this->assign_vars(array(
			'gotoinsec'	=> $time,
			'goto'		=> $dest,
		));
	}
	
	public function message($mes, $dest = false, $time = 3, $Fatal = false)
	{
		global $LNG, $THEME;
	
		$this->assign_vars(array(
			'mes'		=> $mes,
			'fcm_info'	=> $LNG['fcm_info'],
			'Fatal'		=> $Fatal,
            'dpath'		=> $THEME->getTheme(),
		));
		
		$this->gotoside($dest, $time);
		$this->show('error_message_body.tpl');
	}
	
	public static function printMessage($Message, $fullSide = true, $redirect = NULL) {
		$template	= new self;
		if(!isset($redirect)) {
			$redirect	= array(false, 0);
		}
		
		$template->message($Message, $redirect[0], $redirect[1], !$fullSide);
		exit;
	}
	
    /**
    * Workaround  for new Smarty Method to add custom props...
    */

    public function __get($name)
    {
        $allowed = array(
        'template_dir' => 'getTemplateDir',
        'config_dir' => 'getConfigDir',
        'plugins_dir' => 'getPluginsDir',
        'compile_dir' => 'getCompileDir',
        'cache_dir' => 'getCacheDir',
        );

        if (isset($allowed[$name])) {
            return $this->{$allowed[$name]}();
        } else {
            return $this->{$name};
        }
    }
	
    public function __set($name, $value)
    {
        $allowed = array(
        'template_dir' => 'setTemplateDir',
        'config_dir' => 'setConfigDir',
        'plugins_dir' => 'setPluginsDir',
        'compile_dir' => 'setCompileDir',
        'cache_dir' => 'setCacheDir',
        );

        if (isset($allowed[$name])) {
            $this->{$allowed[$name]}($value);
        } else {
            $this->{$name} = $value;
        }
    }
}
