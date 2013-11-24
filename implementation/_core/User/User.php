<?php

	namespace at\foundation\core\User;
	use at\foundation\core;	
	use at\foundation\core\User\view\UserAdminView;
	use at\foundation\core\User\view\UserFrontView;

	require_once('model/User.php');
	require_once('model/UserGroup.php');
	require_once('model/UserData.php');
	require_once('model/UserDataGroup.php');
	
	require_once('view/UserAdminView.php');
	require_once('view/UserFrontView.php');
	
    class User extends core\AbstractService implements core\IService  {
        // MVC Objects
        private $viewAdmin;
        private $viewFront;
        
    	// User Objects
    	private $loggedInUser;
        private $viewingUser;
        
        // tmp Groups -> dataHandler
     //   private $groups;
        
        const STATUS_ACTIVE = 1;
        const STATUS_HAS_TO_ACTIVATE = 2;
        const STATUS_BLOCKED = 3;
        const STATUS_DELETED = 4;
        
        const DATA_TYPE_INT = 0;
        const DATA_TYPE_STRING = 1;
        const DATA_TYPE_CHECKBOX = 2;
        const DATA_TYPE_DROPDWN = 3;
        const DATA_TYPE_IMAGE = 4;
        const DATA_TYPE_EMAIL = 5;
        const DATA_TYPE_TEXT = 6;
        const DATA_TYPE_DATE = 7;
        
        const VISIBILITY_HIDDEN = 0;
        const VISIBILITY_VISIBLE = 1;
        const VISIBILITY_FORCED = 2;
        
        function __construct(){
        	$this->name = 'User';
        	$this->ini_file = $GLOBALS['to_root'].'_core/User/User.ini';
            parent::__construct();
            
            $this->viewAdmin = new UserAdminView($this->settings, $this);
            $this->viewFront = new UserFrontView($this->settings,  $this);
           	           	
            //$this->debugVar($_SESSION['User'] == null);
            //$this->debugVar($_SESSION['User']['loggedInUser'] == null);
            
            //load User Data From Session if available
            if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])){
            	$_SESSION['User']['viewingUser'] = $this->fixObject($_SESSION['User']['viewingUser']);
            	$_SESSION['User']['loggedInUser'] = $this->fixObject($_SESSION['User']['loggedInUser']);
            	
            	if(get_class($_SESSION['User']['loggedInUser']) != 'model\User') $this->debugVar('fixObject ERROR');
            	
            	$this->loggedInUser = $_SESSION['User']['loggedInUser'];
            	
            	if(isset($_SESSION['User']['viewingUser'])) $this->viewingUser = $_SESSION['User']['viewingUser'];
            	else $this->viewingUser = $this->loggedInUser;
            	
            } else $this->loggedInUser = null;
            
        }
         
        private function fixObject (&$object) {
  			if (!is_object ($object) && gettype ($object) == 'object')
    			return ($object = unserialize (serialize ($object)));
  			return $object;
		}
         
        
         public function render($args){
         	/*$this->debugVar($this->loggedInUser);
         	$this->debugVar($_SESSION['User']);*/
         	$chapter = (isset($args['chapter']) && $args['chapter'] != '') ? $args['chapter'] : '';
            $action = (isset($args['action'])) ? $args['action'] : '';
          	$page = (isset($args['page']) && $args['page'] > 0) ? $args['page'] : 1;
        	$id = (isset($args['id']) && $args['id'] > 0) ? $args['id'] : -1;
        	
			
			switch($action){
          		case 'view.login_form':
					$target = isset($args['target']) ? $args['target'] : '';
					return $this->viewFront->tplLogin($target);
					break;
          		case 'view.user_menu':
          			return $this->viewFront->tplUserMenu();
          			break;
          		case 'view.register_form':
          			$group = (isset($args['group'])) ? $args['group'] : '';
          			return $this->viewFront->tplRegister($group);
          			break;
          		case 'view.confirm_form':
          			return '--confirm--';
          			break;

				case 'do.login':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			
         			return $this->login($nick, $pwd);
         			break;
         		case 'do.logout':
         			return $this->logout();
         			break;
         		case 'do.register':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			$pwd2 = isset($args['pwd2']) ? $args['pwd2'] : '';
         			$email = isset($args['email']) ? $args['email'] : '';
         			$group = isset($args['group']) ? $args['group'] : '';
         			
         			return $this->register($nick, $email, $group, $pwd, $pwd2);
         			break;
         		case 'do.newUser':
         			$nick = isset($args['nick']) ? $args['nick'] : '';
         			$pwd = isset($args['pwd']) ? $args['pwd'] : '';
         			$pwd2 = isset($args['pwd2']) ? $args['pwd2'] : '';
         			$email = isset($args['email']) ? $args['email'] : '';
         			$group = isset($args['group']) ? $args['group'] : '';
         			$status = isset($args['status']) ? $args['status'] : '';
         			
    				if($this->checkRight('administer_user') && $this->checkRight('administer_group', $_POST['eu_group'])){
    					
    					$nId = $this->register($nick, $mail, $group, $pwd, $pwd2, $status);
    					if($nId !== false){
    						return $nId;
    					} else return false;
    				} else {
    					$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
    					return false;
    				}
         			break;
         		case 'activateRegistration':
         			$code = isset($args['code']) ? $args['code'] : '';
         			return $this->activateRegistration($code);
         			break;
         		case 'rejectRegistration':
         			$code = isset($args['code']) ? $args['code'] : '';
         			return $this->rejectActivation($code);
         			break;
					
          		default:
          			return 'idk';
          			break;
          	}  
			
            switch($chapter){
            	case 'view.user':
            		return $this->viewAdmin->tplUser($page);
            		break;
            	case 'view.edit_user':
            		return $this->viewAdmin->tplUserEdit($id);
               		break;
            	case 'view.new_user':
            		return $this->viewAdmin->tplUserNew($id);
            		break;
            	case 'usergroup':
            		return 'usergroup';
            		break;
            	case 'edit_usergroup':
            		return 'edit_usergroup';
            		break;
            	case 'new_usergroup':
            		return 'new_usergroup';
            		break;
            	case 'userdata':
            		return $this->viewAdmin->tplUserData($page);
            		break;
            	/* --- profile --- */
            	case 'profile':
            		return $this->viewAdmin->tplProfile();
            		break;
            	case 'profile_data':
            		return $this->viewAdmin->tplProfileData();
            		break;
            	case 'profile_notifications':
            		return $this->viewAdmin->tplProfileNotifications();
            		break;
            	case 'profile_privacy':
            		return $this->viewAdmin->tplProfilePrivacy();
            		break;
            	default:
            		switch($action){
            			case 'unset_viewing_user':
            				$this->viewingUser = $this->loggedInUser;
          					$_SESSION['User']['viewingUser'] = $this->viewingUser;
          					return true;
               				break;
            			case 'set_viewing_user':
            				if($this->checkRight('can_change_viewing_user') && $id != -1){
            					$this->viewingUser = models\User::getUser($id);
            					$_SESSION['User']['viewingUser'] = $this->viewingUser;
            					return true;
            				} else {
            					$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
            					return false;
            				}
            				break;
            			case 'edit_user_change_pwd':
            				$pwd = (isset($args['pwd'])) ? $args['pwd'] : '';
            				$pwd1 = (isset($args['pwd1'])) ? $args['pwd1'] : '';
            				$u = models\User::getUser($id);
            				if($u != null && ($this->checkRight('administer_group', $u->getGroup()->getId()) || $this->checkRight('edit_user', $u->getId()))){
            					if($pwd == $pwd1){
            						if($this->editUser($u->getId(), '', $pwd)) {
            							$this->_msg($this->_('Password changed successfull'), Messages::INFO);
            							return true;
            						} else {
            							$this->_msg($this->_('Password could not be changed'), Messages::ERROR);
            							return false;
            						}
            					} else {
            						$this->_msg($this->_('Passwords dont match', 'core'), Messages::ERROR);
            						return false;
            					}
            				} else {
            					$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
            					return false;
            				}
            				break;
            			case 'profile_edit_email':
            				$email = (isset($args['email'])) ? $args['email'] : '';
            				return $this->editUser(-1, '', '', $email);
            				break;
            			case 'profile_change_pwd':
            				$pwd = (isset($args['pwd'])) ? $args['pwd'] : '';
            				$pwd1 = (isset($args['pwd1'])) ? $args['pwd1'] : '';
            				$pwd_old = (isset($args['pwd_old'])) ? $args['pwd_old'] : '';
            				
            				if($pwd == $pwd1){
            					if($this->rightPwd($this->loggedInUser->getNick(), $pwd_old)){
            						if($this->editUser(-1, '', $pwd)) {
            							$this->_msg($this->_('Password changed successfull'), Messages::INFO);
            							return true;
            						} else {
            							$this->_msg($this->_('Password could not be changed'), Messages::ERROR);
            							return false;
            						}
            					} else {
            						$this->_msg($this->_('Wrong Password', 'core'), Messages::ERROR);
            						return false;
            					}
            				} else {
            					$this->_msg($this->_('Passwords dont match', 'core'), Messages::ERROR);
            					return false;
            				}
            				break;
            			default:
        					return $this->viewAdmin->tplUsercenter();
            				break;
            		}
            		break;
            }
         }
         
        public function getSettings() { return $this->settings; }

		public function setup(){
			$error = true;
			include_once('setup/setup.php');
        	return $error;
		}
    	/**
         * handles Post Variables in Admincenter
         */
        public function handleAdminPost(){
//         	print_r($_POST);
		    if(isset($_POST['action'])){
		    	switch($_POST['action']){
		    		case 'editUser':
		    			if(isset($_POST['eu_id']) && isset($_POST['eu_mail']) && isset($_POST['eu_status']) && isset($_POST['eu_group'])){
		    				$user = models\User::getUser($_POST['eu_id']);
		    				
		    				if($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup()->getId())){
		    					//potential security risk -> check if authorized to set new group
		    					$group = $this->checkRight('administer_group', $_POST['eu_group']) ? $_POST['eu_group'] : -1;
		    					
		    					// edit password
		    					if((($_POST['eu_pwd_new'] != '' || $_POST['eu_pwd_new2'] != '') && $_POST['eu_pwd_new'] == $_POST['eu_pwd_new2']) ||
		    							($_POST['eu_pwd_new'] == '' && $_POST['eu_pwd_new2'] == '')) {
		    						
		    						$pwd = $_POST['eu_pwd_new'] == $_POST['eu_pwd_new2'] ? $_POST['eu_pwd_new'] : '';
		    						
		    						if($this->editUser($_POST['eu_id'], '', $pwd, $_POST['eu_mail'], $_POST['eu_status'], $group, array())){
		    							$this->_msg($this->_('_User Update success', 'core'), Messages::INFO);
		    						
		    							header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['back_link']);
		    							exit(0);
		    						} else $this->_msg($this->_('_User Update error', 'core'), Messages::ERROR);
		    					} else {
		    						$this->_msg($this->_('_Passwords have to match', 'core'), Messages::ERROR);
		    					}
		    					
		    				} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
		    			}
		    			break;
		    		/*case 'upload':
		    			$this->executeNewProfileImage();
		    			break;*/
		    		case 'newUser':
		    			//TODO: save data if error 
		    			if(isset($_POST['eu_nick']) && isset($_POST['eu_mail']) && isset($_POST['eu_status']) && isset($_POST['eu_group']) && isset($_POST['eu_pwd_new']) && isset($_POST['eu_pwd_new2'])){
		    				if($this->checkRight('administer_user') && $this->checkRight('administer_group', $_POST['eu_group'])){
		    					
		    					$nId = $this->register($_POST['eu_nick'], $_POST['eu_mail'], $_POST['eu_group'], $_POST['eu_pwd_new'], $_POST['eu_pwd_new2'], $_POST['eu_status']);
		    					if($nId !== false){
		    						if(isset($_POST['back_link'])) header('Location: '.$_SERVER["HTTP_REFERER"].$_POST['back_link'].$nId.'/');
		    						else return true;
		    					}
		    				} else $this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
		    			}
		    			break;
		    		default:
		    			break;
		    	}
		    }
        }
         
        /* functions */
        /**
         * returnes false if pwd is wrong
         * @param string $nick
         * @param string $pwd
         */
        private function rightPwd($nick, $pwd){
        	$hash = ($this->settings->no_nick_needed) ? models\User::getUserHashByEMail($nick) : models\User::getUserHashByNick($nick);
        	if($hash != ''){
        		
        		$salt = substr($hash, strpos($hash, '#')+1);
         		
//         		$this->_msg($salt);
//          		$this->_msg($this->hashPassword($pwd, $salt));
//          		$this->_msg($this->hashPassword($pwd, $salt));
//          		$this->_msg($this->hashPassword($pwd, $salt));
         		 
        		if($hash == $this->hashPassword($pwd, $salt)){  
        			return true;
        		} else return false;
        	} else return false;
        }
        
        /**
         * 
         * Logs in User by nick and pwd
         * @param $nick
         * @param $pwd
         * 
         * @return boolean True if successfully logged in
         */
        public function login($nick, $pwd){
        	if($this->rightPwd($nick, $pwd)){        			
        		$u = ($this->settings->no_nick_needed) ? models\User::getUserByEMail($nick) : models\User::getUserByNick($nick);
        		switch($u->getStatus()){
        			case self::STATUS_ACTIVE:
        				$this->setLoggedInUser($u);
		        		
		        		// regenerate Session ID to prevent Unautorized Access through Session Hijacking
		        		session_regenerate_id();
		        		
		        		// update creation time
		        		$_SESSION['User']['created_time'] = time();
		        		
		        		// set activation time
		        		$_SESSION['User']['last_activity'] = time();
		        		
		        		//check if default password is still used
		        		if($this->getLoggedInUser()->getGroup()->getName() == 'root' && $pwd=='root'){
		        			$_SESSION['User']['defaultPwd'] = 'true';
		        		}
		        		
		        		$this->setLastLogin();
		        		
		        		$this->_msg($this->_('_login success', 'core'), Messages::INFO);
		        		return true;
        				break;
        			case self::STATUS_BLOCKED:
        				$this->_msg($this->_('_login blocked', 'core'), Messages::ERROR);
        				return false;
        				break;
        			case self::STATUS_DELETED:
        				$this->_msg($this->_('_login deleted', 'core'), Messages::ERROR);
        				return false;
        				break;
        			case self::STATUS_HAS_TO_ACTIVATE:
        				$this->_msg($this->_('_login has to activate', 'core'), Messages::ERROR);
        				return false;
        				break;
        			default:
        				$this->_msg($this->_('_wrong pwd', 'core'), Messages::ERROR);
        				return false;
        				break;
        		}
        	} else {
        		$this->_msg($this->_('_wrong pwd', 'core'), Messages::ERROR);
        		return false;
        	}
        }
         
        /**
         * logs out current User
         */
		public function logout() {
         	$this->loggedInUser = null;
        	$_SESSION = array();
        	session_unset ();
        	session_destroy ();
        	
        	session_start();
        	return true;
         }
         
    	
		/**
		 * registers user
		 * @param string $nick
		 * @param string $pwd
		 * @param string $email
		 * @param int $group
		 * @param int $status
		 */
    	public function register($nick, $email, $group, $pwd, $pwd2, $status=User::STATUS_HAS_TO_ACTIVATE, $data=array()){
		    if($status == -1) $status = User::STATUS_HAS_TO_ACTIVATE;			   			
    		if($this->checkNickAvailability($nick) || ($nick == '' && $this->settings->no_nick_needed)){
    			if(strpos($this->settings->register_groups, ':'.$group.':') !== false || $this->checkRight('administer_group', $group) &&
    			   ($status==User::STATUS_HAS_TO_ACTIVATE || $this->checkRight('administer_user'))){
    			   	if($pwd == $pwd2){
    			   		if($this->sp->txtfun->getPasswordStrength($pwd) >= $this->settings->pwd_min_strength){
    			   			if($email != '' && $this->sp->txtfun->isEmail($email)){
    			   				
    			   				$user = new model\User($nick, $email, $group, $status);
    			   				$user->setPassword($pwd);
								
		    			   		if($user->save()) {	
		    			   			$ok = true;	 
		    			   			foreach($data as $key=>$value) {
		    			   				$obj = $this->getUserDataById($key);
		    			   				// security check to not insert data for other groups
		    			   				if($obj->usedByGroup($group)){
		    			   					$x = ($this->mysqlInsert('INSERT INTO '.ServiceProvider::get()->db->prefix.'userdata_user 
		    			   										(`u_id`, `ud_id`, `value`, `last_change`) VALUES
		    			   										(\''.ServiceProvider::get()->db->escape($user->getId()).'\',
		    			   										\''.ServiceProvider::get()->db->escape($key).'\',
		    			   										\''.ServiceProvider::get()->db->escape($value).'\', NOW());') == 0);
		    			   					$ok = $ok && $x;
		    			   				}
		    			   			}

		    			   			if($ok) {
			    			   			if($status == User::STATUS_HAS_TO_ACTIVATE){
			    			   				$mail = new ViewDescriptor($this->settings->tpl_activation_mail);
			    			   				
			    			   				$mail->addValue('nick', $nick);
			    			   				$mail->addValue('id', $id);
			    			   				$mail->addValue('email', $email);
			    			   				$mail->addValue('group', $group);
			    			   				$mail->addValue('pwd', $pwd);
			    			   				$mail->addValue('code', $activate_code);
			    			   				
			    			   				if($this->sp->ref('Mail')->send($email, $this->_('_Registered EMail'), $mail->render())){
			    			   					$this->_msg($this->_('New user created successfully', 'core'), Messages::INFO);
			    			   				} else {
			    			   					$this->_msg($this->_('Activation mail vould not be sent', 'core'), Messages::ERROR);
			    			   				}
			    			   			} else $this->_msg($this->_('New user created successfully', 'core'), Messages::INFO);
		    							return $id;
		    			   			} else {
		    			   				// delete every entered data
		    			   				$u->delete();
		    			   				$this->_msg($this->_('New user could not be created__', 'core'), Messages::ERROR);
		    							return false;
		    			   			}
		    					} else {
		    						$this->_msg($this->_('New user could not be created', 'core'), Messages::ERROR);
		    						return false;
		    					}
    			   			} else {
    			   				$this->_msg($this->_('Please enter a valid email'), Messages::ERROR);
    							return false;
    			   			}
    			   		} else {
    			   			$this->_msg($this->_('New Password is too weak', 'core'), Messages::ERROR);
    						return false;
    			   		}
    			   	} else {
    			   		$this->_msg($this->_('Different Passwords', 'core'), Messages::ERROR);
    					return false;
    			   	}
    			} else {
    				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        			return false;
    			}
    		} else { 
    			$this->_msg($this->_('_Nick not available', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
        /**
         * checks POST data and returnes true if all Data is valid
         * @param unknown_type $group
         */
    	public function checkRegisterData($group){
    		if(isset($_POST['ru_mail'])){
	    		$group = $this->getUserGroup($group);
//	    		$this->debugVar($_POST);
//	    		print_r($_POST);
	    		// first check if basic data is available
	    		if(isset($_POST['ru_mail']) && isset($_POST['ru_pwd_new']) && isset($_POST['ru_pwd_new2']) && $group != null) {
	    			// nick availability will be checked later
	    			
	    			// check extra user data
	    			$userData = $this->getUserDataForGroup($group);
	    			
	    			$ok = true;
	    			
	    			foreach($userData as $d){
// 	    				if($d->isForcedAtRegister()) print_r($d->getName());
	    				$ok = $ok && (($d->isForcedAtRegister() && isset($_POST['ru_ud'][$d->getId()]) && $_POST['ru_ud'][$d->getId()] != '') || !$d->isForcedAtRegister());
	    			}
	    			
	    			if(!$ok){
	    				$this->_msg($this->_('_Enter all data', 'core'), Messages::ERROR);
		        		return false;
	    			} else return true;
	    		} else {
	    			$this->_msg($this->_('_Enter all data', 'core'), Messages::ERROR);
		        	return false;
	    		}
    		}
    	}
    	
    	public function activateRegistration($code){
    		if($code!= '' && strlen($code) == 32){
    			if(model\User::activateUser($code)){
					$this->_msg($this->_('_Activation success', 'core'), Messages::INFO);
				} else {
					$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
				}
    		} else {
    			$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	public function rejectActivation($code){
    		if($code!= '' && strlen($code) == 32){
    			$g = model\User::getUserByActivationCode($code);
				if($g !== null){
					if($g->delete()) {
						$this->_msg($this->_('_Rejection success', 'core'), Messages::INFO);
	        			return true;
					} else {
						$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
	        			return false;
					}
				} else {
					$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
        			return false;
				}
    		} else {
    			$this->_msg($this->_('_Rejection error', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
		/**
		 *	Calls setLastLogin on the currently logged in User
		 *	@see: models\User::setLastLogin
		 */
    	public function setLastLogin(){
    		if($this->sp->user->isLoggedIn()){
    			return $this->sp->user->getLoggedInUser()->setLastLogin();
    		} else return false;
    	}
    	
    	/**
    	 * deletes User by Id
    	 * @param $id
    	 */
    	public function deleteUser($id){
    		if($this->checkRight('administer_user')){
    			return model\User::deleteById($id);
    		} else {
    			$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	/**
    	 * edits User by given id
    	 * @param $id
    	 * @param $nick
    	 * @param $pwd
    	 * @param $email
    	 * @param $status
    	 * @param $group
    	 * @param $userData
    	 */
    	public function editUser($id=-1, $nick='', $pwd='', $email='', $status=-1, $group=-1, $userData=array()){
    		if($id == -1) $id = $this->sp->user->getLoggedInUser()->getId();
			
    		if($id == $this->sp->user->getLoggedInUser()->getId() || $this->checkRight('administer_user', $id)){
    			
    			$query = array();
    			$err = false;
    			
				$user = model\User::getUser($id);
				
    			// nick just can be changed by authorized uer and if available
    			if($nick != '' && $this->checkRight('administer_user')) {
    				if($this->checkNickAvailability($nick)){
    					$user->setNick($nick);
    				} else $this->_msg($this->_('Nick not available'), Messages::ERROR);
    			} else $nick = '';
    			
    			// accept email just if it is an email
    			if($email != '') {
    				if($this->sp->txtfun->isEmail($email)){
    					$user->setEmail($email);
    				} else {
    					$this->_msg($this->_('Please enter a valid email'), Messages::ERROR);
    					$err = true;
    				}
    			}
    			// create new password hash
    			if($pwd != '') {
    				if($this->sp->txtfun->getPasswordStrength($pwd) >= $this->settings->pwd_min_strength){
    					$user->setPassword($pwd);
    				} else {
    					$this->_msg($this->_('New Password is too weak'), Messages::ERROR);
    					$err = true;
    				}
       			}
       			
       			if($status != -1 && $this->checkRight('administer_user')){
       				$user->setStatus($status);
       				if($status != User::STATUS_HAS_TO_ACTIVATE) $user->setStatus('');
       			}
       			
       			if($group != -1 && $this->checkRight('administer_user')){
       				$user->setGroupId($group);
       			}
       			
       			//TODO: userData
       			       			
       			if(!$err) {
       				if($user->save()) {
       					if($id == $this->sp->user->getLoggedInUser()->getId()) $this->sp->user->updateActiveUsers();
       					return true;
       				} else {
       					return false;
       				}
       			} else return false;
      		} else return false;
    	}
		
		
         
        /**
         * returnes Viewing User if allowed
         */
         public function getViewingUser() {
         	if($this->checkRight('can_change_viewing_user')){
         		return $this->viewingUser;
         	} else return $this->getLoggedInUser();
         }
         /**
          * sets Viewing User
          * @param model\User $u_id
          */
         public function setViewingUser(model\User $user){
         	if($this->checkRight('can_change_viewing_user')){
         		$this->viewingUser = $user;
         		$_SESSION['User']['viewingUser'] = $user;
         	}
         }
         
         /**
          * sets viewng User by Id
          * @param $u_id
          */
         public function setViewingUserById($u_id){
         	$this->setViewingUser(models\User::getUser($u_id));
         }
         
		/**
		 *	@param model\User $user
		 */
        private function setLoggedInUser($user) {
         	if($user != null){
         		$this->loggedInUser = $user;
         		$_SESSION['User']['loggedInUser'] = $user;
         	} 
        }
         
         /**
          * returns logged in User
          * @return at\foundation\core\User\model\User
          */
         public function getLoggedInUser() {
         	if($this->loggedInUser == null){
         		if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])) $this->loggedInUser = $_SESSION['User']['loggedInUser'];
         	}
         	return $this->loggedInUser;
         }
         
         /**
          * updates data for viewing and loggedin User
          */
         public function updateActiveUsers() {
         	$this->setLoggedInUser(model\User::getUser($this->loggedInUser->getId()));
         	$this->setViewingUser(model\User::getUser($this->viewingUser->getId()));
         }
         
         public function isLoggedIn() {
         	//$this->debugVar($this->getLoggedInUser());
         	return $this->getLoggedInUser() != null;
         }
         
         /**
          * checks Session data for session expiration
          * Session will expire as defined in settings
          * Additionally ID regeneration will be triggered
          */
     	 public function checkSessionExpiration() {
     	 	if($this->isLoggedIn()){
	        	// create session creation time
				if(!isset($_SESSION['User']['created_time'])) $_SESSION['User']['created_time'] = -1;
				
				// regenerate session id after specified time	
	        	if($this->settings->session_regenerate_after > -1 && time() - $_SESSION['User']['created_time'] > $this->settings->session_regenerate_after){
	        		session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
	    			$_SESSION['User']['created_time'] = time();  // update creation time
	        	}
	        	        	
	        	if($this->loggedInUser != null && isset($_SESSION['User']['last_activity']) && isset($_SESSION['User']['last_activity']) && (time() - $_SESSION['User']['last_activity']) > $this->settings->session_idle_time){
	
	        		$this->logout();
	        		$this->_msg($this->_('_session_expired'), Messages::ERROR);
	        		$GLOBALS['session_expired'] = true;
	        	}
	        	
	        	$_SESSION['User']['last_activity'] = time();
     	 	}
        } 
   	 	
        /**
         * hashes Passwort by using TextFunctions hashString function
         * @param $pwd
         * @param $salt
         */
        public function hashPassword($pwd, $salt){
        	return $this->sp->txtfun->hashString($pwd, $salt, 'whirlpool');
        }

    }
 ?>