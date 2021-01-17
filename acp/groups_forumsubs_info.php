<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\acp;

class groups_forumsubs_info
{
	public function module()
	{
		return [
			'filename' => '\david63\forumsubs\acp\groups_forumsubs_module',
			'title' => 'FORUM_SUBSCRIPTIONS',
			'modes' => [
				'main' => ['title' => 'FORUM_SUBSCRIPTIONS', 'auth' => 'ext_david63/forumsubs && acl_a_user', 'cat' => ['ACP_CAT_USERS']],
			],
		];
	}
}
