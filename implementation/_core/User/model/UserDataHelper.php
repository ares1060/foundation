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

		
		/* ========== GROUPS ========= */
		/**
		 * returnes userGroup by Id
		 * @param unknown_type $id
		 */
		public function getUserGroup($id) {
			if(!isset($this->groups[$id])){
				$u = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup WHERE id="'.mysqli_real_escape_string($id).'"');
				if($u != array()){
					$this->groups[$id] = new UserGroup($u['id'], $u['name']);
				}
			}
			return $this->groups[$id];
		}
		/**
		 * returnes array of all Usergroups
		 */
		public function getGroups() {
			$this->groups = array();
			$g = $this->mysqlArray('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup');
			if($g != array()){
				foreach($g as $group) {
					$this->groups[] = new UserGroup($group['id'], $group['name']);
				}
			}
			return $this->groups;
		}
		/* ========== USERDATA GROUP ========= */
		public function getUserDataGroupById($id){
		$q = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata_datagroup WHERE id = "'.mysqli_real_escape_string($id).'"');
			if($q != null){
				return new UserDataGroup($q['id'], $q['name']);
			}
		}
		/* ========== USERDATA ========= */
		public function getUserDataById($id){
			$q = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE id = "'.mysqli_real_escape_string($id).'"');
			if($q != null){
				$t = new UserData($q['id'], $q['name'], $this->getUserDataGroupById($q['group']), $q['type'], $q['type'], $q['vis_reg'], $q['vis_login'], $q['vis_edit']);
				$q = $this->mysqlArray('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata_usergroup WHERE ud_id = "'.mysqli_real_escape_string($id).'"');
				foreach($q as $row){
					$t->addUserGroup($row['ug_id']);
				}
				return $t;
			}
			
		}
		
		public function getUserDataByName($name) {
			$q = $this->mysqlRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE name = "'.mysqli_real_escape_string($name).'"');

			if($q != null){

				$t = new UserData($q['id'], $q['name'], $this->getUserDataGroupById($q['group']), $q['type'], $q['type'], $q['vis_reg'], $q['vis_login'], $q['vis_edit']);

				$q = $this->mysqlArray('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata_usergroup WHERE ud_id = "'.mysqli_real_escape_string($q['id']).'"');

				foreach($q as $row){

					$t->addUserGroup($row['ug_id']);

				}

				return $t;

			}
		}
		
		/**

		 * returnes UserData object by given id

		 * is used by UserInfo->loadData(ServiceProvider $sp)

		 * @param $id

		 */

		public function getUserDataByUserId($id){

			$user = $this->getUser($id);
			

// 			$data = $this->mysqlArray('SELECT *, udg.name as gName, ud.name as dName FROM `'.ServiceProvider::get()->db->prefix.'userdata` ud

// 					LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdata_usergroup` udg ON ud.g_id = udg.g_id

// 					LEFT JOIN 
// 						(SELECT * FROM `'.ServiceProvider::get()->db->prefix.'userdata_user` WHERE 
// 								u_id="'.mysqli_real_escape_string($user->getId()).'") uud ON uud.d_id = ud.ud_id

// 					LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdata_datagroup` ud_g ON ud.ud_id = ud_g.d_id

// 					WHERE ud_g.g_id = "'.mysqli_real_escape_string($user->getGroupId()).'"');

			$data = $this->mysqlArray('SELECT * FROM `'.ServiceProvider::get()->db->prefix.'userdata_user` ud_u
												LEFT JOIN '.ServiceProvider::get()->db->prefix.'userdata ud ON ud_u.ud_id = ud.id
												LEFT JOIN (
													SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata_usergroup WHERE 
													ug_id = \''.mysqli_real_escape_string($user->getGroupId()).'\'
													) ud_ug ON ud_ug.ud_id = ud.id
										WHERE ud_u.u_id = \''.mysqli_real_escape_string($user->getId()).'\' AND
											   ud_ug.ug_id = \''.mysqli_real_escape_string($user->getGroupId()).'\'');
			
			$data_ar = array();
			if($data != array()) {
				foreach($data as $d){
					$data_ar[$d['name']] = $d['value'];
				}
			}
			return $data_ar;

		}
		
		public function setUserDataByUserIdAndDataName($id, $name, $value){
			$user = $this->getUser($id);
			$data = $this->getUserDataByName($name);
// 			echo 'UPDATE `'.ServiceProvider::get()->db->prefix.'userdata_user` ud_u 
// 													LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdata` ud ON ud_u.ud_id = ud.id
// 												SET ud_u.value ="'.mysqli_real_escape_string($value).'"
// 												WHERE ud.name ="'.mysqli_real_escape_string($name).'" AND ud_u.u_id="'.mysqli_real_escape_string($user->getId()).'"';
			$query = $this->mysqlRow('SELECT COUNT(*) c FROM `'.ServiceProvider::get()->db->prefix.'userdata_user` WHERE ud_id ="'.mysqli_real_escape_string($data->getId()).'" AND u_id="'.mysqli_real_escape_string($user->getId()).'"');
			
			if($query) {
				if($query['c'] > 0) {
					$query = $this->mysqlUpdate('UPDATE `'.ServiceProvider::get()->db->prefix.'userdata_user` SET value ="'.mysqli_real_escape_string($value).'"
												WHERE ud_id ="'.mysqli_real_escape_string($data->getId()).'" AND u_id="'.mysqli_real_escape_string($user->getId()).'"');
				} else {
					$query = $this->mysqlUpdate('INSERT INTO `'.ServiceProvider::get()->db->prefix.'userdata_user` (`value`, `ud_id`, `u_id`) values
												("'.mysqli_real_escape_string($value).'", "'.mysqli_real_escape_string($data->getId()).'", "'.mysqli_real_escape_string($user->getId()).'")');
												
				}
				
				return $query;
			} else return false;
		}
		
		public function getUserData($page=-1, $perPage=-1){
			$return = array();
        	
			$all = $this->getAllUserDataCount(-1, -1);

			$from = ($page-1)*($this->_setting('perpage.user_data'));
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.mysqli_real_escape_string($from).', '.mysqli_real_escape_string($this->_setting('perpage.user_data')).';';
			
			$u1 = $this->mysqlArray('SELECT ud.id d_id, ud.name d_name, ud.group d_group,
											udg.name d_group_name, ud.type d_type,
											ud.info d_info, ud.vis_reg d_vis_reg,
											ud.vis_login d_vis_login, ud.vis_edit d_vis_edit FROM '.ServiceProvider::get()->db->prefix.'userdata ud 
											LEFT JOIN '.ServiceProvider::get()->db->prefix.'userdata_datagroup udg ON ud.group = udg.id ORDER BY ud.group '.$limit.'');
			if($u1 != array()){
				foreach($u1 as $u) {
					$tmp = new UserData($u['d_id'], $u['d_name'], new UserDataGroup($u['d_group'], $u['d_group_name']), $u['d_type'], $u['d_info'], $u['d_vis_reg'], $u['d_vis_login'], $u['d_vis_edit']);
					$u2 = $this->mysqlArray('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdata_usergroup WHERE ud_id="'.mysqli_real_escape_string($u['d_id']).'"');
					if($u2 != array()){
						foreach($u2 as $u3){
							$tmp->addUserGroup($u3['ug_id']);
						}
					}
					$return[] = $tmp;
					unset($tmp);
				}
			}
			return $return;
		}
		/**
		 * returnes count of all userdata
		 */
		public function getAllUserDataCount(){
			$u = $this->mysqlRow('SELECT COUNT(*) count FROM '.ServiceProvider::get()->db->prefix.'userdata');
			if($u) return $u['count'];
			else return -1;
		}
		
		/**
		 * returnes User data for given group
		 * @param unknown_type $group
		 */
		public function getUserDataForGroup($group){
			if(!is_object($group) || get_class($group) != 'UserGroup'){
				$group = $this->getUserGroup($group);
			}
			
			
			if($group != null){
				if(!isset($this->userDataForGroup[$group->getId()])){
					$u1 = $this->mysqlArray('SELECT ud.id d_id, ud.name d_name, ud.group d_group,
											udg.name d_group_name, ud.type d_type,
											ud.info d_info, ud.vis_reg d_vis_reg,
											ud.vis_login d_vis_login, ud.vis_edit d_vis_edit FROM '.ServiceProvider::get()->db->prefix.'userdata ud 
											LEFT JOIN '.ServiceProvider::get()->db->prefix.'userdata_datagroup udg ON ud.group = udg.id 
											RIGHT JOIN '.ServiceProvider::get()->db->prefix.'userdata_usergroup udug ON ud.id = udug.ud_id WHERE udug.ug_id="'.mysqli_real_escape_string($group->getId()).'"');
					if($u1 != array()){
						foreach($u1 as $u) {
							if(!isset($this->userDataForGroup[$group->getId()])) $this->userDataForGroup[$group->getId()] = array(); 
							$this->userDataForGroup[$group->getId()][] = new UserData($u['d_id'], $u['d_name'], new UserDataGroup($u['d_group'], $u['d_group_name']), $u['d_type'], $u['d_info'], $u['d_vis_reg'], $u['d_vis_login'], $u['d_vis_edit']);
						}
					}
				}
				return $this->userDataForGroup[$group->getId()];
			} else return false;
			
		}
		
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