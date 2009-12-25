<?php
/**
 *
 * @package MODX Generator
 * @version $Id$
 * @copyright (c) tumba25
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
 *
 */

//MODX version
if(!defined('MODX_VERSION'))
{
	define('MODX_VERSION', '1.2.3');
}

// Don't touch this line. This max setting is needed for now.
// The finds should never reach this but leave it for safety.
define('MAX_SEARCH_ROWS', 12);

// Diff options.
define('DIFF_BASIC', 0);
define('DIFF_CUSTOM', 1);

// Type definitions (type)
//define('NOP', 0);
define('INLINE', 1);
define('EDIT', 2);

// Edit definitions (add-type)
define('DO_NOTHING', 0);
define('ADD_BEFORE', 1);
define('ADD_AFTER', 2);
define('REPLACE', 3);
define('INLINE_ADD_AFTER', 4);
define('INLINE_ADD_BEFORE', 5);
define('INLINE_REPLACE', 6);

// Error messages from gen_find()
define('MOVE_UP', 7);
define('MOVE_DOWN', 8);
define('NO_FIND', 8);
