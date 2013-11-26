<?php
	
	/**
	 * @package at\foundation\core\User\model\User
	 */

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
	class User extends core\BaseModel {
        private $nick;
        private $email;
        private $groupId;
        private $status;
        private $pwd;
        private $id;
        
        private $userData;
        private $group;
			
		private static $users = array();
			
        public function __construct($nick = '', $email = '', $group = '', $status = '') {
        	$this->nick = $nick;
            $this->email = $email;
            $this->id = '';
            $this->groupId = $group;
            $this->status = $status;
            $this->pwd = '';
            $this->userData = null;
			$this->group = null;
            parent::__construct(ServiceProvider::get()->db->prefix.'user', array());
        }
		
		
	/* STATIC HELPER */
		
		/**
		 * returns nick availability
		 * @param string $nick
		 * @return boolean True if nick is available
		 */
		public static function checkNickAvailability($nick){
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE nick="'.ServiceProvider::get()->db->escape($nick).'"');
			if($u != array()) return false;
			else return true;
		}
		
		/**
		 * returns Users 
		 * @param int $page
		 * @param int $perPage
		 * @return User[] An array with the fetched Users
		 */
		public static function getUsers($page=-1, $perPage=-1) {
			$return = array();
        	
			$all = self::getAllUserCount(-1, -1);
        	
			$from = ($page-1)*(ServiceProvider::get()->user->settings->perpage_user);
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.ServiceProvider::get()->db->escape($from).', '.ServiceProvider::get()->db->escape(ServiceProvider::get()->user->settings->perpage_user).';';
			
			$u1 = ServiceProvider::get()->db->fetchAll('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user '.$limit);
			if($u1 != array()){
				foreach($u1 as $u) {
					$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
					$ui->setId($u['id'])->setHash($u['hash']); 
					$return[] = $ui;
				}
			}
			return $return;
		}
		
		/**
		 * returns User by Id
		 * @param int $id
		 * @return User
		 */
		public static function getUser($id){
			if(!isset(self::$users[$id])) {
				$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE id="'.ServiceProvider::get()->db->escape($id).'"');
				if($u != array()){
					$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
					$ui->setId($u['id'])->setHash($u['hash']); 
					self::$users[$id] = $ui;
				} else {
					return null;
				}
			}
			return self::$users[$id];
		}
		
		/**
		 * returns User by Id
		 * @see getUser
		 * @param int $id
		 * @return User
		 */
		public static function getUserById($id){
			return self::getUser($id);
		}
		
		/**
		 * returns count of all users
		 * @return int
		 */
		public static function getAllUserCount(){
			$u = ServiceProvider::get()->db->fetchRow('SELECT COUNT(*) count FROM '.ServiceProvider::get()->db->prefix.'user');
			if($u) return $u['count'];
			else return -1;
		}
		
		/**
		 * returns User by Nick
		 * @param string $nick
		 * @return User
		 */
		public static function getUserByNick($nick){
			foreach(self::$users as $u){
				if($u->getNick() == $nick) return $u;
			}
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE nick="'.ServiceProvider::get()->db->escape($nick).'"');
			if($u != array()){
				$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
				$ui->setId($u['id'])->setHash($u['hash']); 
				self::$users[$u['id']] = $ui;
				return self::$users[$u['id']];
			}
			return null;
		}
		
		/**
		 * gets User Info Object by User Data id and data value
		 * @param int $data_id
		 * @param string $value
		 * @return User
		 */
		public static function getUserByData($data_id, $value){
			if($data_id > 0 && $value != ''){
				$array = ServiceProvider::get()->db->fetchAll('SELECT * FROM
						'.ServiceProvider::get()->db->prefix.'userdata_user du
						LEFT JOIN '.ServiceProvider::get()->db->prefix.'user u ON du.u_id = u.id
						WHERE du.value=\''.ServiceProvider::get()->db->escape($value).'\' AND du.ud_id = \''.ServiceProvider::get()->db->escape($data_id).'\';');
				 
				if($array != array()) {
					$u = $array[0];
					$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
					$ui->setId($u['id'])->setHash($u['hash']); 
					self::$users[$u['id']] = $ui;

					return self::getUser($array[0]['id']);
				}else return null;
			} else return null;
		}
		
		/**
		 * returns User by EMail
		 * @param string $mail
		 * @return User
		 */
		public static function getUserByMail($mail){
			foreach(self::$users as $u){
				if($u->getEMail() == $mail) return $u;
			}
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE email="'.ServiceProvider::get()->db->escape($mail).'"');
			if($u != array()){
				$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
				$ui->setId($u['id'])->setHash($u['hash']); 
				self::$users[$u['id']] = $ui;
				return self::$users[$u['id']];
			}
			return null;
		}
		
		/**
		 * returns User by given activation code
		 * @param string $activationCode
		 * @return User
		 */
		public static function getUserByActivationCode($activationCode){
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE activate="'.ServiceProvider::get()->db->escape($activationCode).'"');
			if($u != array()){
				$ui = new User($u['nick'], $u['email'], $u['group'], $u['status']);
				$ui->setId($u['id'])->setHash($u['hash']); 
				self::$users[$u['id']] = $ui;
				return self::$users[$u['id']];
			}
			return null;
		}
		
		/**
		 * returns users Hash for Login routine
		 * @param string $mail
		 * @return string
		 */
		public static function getUserHashByMail($mail){
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE email="'.ServiceProvider::get()->db->escape($mail).'"');
	       	if($u != '' && $u != array() && isset($u['hash'])){
	       		return $u['hash'];
	       	} else return '';
		}
		
		/**
		 * returns users Hash for Login routine
		 * @param string $nick
		 * @return string
		 */
		public static function getUserHashByNick($nick){
			$u = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE nick="'.ServiceProvider::get()->db->escape($nick).'"');
	       	if($u != '' && $u != array() && isset($u['hash'])){
	       		return $u['hash'];
	       	} else return '';
		}
		
		/**
		 *	Activates the user with the given activation code.
		 *	@param string $activationCode The activation code to activate
		 *	@return bool True if activated successfully
		 */
		public static function activateUser($activationCode) {
			$g = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'user WHERE activate="'.ServiceProvider::get()->db->escape($activationCode).'"');
			if($g !== false){
				$q = ServiceProvider::get()->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'user SET activate="", status="'.at\foundation\core\User\User::STATUS_ACTIVE.'" WHERE activate="'.ServiceProvider::get()->db->escape($activationCode).'"');
				if($q !== false){
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 *	Deletes the user with the given id from the database
		 */
		public static function deleteById($id){
			$ok = ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'user WHERE id=\''.ServiceProvider::get()->db->escape($id).'\';');
			UserData::deleteDataForUser($id);
			return $ok;
		}
		
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update user
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'user SET
						nick = \''.$this->sp->db->escape($this->nick).'\',
						hash = \''.$this->sp->db->escape($this->pwd).'\',
						group = \''.$this->sp->db->escape($this->groupId).'\',
						email = \''.$this->sp->db->escape($this->email).'\',
						status = \''.$this->sp->db->escape($this->status).'\'
					WHERE id="'.$this->sp->db->escape($this->id).'"');
			} else {
				//insert user
				$activate_code = ($this->status == core\User\User::STATUS_HAS_TO_ACTIVATE) ? md5(time().$this->sp->ref('TextFunctions')->generatePassword(20, 10, 0, 0)): ''; 
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'user 
								(`nick`, `hash`, `group`, `email`, `status`, `created`, `last_login`, `activate`) VALUES 
								(\''.$this->sp->db->escape($this->nick).'\', 
									\''.$this->sp->db->escape($this->pwd).'\', 
									\''.$this->sp->db->escape($this->groupId).'\', 
									\''.$this->sp->db->escape($this->email).'\',
									\''.$this->sp->db->escape($this->status).'\',
									\''.$this->sp->db->escape(time()) .'\',
									\'-1\',
									\''.$activate_code.'\');');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
			}
		}
		
		/**
		 *	Deletes the user from the database and all linked userdata
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'user WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			if($ok) UserData::deleteDataForUser($this->id);
			
			//TODO remove all links where possible
			return $ok;
		}

        //setter
		public function setPassword($pwd){
			$this->pwd = $this->sp->user->hashPassword($pwd, $this->sp->txtfun->generatePassword(51, 13, 7, 7));
			return $this;
		}
		
		/**
		 *	Saves the current time as lastLogin time to the database
		 */
    	public function setLastLogin(){
    		return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'user SET `last_login` = \''.ServiceProvider::get()->db->escape(time()).'\' WHERE `id`=\''.$this->sp->db->escape($this->id).'\';');
    	}
	   
		public function setNick($nick){ $this->nick = $nick; return $this; }
        public function setEmail($email){ $this->email = $email; return $this; }
        public function setGroup($gid) { $this->groupId = $gid; $this->group=null; return $this; }
        public function setGroupId($gid) { $this->groupId = $gid; $this->group=null; return $this; }
        public function setStatus($status) { $this->status = $status; return $this; }
	   
		private function setId($id) { $this->id = $id; return $this; }
		private function setHash($pwd) { $this->pwd = $pwd; return $this; }
		
		// getter
		public function getNick(){ return $this->nick; }
		public function getEmail(){ return $this->email; }
		public function getId(){ return $this->id; }
		public function getGroup() { 
			if($this->group == null) $this->group = UserGroup::getGroupById($this->groupId);
			return $this->group;
		}
		public function getStatus() { return $this->status; }
		public function getUserData() { 
			if($this->userData == null) $this->userData = UserData::getDataForUser($this->id);
			return $this->userData; 
		}
		public function getGroupId() { return $this->groupId; }
		public function isActive() { return ($this->status == at\foundation\core\User\User::STATUS_ACTIVE); }
		
    }
?>