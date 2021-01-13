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
use phpbb\language\language;
use david63\forumsubs\core\functions;
use david63\forumsubs\core\ext_functions;

/**
 * Admin controller
 */
class admin_controller
{
	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var functions */
	protected $functions;

	/** @var functions */
	protected $ext_functions;

	/** @var string */
	protected $ext_images_path;

	/**
	 * Constructor for admin controller
	 *
	 * @param template			$template       	Template object
	 * @param language			$language       	Language object
	 * @param functions			$functions      	Functions for the extension
	 * @param ext_functions		$ext_functions		Functions for the extension
	 * @param string			$ext_images_path	Path to this extension's images
	 *
	 * @return \david63\forumsubs\controller\admin_controller
	 *
	 * @access public
	 */
	public function __construct(template $template, language $language, functions $functions, ext_functions $ext_functions, string $ext_images_path)
	{
		$this->template   		= $template;
		$this->language   		= $language;
		$this->functions  		= $functions;
		$this->ext_functions	= $ext_functions;
		$this->ext_images_path	= $ext_images_path;
	}

	/**
	 * Display the output for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_output()
	{
		// Add the language files
		$this->language->add_lang(['acp_forumsubs', 'acp_common'], $this->functions->get_ext_namespace());

		$back = false;

		$forums = make_forum_select(false, false, false, false, true, false, true);

		foreach ($forums as $forum_id => $forum_data)
		{
			$this->template->assign_block_vars('forum_data', [
				'FORUM_ID'			=> $forum_id,
				'FORUM_NAME'		=> $forum_data['forum_name'],
				// Need to handle forums without a category
				'FORUM_TYPE'		=> ($forum_data['forum_type'] == 1 & $forum_data['parent_id'] == 0) ? 9 : $forum_data['forum_type'],
				'FORUM_PADDING'		=> $forum_data['padding'],
				'SUBSCRIBED_COUNT'	=> $this->ext_functions->get_subscribed_user_count($forum_id),
				'SUBSCRIBERS'		=> $this->ext_functions->get_subscribed_users($forum_id),
			]);
		}

		// Template vars for header panel
		$version_data = $this->functions->version_check();

		// Are the PHP and phpBB versions valid for this extension?
		$valid = $this->functions->ext_requirements();

		$this->template->assign_vars([
			'DOWNLOAD' 			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'EXT_IMAGE_PATH'	=> $this->ext_images_path,

			'HEAD_TITLE' 		=> $this->language->lang('FORUM_SUBSCRIPTIONS'),
			'HEAD_DESCRIPTION' 	=> $this->language->lang('SUBSCRIPTIONS_EXPLAIN'),

			'NAMESPACE' 		=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_BACK' 			=> $back,
			'S_VERSION_CHECK' 	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER' 	=> $this->functions->get_meta('version'),
		]);
	}
}
