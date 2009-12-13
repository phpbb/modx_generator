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
 * USAGE:
 * php modx_gen.php -o path/to/unchanged/dir (or file) -n path/to/changed/dir (or file)
 *
 * The parameters to modx_gen.php needs to be after "php modx_gen.php".
 * -o, --old = Original files, path can be absolute or relative.
 * -n, --new = Modified files, path can be absolute or relative.
 * -c, --custom = This is an install file for a addition style or language (subsilver2 is a additional style).
 *       Without --custom only the prosilver style and English language will be compared.
 *       Additional languages and styles need separate install files.
 * -h, --help = print this text.
 * -f, --outfile = path and name of file to generate. Defautls to stdout
 * -v, --verbose = Tell what happens.
 */

// Get the execution time.
// Should be removed before final release.
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

$phpEx = substr(strrchr(__FILE__, '.'), 1);
$script_path = dirname(__FILE__);

$script_path .= (strpos($script_path, '/') !== false) ? '/' : '\\';
$dir_separator = (strpos($script_path, '/') !== false) ? '/' : '\\';

require($script_path . 'diff' . $dir_separator . 'Diff.' . $phpEx);
require($script_path . 'diff' . $dir_separator . 'inline.' . $phpEx);

require($script_path . 'parse_diff.' . $phpEx);
require($script_path . 'modx_writer.' . $phpEx);

require($script_path . 'constants.' . $phpEx);
require($script_path . 'config.' . $phpEx);
require($script_path . 'functions.' . $phpEx);

$args = parse_args($argv);

$slap = $diff_files = $diff_dirs = $file_copy = $where_changes = false;
if (empty($args['old']) || empty($args['new']) || isset($args['help']))
{
	$slap = true;
}
else if (is_dir($args['new']) && is_dir($args['old']))
{
	$diff_dirs = true;
}
else if (is_file($args['new']) && is_file($args['old']))
{
	$diff_files = true;
}
else
{
	$slap = true;
}

if ($slap)
{
	echo 'USAGE:' . "\n";
	echo 'php modx_gen.php -o path/to/unchanged/dir (or file) -n path/to/changed/dir (or file)' . "\n\n";
	echo 'The parameters to modx_gen.php needs to be after "php modx_gen.php".' . "\n";
	echo '-o, --old = Original files, path can be absolute or relative.' . "\n";
	echo '-n, --new = Modified files, path can be absolute or relative.' . "\n";
	echo '      Both old and new need to be either files or dirs.' . "\n";
	echo '-c, --custom = This is an install file for a addition style or language (subsilver2 is a additional style).' . "\n";
	echo '      Without --custom only the prosilver style and English language will be compared.' . "\n";
	echo '      Additional languages and styles need separate install files.' . "\n";
	echo '-h, --help = print this text.' . "\n";
	echo '-f, --outfile = path and name of file to generate. Defautls to stdout.' . "\n";
	echo '-v, --verbose = tell what happens.' . "\n";
	exit;
}

if ($diff_files)
{
	$file = basename($args['old']);

	$old_file = file($args['old']);
	$new_file = file($args['new']);

	if ($old_file == $new_file)
	{
		echo 'The files are identical' . "\n";
	}

	$diff = new Text_Diff('native', array($old_file, $new_file));
	unset($old_file, $new_file);

	$renderer = new Text_Diff_Renderer_inline();
	$file_diff = $renderer->render($diff);
	unset($renderer, $diff);

	$parser = new parse_diff();
	$file_diff = $parser->parse($file_diff);

	if (!empty($file_diff))
	{
		$xml = new modx_writer();

		$where_changes = true;
		$xml->generate_xml($file, $file_diff);
	}
}
else
{
	// Add / to the end of the paths if it's not already there
	$args['old'] .= (substr($args['old'], -1) != $dir_separator) ? $dir_separator : '';
	$args['new'] .= (substr($args['new'], -1) != $dir_separator) ? $dir_separator : '';

	// Get the files
	if ($args['verbose'])
	{
		echo 'Getting files' . "\n";
	}
	get_dir_contents($args['old'], $old_arr);
	get_dir_contents($args['new'], $new_arr);

	// Sort them alphabetically
	if ($args['verbose'])
	{
		echo 'Sorting files' . "\n";
	}
	$old_arr = directory_sort($old_arr);
	$new_arr = directory_sort($new_arr);

	$xml = new modx_writer();
	$parser = new parse_diff();

	// Start with a check for new files
	if ($args['verbose'])
	{
		echo 'Checking for missing files' . "\n";
	}
	check_missing($old_arr, $new_arr);

	foreach ($old_arr as $file)
	{
		$ext = substr(strrchr($file, '.'), 1);

		if (in_array($ext, $ignore_ext) || !file_exists($args['new'] . $file))
		{
			continue;
		}
		if ($args['verbose'])
		{
			echo 'Comparing file: ' . $file;
		}

		$old_file = file($args['old'] . $file);
		$new_file = file($args['new'] . $file);

		if ($old_file != $new_file)
		{
			if (filesize($args['old'] . $file) == 0)
			{
				echo $args['old'] . $file . ' is empty, can\'t create a find in an empty file.' . "\n";
				continue;
			}

			if ($args['verbose'])
			{
				echo '... Differences found.' . "\n";
			}

			$diff = new Text_Diff('native', array($old_file, $new_file));
			unset($old_file, $new_file);

			$renderer = new Text_Diff_Renderer_inline();
			$file_diff = $renderer->render($diff);
			unset($renderer, $diff);

			$file_diff = $parser->parse($file_diff);

			if ($file_diff)
			{
				$where_changes = true;
				$xml->generate_xml($file, $file_diff);
			}
		}
		else if ($args['verbose'])
		{
			echo '... Identical.' . "\n";
		}
	}
}

if (isset($xml) && $where_changes)
{
	$out_file = (isset($args['outfile'])) ? $args['outfile'] : '';
	$xml->modx_close($out_file);
}

// Should be removed before final release.
echo 'Memory peak: ' . memory_get_peak_usage(true) . "\n";
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo 'Execution time: ' . $totaltime . ' seconds' . "\n";