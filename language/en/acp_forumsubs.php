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
	'CHECK'							=> 'Check/Uncheck',

	'FORUM_NAME'					=> 'Forum name',
	'FORUM_SUBS_UPDATED'			=> 'Forum subscriptions updated',
	'FORUM_SUBSCRIPTIONS'			=> 'Forum subscriptions',
	'FORUM_SUBSCRIPTIONS_EXPLAIN'	=> 'Here you will find the forums that the user is subscribed to.<br><em>(Click a forum category to expand/collapse its forums.)</em>',

	'NO_DATA'						=> '&nbsp;The user is not subscribed to any forums.',
	'NO_SUBSCRIBERS'				=> 'No users are subscribed to this forum',

	'SUBSCRIBER_COUNT'				=> 'Subscriber count',
	'SUBSCRIBERS'					=> 'Forum subscribers',
	'SUBSCRIPTIONS_EXPLAIN'			=> 'Here you will find the members that are subscribed to each forum.<br><em>(Click a forum category to expand/collapse its forums.)</em>',
]);
