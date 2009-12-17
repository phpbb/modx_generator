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
	'config.php',
	'store',
	'docs',
	'.svn',
);

/**
 * File extensions ignore while diffing
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

// Ignore version strings like @version $Id$
// This setting is ignored for now.
$ignore_file_version = true;
