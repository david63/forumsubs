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
		$update_data = [];
		// Add new permissions
		$update_data[] = ['permission.add', ['u_forumsubs_view', true]];

		// Add new module
		$update_data[] = ['module.add', [
			'acp',
			'ACP_CAT_USERS',
			[
				'module_basename'	=> 'acp_users',
				'module_langname'	=> 'USER_SUBSCRIPTIONS',
				'module_mode'		=> 'forumsubs',
				'module_display'	=> false,
				'module_auth'		=> 'ext_david63/forumsubs && acl_a_user',
			],
		]];

		if ($this->module_check())
		{
			$update_data[] = ['module.add', ['acp', 'ACP_CAT_USERGROUP', 'ACP_USER_UTILS']];
		}

		$update_data[] = ['module.add', [
			'acp', 'ACP_USER_UTILS', [
				'module_basename' => '\david63\forumsubs\acp\forumsubs_module',
				'modes' => ['main'],
			],
		]];

		return $update_data;
	}

	protected function module_check()
	{
		$sql = 'SELECT module_id
                FROM ' . $this->table_prefix . "modules
                WHERE module_class = 'acp'
                    AND module_langname = 'ACP_USER_UTILS'
                    AND right_id - left_id > 1";

		$result    = $this->db->sql_query($sql);
		$module_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		// return true if module is empty, false if has children
		return (bool) !$module_id;
	}
}
