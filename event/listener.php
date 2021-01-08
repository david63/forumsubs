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

use david63\forumsubs\controller\main_controller;
use david63\forumsubs\controller\acp_controller;
use david63\forumsubs\controller\ucp_controller;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/**
	 * Constructor for listener
	 *
	 * @param main_controller	$main_controller	Main controller
	 * @param acp_controller	$acp_controller		ACP controller
	 * @param ucp_controller	$ucp_controller		UCP controller
	 *
	 * @access public
	 */
	public function __construct(main_controller $main_controller, acp_controller $acp_controller, ucp_controller $ucp_controller)
	{
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
			'core.permissions' 					=> 'add_permissions',
			'core.viewforum_modify_topics_data' => 'forum_subscriptions',
			'core.acp_users_mode_add' 			=> 'fsub_acp_users',
			'core.memberlist_view_profile'		=> 'ucp_profile_view',
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
}
