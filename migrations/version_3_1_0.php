<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\migrations;

use phpbb\db\migration\migration;

class version_3_1_0 extends migration
{
	/**
	 * @return array Array update data
	 * @access public
	 */
	public function update_data()
	{
		return [
			// Add new permissions
			['permission.add', ['u_forumsubs_view', true]],

			// Add new module
			['module.add', [
				'acp',
				'ACP_CAT_USERS',
				[
					'module_basename'	=> 'acp_users',
					'module_langname'	=> 'USER_SUBSCRIPTIONS',
					'module_mode'		=> 'forumsubs',
					'module_display'	=> false,
					'module_auth'		=> 'ext_david63/forumsubs && acl_a_user',
				],
			]],
		];
	}
}
