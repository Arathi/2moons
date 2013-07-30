<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan
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
 * @author Jan <info@2moons.cc>
 * @copyright 2006 Perberos <ugamela@perberos.com.ar> (UGamela)
 * @copyright 2008 Chlorel (XNova)
 * @copyright 2009 Lucky (XGProyecto)
 * @copyright 2012 Jan <info@2moons.cc> (2Moons)
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 2.0 (2012-11-31)
 * @info $Id: ArrayUtil.class.php 2746 2013-05-18 11:38:36Z slaver7 $
 * @link http://code.google.com/p/2moons/
 */

class ArrayUtil
{
	static public function combineArrayWithSingleElement($keys, $var)
	{
		if(empty($keys))
		{
			return array();
		}
		return array_combine($keys, array_fill(0, count($keys), $var));
	}

	static public function combineArrayWithKeyElements($keys, $var)
	{
		$temp	= array();
		foreach($keys as $key)
		{
			if(isset($var[$key]))
			{
				$temp[$key]	= $var[$key];
			}
			else
			{
				$temp[$key]	= $key;
			}
		}
		
		return $temp;
	}
	
	// http://www.php.net/manual/en/function.array-key-exists.php#81659
	static public function arrayKeyExistsRecursive($needle, $haystack)
	{
		$result = array_key_exists($needle, $haystack);
		
		if ($result)
		{
			return $result;
		}
		
		foreach ($haystack as $v)
		{
			if (is_array($v))
			{
				$result = self::arrayKeyExistsRecursive($needle, $v);
			}
			
			if ($result)
			{
				return $result;
			}
		}
		
		return $result;
	}
}