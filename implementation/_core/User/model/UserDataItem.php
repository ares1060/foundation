<?php

	/**
	 * @package at\foundation\core\User\model\UserDataItem
	 */

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
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
	
		/**
		 * @param int $id
		 * @param string $name
		 * @return UserDataItem[]
		 */
		public static function getUserDataItemByUserAndName($id, $name){
			$row = ServiceProvider::get()->db->fetchRow('SELECT *, ud.id AS id FROM `'.ServiceProvider::get()->db->prefix.'userdata` AS ud 
															LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdatafield` AS udf 
																ON ud.field_id = udf.id; 
															WHERE ud.user_id = \''.ServiceProvider::get()->db->escape($id).'\' AND udf.name = \''.ServiceProvider::get()->db->escape($name).'\' LIMIT 0,1;');
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
		 * @return boolean
		 */
		public static function deleteDataForUser($uid) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE user_id=\''.ServiceProvider::get()->db->escape($uid).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'userdata 
								(`user_id`, `field_id`, `value`, `last_change`) VALUES 
								(\''.ServiceProvider::get()->db->escape($this->userId).'\', 
									\''.ServiceProvider::get()->db->escape($this->fieldId).'\', 
									\''.ServiceProvider::get()->db->escape($this->value).'\', 
									NOW()
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					$this->changed = false;
					return true;
				} else {
					return false;
				}
				
			} else if($this->changed) {
				//update
				$ok = $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'userdata SET
						field_id = \''.ServiceProvider::get()->db->escape($this->fieldId).'\',
						user_id = \''.ServiceProvider::get()->db->escape($this->userId).'\',
						value = \''.ServiceProvider::get()->db->escape($this->value).'\',
						last_change = NOW()
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
				$this->changed = !$ok;
				return $ok;
			}
			return true;
		}
		
		/**
		 *	Deletes the user data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
			return $ok;
		}
		
		//setter
		private function setId($id) { $this->id = $id; return $this; }
		public function __setId($id) { $this->id = $id; return $this; }
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
		/**
		 * @return UserDataField
		 */
		public function getField() { 
			if($this->field == null) UserDataField::getField($this->fieldId);
			return $this->field;
		}
		public function getFieldId() { return $this->fieldId; }
	}
?>