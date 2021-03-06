<?php

	/**
	* @package at\foundation\core\User\model\UserData
	*/

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;

	use at\foundation\core\User\model\UserDataItem;
	use at\foundation\core\User\model\UserDataField;

	
	class UserData extends core\BaseModel {
		private $id;
		private $userId;
		private $user;
		private $data;
		private $keys;
	   
		function __construct($userId){
			$this->userId = $userId;
			
			$qry = ServiceProvider::get()->db->fetchAll('SELECT *, ud.id AS id FROM `'.ServiceProvider::get()->db->prefix.'userdata` AS ud 
															LEFT JOIN `'.ServiceProvider::get()->db->prefix.'userdatafield` AS udf 
																ON ud.field_id = udf.id 
															WHERE ud.user_id = \''.ServiceProvider::get()->db->escape($userId).'\';');
															
			$this->data = array();
			$this->keys = array();
												
			if($qry != array()) {
				foreach($qry as $d){
					$di = new UserDataItem($d['user_id'], $d['field_id'], $d['value']);
					$di->__setId($d['id']);
					$this->data[$d['name']] = $di;
					$this->keys[] = $d['name'];
				}
			}		
			
			parent::__construct('', array());
		}
		
	/* STATIC HELPER */
	
		/**
		 * returns the UserData for the given User
		 * @param $id
		 * @return UserData
		 */
		public static function getDataForUser($id){
			return new UserData($id);
		}
	
		/**
		 *	Deletes all UserData entries for the given user id
		 * @return boolean
		 */
		public static function deleteDataForUser($id) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE user_id=\''.ServiceProvider::get()->db->escape($id).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Returns the UserDataItem for the given name
		 * @param string $name The name of the corresponding UserDataField
		 * @param bool $create If true an empty UserDataItem is created if it doesn't already exist.
		 * @return at\foundation\core\User\model\UserDataItem The resulting user data item or null
		 */
		public function get($name, $create=false){
			if(isset($this->data[$name])) return $this->data[$name];
			else {
				$f = UserDataField::getUserDataFieldByName($name);
				if(!$f) return null;
				if($create){
					$udi = new UserDataItem($this->userId, $f->getId(), '');
					$this->data[$name] = $udi;
					$this->keys[] = $name;
					return $udi;
				} else {
					return null;
				}
			}
		}
		
		/**
		 * Returns the UserDataItem for the given name
		 * @see get()
		 * @param string $name The name of the corresponding UserDataField
		 * @param object $default The default value of the field which is returned if it doesn't exist
		 * @param bool $create If true an UserDataItem containing the value given in $default is created if it doesn't already exist.
		 * @return at\foundation\core\User\model\UserDataItem The resulting user data item or null
		 */
		public function opt($name, $default='', $create=false){
			if(isset($this->data[$name])) return $this->data[$name];
			else {
				$f = UserDataField::getUserDataFieldByName($name);
				if(!$f) return null;

				$udi = new UserDataItem($this->userId, $f->getId(), $default);
				if($create) {
					$this->data[$name] = $udi;
					$this->keys[] = $name;
					//$udi->save();
				}
				return $udi;
			}
		}
		
		/**
		 * Returns true if the user has a userdataitem for the given name
		 * @param string $name The name of the corresponding UserDataField
		 * @return bool True if available
		 */
		public function has($name){
			return isset($this->data[$name]);
		}
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			//save all UserDataItems
			foreach($this->data as &$udi) {
				$udi->save();
			}
			unset($udi);
		}
		
		/**
		 *	Deletes all the UserData
		 */
		public function delete(){
			$this->data = array();
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE user_id=\''.$this->sp->db->escape($this->userId).'\';');
			return $ok;
		}
		
		//setter
		private function setId($Id) { $this->id = $id; return $this; }
		public function setUser($id) { $this->userId = $id; $this->user=null; return $this; }
		public function setUserId($id) { $this->userId = $id; $this->user=null; return $this; }
		public function setValue($value) { $this->value = $value; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getValue() { return $this->value; }
		public function getUser() { 
			if($this->user == null) User::getUser($this->userId);
			return $this->user;
		}
		public function getUserId() { return $this->userId; }
		public function getFieldNames() { return $this->keys; }
	}
?>