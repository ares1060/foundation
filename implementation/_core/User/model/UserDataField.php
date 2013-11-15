<?php
	namespace at\foundation\core\User\model;
	use at\foundation\core;
	
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
	
		
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update user
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'userdata SET
						
					WHERE id="'.mysqli_real_escape_string($this->id).'"');
			} else {
				//insert user
				$activate_code = ($this->status == core\User::STATUS_HAS_TO_ACTIVATE) ? md5(time().$this->sp->ref('TextFunctions')->generatePassword(20, 10, 0, 0)): ''; 
				$succ = $this->db->fetchBoolean('INSERT INTO '.$this->sp->db->prefix.'userdata 
								() VALUES 
								();');
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
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'userdata WHERE id=\''.mysqli_real_escape_string($this->id).'\';');
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