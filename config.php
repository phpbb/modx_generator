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
 * modx_diff configuration file.
 */

/**
 * The ignore array contains files and directories to ignore.
 */
$ignore = array(
	'Thumbs.db',
	'cache',
	'install',
	'files',
	'config.php',
	'store',
	'docs',
	'.svn',
	'.DS_Store',
);

/**
 * File extensions to ignore while diffing
 * Images and other binary file types.
 */
$ignore_ext = array(
	'jpg',
	'jpeg',
	'gif',
	'png',
	'zip',
	'tar',
	'rar',
);

/**
 * Default settings for the script parameters.
 * They can be overridden at runtime by using the parameters in the command line.
 */
$defaults = array(
	// You need to specify a path for the first three if you use them.
	// 'old' for the original files, path can be absolute or relative.
	// If you define a value here and want to override it, you need to use -o or --old at the command line.
	'old' => '',
	// 'new' for the modified files, path can be absolute or relative.
	'new' => '',
	//'modxfile' for -m, --modxfile = path and name of file to generate. Defautls to stdout.
	// You need to specify path and file name if you want to use this.
	'modxfile' => '',
	// 'root' for -r, --root = Creates a root directory containing the files missing in old.
	'root' => '',
	// The following are just on or off (true or false).
	// 'force' for -f, --force = Replaces the root directory if it exists.
	'force' => false,
	// 'verbose' for -v, --verbose = Tell what happens.
	'verbose' => false,
	// 'custom' for -c, --custom = This is an install file for a addition style or language.
	'custom' => false,
	// 'ignore_version' for -i, --ignore-version = ignore SVN version info at the top of files.
	'ignore_version' => true,
);
