<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS LastLogin Utility
 * 
 * Utility LastLogin
 *
 * PHP version 5
 * @copyright  Glen Langer 2011
 * @author     Glen Langer
 * @package    GLLastLogin
 * @license    LGPL
 * @version    1.8.0
 */


/**
 * Class LastLogin
 * 
 * From TL 2.8 you can use prefix "cache_". Thus the InserTag will be not cached. (when "cache" is enabled)
 * 
 * Last Login:
 * {{last_login}}
 * {{last_login::d.m.Y}}
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
 * @copyright  Glen Langer 2009..2011
 * @author     Glen Langer
 * @package    GLLastLogin
 */
class LastLogin extends Frontend
{
	public function LLreplaceInsertTags($strTag)
	{
		if (!in_array('memberlist', $this->Config->getActiveModules()))
		{
			//memberlist Modul fehlt, Abbruch
			$this->log('memberlist extension required!', 'LastLogin replaceInsertTags', 'ERROR');
			return '';
		}
		$avatar = '';
		if (in_array('avatar', $this->Config->getActiveModules()))
		{
			$avatar = ',avatar';	//tested width avatar 1.0.1 stable
		}
		$arrTag = trimsplit('::', $strTag);
		
		$login_check = false;
		if (FE_USER_LOGGED_IN) 
		{
			$login_check = true;
		} else {
			if (isset($GLOBALS['TL_CONFIG']['mod_lastlogin_login_check']) && $GLOBALS['TL_CONFIG']['mod_lastlogin_login_check'] === false) 
			{
				$login_check = true;
			}
		}		
		
		//member last login
		if ($arrTag[0] == 'last_login' || $arrTag[0] == 'cache_last_login')
		{
		    if (FE_USER_LOGGED_IN)
    		{
	       		$this->import('FrontendUser', 'User');
    		    $this->import('Database');
    		    $strDate = '';
    		    if ($this->User->id !== null) {
				    // DB fragen, wenn kein Datum (Erster Login), dann aktuelles Datum = erster Login :-)
				    //$objLogin = $this->Database->prepare("SELECT logout_tstamp FROM tl_member WHERE id=?")
				    $objLogin = $this->Database->prepare("SELECT lastLogin FROM tl_member WHERE id=?")
				                               ->limit(1)
				                               ->execute($this->User->id);
		            // Parameter angegeben? Dann nutzen, ansonsten System Definition
		            if (isset($arrTag[1])) {
		            	$strDateFormat = $arrTag[1];
					} else {
						$strDateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
					}
				    $strDate = ($objLogin->lastLogin ? date($strDateFormat, $objLogin->lastLogin) : date($strDateFormat));
    		    }
    			return $strDate;
			}
		}
		
		//members online
		if ($arrTag[0] == 'last_login_members_online' || $arrTag[0] == 'cache_last_login_members_online') {
			if ($login_check)
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
					if (!isset($arrTag[1])) {
						$arrTag[1] = 'username';	// Default Angabe
					}
					if ($arrTag[1] == 'list') { 	
						$arrTag[1] = 'username';	// Default fuer Liste
						if (isset($arrTag[2])) {
							$arrTag[3] = $arrTag[2];	// Zahl sichern wenn vorhanden
						}
						$arrTag[2] = 'list';		// list von 1 nach 2 verschieben
					}
					switch ($arrTag[1]) {
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
					$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
					                                   . " FROM tl_member tlm, tl_session tls"
					                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
								     ->execute(time()-300,'FE_USER_AUTH');
					if ($objUsers->numRows < 1) {
						$MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
					} else {
						$arrUser = array();
						while ($objUsers->next()) {
							//auf Public Freigabe prüfen
							$publicFields = deserialize($objUsers->publicFields, true);
							switch ($arrTag[1]) {
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
									if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) {
										$arrUser[] = $objUsers->name;
									}
									break;
							    case 'avatar':
									if (in_array('avatar', $this->Config->getActiveModules()))
									{
										//avatar Modul vorhanden
										//Umweg damit Default Bild kommt
										$avatarFile = Avatar::filename($objUsers->avatar);
										$arrUser[]  = Avatar::img($avatarFile);
									}
									break;
								default:
									$arrUser[] = $objUsers->name;
									break;
							}
						}
						if (isset($arrTag[2]) && $arrTag[2] == 'list') {
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
							if (isset($arrTag[3])) {
								$objTemplate->count = (int)$arrTag[3];
							} else {
								$objTemplate->count = false;
							}
							$MembersOnline = $objTemplate->parse();
						} else {
							// comma separated
			     			$MembersOnline = implode(', ',$arrUser); 
						}
					}
					return $MembersOnline;
    			//} // $this->User->id
    		}
		}

		//members online link
		if ($arrTag[0] == 'last_login_members_online_link' || $arrTag[0] == 'cache_last_login_members_online_link') {
			if ($login_check)
    		{
    			$this->import('Database');
    			$this->loadLanguageFile('tl_last_login');
    			$this->import('FrontendUser', 'User');
    			//if ($this->User->id !== null) {
	    			// ::username
	    			// ::firstname
	    			// ::lastname
	    			// ::fullname
	    			if (!isset($arrTag[1])) {
	    				$arrTag[1] = 'username';	// Default Angabe
	    			}
	    			if (!isset($arrTag[2])) {
	    				$arrTag[2] = 'memberlist';	// Default Angabe
	    			}
	    			switch ($arrTag[1]) {
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
	    			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name_id.", publicFields".$avatar.""
	    			                                   . " FROM tl_member tlm, tl_session tls"
	    			                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
								     ->execute(time()-300,'FE_USER_AUTH');
					if ($objUsers->numRows < 1) {
						$MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
					} else {
						$arrUser = array();
						while ($objUsers->next()) {
							 //auf Public Freigabe prüfen
							$publicFields = deserialize($objUsers->publicFields, true);
							switch ($arrTag[1]) {
								case 'id':
									$arrUser[] = array($objUsers->name,$objUsers->id);
									break;
								case 'username':
									$arrUser[] = array($objUsers->name,$objUsers->id);
									break;
							    case 'firstname':
									if (in_array('firstname', $publicFields)) {
										$arrUser[] = array($objUsers->name,$objUsers->id);
									}
									break;
							    case 'lastname':
									if (in_array('lastname', $publicFields)) {
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
										$avatarFile = Avatar::filename($objUsers->avatar);
										// username, id, Bild
										$arrUser[]  = array($objUsers->name,$objUsers->id,Avatar::img($avatarFile));
									}
									break;
								default:
									$arrUser[] = array($objUsers->name,$objUsers->id);
									break;
							}
						}
						// unordered list
						if ($arrTag[1] != 'avatar') {
							$objTemplate = new FrontendTemplate('mod_last_login_members_link');
							/*
							<div class="mod_last_login">
								<ul class="members_online_link">
									<li><a href="memberlist.html?show=4" title="Profile view">Donna Evans</a></li>
									<li><a href="memberlist.html?show=5" title="Profile view">John Smith</a></li>
								</ul>
							</div>
							*/
						} else {
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
						if (isset($arrTag[3])) {
							$objTemplate->count = (int)$arrTag[3];
						} else {
							$objTemplate->count = false;
						}
						$objTemplate->users = $arrUser;
						$strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . $arrTag[2] . $GLOBALS['TL_CONFIG']['urlSuffix'];
						$objTemplate->memberlink = $strUrl;
						$objTemplate->title = $GLOBALS['TL_LANG']['last_login']['profile'];
						$MembersOnline = $objTemplate->parse();
					}
					return $MembersOnline;
    			//} // $this->User->id
    		}
		}
		
		//number of registered members
		if ($arrTag[0] == 'last_login_number_registered_members' || $arrTag[0] == 'cache_last_login_number_registered_members') {
			$this->import('Database');
			$objLogin = $this->Database->prepare("SELECT count(`id`) AS ANZ FROM `tl_member`"
			                                   . " WHERE `disable`!=? AND `login`=?")
    		                           ->limit(1)
    		                           ->execute(1,1);
    		return $objLogin->ANZ;
		}
		//number of online members
		if ($arrTag[0] == 'last_login_number_online_members' || $arrTag[0] == 'cache_last_login_number_online_members') {
			$this->import('Database');
			$objUsers = $this->Database->prepare("SELECT count(DISTINCT username) AS ANZ"
			                                   . " FROM tl_member tlm, tl_session tls"
			                                   . " WHERE tlm.id=tls.pid AND tls.tstamp>? AND tls.name=?")
			                 ->limit(1)
						     ->execute(time()-300,'FE_USER_AUTH');
			if ($objUsers->numRows < 1) {
				$NumberMembersOnline = 0;
			} else {
				$NumberMembersOnline = $objUsers->ANZ;
			}
    		return $NumberMembersOnline;
		}
		
		//number of offline members
		if ($arrTag[0] == 'last_login_number_offline_members' || $arrTag[0] == 'cache_last_login_number_offline_members') {
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
									     ->execute('FE_USER_AUTH', time()-300,
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
									               'FE_USER_AUTH', time()-300);
			if ($objUsers->numRows < 1) {
				$NumberMembersOffline = 0;
			} else {
				$NumberMembersOffline = $objUsers->numRows;
			}
    		return $NumberMembersOffline;
		}
		
		//members offline
		if ($arrTag[0] == 'last_login_members_offline' || $arrTag[0] == 'cache_last_login_members_offline') {
			if ($login_check)
    		{
    			$this->import('Database');
    			$this->loadLanguageFile('tl_last_login');
    			// ::username
    			// ::firstname
    			// ::lastname
    			// ::fullname
    			if (!isset($arrTag[1])) {
    				$arrTag[1] = 'username';	// Default Angabe
    			}
    			if ($arrTag[1] == 'list') {
    				$arrTag[1] = 'username';	// Default fuer Liste
    				if (isset($arrTag[2])) {
						$arrTag[3] = $arrTag[2];	// Zahl sichern wenn vorhanden
					}
    				$arrTag[2] = 'list';
    			}
    			switch ($arrTag[1]) {
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
    			* Nutzer die innerhalb der Session Zeit seit 5 Minuten nichts mehr getan haben (also Session Inhaber)
				* Nutzer Heute oder Gestern online und heute offline durch abmelden
				* Nutzer Heute online aber keine Session mehr, und nicht abgemeldet (aber Session gelöscht)
				*/
    			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm, tl_session tls"
    			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp<?"
    			                                   . " UNION ALL"
    			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm"
    			                                   . " WHERE (tlm.currentLogin<tlm.lastLogin) AND tlm.lastLogin BETWEEN ? AND ?"
    			                                   . " UNION ALL"
    			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm"
    			                                   . " WHERE (tlm.currentLogin>tlm.lastLogin) AND (tlm.currentLogin>= ?)"
    			                                   . " AND ".$llmo." NOT IN ("
    			                                   . " SELECT DISTINCT ".$llmo_name.""
    			                                   . " FROM tl_member tlm, tl_session tls"
    			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp>?)"
    			                                   )
									     ->execute('FE_USER_AUTH', time()-300,
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
									               'FE_USER_AUTH', time()-300);
				if ($objUsers->numRows < 1) {
					$MembersOnline = $GLOBALS['TL_LANG']['last_login']['nobody'];
				} else {
					$arrUser = array();
					while ($objUsers->next()) {
						$publicFields = deserialize($objUsers->publicFields, true);
						switch ($arrTag[1]) {
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
								if (in_array('firstname', $publicFields) && in_array('lastname', $publicFields)) {
									$arrUser[] = $objUsers->name;
								}
								break;
							case 'avatar':
								if (in_array('avatar', $this->Config->getActiveModules()))
								{
									//avatar Modul vorhanden
									//Umweg damit Default Bild kommt
									$avatarFile = Avatar::filename($objUsers->avatar);
									$arrUser[]  = Avatar::img($avatarFile);
								}
						break;
							default:
								$arrUser[] = $objUsers->name;
								break;
						}
					}
					if (isset($arrTag[2]) && $arrTag[2] == 'list') {
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
						if (isset($arrTag[3])) {
							$objTemplate->count = (int)$arrTag[3];
						} else {
							$objTemplate->count = false;
						}
						$objTemplate->users = array_unique($arrUser);
						$MembersOnline = $objTemplate->parse();
					} else {
						// comma separated
		     			$MembersOnline = implode(', ',array_unique($arrUser)); 
					}
				}
				return $MembersOnline;
    		}
		}
		
		//members offline link
		if ($arrTag[0] == 'last_login_members_offline_link' || $arrTag[0] == 'cache_last_login_members_offline_link') {
			if ($login_check)
    		{
    			$this->import('Database');
    			$this->loadLanguageFile('tl_last_login');
    			// ::username
    			// ::firstname
    			// ::lastname
    			// ::fullname
    			if (!isset($arrTag[1])) {
    				$arrTag[1] = 'username';	// Default Angabe
    			}
    			if ($arrTag[1] == 'list') {
    				$arrTag[1] = 'username';	// Default fuer Liste
    				if (isset($arrTag[2])) {
						$arrTag[3] = $arrTag[2];	// Zahl sichern wenn vorhanden
					}
    				$arrTag[2] = 'list';
    			}
    			switch ($arrTag[1]) {
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
    			* Nutzer die innerhalb der Session Zeit seit 5 Minuten nichts mehr getan haben (also Session Inhaber)
				* Nutzer Heute oder Gestern online und heute offline durch abmelden
				* Nutzer Heute online aber keine Session mehr, und nicht abgemeldet (aber Session gelöscht)
				*/
    			$objUsers = $this->Database->prepare("SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm, tl_session tls"
    			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp<?"
    			                                   . " UNION ALL"
    			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm"
    			                                   . " WHERE (tlm.currentLogin<tlm.lastLogin) AND tlm.lastLogin BETWEEN ? AND ?"
    			                                   . " UNION ALL"
    			                                   . " SELECT DISTINCT ".$llmo_name.", publicFields".$avatar.""
    			                                   . " FROM tl_member tlm"
    			                                   . " WHERE (tlm.currentLogin>tlm.lastLogin) AND (tlm.currentLogin>= ?)"
    			                                   . " AND ".$llmo." NOT IN ("
    			                                   . " SELECT DISTINCT ".$llmo.""
    			                                   . " FROM tl_member tlm, tl_session tls"
    			                                   . " WHERE tlm.id=tls.pid AND tls.name=? AND tls.tstamp>?)"
    			                                   )
									     ->execute('FE_USER_AUTH', time()-300,
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")), time(),
									               mktime(0, 0, 0, date("m"), date("d"), date("Y")),
									               'FE_USER_AUTH', time()-300);
				if ($objUsers->numRows < 1) {
					$MembersOffline = $GLOBALS['TL_LANG']['last_login']['nobody'];
				} else {
					$arrUser = array();
					while ($objUsers->next()) {
						$publicFields = deserialize($objUsers->publicFields, true);
						switch ($arrTag[1]) {
							case 'id':
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
							case 'username':
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
						    case 'firstname':
								if (in_array('firstname', $publicFields)) {
									$arrUser[] = array($objUsers->name,$objUsers->id);
								}
								break;
						    case 'lastname':
								if (in_array('lastname', $publicFields)) {
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
									$avatarFile = Avatar::filename($objUsers->avatar);
									// username, id, Bild
									$arrUser[]  = array($objUsers->name,$objUsers->id,Avatar::img($avatarFile));
								}
								break;
							default:
								$arrUser[] = array($objUsers->name,$objUsers->id);
								break;
						}
					}
					// unordered list
					if ($arrTag[1] != 'avatar') {
						$objTemplate = new FrontendTemplate('mod_last_login_members_offline_link');
						/*
						<div class="mod_last_login_offline">
							<ul class="members_offline_link">
								<li><a href="memberlist.html?show=4" title="Profile view">Donna Evans</a></li>
								<li><a href="memberlist.html?show=5" title="Profile view">John Smith</a></li>
							</ul>
						</div>
						*/
					} else {
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
					if (isset($arrTag[3])) {
						$objTemplate->count = (int)$arrTag[3];
					} else {
						$objTemplate->count = false;
					}
					$objTemplate->users = $arrUser;
					$strUrl = ($GLOBALS['TL_CONFIG']['rewriteURL'] ? '' : 'index.php/') . $arrTag[2] . $GLOBALS['TL_CONFIG']['urlSuffix'];
					$objTemplate->memberlink = $strUrl;
					$objTemplate->title = $GLOBALS['TL_LANG']['last_login']['profile'];
					$MembersOffline = $objTemplate->parse();
				}
				return $MembersOffline;
    		}
		}
		
		//not for me
		return false;
	}
}

?>