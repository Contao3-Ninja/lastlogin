<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package Lastlogin
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'BugBuster',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'BugBuster\LastLogin\LastLogin' => 'system/modules/lastlogin/classes/LastLogin.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_last_login_members'                     => 'system/modules/lastlogin/templates',
	'mod_last_login_members_link'                => 'system/modules/lastlogin/templates',
	'mod_last_login_members_link_avatar'         => 'system/modules/lastlogin/templates',
	'mod_last_login_members_offline'             => 'system/modules/lastlogin/templates',
	'mod_last_login_members_offline_link'        => 'system/modules/lastlogin/templates',
	'mod_last_login_members_offline_link_avatar' => 'system/modules/lastlogin/templates',
));
