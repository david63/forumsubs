<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\controller;

use phpbb\template\template;
use phpbb\db\driver\driver_interface;
use phpbb\auth\auth;
use phpbb\language\language;
use david63\forumsubs\core\functions;

class main_controller
{
	/** @var template */
	protected $template;

	/** @var driver_interface */
	protected $db;

	/** @var auth */
	protected $auth;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var string custom tables */
	protected $tables;

	/**
	 * Constructor for listener
	 *
	 * @param template				$template		Template object
	 * @param driver_interface		$db				The db connection
	 * @param auth 					$auth			Auth object
	 * @param language				$language		Language object
	 * @param functions				$functions		Functions for the extension
	 * @param array					$tables			phpBB db tables
	 *
	 * @return \david63\forumsubs\controller\main_controller
	 * @access public
	 */
	public function __construct(template $template, driver_interface $db, auth $auth, language $language, functions $functions, array $tables)
	{
		$this->template		= $template;
		$this->db			= $db;
		$this->auth			= $auth;
		$this->language		= $language;
		$this->functions	= $functions;
		$this->tables		= $tables;
	}

	/**
	 * Controller for forumsubs
	 *
	 * @param string		$forum_id
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function forum_subscriptions($forum_id)
	{
		// Add the language files
		$this->language->add_lang('forumsubs', $this->functions->get_ext_namespace());

		// Get the member's subscriptions
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.user_id, u.username, u.user_colour, fw.forum_id',
			'FROM'		=> [
				$this->tables['users']			=> 'u',
				$this->tables['forums_watch']	=> 'fw',
			],
			'WHERE'		=> 'u.user_id = fw.user_id
				AND fw.forum_id = ' . (int) $forum_id,
			'ORDER_BY'	=> 'u.username_clean',
		]);

		$result = $this->db->sql_query($sql);

		$num_rows = $result->num_rows;

		// Send the data to the template
		if ($num_rows == 0)
		{
			$display_data = false;
		}
		else
		{
			$display_data = true;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('member_subs', [
					'USERNAME' 		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'FORUM_POSTS'	=> $this->functions->get_user_post_count($row['forum_id'], $row['user_id']),
				]);
			}
		}

		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'FS_NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'SUBSCRIBERS'		=> $this->language->lang('SUBSCRIBERS', $num_rows),

			'S_DISPLAY_DATA'	=> $display_data,
			'S_CAN_VIEW_SUBS'   => ($this->auth->acl_get('u_forumsubs_view')) ? true : false,
		));
	}
}
