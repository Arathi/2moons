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
 * @version 1.7.2 (2013-03-18)
 * @info $Id: vars.php 2746 2013-05-18 11:38:36Z slaver7 $
 * @link http://2moons.cc/
 */

// VARS DB -> SCRIPT WRAPPER

$cache	= Cache::get();
$cache->add('vars', 'VarsBuildCache');
extract($cache->getData('vars'));

$resource[901] = 'metal';
$resource[902] = 'crystal';
$resource[903] = 'deuterium';
$resource[911] = 'energy';
$resource[921] = 'darkmatter';

$reslist['ressources']  = array(901, 902, 903, 911, 921);
$reslist['resstype'][1] = array(901, 902, 903);
$reslist['resstype'][2] = array(911);
$reslist['resstype'][3] = array(921);