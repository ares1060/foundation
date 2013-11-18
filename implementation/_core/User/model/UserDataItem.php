<?php
	namespace at\foundation\core\User\model;
	use at\foundation\core;
	
	class UserDataItem extends core\BaseModel {
		private $id;
		private $userId;
		private $user;
		private $fieldId;
		private $field;
		private $value;
		private $changed;
	   
		function __construct($userId, $fieldId, $value){
			$this->id = '';
			$this->userId = $userId;
			$this->fieldId = $fieldId;
			$this->value = $value;
			$this->changed = false;
			
			parent::__construct(ServiceProvider::get()->db->prefix.'userdata', array());
		}
		
	/* STATIC HELPER */
	
		public static function getUserDataItemByUserAndName($id, $name){
			$row = ServiceProvider::get()->db->fetchRow('SELECT * FROM `'.ServiceProvider::get()->db->prefix.'userdata` AS ud 
															LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdatafield` AS udf 
																ON ud.field_id = udf.id; 
															WHERE ud.user_id = \''.mysqli_real_escape_string($id).'\' AND udf.name = \''.mysqli_real_escape_string($name).'\' LIMIT 0,1;');
			if($row && isset($row['id'])){
				$o = new UserDataItem($row['user_id'], $row['field_id'], $row['value']);
				$o->setId($row['id']);
				return $o;
			} else {
				return null;
			}
		}
	
		/**
		 *	Deletes all UserData entries for the given user id
		 */
		public static function deleteDataForUser($uid) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE user_id=\''.mysqli_real_escape_string($uid).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->db->fetchBoolean('INSERT INTO '.$this->sp->db->prefix.'userdata 
								(`user_id`, `field_id`, `value`, `last_change`) VALUES 
								(\''.mysqli_real_escape_string($this->userId).'\', 
									\''.mysqli_real_escape_string($this->fieldId).'\', 
									\''.mysqli_real_escape_string($this->value).'\', 
									NOW()
								);');
				if($succ) {
					$this->id = mysqli_insert_id();
					return true;
				} else {
					return false;
				}
				
			} else if($this->changed) {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'userdata SET
						field_id = \''.mysqli_real_escape_string($this->fieldId).'\',
						user_id = \''.mysqli_real_escape_string($this->userId).'\',
						value = \''.mysqli_real_escape_string($this->value).'\',
						last_change = NOW()
					WHERE id="'.mysqli_real_escape_string($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the user data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE id=\''.mysqli_real_escape_string($this->id).'\';');
			return $ok;
		}
		
		//setter
		private function setId($Id) { $this->id = $id; return $this; }
		public function __setId($Id) { $this->id = $id; return $this; }
		public function setUser($id) { $this->userId = $id; $this->user=null; $this->changed = true; return $this; }
		public function setUserId($id) { $this->userId = $id; $this->user=null; $this->changed = true; return $this; }
		public function setField($id) { $this->fieldId = $id; $this->field=null; $this->changed = true; return $this; }
		public function setFieldId($id) { $this->fieldId = $id; $this->field=null; $this->changed = true; return $this; }
		public function setValue($value) { $this->value = $value; $this->changed = true; return $this; }
		
		// getter
		public function getId() { return $this->id; }
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