<?php
	
	namespace at\foundation\core\User\model;
	use at\foundation\core;

	class User extends core\BaseModel {
        private $nick;
        private $email;
        private $group;
        private $status;
        private $pwd;
        private $id;
        
        private $userData;
				
        public function __construct($nick, $email, $group, $pwd, $status, $id = '') {
            $this->nick = $nick;
            $this->email = $email;
            $this->id = $id;
            $this->group = $group;
            $this->status = $status;
            $this->pwd = $pwd;
            $this->userData = null;
            parent::__construct($this->sp->db->prefix.'user', array());
        }
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update user
			} else {
				//insert user
				$activate_code = ($this->status == core\User::STATUS_HAS_TO_ACTIVATE) ? md5(time().$this->sp->ref('TextFunctions')->generatePassword(20, 10, 0, 0)): ''; 
				$succ = $this->db->fetchBoolean('INSERT INTO '.$this->sp->db->prefix.'user 
								(`nick`, `hash`, `group`, `email`, `status`, `created`, `last_login`, `activate`) VALUES 
								(\''.mysqli_real_escape_string($this->nick).'\', 
									\''.mysqli_real_escape_string($this->pwd).'\', 
									\''.mysqli_real_escape_string($this->group).'\', 
									\''.mysqli_real_escape_string($this->email).'\',
									\''.mysqli_real_escape_string($this->status).'\',
									\''.mysqli_real_escape_string(time()) .'\',
									\'-1\',
									\''.$activate_code.'\');');
				if($succ) {
					$this->id = mysqli_insert_id();
					return true;
				} else {
					return false;
				}
			}
		}
       
        //setter
		public function setPassword($pwd){
			$this->pwd = $this->sp->user->hashPassword($pwd, $this->sp->ref('TextFunctions')->generatePassword(51, 13, 7, 7));
		}
       
       
        // getter
        public function getNick(){ return $this->nick; }
        public function getEmail(){ return $this->email; }
        public function getId(){ return $this->id; }
        public function getGroup() { return $this->group; }
        public function getStatus() { return $this->status; }
        public function getUserData() { return $this->userData; }
        public function getGroupId() { return $this->getGroup()->getId(); }
        //public function getField($name) {if(isset($this->fields[$name])) return $this->fields[$name]; else return false;}
		
    }
?>