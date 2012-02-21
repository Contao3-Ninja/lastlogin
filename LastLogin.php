<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Utility LastLogin
 *
 * PHP version 5
 * @copyright  Glen Langer 2012
 * @author     Glen Langer
 * @package    GLLastLogin
 * @license    LGPL
 * @version    1.9.0
 */


/**
 * Class LastLogin
 * 
 * From TL 2.8 you can use prefix "cache_". Thus the InserTag will be not cached. (when "cache" is enabled)
 * 
 * Last Login:
 * {{last_login}}
 * {{last_login::d.m.Y}}
 * {{last_login::zero}}
 * {{last_login::zero::d.m.Y}}
 * 
 * Members Online:
 * Display of names separated by commas.
 * {{last_login_members_online}}
 * {{last_login_members_online::username}}
 * {{last_login_members_online::firstname}}
 * {{last_login_members_online::lastname}}
 * {{last_login_members_online::fullname}}
 * {{last_login_members_online::avatar}}  // funktioniert, macht aber keinen Sinn mit Komma dazwischen...
 * 
 * Display of names as unordered list.
 * {{last_login_members_online::list}}
 * {{last_login_members_online::username::list}}
 * {{last_login_members_online::firstname::list}}
 * {{last_login_members_online::lastname::list}}
 * {{last_login_members_online::fullname::list}}
 * {{last_login_members_online::avatar::list}}
 * {{last_login_members_online::avatar::list::5}}
 * 
 * Members Online linked (for Memberlist Module):
 * Display of names as unordered list linked 
 * (here: memberlist = alias name of page with memberlist module)
 * {{last_login_members_online_link::username::memberlist}}
 * {{last_login_members_online_link::firstname::memberlist}}
 * {{last_login_members_online_link::lastname::memberlist}}
 * {{last_login_members_online_link::fullname::memberlist}}
 * {{last_login_members_online_link::avatar::memberlist}}
 * {{last_login_members_online_link::avatar::memberlist::5}}
 * 
 * Display number of registered members
 * {{last_login_number_registered_members}}
 * 
 * Display number of online members
 * {{last_login_number_online_members}}
 * 
 * Display number of offline members (logout today)
 * {{last_login_number_offline_members}}
 * 
 * Members Offline:
 * Display of names separated by commas.
 * {{last_login_members_offline}}
 * {{last_login_members_offline::username}}
 * {{last_login_members_offline::firstname}}
 * {{last_login_members_offline::lastname}}
 * {{last_login_members_offline::fullname}}
 * {{last_login_members_offline::avatar}}  // funktioniert, macht aber keinen Sinn mit Komma dazwischen...
 * 
 * Display of names as unordered list.
 * {{last_login_members_offline::list}}
 * {{last_login_members_offline::username::list}}
 * {{last_login_members_offline::firstname::list}}
 * {{last_login_members_offline::lastname::list}}
 * {{last_login_members_offline::fullname::list}}
 * {{last_login_members_offline::avatar::list::5}}
 * 
 * Members Offline linked (for Memberlist Module)
 * Display of names as unordered list linked 
 * (here: memberlist = alias name of page with memberlist module)
 * {{last_login_members_offline_link::username::memberlist}}
 * {{last_login_members_offline_link::firstname::memberlist}}
 * {{last_login_members_offline_link::lastname::memberlist}}
 * {{last_login_members_offline_link::fullname::memberlist}}
 * {{last_login_members_offline_link::avatar::memberlist}}
 * {{last_login_members_offline_link::avatar::memberlist::5}}
 * 
 * 
 * @copyright  Glen Langer 2009..2012
 * @author     Glen Langer
 * @package    GLLastLogin
 */
class LastLogin extends Frontend
{
    private $arrTag = false;
    private $login_check = false;
    private $avatar = '';
    
	public function LLreplaceInsertTags($strTag)
	{
		if (!in_array('memberlist', $this->Config->getActiveModules()))
		{
			//memberlist Modul fehlt, Abbruch
			$this->log('memberlist extension required!', 'LastLogin replaceInsertTags', 'ERROR');
			return '';
		}
		
		if (in_array('avatar', $this->Config->getActiveModules()))
		{
			$this->avatar = ',avatar';	//tested width avatar 1.0.1 stable
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
		switch ( $this->arrTag[0] )
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
	
	
	private function LL_last_login()
	{
		//member last login
		// {{cache_last_login}}
		// {{cache_last_login::d.m.Y}}
		// {{cache_last_login::zero}}
		// {{cache_last_login::zero::d.m.Y}}
	    if (FE_USER_LOGGED_IN)
		{
       		$this->import('FrontendUser', 'User');
		    $this->import('Database');
		    $strDate = '';
		    $zero = false;
		    $strDateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
		    if ($this->User->id !== null) 
		    {
			    // DB fragen, wenn kein Datum (Erster Login), dann aktuelles Datum = erster Login :-)
			    //$objLogin = $this->Database->prepare("SELECT logout_tstamp FROM tl_member WHERE id=?")
			    $objLogin = $this->Database->prepare("SELECT lastLogin FROM tl_member WHERE id=?")
			                               ->limit(1)
			                               ->execute($this->User->id);
	            // zero Parameter angegeben? 
	            if ( isset($this->arrTag[1]) && $this->arrTag[1] == 'zero' ) 
	            {
	            	$zero = true;
	            }
	            // date Definition angegeben?
	            if ( isset($this->arrTag[1]) && $this->arrTag[1] != 'zero' ) 
	            {
	                $strDateFormat = $this->arrTag[1]; // date
	            }
			    if ( isset($this->arrTag[2]) ) 
			    {
			        $strDateFormat = $this->arrTag[2]; // date
			    }

			    // Auswertung
				if ($objLogin->lastLogin > 0)
				{
				    $strDate = date($strDateFormat, $objLogin->lastLogin);
				} 
				// first login
				elseif ($zero) 
				{
				    $strDate = 0;
				} 
				else 
				{
				    $strDate = date($strDateFormat);
				}
				/* D_E_B_U_G 
				$this->log('Debug: START', 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: strTag='.$strTag, 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: User-ID='.$this->User->id, 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: lastlog='.$objLogin->lastLogin, 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: zero='.(int)$zero, 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: strDate='.$strDate, 'Debug LastLogin', TL_CONFIGURATION );
				$this->log('Debug: ENDE', 'Debug LastLogin', TL_CONFIGURATION );
				*/
				return $strDate;
		    } //$this->User->id
		} //FE_USER_LOGGED_IN
		return false;
	}
		
	private function LL_last_login_members_online()
	{
	    //members online
		if ($this->login_check)
		{
			$this->import('Database');
			$this->loadLanguageFile('tl_last_login');
			$this->import('FrontendUser', 'User');
			//if ($this->User->id !== null) {
				// ::id
				// ::username
				// ::firstname
				// ::lastname
				// ::fullname
				if (!isset($this->arrTag[1])) 
				{
					$this->arrTag[1] = 'username';	// Default Angabe
				}
				if ($this->arrTag[1] == 'list') 
				{ 	
					$this->arrTag[1] = 'username';	// Default fuer Liste
					if (isset($this->arrTag[2])) 
					{
						$this->arrTag[3] = $this->arrTag[2];	// Zahl sichern wenn vorhanden
					}
					$this->arrTag[2] = 'list';		// list von 1 nach 2 verschieben
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
				$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
				                                   . " FROM tl_member tlm, tl_session tls"
				                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
							     ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],'FE_USER_AUTH');
				if ($objUsers->numRows < 1) 
				{
					$MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
				} 
				else 
				{
					$arrUser = array();
					while ($objUsers->next()) 
					{
						//auf Public Freigabe prüfen
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
								if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) 
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
									$arrUser[]  = Avatar::img($this->avatarFile);
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
						$objTemplate = new FrontendTemplate('mod_last_login_members');
						$objTemplate->users = $arrUser;
						if (isset($this->arrTag[3])) 
						{
							$objTemplate->count = (int)$this->arrTag[3];
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
		     			$MembersOnline = implode(', ',$arrUser); 
					}
				}
				return $MembersOnline;
			//} // $this->User->id
		}
		return false;
	}

	private function LL_last_login_members_online_link()
	{
	    //members online link
		if ($this->login_check)
		{
			$this->import('Database');
			$this->loadLanguageFile('tl_last_login');
			$this->import('FrontendUser', 'User');
			//if ($this->User->id !== null) {
    			// ::username
    			// ::firstname
    			// ::lastname
    			// ::fullname
    			if (!isset($this->arrTag[1])) 
    			{
    				$this->arrTag[1] = 'username';	// Default Angabe
    			}
    			if (!isset($this->arrTag[2])) 
    			{
    				$this->arrTag[2] = 'memberlist';	// Default Angabe
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
    			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name_id.", publicFields".$this->avatar.""
    			                                   . " FROM tl_member tlm, tl_session tls"
    			                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
							     ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],'FE_USER_AUTH');
				if ($objUsers->numRows < 1) 
				{
					$MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
				} 
				else 
				{
					$arrUser = array();
					while ($objUsers->next()) 
					{
						 //auf Public Freigabe prüfen
						$publicFields = deserialize($objUsers->publicFields, true);
						switch ($this->arrTag[1]) 
						{
							case 'id':
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
							case 'username':
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
						    case 'firstname':
								if (in_array('firstname', $publicFields)) 
								{
									$arrUser[] = array($objUsers->name,$objUsers->id);
								}
								break;
						    case 'lastname':
								if (in_array('lastname', $publicFields)) 
								{
									$arrUser[] = array($objUsers->name,$objUsers->id);
								}
								break;
						    case 'fullname':
								if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) 
								{
									$arrUser[] = array($objUsers->name,$objUsers->id);
								}
								break;
							case 'avatar':
								if (in_array('avatar', $this->Config->getActiveModules()))
								{
									//avatar Modul vorhanden
									//Umweg damit Default Bild kommt
									$this->avatarFile = Avatar::filename($objUsers->avatar);
									// username, id, Bild
									$arrUser[]  = array($objUsers->name,$objUsers->id,Avatar::img($this->avatarFile));
								}
								break;
							default:
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
						}
					}
					// unordered list
					if ($this->arrTag[1] != 'avatar') 
					{
						$objTemplate = new FrontendTemplate('mod_last_login_members_link');
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
						$objTemplate = new FrontendTemplate('mod_last_login_members_link_avatar');
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
						$objTemplate->count = (int)$this->arrTag[3];
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
			//} // $this->User->id
    	}
    	return false;
	}
		
	private function LL_last_login_number_registered_members()
	{
	    //number of registered members
		$this->import('Database');
		$objLogin = $this->Database->prepare("SELECT count(`id`) AS ANZ FROM `tl_member`"
		                                   . " WHERE `disable`!=? AND `login`=?")
		                           ->limit(1)
		                           ->execute(1,1);
		return $objLogin->ANZ;
	}
		
	private function LL_last_login_number_online_members()
	{
	    //number of online members
		$this->import('Database');
		$objUsers = $this->Database->prepare("SELECT count(DISTINCT username) AS ANZ"
		                                   . " FROM tl_member tlm, tl_session tls"
		                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
		                 ->limit(1)
					     ->execute(time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],'FE_USER_AUTH');
		if ($objUsers->numRows < 1) 
		{
			$NumberMembersOnline = 0;
		} 
		else 
		{
			$NumberMembersOnline = $objUsers->ANZ;
		}
		return $NumberMembersOnline;
	}
		
	private function LL_last_login_number_offline_members()
	{
	    //number of offline members
		$this->import('Database');
		$llmo_name = 'tlm.username as name';
		$llmo      = 'tlm.username';
		$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields"
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp<?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields"
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin<tlm.lastLogin) AND tlm.lastLogin BETWEEN ? AND ?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields"
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin>tlm.lastLogin) AND (tlm.currentLogin>= ?)"
			                                   . " AND ".$llmo." NOT IN ("
			                                   . " SELECT DISTINCT ".$llmo_name.""
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp>?)"
			                                   )
								     ->execute('FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
								               'FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout']);
		if ($objUsers->numRows < 1) 
		{
			$NumberMembersOffline = 0;
		} 
		else 
		{
			$NumberMembersOffline = $objUsers->numRows;
		}
		return $NumberMembersOffline;
	}
		
	private function LL_last_login_members_offline()
	{
	    //members offline
		if ($this->login_check)
		{
			$this->import('Database');
			$this->loadLanguageFile('tl_last_login');
			// ::username
			// ::firstname
			// ::lastname
			// ::fullname
			if (!isset($this->arrTag[1])) 
			{
				$this->arrTag[1] = 'username';	// Default Angabe
			}
			if ($this->arrTag[1] == 'list') 
			{
				$this->arrTag[1] = 'username';	// Default fuer Liste
				if (isset($this->arrTag[2])) 
				{
					$this->arrTag[3] = $this->arrTag[2];// Zahl sichern wenn vorhanden
				}
				$this->arrTag[2] = 'list';
			}
			switch ($this->arrTag[1]) 
			{
				case 'username':
					$llmo_name = 'tlm.username as name';
					$llmo = 'tlm.username';
					break;
			    case 'firstname':
					$llmo_name = 'tlm.firstname as name';
					$llmo = 'tlm.firstname';
					break;
			    case 'lastname':
					$llmo_name = 'tlm.lastname as name';
					$llmo = 'tlm.lastname';
					break;
			    case 'fullname':
					$llmo_name = 'CONCAT(tlm.firstname," ",tlm.lastname) as name';
					$llmo = 'CONCAT(tlm.firstname," ",tlm.lastname)';
					break;
				default:
					$llmo_name = 'tlm.username as name';
					$llmo = 'tlm.username';
					break;
			}
			/*
			* Nutzer die außerhalb der Verfallszeit einer Session nichts mehr getan haben (also Session Inhaber, aber abgelaufen)
			* Nutzer Heute oder Gestern online und heute offline durch abmelden
			* Nutzer Heute online aber keine Session mehr, und nicht abgemeldet (aber Session gelöscht)
			*/
			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp<?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin<tlm.lastLogin) AND tlm.lastLogin BETWEEN ? AND ?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin>tlm.lastLogin) AND (tlm.currentLogin>= ?)"
			                                   . " AND ".$llmo." NOT IN ("
			                                   . " SELECT DISTINCT ".$llmo_name.""
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp>?)"
			                                   )
								     ->execute('FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
								               'FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout']);
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
					switch ($this->arrTag[1]) {
						case 'id':
							$arrUser[] = $objUsers->name;
							break;
						case 'username':
							$arrUser[] = $objUsers->name;
							break;
					    case 'firstname':
							if (in_array('firstname', $publicFields)) {
								$arrUser[] = $objUsers->name;
							}
							break;
					    case 'lastname':
							if (in_array('lastname', $publicFields)) {
								$arrUser[] = $objUsers->name;
							}
							break;
					    case 'fullname':
							if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) 
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
								$arrUser[]  = Avatar::img($this->avatarFile);
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
					$objTemplate = new FrontendTemplate('mod_last_login_members_offline');
					if (isset($this->arrTag[3])) 
					{
						$objTemplate->count = (int)$this->arrTag[3];
					} 
					else 
					{
						$objTemplate->count = false;
					}
					$objTemplate->users = array_unique($arrUser);
					$MembersOnline = $objTemplate->parse();
				} 
				else 
				{
					// comma separated
	     			$MembersOnline = implode(', ',array_unique($arrUser)); 
				}
			}
			return $MembersOnline;
		}
		return false;
	}
		
    private function LL_last_login_members_offline_link()
	{
	    //members offline link
		if ($this->login_check)
		{
			$this->import('Database');
			$this->loadLanguageFile('tl_last_login');
			// ::username
			// ::firstname
			// ::lastname
			// ::fullname
			if (!isset($this->arrTag[1])) 
			{
				$this->arrTag[1] = 'username';	// Default Angabe
			}
			if ($this->arrTag[1] == 'list') 
			{
				$this->arrTag[1] = 'username';	// Default fuer Liste
				if (isset($this->arrTag[2])) 
				{
					$this->arrTag[3] = $this->arrTag[2];	// Zahl sichern wenn vorhanden
				}
				$this->arrTag[2] = 'list';
			}
			switch ($this->arrTag[1]) 
			{
				case 'username':
					$llmo_name = 'tlm.username as name, tlm.id as id';
					$llmo = 'tlm.username';
					break;
			    case 'firstname':
					$llmo_name = 'tlm.firstname as name, tlm.id as id';
					$llmo = 'tlm.firstname';
					break;
			    case 'lastname':
					$llmo_name = 'tlm.lastname as name, tlm.id as id';
					$llmo = 'tlm.lastname';
					break;
			    case 'fullname':
					$llmo_name = 'CONCAT(tlm.firstname," ",tlm.lastname) as name, tlm.id as id';
					$llmo = 'CONCAT(tlm.firstname," ",tlm.lastname)';
					break;
				default:
					$llmo_name = 'tlm.username as name, tlm.id as id';
					$llmo = 'tlm.username';
					break;
			}
			/*
			* Nutzer die außerhalb der Verfallszeit einer Session nichts mehr getan haben (also Session Inhaber, aber abgelaufen)
			* Nutzer Heute oder Gestern online und heute offline durch abmelden
			* Nutzer Heute online aber keine Session mehr, und nicht abgemeldet (aber Session gelöscht)
			*/
			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp<?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin<tlm.lastLogin) AND tlm.lastLogin BETWEEN ? AND ?"
			                                   . " UNION ALL"
			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$this->avatar.""
			                                   . " FROM tl_member tlm"
			                                   . " WHERE (tlm.currentLogin>tlm.lastLogin) AND (tlm.currentLogin>= ?)"
			                                   . " AND ".$llmo." NOT IN ("
			                                   . " SELECT DISTINCT ".$llmo.""
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp>?)"
			                                   )
								     ->execute('FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout'],
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
								               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
								               'FE_USER_AUTH', time() - $GLOBALS['TL_CONFIG']['sessionTimeout']);
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
							$arrUser[] = array($objUsers->name,$objUsers->id);
							break;
						case 'username':
							$arrUser[] = array($objUsers->name,$objUsers->id);
							break;
					    case 'firstname':
							if (in_array('firstname', $publicFields)) 
							{
								$arrUser[] = array($objUsers->name,$objUsers->id);
							}
							break;
					    case 'lastname':
							if (in_array('lastname', $publicFields)) 
							{
								$arrUser[] = array($objUsers->name,$objUsers->id);
							}
							break;
					    case 'fullname':
							if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) {
								$arrUser[] = array($objUsers->name,$objUsers->id);
							}
							break;
						case 'avatar':
							if (in_array('avatar', $this->Config->getActiveModules()))
							{
								//avatar Modul vorhanden
								//Umweg damit Default Bild kommt
								$this->avatarFile = Avatar::filename($objUsers->avatar);
								// username, id, Bild
								$arrUser[]  = array($objUsers->name,$objUsers->id,Avatar::img($this->avatarFile));
							}
							break;
						default:
							$arrUser[] = array($objUsers->name,$objUsers->id);
							break;
					}
				}
				// unordered list
				if ($this->arrTag[1] != 'avatar') 
				{
					$objTemplate = new FrontendTemplate('mod_last_login_members_offline_link');
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
					$objTemplate = new FrontendTemplate('mod_last_login_members_offline_link_avatar');
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
					$objTemplate->count = (int)$this->arrTag[3];
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

?>