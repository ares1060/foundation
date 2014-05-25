<?php
	
	/**
	 * @package at\foundation\core\User\model\UserDataField
	 */

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
	class UserDataField extends core\BaseModel {
		private $id;
		private $name;
		private $groupId;
		private $group;
		private $type;
		private $info;
		private $visibility;
		private $userGroups;
	   
		function __construct($name='', $group='', $type='', $info='', $visibility_register='', $visibility_login='', $visibility_edit=''){
			$this->id = '';
			$this->name = $name;
			$this->groupId = $group;
			$this->type = $type;
			$this->info = $info;
			$this->userGroups = array();
			$this->visibility = array('register'=>$visibility_register, 'login'=>$visibility_login, 'edit'=>$visibility_edit);
			parent::__construct(ServiceProvider::get()->db->prefix.'userdata', array());
		}
		
	/* STATIC HELPER */
	
		/**
		* Fetches all UserDataFields
		* @return UserDataField[]
		*/
		public static function getUserDataFields(){
			$res = ServiceProvider::get()->db->fetchAll('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdatafield');
			$out = array();
			foreach($res as $q){
				$t = new UserDataField($q['name'], $q['group'], $q['type'], $q['info'], $q['vis_register'], $q['vis_login'], $q['vis_edit']);
				$t->setId($q['id']);
				$out[] = $t;
			}
			
			return $out;
		}
		
		/**
		 * Fetches the UserDataField with the given id
		 * @return UserDataField
		 */
		public static function getUserDataFieldById($id){
			$q = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdatafield WHERE id = "'.ServiceProvider::get()->db->escape($id).'"');
			if($q != null){
				$t = new UserDataField($q['name'], $q['group'], $q['type'], $q['info'], $q['vis_register'], $q['vis_login'], $q['vis_edit']);
				$t->setId($q['id']);
				return $t;
			} else return null;
		}
		
		/**
		 * Fetches the UserDataField with the given name
		 * @return UserDataField
		 */
		public static function getUserDataFieldByName($name) {
			$q = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'userdatafield WHERE name = "'.ServiceProvider::get()->db->escape($name).'"');
			if($q != null){
				$t = new UserDataField($q['name'], $q['group'], $q['type'], $q['info'], $q['vis_register'], $q['vis_login'], $q['vis_edit']);
				$t->setId($q['id']);
				return $t;
			} else return null;
		}
		
		
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'userdatafield SET
						name = \''.$this->sp->db->escape($this->name).'\',
						info = \''.$this->sp->db->escape($this->info).'\',
						type = \''.$this->sp->db->escape($this->type).'\',
						group = \''.$this->sp->db->escape($this->groupId).'\',
						vis_registerister = \''.$this->sp->db->escape($this->visibility['register']).'\',
						vis_login = \''.$this->sp->db->escape($this->visibility['login']).'\',
						vis_edit = \''.$this->sp->db->escape($this->visibility['edit']).'\'
					WHERE id="'.$this->sp->db->escape($this->id).'"');
			} else {
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'userdatafield 
								(`name`, `info`, `type`, `group`, `vis_registerister`, `vis_login`, `vis_edit`) VALUES 
								(\''.$this->sp->db->escape($this->name).'\', 
									\''.$this->sp->db->escape($this->info).'\', 
									\''.$this->sp->db->escape($this->type).'\', 
									\''.$this->sp->db->escape($this->groupId).'\', 
									\''.$this->sp->db->escape($this->visibility['register']).'\', 
									\''.$this->sp->db->escape($this->visibility['login']).'\',
									\''.$this->sp->db->escape($this->visibility['edit']).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
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
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdatafield WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			//TODO remove all links where possible
			return $ok;
		}
		
		//setter
		public function addUserGroup($id) { if(!isset($this->userGroups[$id])) $this->userGroups[$id] = $id; return $this; }

		private function setId($id) { $this->id = $id; return $this; }
		public function setName($name) { $this->name = $name; return $this; }
		public function setGroup($gid) { $this->groupId = $gid; $this->group=null; return $this; }
        public function setGroupId($gid) { $this->groupId = $gid; $this->group=null; return $this; }
		public function setType($type) { $this->type = $type; return $this; }
		public function setInfo($info) { $this->info = $info; return $this; }
		public function setVisibleAtRegister($val) { $this->visibility['register'] = $val; return $this; }
		public function setVisibleAtLogin($val) { $this->visibility['login'] = $val; return $this; }
		public function setVisibleAtEdit($val) { $this->visibility['edit'] = $val; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getGroup() { 
			if($this->group == null) UserGroup::getGroupById($this->groupId);
			return $this->group;
		}
		public function getGroupId() { return $this->groupId; }
		public function getType() { return $this->type; }
		public function getInfo() { return $this->info; }
		public function getVisibleAtRegister() { return $this->visibility['register']; }
		public function getVisibleAtLogin() { return $this->visibility['login']; }
		public function getVisibleAtEdit() { return $this->visibility['edit']; }
		
		public function isForcedAtRegister() { return $this->visibility['register'] == User::VISIBILITY_FORCED; }
		public function isVisibleAtRegister() { return  ($this->visibility['register'] == User::VISIBILITY_FORCED) || ($this->visibility['register'] == User::VISIBILITY_VISIBLE); }
		
		public function usedByGroup($id) { return isset($this->userGroups[$id]); }
	}
?>