<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * 
 * Utility LastLogin - Config
 * 
 * PHP version 5
 * @copyright  Glen Langer 2011
 * @author     Glen Langer
 * @package    GLLastLogin
 * @license    LGPL
 * @version    1.8.0
 */

/**
 * Register hook functions
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('LastLogin', 'LLreplaceInsertTags');


/**
 * Abschaltung der Login Bedingung mit "false", Default ist "true"
 * Updatesicher sollte dies in der localconfig.php eingetragen werden.
 * Vorher Frontend Nutzer Einverständnis einholen.
 * 
 * $GLOBALS['TL_CONFIG']['mod_lastlogin_login_check'] = false;
 * 
 */

?>