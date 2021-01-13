<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\acp;

class forumsubs_info
{
	public function module()
	{
		return [
			'filename' => '\david63\forumsubs\acp\forumsubs_module',
			'title' => 'FORUM_SUBSCRIPTIONS',
			'modes' => [
				'main' => ['title' => 'FORUM_SUBSCRIPTIONS', 'auth' => 'ext_david63/forumsubs && acl_a_user', 'cat' => ['ACP_CAT_USERS']],
			],
		];
	}
}
