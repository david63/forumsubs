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
	'FORUM_GROUP_SELECT'			=> 'Select the group',
	'FORUM_GROUP_SELECT_EXPLAIN'	=> 'This is the group to which the subscriptions will apply.',

	'GROUP_FORUM_SUBSCRIPTIONS'		=> 'Group forum subscriptions',
	'GROUP_SUBSCRIPTIONS_EXPLAIN'	=> 'Here you can select the group for which you want to subscribe users and then select the fora that they will be subscribed to.',

	'NO_GROUP_SELECTED'				=> 'No group has been selected - please try again.',

	'SELECT_FORUMS'					=> 'Select the forum(s)',
	'SELECT_FORUMS_EXPLAIN'			=> 'Select the forum(s) that this group is to be subscribed to.<br><br>Hold the “ctrl” key to select multiple fora or to unselect a forum.',
	'SELECT_GROUP'					=> 'Select a group',
	'SELECTED_GROUP'				=> 'Selecting the forum(s) for <strong>%s</strong> group',
]);
