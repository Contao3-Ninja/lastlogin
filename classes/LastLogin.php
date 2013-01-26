<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Utility LastLogin
 *
 * @copyright  Glen Langer 2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    LastLogin
 * @license    LGPL
 * @version    3.0.0
 * @filesource
 * @see	       https://github.com/BugBuster1701/lastlogin
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\LastLogin;

/**
 * Class LastLogin
 * 
 * From TL 2.8 you can use prefix "cache_". The InserTag will be not cached nows. (when "cache" is enabled)
 * 
 * Last Login:
 * {{cache_last_login}}
 * {{cache_last_login::d.m.Y}}
 * {{cache_last_login::zero}}
 * {{cache_last_login::zero::d.m.Y}}
 * 
 * Members Online:
 * Display of names separated by commas.
 * {{cache_last_login_members_online}}
 * {{cache_last_login_members_online::username}}
 * {{cache_last_login_members_online::firstname}}
 * {{cache_last_login_members_online::lastname}}
 * {{cache_last_login_members_online::fullname}}
 * {{cache_last_login_members_online::avatar}}  // funktioniert, macht aber keinen Sinn mit Komma dazwischen...
 * 
 * Display of names as unordered list.
 * {{cache_last_login_members_online::list}}
 * {{cache_last_login_members_online::username::list}}
 * {{cache_last_login_members_online::firstname::list}}
 * {{cache_last_login_members_online::lastname::list}}
 * {{cache_last_login_members_online::fullname::list}}
 * {{cache_last_login_members_online::avatar::list}}
 * {{cache_last_login_members_online::avatar::list::5}}
 * 
 * Members Online linked (for Memberlist Module):
 * Display of names as unordered list linked 
 * (here: memberlist = alias name of page with memberlist module)
 * {{cache_last_login_members_online_link::username::memberlist}}
 * {{cache_last_login_members_online_link::firstname::memberlist}}
 * {{cache_last_login_members_online_link::lastname::memberlist}}
 * {{cache_last_login_members_online_link::fullname::memberlist}}
 * {{cache_last_login_members_online_link::avatar::memberlist}}
 * {{cache_last_login_members_online_link::avatar::memberlist::5}}
 * 
 * Display number of registered members
 * {{cache_last_login_number_registered_members}}
 * 
 * Display number of online members
 * {{cache_last_login_number_online_members}}
 * 
 * Display number of offline members (logout today)
 * {{cache_last_login_number_offline_members}}
 * 
 * Members Offline:
 * Display of names separated by commas.
 * {{cache_last_login_members_offline}}
 * {{cache_last_login_members_offline::username}}
 * {{cache_last_login_members_offline::firstname}}
 * {{cache_last_login_members_offline::lastname}}
 * {{cache_last_login_members_offline::fullname}}
 * {{cache_last_login_members_offline::avatar}}  // funktioniert, macht aber keinen Sinn mit Komma dazwischen...
 * 
 * Display of names as unordered list.
 * {{cache_last_login_members_offline::list}}
 * {{cache_last_login_members_offline::username::list}}
 * {{cache_last_login_members_offline::firstname::list}}
 * {{cache_last_login_members_offline::lastname::list}}
 * {{cache_last_login_members_offline::fullname::list}}
 * {{cache_last_login_members_offline::avatar::list::5}}
 * 
 * Members Offline linked (for Memberlist Module)
 * Display of names as unordered list linked 
 * (here: memberlist = alias name of page with memberlist module)
 * {{cache_last_login_members_offline_link::username::memberlist}}
 * {{cache_last_login_members_offline_link::firstname::memberlist}}
 * {{cache_last_login_members_offline_link::lastname::memberlist}}
 * {{cache_last_login_members_offline_link::fullname::memberlist}}
 * {{cache_last_login_members_offline_link::avatar::memberlist}}
 * {{cache_last_login_members_offline_link::avatar::memberlist::5}}
 * 
 * 
 * @copyright  Glen Langer 2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    LastLogin
 */
class LastLogin extends \Frontend
{
    /**
     * Array with splitted Tag parts
     * @var mixed
     */
    private $arrTag = false;

    /**
     * Login check needed
     * @var bool
     */
    private $login_check = false;

    /**
     * SQL part for avatar extension
     * @var string
     */
    private $avatar = '';

    /**
     * LastLogin Replace Insert-Tag Main Methode
     * @param string $strTag
     * @return mixed    false: no correct tag
     *                  string: return value of the Insert-Tag
     * @access public
     */
    public function ReplaceInsertTagsLastLogin($strTag)
    {
        if (!in_array('memberlist', $this->Config->getActiveModules())) 
        {
            //memberlist Modul fehlt, Abbruch
            $this->log('memberlist extension required!', 'LastLogin replaceInsertTags', 'ERROR');
            return '';
        }
        
        if (in_array('avatar', $this->Config->getActiveModules())) 
        {
            $this->avatar = ',avatar'; //tested with avatar 1.0.1 stable
        }
        
        if (FE_USER_LOGGED_IN) 
        {
            $this->login_check = true;
        } 
        else 
        {
            if (isset($GLOBALS['TL_CONFIG']['mod_lastlogin_login_check']) &&
                      $GLOBALS['TL_CONFIG']['mod_lastlogin_login_check'] === false) 
            {
                $this->login_check = true;
            }
        }
        
        $this->arrTag = trimsplit('::', $strTag);
        switch ($this->arrTag[0]) 
        {
            case "last_login":
            case "cache_last_login":
                return $this->LL_last_login();
                break;
            case "last_login_members_online":
            case "cache_last_login_members_online":
                return $this->LL_last_login_members_online();
                break;
            case "last_login_members_online_link":
            case "cache_last_login_members_online_link":
                return $this->LL_last_login_members_online_link();
                break;
            case "last_login_number_registered_members":
            case "cache_last_login_number_registered_members":
                return $this->LL_last_login_number_registered_members();
                break;
            case "last_login_number_online_members":
            case "cache_last_login_number_online_members":
                return $this->LL_last_login_number_online_members();
                break;
            case "last_login_number_offline_members":
            case "cache_last_login_number_offline_members":
                return $this->LL_last_login_number_offline_members();
                break;
            case "last_login_members_offline":
            case "cache_last_login_members_offline":
                return $this->LL_last_login_members_offline();
                break;
            case "last_login_members_offline_link":
            case "cache_last_login_members_offline_link":
                return $this->LL_last_login_members_offline_link();
                break;
            default:
                //not for me
                return false;
        }
    } //function LLreplaceInsertTags

    /**
     * Insert-Tag: Last Login
     * @return mixed    false: FE user not logged in
     *                  string: return value of the Insert-Tag
     * @access private
     */
    private function LL_last_login ()
    {
        //member last login
        // {{cache_last_login}}
        // {{cache_last_login::d.m.Y}}
        // {{cache_last_login::zero}}
        // {{cache_last_login::zero::d.m.Y}}
        if (FE_USER_LOGGED_IN) 
        {
            $this->import('FrontendUser', 'User');
            $strDate = '';
            $zero = false;
            $strDateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
            if ($this->User->id !== null) 
            {
                $objLogin = \Database::getInstance()->prepare("SELECT lastLogin FROM tl_member WHERE id=?")
                                                    ->limit(1)
                                                    ->execute($this->User->id);
                // zero Parameter angegeben? 
                if (isset($this->arrTag[1]) &&
                          $this->arrTag[1] == 'zero') 
                {
                    $zero = true;
                }
                // date Definition angegeben und != zero?
                if (isset($this->arrTag[1]) &&
                          $this->arrTag[1] != 'zero') 
                {
                    $strDateFormat = $this->arrTag[1]; // date
                }
                // wenn zweiter Parameter, muss date Definition sein
                if (isset($this->arrTag[2])) 
                {
                    $strDateFormat = $this->arrTag[2]; // date
                }
                
                // Auswertung
                if ($objLogin->lastLogin > 0) 
                {
                    $strDate = date($strDateFormat, $objLogin->lastLogin);
                } // first login
                elseif ($zero) 
                {
                    $strDate = 0;
                } 
                else 
                {
                    $strDate = date($strDateFormat);
                }
                return $strDate;
            } //$this->User->id
        } //FE_USER_LOGGED_IN
        return false;
    }
    
    /**
     * Insert-Tag: Last Login Members Online
     * @return mixed    false: FE user not logged in
     *                  string: return value of the Insert-Tag
     * @access private
     */
    private function LL_last_login_members_online ()
    {
        //members online
        if ($this->login_check) 
        {
            $this->loadLanguageFile('tl_last_login');
            $this->import('FrontendUser', 'User');
            // ::id
            // ::username
            // ::firstname
            // ::lastname
            // ::fullname
            if (!isset($this->arrTag[1])) 
            {
                $this->arrTag[1] = 'username'; // Default Angabe
            }
            if ($this->arrTag[1] == 'list') 
            {
                $this->arrTag[1] = 'username'; // Default fuer Liste
                if (isset($this->arrTag[2])) 
                {
                    $this->arrTag[3] = $this->arrTag[2]; // Zahl sichern wenn vorhanden
                }
                $this->arrTag[2] = 'list'; // list von 1 nach 2 verschieben
            }
            switch ($this->arrTag[1]) 
            {
                case 'id':
                    $llmo_name = 'tlm.id as name';
                    break;
                case 'username':
                    $llmo_name = 'tlm.username as name';
                    break;
                case 'firstname':
                    $llmo_name = 'tlm.firstname as name';
                    break;
                case 'lastname':
                    $llmo_name = 'tlm.lastname as name';
                    break;
                case 'fullname':
                    $llmo_name = 'CONCAT(tlm.firstname," ",tlm.lastname) as name';
                    break;
                default:
                    $llmo_name = 'tlm.username as name';
                    break;
            }
            // alle die eine zeitlich gueltige Session haben
            $objUsers = \Database::getInstance()->prepare("SELECT DISTINCT tlm.id, " . $llmo_name . ", publicFields" . $this->avatar ."
                                                           FROM tl_member tlm, tl_session tls 
                                                           WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
                                                ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'], 'FE_USER_AUTH');
            if ($objUsers->numRows < 1) 
            {
                $MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
            } 
            else 
            {
                $arrUser = array();
                while ($objUsers->next()) 
                {
                    //auf Public Freigabe pruefen
                    $publicFields = deserialize($objUsers->publicFields, true);
                    switch ($this->arrTag[1]) 
                    {
                        case 'id':
                            $arrUser[] = $objUsers->name;
                            break;
                        case 'username':
                            $arrUser[] = $objUsers->name;
                            break;
                        case 'firstname':
                            if (in_array('firstname', $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'lastname':
                            if (in_array('lastname', $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'fullname':
                            if (in_array('firstname', $publicFields) &&
                                in_array('lastname', $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'avatar':
                            if (in_array('avatar', $this->Config->getActiveModules())) 
                            {
                                //avatar Modul vorhanden
                                //Umweg damit Default Bild kommt
                                $this->avatarFile = Avatar::filename($objUsers->avatar);
                                $arrUser[] = Avatar::img($this->avatarFile);
                            }
                            break;
                        default:
                            $arrUser[] = $objUsers->name;
                            break;
                    }
                }
                if (isset($this->arrTag[2]) && $this->arrTag[2] == 'list') 
                {
                    // unordered list
                    /*
                    <div class="mod_last_login">
                      <ul class="members_online">
                        <li>Donna Evans</li>
                        <li>John Smith</li>
                      </ul>
                    </div>
                    */
                    $objTemplate = new \FrontendTemplate('mod_last_login_members');
                    $objTemplate->users = $arrUser;
                    if (isset($this->arrTag[3])) 
                    {
                        $objTemplate->count = (int) $this->arrTag[3];
                    } 
                    else 
                    {
                        $objTemplate->count = false;
                    }
                    $MembersOnline = $objTemplate->parse();
                } 
                else 
                {
                    // comma separated
                    $MembersOnline = implode(', ', $arrUser);
                }
            }
            return $MembersOnline;
        }
        return false;
    }

    /**
     * Insert-Tag: Last Login Members Online Link
     * @return mixed    false: FE user not logged in
     *                  string: return value of the Insert-Tag
     * @access private
     */
    private function LL_last_login_members_online_link ()
    {
        //members online link
        if ($this->login_check) 
        {
            $this->loadLanguageFile('tl_last_login');
            $this->import('FrontendUser', 'User');
            // ::username
            // ::firstname
            // ::lastname
            // ::fullname
            if (! isset($this->arrTag[1])) 
            {
                $this->arrTag[1] = 'username'; // Default Angabe
            }
            if (! isset($this->arrTag[2])) 
            {
                $this->arrTag[2] = 'memberlist'; // Default Angabe
            }
            switch ($this->arrTag[1]) 
            {
                case 'username':
                    $llmo_name_id = 'tlm.username as name, tlm.id as id';
                    break;
                case 'firstname':
                    $llmo_name_id = 'tlm.firstname as name, tlm.id as id';
                    break;
                case 'lastname':
                    $llmo_name_id = 'tlm.lastname as name, tlm.id as id';
                    break;
                case 'fullname':
                    $llmo_name_id = 'CONCAT(tlm.firstname," ",tlm.lastname) as name, tlm.id as id';
                    break;
                default:
                    $llmo_name_id = 'tlm.username as name, tlm.id as id';
                    break;
            }
            // alle die eine zeitlich gueltige Session haben
            $objUsers = \Database::getInstance()->prepare("SELECT DISTINCT " . $llmo_name_id . ", publicFields" . $this->avatar ."
                                                           FROM tl_member tlm, tl_session tls 
                                                           WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
                                                ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'], 'FE_USER_AUTH');
            if ($objUsers->numRows < 1) 
            {
                $MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
            } 
            else 
            {
                $arrUser = array();
                while ($objUsers->next()) 
                {
                    //auf Public Freigabe pruefen
                    $publicFields = deserialize($objUsers->publicFields, true);
                    switch ($this->arrTag[1]) 
                    {
                        case 'id':
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                        case 'username':
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                        case 'firstname':
                            if (in_array('firstname', $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'lastname':
                            if (in_array('lastname', $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'fullname':
                            if (in_array('firstname', $publicFields) &&
                                in_array('lastname' , $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'avatar':
                            if (in_array('avatar', $this->Config->getActiveModules())) 
                            {
                                //avatar Modul vorhanden
                                //Umweg damit Default Bild kommt
                                $this->avatarFile = Avatar::filename($objUsers->avatar);
                                // username, id, Bild
                                $arrUser[] = array($objUsers->name, $objUsers->id,Avatar::img($this->avatarFile));
                            }
                            break;
                        default:
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                    }
                }
                // unordered list
                if ($this->arrTag[1] != 'avatar') 
                {
                    $objTemplate = new \FrontendTemplate('mod_last_login_members_link');
                    /*
                    <div class="mod_last_login">
                      <ul class="members_online_link">
                        <li><a href="memberlist.html?show=4" title="Profile view">Donna Evans</a></li>
                        <li><a href="memberlist.html?show=5" title="Profile view">John Smith</a></li>
                      </ul>
                    </div>
                    */
                } 
                else 
                {
                    $objTemplate = new \FrontendTemplate('mod_last_login_members_link_avatar');
                    /*
                    <div class="mod_last_login">
                      <ul class="members_online_link_avatar">
                        <li><a href="memberlist.html?show=4" title="Profile view Donna Evans"><img width="32" height="31" class="avatar" alt="avatar" src="tl_files/avatars/member_4.jpg"></a></li>
                        <li><a href="memberlist.html?show=5" title="Profile view John Smith"><img width="32" height="31" class="avatar" alt="avatar" src="tl_files/avatars/member_5.jpg"></a></li>
                      </ul>
                    </div>
                    */
                }
                if (isset($this->arrTag[3])) 
                {
                    $objTemplate->count = (int) $this->arrTag[3];
                } 
                else 
                {
                    $objTemplate->count = false;
                }
                $objTemplate->users = $arrUser;
                $strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . $this->arrTag[2] . $GLOBALS['TL_CONFIG']['urlSuffix'];
                $objTemplate->memberlink = $strUrl;
                $objTemplate->title = $GLOBALS['TL_LANG']['last_login']['profile'];
                $MembersOnline = $objTemplate->parse();
            }
            return $MembersOnline;
        }
        return false;
    }

    /**
     * Insert-Tag: Last Login Number Registered Members (aktiv, login allowed)
     * @return integer    number of registered members
     * @access private
     */
    private function LL_last_login_number_registered_members ()
    {
        //number of registered members
        $objLogin = \Database::getInstance()->prepare("SELECT count(`id`) AS ANZ FROM `tl_member` 
                                                       WHERE `disable`!=? AND `login`=?")
                                            ->limit(1)
                                            ->execute(1, 1);
        return $objLogin->ANZ;
    }

    /**
     * Insert-Tag: Last Login Number Online Members
     * @return integer    number of online members
     * @access private
     */
    private function LL_last_login_number_online_members ()
    {
        //number of online members
        // alle die eine zeitlich gueltige Session haben
        $objUsers = \Database::getInstance()->prepare("SELECT count(DISTINCT username) AS ANZ 
                                                       FROM tl_member tlm, tl_session tls
                                                       WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
                                            ->limit(1)
                                            ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'], 'FE_USER_AUTH');
        if ($objUsers->numRows < 1) 
        {
            $NumberMembersOnline = 0;
        } else {
            $NumberMembersOnline = $objUsers->ANZ;
        }
        return $NumberMembersOnline;
    }

    /**
     * Insert-Tag: Last Login Number Offline Members
     * @return integer    number of offline members
     * @access private 
     */
    private function LL_last_login_number_offline_members ()
    {
        //number of offline members
        //die heute einmal Online waren und jetzt Offline sind (inaktiv oder heute abgemeldet)
        //$llmo_name = 'tlm.username as name';
        $llmo = 'tlm.id';
        // Alle (aktive) 
        // abzueglich alle die eine zeitlich gueltige Session haben (online aktiv)
        // abzueglich gestern oder aelter angemeldet und wieder abgemeldet (ohne Session)
        // = offline members (lange inaktiv oder heute abgemeldet) 
        $objUsers = \Database::getInstance()->prepare("SELECT COUNT(" . $llmo . ") as ANZ FROM tl_member tlm 
                                           WHERE `disable`!=? AND `login`=? AND " . $llmo . "
                                           NOT IN (
                                               SELECT " . $llmo . "
                                               FROM tl_member tlm, tl_session tls 
                                               WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?
                                               )
                                           AND " . $llmo . "
                                           NOT IN ( 
                                               SELECT " . $llmo . "
                                               FROM tl_member tlm 
                                               WHERE tlm.currentLogin<= ?
                                               AND " . $llmo . "
                                               NOT IN (SELECT DISTINCT pid AS id FROM tl_session
                                                       WHERE name=?)
                                               )"
                                            )
                                   ->execute(1,1
                                            ,time() - $GLOBALS['TL_CONFIG']['sessionTimeout']
                                            ,'FE_USER_AUTH'
                                            ,mktime(0, 0, 0, date("m"), date("d"), date("Y"))
                                            ,'FE_USER_AUTH'
                                           );
        $NumberMembersOffline = $objUsers->ANZ;
        return $NumberMembersOffline;
    }

    /**
     * Insert-Tag: Last Login Members Offline
     * @return string    false: FE user not logged in
     *                   string: members offline
     * @access private 
     */
    private function LL_last_login_members_offline ()
    {
        //members offline
        if ($this->login_check) 
        {
            $this->loadLanguageFile('tl_last_login');
            // ::username
            // ::firstname
            // ::lastname
            // ::fullname
            if (!isset($this->arrTag[1])) 
            {
                $this->arrTag[1] = 'username'; // Default Angabe
            }
            if ($this->arrTag[1] == 'list') 
            {
                $this->arrTag[1] = 'username'; // Default fuer Liste
                if (isset($this->arrTag[2])) 
                {
                    $this->arrTag[3] = $this->arrTag[2]; // Zahl sichern wenn vorhanden
                }
                $this->arrTag[2] = 'list';
            }
            switch ($this->arrTag[1]) 
            {
                case 'username':
                    $llmo_name = 'tlm.username as name';
                    $llmo = 'tlm.id';
                    break;
                case 'firstname':
                    $llmo_name = 'tlm.firstname as name';
                    $llmo = 'tlm.id';
                    break;
                case 'lastname':
                    $llmo_name = 'tlm.lastname as name';
                    $llmo = 'tlm.id';
                    break;
                case 'fullname':
                    $llmo_name = 'CONCAT(tlm.firstname," ",tlm.lastname) as name';
                    $llmo = 'tlm.id';
                    break;
                default:
                    $llmo_name = 'tlm.username as name';
                    $llmo = 'tlm.id';
                    break;
            }
            // Alle (aktive) 
            // abzueglich alle die eine zeitlich gueltige Session haben (online aktiv)
            // abzueglich gestern oder aelter angemeldet und wieder abgemeldet (ohne Session)
            // = offline members (lange inaktiv oder heute abgemeldet) 
            $objUsers = \Database::getInstance()->prepare("SELECT " . $llmo_name . ", publicFields" . $this->avatar . "
                                               FROM tl_member tlm 
                                               WHERE `disable`!=? AND `login`=? AND " . $llmo . "
                                               NOT IN (
                                                   SELECT " . $llmo . "
                                                   FROM tl_member tlm, tl_session tls
                                                   WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?
                                                   )
                                               AND " . $llmo . "
                                               NOT IN ( 
                                                   SELECT " . $llmo . "
                                                   FROM tl_member tlm 
                                                   WHERE tlm.currentLogin<= ?
                                                   AND " . $llmo . "
                                                   NOT IN (SELECT DISTINCT pid AS id FROM tl_session
                                                           WHERE name=?)
                                                   )"
                                                )
                                        ->execute(1,1
                                                 ,time() - $GLOBALS['TL_CONFIG']['sessionTimeout']
                                                 ,'FE_USER_AUTH'
                                                 ,mktime(0, 0, 0, date("m"), date("d"), date("Y"))
                                                 ,'FE_USER_AUTH'
                                                );
            if ($objUsers->numRows < 1) 
            {
                $MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
            } 
            else 
            {
                $arrUser = array();
                while ($objUsers->next()) 
                {
                    $publicFields = deserialize($objUsers->publicFields, true);
                    switch ($this->arrTag[1]) 
                    {
                        case 'id':
                            $arrUser[] = $objUsers->name;
                            break;
                        case 'username':
                            $arrUser[] = $objUsers->name;
                            break;
                        case 'firstname':
                            if (in_array('firstname', $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'lastname':
                            if (in_array('lastname', $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'fullname':
                            if (in_array('firstname', $publicFields) &&
                                in_array('lastname' , $publicFields)) 
                            {
                                $arrUser[] = $objUsers->name;
                            }
                            break;
                        case 'avatar':
                            if (in_array('avatar', $this->Config->getActiveModules())) 
                            {
                                //avatar Modul vorhanden
                                //Umweg damit Default Bild kommt
                                $this->avatarFile = Avatar::filename($objUsers->avatar);
                                $arrUser[] = Avatar::img($this->avatarFile);
                            }
                            break;
                        default:
                            $arrUser[] = $objUsers->name;
                            break;
                    }
                }
                if (isset($this->arrTag[2]) && $this->arrTag[2] == 'list') 
                {
                    // unordered list
                    /*
                    <div class="mod_last_login_offline">
                    <ul class="members_offline">
                      <li>Donna Evans</li>
                      <li>John Smith</li>
                    </ul>
                    </div>
                    */
                    $objTemplate = new \FrontendTemplate('mod_last_login_members_offline');
                    if (isset($this->arrTag[3])) 
                    {
                        $objTemplate->count = (int) $this->arrTag[3];
                    } 
                    else 
                    {
                        $objTemplate->count = false;
                    }
                    $objTemplate->users = $arrUser; //array_unique($arrUser);
                    $MembersOnline = $objTemplate->parse();
                } 
                else 
                {
                    // comma separated
                    $MembersOnline = implode(', ', $arrUser); //implode(', ', array_unique($arrUser));
                }
            }
            return $MembersOnline;
        }
        return false;
    }

    /**
     * Insert-Tag: Last Login Members Offline Link
     * @return string    false: FE user not logged in
     *                   string: members offline linked
     * @access private
     */
    private function LL_last_login_members_offline_link ()
    {
        //members offline link
        if ($this->login_check) 
        {
            $this->loadLanguageFile('tl_last_login');
            // ::username
            // ::firstname
            // ::lastname
            // ::fullname
            if (!isset($this->arrTag[1])) 
            {
                $this->arrTag[1] = 'username'; // Default Angabe
            }
            if ($this->arrTag[1] == 'list') 
            {
                $this->arrTag[1] = 'username'; // Default fuer Liste
                if (isset($this->arrTag[2])) 
                {
                    $this->arrTag[3] = $this->arrTag[2]; // Zahl sichern wenn vorhanden
                }
                $this->arrTag[2] = 'list';
            }
            switch ($this->arrTag[1]) 
            {
                case 'username':
                    $llmo_name = 'tlm.username as name, tlm.id as id';
                    $llmo = 'tlm.id';
                    break;
                case 'firstname':
                    $llmo_name = 'tlm.firstname as name, tlm.id as id';
                    $llmo = 'tlm.id';
                    break;
                case 'lastname':
                    $llmo_name = 'tlm.lastname as name, tlm.id as id';
                    $llmo = 'tlm.id';
                    break;
                case 'fullname':
                    $llmo_name = 'CONCAT(tlm.firstname," ",tlm.lastname) as name, tlm.id as id';
                    $llmo = 'tlm.id';
                    break;
                default:
                    $llmo_name = 'tlm.username as name, tlm.id as id';
                    $llmo = 'tlm.id';
                    break;
            }
            // Alle (aktive) 
            // abzueglich alle die eine zeitlich gueltige Session haben (online aktiv)
            // abzueglich gestern oder aelter angemeldet und wieder abgemeldet (ohne Session)
            // = offline members (lange inaktiv oder heute abgemeldet) 
            $objUsers = \Database::getInstance()->prepare("SELECT " . $llmo_name . ", publicFields" . $this->avatar . "
                                               FROM tl_member tlm 
                                               WHERE `disable`!=? AND `login`=? AND " . $llmo . "
                                               NOT IN (
                                                   SELECT " . $llmo . "
                                                   FROM tl_member tlm, tl_session tls
                                                   WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?
                                                   )
                                               AND " . $llmo . "
                                               NOT IN (
                                                   SELECT " . $llmo . "
                                                   FROM tl_member tlm 
                                                   WHERE tlm.currentLogin<= ?
                                                   AND " . $llmo . "
                                                   NOT IN (SELECT DISTINCT pid AS id FROM tl_session
                                                           WHERE name=?)
                                                   )"
                                                )
                                        ->execute(1,1
                                                 ,time() - $GLOBALS['TL_CONFIG']['sessionTimeout']
                                                 ,'FE_USER_AUTH'
                                                 ,mktime(0, 0, 0, date("m"), date("d"), date("Y"))
                                                 ,'FE_USER_AUTH'
                                                );
            if ($objUsers->numRows < 1) 
            {
                $MembersOffline = $GLOBALS['TL_LANG']['last_login']['nobody'];
            } 
            else 
            {
                $arrUser = array();
                while ($objUsers->next()) 
                {
                    $publicFields = deserialize($objUsers->publicFields, true);
                    switch ($this->arrTag[1]) 
                    {
                        case 'id':
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                        case 'username':
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                        case 'firstname':
                            if (in_array('firstname', $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'lastname':
                            if (in_array('lastname', $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'fullname':
                            if (in_array('firstname', $publicFields) &&
                                in_array('lastname' , $publicFields)) 
                            {
                                $arrUser[] = array($objUsers->name, $objUsers->id);
                            }
                            break;
                        case 'avatar':
                            if (in_array('avatar', $this->Config->getActiveModules())) 
                            {
                                //avatar Modul vorhanden
                                //Umweg damit Default Bild kommt
                                $this->avatarFile = Avatar::filename($objUsers->avatar);
                                // username, id, Bild
                                $arrUser[] = array($objUsers->name, $objUsers->id, Avatar::img($this->avatarFile));
                            }
                            break;
                        default:
                            $arrUser[] = array($objUsers->name, $objUsers->id);
                            break;
                    }
                }
                // unordered list
                if ($this->arrTag[1] != 'avatar') 
                {
                    $objTemplate = new \FrontendTemplate('mod_last_login_members_offline_link');
                    /*
                    <div class="mod_last_login_offline">
                    <ul class="members_offline_link">
                      <li><a href="memberlist.html?show=4" title="Profile view">Donna Evans</a></li>
                      <li><a href="memberlist.html?show=5" title="Profile view">John Smith</a></li>
                    </ul>
                    </div>
                    */
                } 
                else 
                {
                    $objTemplate = new \FrontendTemplate('mod_last_login_members_offline_link_avatar');
                    /*
                    <div class="mod_last_login_offline">
                    <ul class="members_offline_link_avatar">
                      <li><a href="memberlist.html?show=4" title="Profile view Donna Evans"><img width="32" height="31" class="avatar" alt="avatar" src="tl_files/avatars/member_4.jpg"></a></li>
                      <li><a href="memberlist.html?show=5" title="Profile view John Smith"><img width="32" height="31" class="avatar" alt="avatar" src="tl_files/avatars/member_5.jpg"></a></li>
                    </ul>
                    </div>
                    */
                }
                if (isset($this->arrTag[3])) 
                {
                    $objTemplate->count = (int) $this->arrTag[3];
                } 
                else 
                {
                    $objTemplate->count = false;
                }
                $objTemplate->users = $arrUser;
                $strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . $this->arrTag[2] . $GLOBALS['TL_CONFIG']['urlSuffix'];
                $objTemplate->memberlink = $strUrl;
                $objTemplate->title = $GLOBALS['TL_LANG']['last_login']['profile'];
                $MembersOffline = $objTemplate->parse();
            }
            return $MembersOffline;
        }
        return false;
    }

} // class

