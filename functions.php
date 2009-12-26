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
function parse_args($argv, $defaults)
{
	$args = $defaults;
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

			case '-m':
			case '--modxfile':
				$args['modxfile'] = $argv[$key + 1];
			break;

			case '-v':
			case '--verbose':
				$args['verbose'] = ($args['verbose']) ? false : true;
			break;

			case '-h':
			case '--help':
				$args['help'] = true;
			break;

			case '-c':
			case '--custom':
				$args['custom'] = ($args['custom']) ? false : true;
			break;

			case '-i':
			case '--ignore-version':
				$args['ignore_version'] = ($args['ignore_version']) ? false : true;
			break;

			case '-r':
			case '--root':
				$args['root'] = $argv[$key + 1];
			break;

			case '-f':
			case '--force':
				$args['force'] = $argv[$key + 1];
			break;

			default:
				continue;
			break;
		}
	}
	return($args);
}

/**
 * Removes ingored parts from the files before diffing.
 */
function rem_ignore(&$old_file, &$new_file)
{
	// The SVN version string is usually at line 5, but it can be anywhere in the beginning.
	// Let's check the 20 first lines only. And assume it's in the same place in both files.
	// Otherwise let's diff that to.
	for ($i = 0; $i < 20; $i++)
	{
		if (!isset($old_file[$i]) || !isset($new_file[$i]))
		{
			return;
		}

		if (strpos($old_file[$i], '@version') !== false && strpos($new_file[$i], '@version') !== false)
		{
			// The easiest way to ignore them is to make them identical.
			$new_file[$i] = $old_file[$i];
			break;
		}
	}
}

/**
 * Checks the directory arrays for missing files in $old_dir and writes a copy-tag if files are missing.
 */
function check_missing($old_arr, $new_arr)
{
	global $xml, $where_changes, $dir_separator, $args;

	$missing = $copy = false;

	foreach ($new_arr as $file)
	{
		if (!in_array($file, $old_arr))
		{
			if (!$missing)
			{
				if (!empty($args['root']))
				{
					// Check that the parent directory for root exists, is a directory and that we have write permissions.
					if (!file_exists($args['root']) || !is_dir($args['root']))
					{
						echo '"' . $args['root'] . '" do not exist or is not a directory.' . "\n";
						exit(20);
					}

					$copy = $args['root'] . ((substr($args['root'], -1) != $dir_separator) ? $dir_separator : '');
					if (!is_writable($args['root']))
					{
						echo '"' . $args['root'] . '" is not writable.' . "\n";
						exit(20);
					}

					$copy .= 'root' . $dir_separator;
					if (file_exists($copy))
					{
						// Ouch, the root directory exists.
						if ($args['force'])
						{
							// We need write permissions here to.
							if (!is_writable($copy))
							{
								echo '"' . $copy . '" is not writable.' . "\n";
								exit(20);
							}

							if ($args['verbose'])
							{
								echo 'Deleting old root directory' . "\n";
							}

							// Now let's delete the old root directory
							delete_dir($copy);
						}
						else
						{
							echo '"' . $copy . '" already exists.' . "\n";
							exit(20);
						}
					}

					if (!@mkdir($copy))
					{
						echo 'Could not create: ' . $copy . "\n";
						exit(20);
					}
				}
				$missing = true;
				$where_changes = true;
				$xml->startElement('copy');
			}

			if ($copy)
			{
				// Copy files to root/.
				if ($args['verbose'])
				{
					echo 'Copying: ' . $file . "\n";
				}
				$dir = dirname($file);
				$dir_arr = explode($dir_separator, $dir);
				$dir = '';
				foreach ($dir_arr as $value)
				{
					$dir .= (($dir == '') ? '' : $dir_separator) . $value;

					// Check that the target directory exists.
					if(!file_exists($copy . $dir))
					{
						if (!@mkdir($copy . $dir))
						{
							echo 'Could not create: ' . $copy . $dir . "\n";
						}
					}
				}
				if (!@copy($args['new'] . $file, $copy . $file))
				{
					echo 'Could not copy: ' . $file . "\n";
					exit(20);
				}
			}
			// On Windows the directory separator will be \, so we need to handle that.
			$file = str_replace('\\', '/', $file);
			$xml->write_element('file', '', array('from' => 'root/' . $file, 'to' => $file));
		}
	}
	if ($missing)
	{
		$xml->endElement();
		return(true);
	}

	return(false);
}

/**
 * Recursive delete a directory.
 */
function delete_dir($dir, $base = '')
{
	global $dir_separator;

	$dir .= (substr($dir, -1) != $dir_separator) ? $dir_separator : '';

	$handle = opendir($dir);
	while (($file = readdir($handle)) !== false)
	{
		if ($file != '.' && $file != '..')
		{
			$path = $dir . $file;
			if (is_file($path))
			{
				unlink($path);
			}
			else if (is_dir($path))
			{
				delete_dir($path, $base . $file);
			}
		}
	}
	closedir($handle);

	rmdir( $dir );
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
			if (!$args['custom'] && is_dir($path))
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
