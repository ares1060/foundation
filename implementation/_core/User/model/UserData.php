<?php
	namespace at\foundation\core\User\model;
	use at\foundation\core;
	
	class UserData extends core\BaseModel {
		private $id;
		private $name;
		private $groupId;
		private $type;
		private $info;
		private $visibility;
		private $userGroups;
	   
		function __construct($name='', $group='', $type='', $info='', $visibility_register='', $visibility_login='', $visibility_edit=''){
			$this->id = $id;
			$this->name = $name;
			$this->groupId = $group;
			$this->type = $type;
			$this->info = $info;
			$this->visibility = array('register'=>$visibility_register, 'login'=>$visibility_login, 'edit'=>$visibility_edit);
			parent::__construct($this->sp->db->prefix.'userdata', array());
		}
		
		public function addUserGroup($id) { if(!isset($this->userGroups[$id])) $this->userGroups[$id] = $id; }
	   
		// getter
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getGroup() { return $this->group; }
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