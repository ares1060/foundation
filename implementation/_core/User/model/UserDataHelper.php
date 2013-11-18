<?php

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\User\model;

	class UserDataHelper extends core\CoreService{
		protected $name = 'User';
		
		private $users;
		private $groups;
		
		private $userDataForGroup; // stores user Data by Group (cache)
		
		function __construct($settings){
			parent::__construct();
			$this->settings = $settings;
			$this->users = array();
			$this->groups = array();
		}	
		
		/** ---  Getter --- */

		
		/** ---  SETTER --- */
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
    		if($this->checkNickAvailability($nick) || ($nick == '' && $this->_setting('no_nick_needed'))){
    			if(strpos($this->_setting('register.groups'), ':'.$group.':') !== false || $this->checkRight('administer_group', $group) &&
    			   ($status==User::STATUS_HAS_TO_ACTIVATE || $this->checkRight('administer_user'))){
    			   	if($pwd == $pwd2){
    			   		if($this->sp->ref('TextFunctions')->getPasswordStrength($pwd) >= $this->_setting('pwd.min_strength')){
    			   			if($email != '' && $this->sp->ref('TextFunctions')->isEmail($email)){
    			   				
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
		    			   										(\''.mysqli_real_escape_string($user->getId()).'\',
		    			   										\''.mysqli_real_escape_string($key).'\',
		    			   										\''.mysqli_real_escape_string($value).'\', NOW());') == 0);
		    			   					$ok = $ok && $x;
		    			   				}
		    			   			}

		    			   			if($ok) {
			    			   			if($status == User::STATUS_HAS_TO_ACTIVATE){
			    			   				$mail = new ViewDescriptor($this->_setting('tpl.activation_mail'));
			    			   				
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
		    			   				$this->mysqlDelete('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata_user WHERE u_id = "'.mysqli_real_escape_string($id).'"');
		    			   				$this->mysqlDelete('DELETE FROM '.ServiceProvider::get()->db->prefix.'user WHERE u_id = "'.mysqli_real_escape_string($id).'"');
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
    		echo 'asdf';
    			$g = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE activate="'.mysqli_real_escape_string($code).'"');
				if($g !== false){
					$q = $this->mysqlUpdate('UPDATE '.ServiceProvider::get()->db->prefix.'user SET activate="", status="'.User::STATUS_ACTIVE.'" WHERE activate="'.mysqli_real_escape_string($code).'"');
					if($q !== false){
						$this->_msg($this->_('_Activation success', 'core'), Messages::INFO);
	        			return true;
					} else {
						$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
	        			return false;
					}
				} else {
					$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
        			return false;
				}
    		} else {
    			$this->_msg($this->_('_Activation error', 'core'), Messages::ERROR);
        		return false;
    		}
    	}
    	
    	public function rejectActivation($code){
    		if($code!= '' && strlen($code) == 32){
    			$g = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE activate="'.mysqli_real_escape_string($code).'"');
				if($g !== false){
					if($this->mysqlDelete('DELETE FROM '.ServiceProvider::get()->db->prefix.'user WHERE activate="'.mysqli_real_escape_string($code).'"')) {
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
    				if($this->sp->txtfun->getPasswordStrength($pwd) >= $this->_setting('pwd.min_strength')){
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
	}
?>