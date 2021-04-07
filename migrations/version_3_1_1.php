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

class version_3_1_1 extends migration
{
	/**
	 * Assign migration file dependencies for this migration
	 *
	 * @return array Array of migration files
	 * @static
	 * @access public
	 */
	public static function depends_on()
	{
		return ['\david63\forumsubs\migrations\version_3_1_0'];
	}

	/**
	 * Revert by removing the forum subscriptions
	 */
	public function revert_data()
	{
		return [
			['custom', [[$this, 'remove_subscriptions']]],
		];
	}

	/**
	 * Remove the forum subscriptions
	 */
	public function remove_subscriptions()
	{
		$this->db->sql_query('DELETE FROM ' . FORUMS_WATCH_TABLE . " WHERE (group_subs) > 0");
	}
}
