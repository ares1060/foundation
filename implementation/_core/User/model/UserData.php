<?php
	namespace at\foundation\core\User\model;
	use at\foundation\core;
	
	class UserData extends core\BaseModel {
		private $userId;
		private $user;
		private $fieldId;
		private $field;
		private $value;
		private $name;
	   
		function __construct($value, $name = ''){
			$this->userId = '';
			$this->fieldId = '';
			$this->value = $value;
			$this->name = $name;
			
			parent::__construct(ServiceProvider::get()->db->prefix.'userdata_user', array());
		}
		
	/* STATIC HELPER */
	
		
	
		/**
		 *	Deletes all UserData entries for the given user id
		 */
		public static function deleteDataForUser($uid) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata_user WHERE u_id=\''.mysqli_real_escape_string($uid).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update user
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'userdata_user SET
						nick = \''.mysqli_real_escape_string($this->nick).'\',
						hash = \''.mysqli_real_escape_string($this->pwd).'\',
						group = \''.mysqli_real_escape_string($this->groupId).'\',
						email = \''.mysqli_real_escape_string($this->email).'\',
						status = \''.mysqli_real_escape_string($this->status).'\'
					WHERE id="'.mysqli_real_escape_string($this->id).'"');
			} else {
				//insert user
				$activate_code = ($this->status == core\User::STATUS_HAS_TO_ACTIVATE) ? md5(time().$this->sp->ref('TextFunctions')->generatePassword(20, 10, 0, 0)): ''; 
				$succ = $this->db->fetchBoolean('INSERT INTO '.$this->sp->db->prefix.'userdata_user 
								(`nick`, `hash`, `group`, `email`, `status`, `created`, `last_login`, `activate`) VALUES 
								(\''.mysqli_real_escape_string($this->nick).'\', 
									\''.mysqli_real_escape_string($this->pwd).'\', 
									\''.mysqli_real_escape_string($this->groupId).'\', 
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
		
		/**
		 *	Deletes the user from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata_user WHERE u_id=\''.mysqli_real_escape_string($this->userId).'\' AND d_id=\''.mysqli_real_escape_string($this->fieldId).'\' ;');
			return $ok;
		}
		
		//setter

		private function setUser($id) { $this->userId = $id; $this->user=null; return $this; }
		private function setUserId($id) { $this->userId = $id; $this->user=null; return $this; }
		private function setField($id) { $this->fieldId = $id; $this->field=null; return $this; }
		private function setFieldId($id) { $this->fieldId = $id; $this->field=null; return $this; }
		public function setValue($value) { $this->value = $value; return $this; }
		
		// getter
		public function getValue() { return $this->value; }
		public function getUser() { 
			if($this->user == null) User::getUser($this->userId);
			return $this->user;
		}
		public function getUserId() { return $this->userId; }
		public function getField() { 
			if($this->field == null) UserDataField::getField($this->fieldId);
			return $this->field;
		}
		public function getFieldId() { return $this->fieldId; }
	}
?>