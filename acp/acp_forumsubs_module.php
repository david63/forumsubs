<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\acp;

class acp_forumsubs_module
{
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name   = 'acp_forum_subscriptions';
		$this->page_title = $phpbb_container->get('language')->lang('FORUM_SUBSCRIPTIONS');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.forumsubs.admin.controller');

		$admin_controller->display_output();
	}
}
