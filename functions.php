<?php
/**
 *
 * @package MODX Generator
 * @version $Id$
 * @copyright (c) tumba25
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
 *
 */

/**
 * parses the args sent to the script
 */
function parse_args($argv)
{
	$args = array();
	$args['diff_opt'] = DIFF_BASIC;
	$args['verbose'] =false;
	foreach ($argv as $key => $value)
	{
		switch($value)
		{
			case '-o':
			case '--old':
				$args['old'] = $argv[$key + 1];
			break;

			case '-n':
			case '--new':
				$args['new'] = $argv[$key + 1];
			break;

			case '-f':
			case '--outfile':
				$args['outfile'] = $argv[$key + 1];
			break;

			case '-v':
			case '--verbose':
				$args['verbose'] = true;
			break;

			case '-h':
			case '--help':
				$args['help'] = true;
			break;

			case '-c':
			case '--custom':
				$args['diff_opt'] = DIFF_CUSTOM;
			break;

			default:
				continue;
			break;
		}
	}
	return($args);
}

/**
 * Checks the directory arrays for missing files in $old_dir and writes a copy-tag if files are missing.
 */
function check_missing($old_arr, $new_arr)
{
	global $xml, $where_changes;

	$missing = false;

	foreach ($new_arr as $file)
	{
		if (!in_array($file, $old_arr))
		{
			if (!$missing)
			{
				$missing = true;
				$where_changes = true;
				$xml->startElement('copy');
			}
			// On Windows the directory separator will be \, so we need to replace that.
			$file = str_replace('\\', '/', $file);
			$xml->write_element('file', '', array('from' => 'root/' . $file, 'to' => $file));
		}
	}
	if ($missing)
	{
		$xml->endElement();
	}
}

/**
 * Sorts the directory in alphabetical order, files first.
 */
function directory_sort($dir)
{
	$filenames = $directories = $files = array();
	foreach ($dir as $key => $value)
	{
		$files[$key] = $value;
		$filenames[$key] = basename($value);
		$directories[$key] = dirname($value);
	}
	array_multisort($directories, SORT_STRING, $filenames, SORT_STRING, $files);

	return($files);
}

/**
 * Reads a directory recursive and puts the result in a array.
 * From evil<3s diff_tools
 * Slightly modified.
 */
function get_dir_contents($dir, &$dir_arr, $base = '')
{
	global $ignore, $args, $dir_separator;

	$ignore = array_merge(array('.', '..'), $ignore);

	$handle = opendir($dir);
	while (($file = readdir($handle)) !== false)
	{
		if (!in_array($file, $ignore))
		{
			$path = $dir . $dir_separator . $file;
			if ($args['diff_opt'] == DIFF_BASIC && is_dir($path))
			{
				// Only diff Prosilver and English
				// Other styles and languages require their own install files.
				if ((basename($dir) == 'styles' && $file != 'prosilver') || (basename($dir) == 'language' && $file != 'en'))
				{
					continue;
				}
			}
			if (is_file($path))
			{
				$dir_arr[] = $base . $file;
			}
			else if (is_dir($path))
			{
				get_dir_contents($path, $dir_arr, $base . $file . $dir_separator);
			}
		}
	}
	closedir($handle);
}
