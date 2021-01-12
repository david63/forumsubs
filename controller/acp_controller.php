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
use phpbb\user;
use phpbb\request\request;
use phpbb\auth\auth;
use phpbb\log\log;
use phpbb\template\template;
use phpbb\language\language;
use david63\forumsubs\core\functions;
use phpbb\db\driver\driver_interface;

/**
 * Event listener
 */
class acp_controller
{
	/** @var user */
	protected $user;

	/** @var request */
	protected $request;

	/** @var auth */
	protected $auth;

	/** @var log */
	protected $log;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var driver_interface */
	protected $db;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var string phpBB tables */
	protected $tables;

	/** @var string */
	protected $ext_images_path;

	/**
	 * Constructor
	 *
	 * @param user					$user				User object
	 * @param request				$request			Request object
	 * @param auth					$auth				Auth object
	 * @param log					$log				Log object
	 * @param template				$template			Template object
	 * @param language				$language			Language object
	 * @param functions   			$functions			Functions for the extension
	 * @param driver_interface		$db					The db connection
	 * @param string				$root_path			phpBB root path
	 * @param string				$php_ext			php ext
	 * @param array					$tables				phpBB db tables
	 * @param string				$ext_images_path	Path to this extension's images
	 *
	 * @return \david63\forumsubs\controller\acp_controller
	 * @access public
	 */
	public function __construct(user $user, request $request, auth $auth, log $log, template $template, language $language, functions $functions, driver_interface $db, string $root_path, string $php_ext, array $tables, string $ext_images_path)
	{
		$this->user				= $user;
		$this->request			= $request;
		$this->auth				= $auth;
		$this->log				= $log;
		$this->template			= $template;
		$this->language			= $language;
		$this->functions		= $functions;
		$this->db				= $db;
		$this->root_path		= $root_path;
		$this->phpEx            = $php_ext;
		$this->tables           = $tables;
		$this->ext_images_path	= $ext_images_path;
	}

	/**
	 * Display a user's forum subscriptions
	 *
	 * @return   void
	 */
	public function acp_forumsubs($event)
	{
		// Add the language files
		$this->language->add_lang(['acp_forumsubs', 'acp_common'], $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'acp_forum_subs';
		add_form_key($form_key);

		$user_id	= $event['user_id'];
		$username	= $event['user_row']['username'];
		$action		= append_sid("{$this->root_path}adm/index.$this->phpEx" . '?i=acp_users&amp;mode=forumsubs&amp;u=' . $user_id);

		// Add/remove subscriptions
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error('FORM_INVALID');
			}

			// Let's remove the user's current subscriptions
			$sql = 'DELETE FROM ' . $this->tables['forums_watch'] . '
				WHERE user_id = ' . (int) $user_id . '';

			$this->db->sql_query($sql);

			// Now we need to add the new subscriptions
			$fora[]			= $this->request->variable('forum_list', [0]);
			$sql_insert_ary = [];

			foreach ($fora as $key => $data)
			{
				foreach ($data as $id => $forum)
				{
					$sql_insert_ary[] = [
						'user_id' 	=> (int) $user_id,
						'forum_id'	=> (int) $forum,
					];
				}
			}

			$this->db->sql_multi_insert($this->tables['forums_watch'], $sql_insert_ary);

			// Add settings change action to the admin log and send updated message
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_USER_FORUM_SUBS', time(), [$username]);
			trigger_error($this->language->lang('FORUM_SUBS_UPDATED') . adm_back_link($action));
		}

		/**
		 * Because of the way the system is written, we need to change to the actual user in order to retrieve
		 * the correct types and methods for the user being viewed - this is nothing more than a HACK
		 */
		$user_data	= $this->change_user($user_id);
		$forums 	= make_forum_select(false, false, false, false, true, false, true);

		foreach ($forums as $forum_id => $forum_data)
		{
			$this->template->assign_block_vars('forum_data', [
				'FORUM_ID'			=> $forum_id,
				'FORUM_NAME'		=> $forum_data['forum_name'],
				// Need to handle forums without a category
				'FORUM_TYPE'		=> ($forum_data['forum_type'] == 1 & $forum_data['parent_id'] == 0) ? 9 : $forum_data['forum_type'],
				'FORUM_PADDING'		=> $forum_data['padding'],
				'FORUM_SUBSCRIBED'	=> $this->functions->is_user_subscribed($forum_id, $user_id),
			]);
		}

		// We are in the ACP, have to have the auths for ACP stuff
		$user_data = $this->change_user($user_id, 'restore', $user_data);

		$version_data	= $this->functions->version_check();
		$valid 			= $this->functions->ext_requirements();

		$this->template->assign_vars([
			'EXT_IMAGE_PATH' 	=> $this->ext_images_path,

			'NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_ACP_USER_SUBS'	=> true,
			'S_VERSION_CHECK' 	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER' 	=> $this->functions->get_meta('version'),
		]);
	}

	/**
	 * Swap the Admin user for the actual user - by RMcGirr83
	 *
	 * @param $user_id   	The user id whose notification types we are looking at
	 * @param $mode      	The mode either replace or restore
	 * @param $bkup_data	An array of the current user's data
	 *
	 * Changes the user in the ACP to that of the user chosen in the ACP
	 *
	 */
	public function change_user($user_id, $mode = 'replace', $bkup_data = false)
	{
		switch ($mode)
		{
			// Change our user to the one being viewed
			case 'replace':
				$bkup_data = ['user_backup' => $this->user->data];

				// Sql to get the user's info
				$sql = 'SELECT *
                    FROM ' . $this->tables['users'] . '
                    WHERE user_id = ' . (int) $user_id;

				$result = $this->db->sql_query($sql);
				$row    = $this->db->sql_fetchrow($result);

				$this->db->sql_freeresult($result);

				$this->user->data = array_merge($this->user->data, $row);

				// Reset the user's auths
				$this->auth->acl($this->user->data);

				unset($row);

				return $bkup_data;
			break;

			// Now we restore the user's stuff
			case 'restore':
				$this->user->data = $bkup_data['user_backup'];

				// Set the auths back to normal
				$this->auth->acl($this->user->data);

				unset($bkup_data);
			break;
		}
	}
}
