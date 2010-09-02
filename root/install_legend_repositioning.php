<?php

/**
*
* @author Tom (Tom Catullo) tom@cortello.com 
* @package umil
* @version $Id install_legend_repositioning.php 1.0.3 2010-02-16 17:45:00GMT Tom $
* @copyright (c) 2009 Cortello Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/umil_legend_repositioning_mod');

if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// We only allow a founder to install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		login_box('', 'LOGIN');
	}

	trigger_error('NOT_AUTHORISED');
}

if (!class_exists('umil'))
{
	include($phpbb_root_path . 'umil/umil.' . $phpEx);
}

$umil = new umil(true);

$mod = array(
	'name'		=> 'Legend Repositioning',
	'version'	=> '1.0.3',
	'config'	=> 'legend_repositioning_version',
	'enable'	=> 'legend_repositioning_enable',
);

if (confirm_box(true))
{
	// Install the base 1.0.3 version
	if (!$umil->config_exists($mod['config']))
	{
		// Lets add a config setting for enabling/disabling the MOD and set it to true
		$umil->config_add($mod['enable'], true);

		// We must handle the version number ourselves.
		$umil->config_add($mod['config'], $mod['version']);


		$umil->table_column_add('phpbb_groups', 'group_position', array('UINT', '0'));

		// Select all groups that have no set position
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . '
			WHERE group_position = 0';
		$result = $db->sql_query($sql);

		// Set default positions for groups (so that all positions are not set to 0)
		$group_position = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$group_position++;
			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_position = ' . $group_position . '
				WHERE group_id = ' . $row['group_id'];
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		// Our final action, we purge the board cache
		$umil->cache_purge();
	}

	// We are done
	trigger_error('Done!');
}
else
{
	confirm_box(false, 'INSTALL_TEST_MOD');
}

// Shouldn't get here.
redirect($phpbb_root_path . $user->page['page_name']);


?>