<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\controller;

/**
 * @ignore
 */
use phpbb\template\template;
use phpbb\language\language;
use phpbb\auth\auth;
use david63\forumsubs\core\functions;
use phpbb\db\driver\driver_interface;

/**
 * Event listener
 */
class ucp_controller
{
	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var auth */
	protected $auth;

	/** @var functions */
	protected $functions;

	/** @var driver_interface */
	protected $db;

	/** @var string phpBB tables */
	protected $tables;

	/**
	 * Constructor
	 *
	 * @param driver_interface		$db				The db connection
	 * @param template				$template 		Template object
	 * @param language				$language 		Language object
	 * @param auth 					$auth			Auth object
	 * @param functions   			$functions		Functions for the extension
	 * @param array					$tables			phpBB db tables
	 *
	 * @return \david63\forumsubs\controller\ucp_controller
	 * @access public
	 */
	public function __construct(template $template, language $language, auth $auth, functions $functions, driver_interface $db, array $tables)
	{
		$this->template		= $template;
		$this->language		= $language;
		$this->auth			= $auth;
		$this->functions	= $functions;
		$this->db			= $db;
		$this->tables		= $tables;
	}

	/**
	 * Display a user's forum subscriptions
	 *
	 * @return   void
	 */
	public function ucp_forumsubs($user_id)
	{
		// Add the language files
		$this->language->add_lang('ucp_forumsubs', $this->functions->get_ext_namespace());

		// Get the member's subscriptions
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'f.forum_id, f.forum_name',
			'FROM'		=> [
				$this->tables['users']			=> 'u',
				$this->tables['forums']			=> 'f',
				$this->tables['forums_watch']	=> 'fw',
			],
			'WHERE'		=> 'u.user_id = fw.user_id
				AND u.user_id 	= ' . (int) $user_id . '
				AND f.forum_id	= fw.forum_id',
			'ORDER_BY'	=> 'f.parent_id, f.left_id',
		]);

		$result = $this->db->sql_query($sql);

		$num_rows	= $col_1 = $result->num_rows;
		$col_2 		= 0;
		$col_2_set	= false;

		// Send the data to the template
		if ($num_rows == 0)
		{
			$display = false;
		}
		else
		{
			$display = true;

			// Let's split the output into two columns if number of fora is greater than 10
			if ($num_rows >= 10)
			{
				$col_1 = ceil($num_rows / 2);
				$col_2 = $num_rows - $col_1;
			}

			for ($i = 0; $i < $col_1; $i++)
			{
				$row = $this->db->sql_fetchrow($result);

				$this->template->assign_block_vars('member_subs_1', [
					'FORUM_NAME'	=> $row['forum_name'],
					'FORUM_POSTS'	=> $this->functions->get_user_post_count($row['forum_id'], $user_id),
					'SUBSCRIBERS'	=> $this->functions->get_subscribed_user_count($row['forum_id']),
				]);
			}

			if ($col_2 > 0)
			{
				$col_2_set = true;

				for ($i = $col_2 + 1; $i < $num_rows; $i++)
				{
					$row = $this->db->sql_fetchrow($result);

					$this->template->assign_block_vars('member_subs_2', [
						'FORUM_NAME'	=> $row['forum_name'],
						'FORUM_POSTS'	=> $this->functions->get_user_post_count($row['forum_id'], $user_id),
						'SUBSCRIBERS'	=> $this->functions->get_subscribed_user_count($row['forum_id']),
					]);
				}
			}
		}

		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'FS_NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'S_CAN_VIEW_SUBS'   => ($this->auth->acl_get('u_forumsubs_view')) ? true : false,
			'S_COL_2_SET'		=> $col_2_set,
			'S_DISPLAY_DATA'	=> $display,
		]);
	}
}
