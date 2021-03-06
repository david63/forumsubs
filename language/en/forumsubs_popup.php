<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * DEVELOPERS PLEASE NOTE
 *
 * All language files should use UTF-8 as their encoding and the files must not contain a BOM.
 *
 * Placeholders can now contain order information, e.g. instead of
 * 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
 * translators to re-order the output of data while ensuring it remains correct
 *
 * You do not need this where single placeholders are used, e.g. 'Message %d' is fine
 * equally where a string contains only two placeholders which are used to wrap text
 * in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
 *
 * Some characters you may want to copy&paste:
 * ’ » “ ” …
 *
 */

$lang = array_merge($lang, [
	'FIND_FORUM_EXPLAIN'	=> 'Use this form to select one or more fora. Use the mark checkboxes to select one or more fora (several fora may be accepted depending on the form itself) and click the “Select marked” button to return to the previous form.',
	'FORUM'					=> 'Forum',
	'FORUM_SELECT'			=> 'Select a forum',

	'MARK_ALL'				=> 'Mark all',

	'SELECT'				=> 'Select',
	'SELECT_MARKED'			=> 'Select marked',
	'SUBSCRIBERS'			=> 'Subscribers',

	'UNMARK_ALL'			=> 'Unmark all',
]);
