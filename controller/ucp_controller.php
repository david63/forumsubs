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
				AND u.user_id 	= ' . $user_id . '
				AND f.forum_id	= fw.forum_id',
			'ORDER_BY'	=> 'f.forum_name',
		]);

		$result = $this->db->sql_query($sql);

		// Send the data to the template
		if ($result->num_rows == 0)
		{
			$display = false;
		}
		else
		{
			$display = true;

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('member_subs', [
					'FORUM_NAME'	=> $row['forum_name'],
					'FORUM_POSTS'	=> $this->functions->get_user_post_count($row['forum_id'], $user_id),
				]);
			}
		}

		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'S_CAN_VIEW_SUBS'   => ($this->auth->acl_get('u_forumsubs_view')) ? true : false,
			'S_DISPLAY_DATA'	=> $display,
		]);
	}
}
