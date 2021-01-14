<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\core;

use phpbb\user;
use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use david63\forumsubs\core\functions;

/**
 * functions
 */
class ext_functions
{
	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var array phpBB tables */
	protected $tables;

	/** @var functions */
	protected $functions;

	/**
	 * Constructor for functions
	 *
	 * @param user					$user			User object
	 * @param auth					$auth			Auth object
	 * @param driver_interface		$db				The db connection
	 * @param language				$language		Language object
	 * @param array					$tables			phpBB db tables
	 * @param functions				$functions		Functions for the extension
	 *
	 * @access public
	 */
	public function __construct(user $user, auth $auth, driver_interface $db, language $language, array $tables, functions $functions)
	{
		$this->user			= $user;
		$this->auth			= $auth;
		$this->db			= $db;
		$this->language		= $language;
		$this->tables		= $tables;
		$this->functions	= $functions;
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

	/**
	* Get count of user's posts for a forum
	*
	* @return $post_count
	* @access public
	*/
	public function get_user_post_count($forum_id, $user_id)
	{
		$sql = 'SELECT COUNT(forum_id) AS post_count
			FROM ' . $this->tables['posts'] . "
				WHERE forum_id	= $forum_id
				AND poster_id 	= $user_id";

		$result		= $this->db->sql_query($sql);
		$post_count	= (int) $this->db->sql_fetchfield('post_count');

		$this->db->sql_freeresult($result);

		return $post_count;
	}

	/**
	* Is user subscribed to a forum
	*
	* @return $post_count
	* @access public
	*/
	public function is_user_subscribed($forum_id, $user_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->tables['forums_watch'] . "
				WHERE forum_id	= $forum_id
				AND user_id 	= $user_id";

		$result		= $this->db->sql_query($sql);
		$subscribed = ($result->num_rows != 0) ? true : false;

		$this->db->sql_freeresult($result);

		return $subscribed;
	}

	/**
	* Get the number of users subscribed to a forum
	*
	* @return $user_count
	* @access public
	*/
	public function get_subscribed_user_count($forum_id)
	{
		$sql = 'SELECT COUNT(user_id) AS user_count
			FROM ' . $this->tables['forums_watch'] . "
				WHERE forum_id	= $forum_id";

		$result		= $this->db->sql_query($sql);
		$user_count	= (int) $this->db->sql_fetchfield('user_count');

		$this->db->sql_freeresult($result);

		return $user_count;
	}

	/**
	* Get the users subscribed to a forum
	*
	* @return $users
	* @access public
	*/
	public function get_subscribed_users($forum_id)
	{
		// Add the language files
		$this->language->add_lang('acp_forumsubs', $this->functions->get_ext_namespace());

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

		$users = ($result->num_rows == 0) ? $this->language->lang('NO_SUBSCRIBERS') : '';

		while ($row = $this->db->sql_fetchrow($result))
		{
			$users .= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) . $this->language->lang('COMMA_SEPARATOR');
		}

		$this->db->sql_freeresult($result);

		// Remove last comma before returning
		return substr($users, 0, -strlen($this->language->lang('COMMA_SEPARATOR')));
	}
}
