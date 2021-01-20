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
use david63\forumsubs\core\functions;
use david63\forumsubs\core\ext_functions;
use phpbb\db\driver\driver_interface;
use phpbb\controller\helper;

class email_controller
{
	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var functions */
	protected $ext_functions;

	/** @var driver_interface */
	protected $db;

	/** @var helper */
	protected $helper;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor
	 *
	 * @param template				$template			Template object
	 * @param language				$language			Language object
	 * @param functions   			$functions			Common functions for the extension
	 * @param ext_functions   		$ext_functions		Functions for the extension
	 * @param driver_interface		$db					The db connection
	 * @param helper				$helper				Helper object
	 * @param array					$tables				phpBB db tables
	 *
	 * @return \david63\forumsubs\controller\email_controller
	 * @access public
	 */
	public function __construct(template $template, language $language, functions $functions, ext_functions $ext_functions, driver_interface $db, helper $helper, array $tables)
	{
		$this->template			= $template;
		$this->language			= $language;
		$this->functions		= $functions;
		$this->ext_functions	= $ext_functions;
		$this->db				= $db;
		$this->helper			= $helper;
		$this->tables           = $tables;
	}

	/**
	 * Display a forum list popup
	 *
	 * @return   void
	 */
	public function forumpopup()
	{
		// Add the language files
		$this->language->add_lang('forumsubs_popup', $this->functions->get_ext_namespace());

		// Select fora with subscriptions
		$sql_ary = [
			'SELECT'	=> 'f.forum_name, f.forum_id',
			'FROM'		=> [
				$this->tables['forums']			=> 'f',
				$this->tables['forums_watch']	=> 'fw',
			],
			'WHERE'		=> 'f.forum_id = fw.forum_id',
			'ORDER_BY'	=> 'f.parent_id, f.left_id',
		];

		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_ary);

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_ary[] = $row;
		};

		$this->db->sql_freeresult($result);

		foreach ($forum_ary as $forum_id => $forum_data)
		{
			$this->template->assign_block_vars('forumrow', [
				'FORUM_ID'		=> $forum_data['forum_id'],
				'FORUM_NAME'	=> $forum_data['forum_name'],
				'USER_COUNT' 	=> $this->ext_functions->get_subscribed_user_count($forum_data['forum_id']),
			]);
		}

		return $this->helper->render('forumlist_popup.html', $this->language->lang('FORUM_SELECT'));
	}
}
