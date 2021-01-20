<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbb\controller\helper;
use phpbb\language\language;
use david63\forumsubs\core\functions;
use david63\forumsubs\core\ext_functions;
use david63\forumsubs\controller\main_controller;
use david63\forumsubs\controller\acp_controller;
use david63\forumsubs\controller\ucp_controller;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var functions */
	protected $ext_functions;

	/** @var main_controller */
	protected $main_controller;

	/** @var acp_controller */
	protected $acp_controller;

	/** @var admin_controller */
	protected $admin_controller;

	/**
	 * Constructor for listener
	 *
	 * @param helper			$helper				Helper object
	 * @param language			$language			Language object
	 * @param functions   		$functions			Common functions for the extension
	 * @param ext_functions   	$ext_functions		Functions for the extension
	 * @param main_controller	$main_controller	Main controller
	 * @param acp_controller	$acp_controller		ACP controller
	 * @param ucp_controller	$ucp_controller		UCP controller
	 *
	 * @access public
	 */
	public function __construct(helper $helper, language $language, functions $functions, ext_functions $ext_functions, main_controller $main_controller, acp_controller $acp_controller, ucp_controller $ucp_controller)
	{
		$this->helper			= $helper;
		$this->language			= $language;
		$this->functions		= $functions;
		$this->ext_functions	= $ext_functions;
		$this->main_controller	= $main_controller;
		$this->acp_controller	= $acp_controller;
		$this->ucp_controller	= $ucp_controller;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions' 						=> 'add_permissions',
			'core.viewforum_modify_topics_data' 	=> 'forum_subscriptions',
			'core.acp_users_mode_add' 				=> 'fsub_acp_users',
			'core.memberlist_view_profile'			=> 'ucp_profile_view',
			'core.acp_email_display'				=> 'add_mass_email',
			'core.acp_email_modify_sql'				=> 'modify_mass_email_sql',
		);
	}

	/**
	 * Add the new permissions
	 *
	 * @param object $event The event object
	 *
	 * @return null
	 * @access public
	 */
	public function add_permissions($event)
	{
		$permissions						= $event['permissions'];
		$permissions['u_forumsubs_view']	= ['lang' => 'ACL_U_VIEW_FORUM_SUBSCRIPTIONS', 'cat' => 'misc'];
		$event['permissions']				= $permissions;
	}

	/**
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function forum_subscriptions($event)
	{
		$this->main_controller->forum_subscriptions($event['forum_id']);
	}

	/**
	 * Process the ACP user data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function fsub_acp_users($event)
	{
		if ($event['mode'] == 'forumsubs')
		{
			$this->acp_controller->acp_forumsubs($event);
		}
	}

	/**
	* Display the subscriptions in the user's profile
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function ucp_profile_view($event)
	{
		$this->ucp_controller->ucp_forumsubs($event['member']['user_id']);
	}

	/**
	 * Add mass email options
	 *
	 * @param object $event The event object
	 *
	 * @return $event
	 * @access public
	 */
	public function add_mass_email($event)
	{
		// Add the language files
		$this->language->add_lang('forumsubs_email', $this->functions->get_ext_namespace());

		$template_data 					= $event['template_data'];
		$template_data['U_FIND_FORUM']	= $this->helper->route('david63_forumsubs_forumpopup');
		$event['template_data'] 		= $template_data;
	}

	/**
	 * Modify the mass email sql
	 *
	 * @param object $event The event object
	 *
	 * @return $event
	 * @access public
	 */
	public function modify_mass_email_sql($event)
	{
		$sql_ary	= $event['sql_ary'];
		$where 		= $sql_ary['WHERE'];

		// Is this a subscribed forum request?
		if (strpos($where, "'fid") !== false)
		{
			// Let's extract the forum ids from the sql
			$lastpos 		= 0;
			$forum_id_ary	= [];

			while (($lastpos = strpos($where, 'fid', $lastpos)) !== false)
			{
				$first_char 	= $lastpos;
				$last_char 		= strpos($where, "'", $lastpos) - 3;
    			$forum_id_ary[]	= substr($where, $first_char + 3, $last_char - $first_char);
				$lastpos 		= $lastpos + 3;
			}

			$subscribed_users = '';

			foreach ($forum_id_ary as $forum_id)
			{
				// Now we need a list of subscribed members
				if ($forum_id)
				{
					$subscribed_users .= $this->ext_functions->get_subscribed_users($forum_id, false);
				}
			}

			if ($subscribed_users)
			{
				// Remove duplicates from the list
				$subscribed_users = implode(',', array_unique(explode(',', $subscribed_users)));

				// Re-write the WHERE clause
				$sql_ary['WHERE'] = 'username_clean IN (' . $subscribed_users . ') AND user_allow_massemail = 1';
			}
			else
			{
				$sql_ary['WHERE'] = $where;
			}
		}

		$event['sql_ary'] = $sql_ary;
	}
}
