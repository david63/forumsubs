<?php
/**
 *
 * @package Forum Subscriptions
 * @copyright (c) 2021 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\forumsubs\controller;

use phpbb\user;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\log\log;
use david63\forumsubs\core\functions;
use david63\forumsubs\core\ext_functions;

class groups_controller
{
	/** @var user */
	protected $user;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var functions */
	protected $functions;

	/** @var functions */
	protected $ext_functions;

	/** @var string */
	protected $ext_images_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor for listener
	 *
	 * @param user				$user       		User object
	 * @param request			$request			Request object
	 * @param template			$template			Template object
	 * @param language			$language			Language object
	 * @param log				$log				Log object
	 * @param functions			$functions			Common functions for the extension
	 * @param ext_functions		$ext_functions		Functions for the extension
	 * @param string			$ext_images_path	Path to this extension's images
	 *
	 * @return \david63\forumsubs\controller\main_controller
	 * @access public
	 */
	public function __construct(user $user, request $request, template $template, language $language, log $log, functions $functions, ext_functions $ext_functions, string $ext_images_path)
	{
		$this->user      		= $user;
		$this->request			= $request;
		$this->template			= $template;
		$this->language			= $language;
		$this->log				= $log;
		$this->functions		= $functions;
		$this->ext_functions	= $ext_functions;
		$this->ext_images_path	= $ext_images_path;
	}

	/**
	 * Controller for forumsubs
	 *
	 * @param string		$forum_id
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function forum_subscriptions()
	{
		// Add the language files
		$this->language->add_lang(['groups_forumsubs', 'acp_common'], $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'group_forum_subs';
		add_form_key($form_key);

		$back 		= false;
		$select 	= true;
		$update		= false;
		$group_name = $forum_options = '';

		// Is the form being submitted?
		if ($this->request->is_set_post('select') || $this->request->is_set_post('update'))
		{
			// Is the submitted form is valid?
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, continue processing
			if ($this->request->is_set_post('select'))
			{
				$update  = true;
				$select = false;

				$forumsubs_group = $this->request->variable('forumsubs_group', 0);

				$hidden_fields = [
					'forumsubs_group' => $forumsubs_group,
				];

				$this->template->assign_var('S_HIDDEN_FIELDS', build_hidden_fields($hidden_fields));

				// Do we have a forum selected?
				if (!$forumsubs_group)
				{
					trigger_error($this->language->lang('NO_GROUP_SELECTED') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$group_name = $this->ext_functions->get_group_name($forumsubs_group);

				// Build the forum selection list
				$subs_fora = json_decode($this->ext_functions->get_group_fora($forumsubs_group));

				$forum_options 	= '';
				$forum_list		= make_forum_select(false, false, true, true, false, false, true);
				$subs_forums	= (!empty($subs_fora)) ? $subs_fora : [];

				foreach ($forum_list as $forum_id => $forum_row)
				{
					$forum_options .= '<option value="' . $forum_id . '"' . ((in_array($forum_id, $subs_forums)) ? ' selected="selected"' : '') . (($forum_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $forum_row['padding'] . $forum_row['forum_name'] . '</option>';
				}
			}
			else if ($this->request->is_set_post('update'))
			{
				$forumsubs_fora 	= json_encode($this->request->variable('forumsubs_forums', [0]));
				$forumsubs_group	= $this->request->variable('forumsubs_group', 0);

				// Now update the tables
				$this->ext_functions->update_tables($forumsubs_group, $forumsubs_fora);

				$group_name = $this->ext_functions->get_group_name($forumsubs_group);

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUMSUBS_GROUPS', time(), [$group_name]);

				// Settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
		}

		// Template vars for header panel
		$version_data = $this->functions->version_check();

		// Are the PHP and phpBB versions valid for this extension?
		$valid = $this->functions->ext_requirements();

		$this->template->assign_vars([
			'DOWNLOAD' 			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'EXT_IMAGE_PATH'	=> $this->ext_images_path,

			'HEAD_TITLE' 		=> $this->language->lang('GROUP_FORUM_SUBSCRIPTIONS'),
			'HEAD_DESCRIPTION' 	=> $this->language->lang('GROUP_SUBSCRIPTIONS_EXPLAIN'),

			'NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_BACK' 			=> $back,
			'S_VERSION_CHECK' 	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER' 	=> $this->functions->get_meta('version'),
		]);

		// Set output vars for display in the template
		$this->template->assign_vars([
			'FORM_SELECT' 			=> $select,
			'FORM_UPDATE' 			=> $update,

			'GROUP_FORUM_SELECT'	=> $forum_options,
			'GROUP_NAME'			=> $this->language->lang('SELECTED_GROUP', $group_name),

			'S_GROUP_SELECT'		=> group_select_options(0, [$this->ext_functions->get_bot_group()]),

			'U_ACTION' 				=> $this->u_action,
		]);
	}
}
