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
