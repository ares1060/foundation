<?php
	
	/**
	 * @package at\foundation\core\User\model\UserGroup
	 */

	namespace at\foundation\core\User\model;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
	class UserGroup extends core\BaseModel  {
		private $id;
		private $name;
		
		private static $groups = array();
		
		function __construct($name = ''){
			$this->id = '';
			$this->name = $name;
			parent::__construct(ServiceProvider::get()->db->prefix.'userdata', array());
		}

	/* STATIC HELPER */
		
		/**
		 *	Fetches the given group from the database
		 *	@return UserGroup
		 */
		public static function getGroup($id){
			if(!isset(self::$groups[$id])) {
				$g = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup WHERE id="'.ServiceProvider::get()->db->escape($id).'"');
				if($g != array()){
					$ug = new UserGroup($g['name']);
					$ug->setId($g['id']);
					self::$groups[$id] = $ug;
				} else {
					return null;
				}
			}
			return self::$groups[$id];
		}
		
		/**
		 *	Fetches the given group from the database
		 *	@see getGroup()
		 *	@return UserGroup
		 */
		public static function getGroupById($id){
			return self::getGroup($id);
		}

		/**
		 * Returns a list of all UserGroups 
		 * @param int $page
		 * @param int $perPage
		 * @return UserGroup[]
		 */
		public static function getGroups($page=-1, $perPage=-1) {
			$return = array();
        	
			$all = self::getGroupCount(-1, -1);
        	
			$from = ($page-1)*(ServiceProvider::get()->user->settings->perpage_user_group);
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.ServiceProvider::get()->db->escape($from).', '.ServiceProvider::get()->db->escape(ServiceProvider::get()->groups->settings->perpage_user_group).';';
			
			$gs = ServiceProvider::get()->db->fetchAll('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup '.$limit);
			if($gs != array()){
				foreach($gs as $g) {
					$ug = new UserGroup($g['name']);
					$ug->setId($g['id']);
					$return[] = $ug;
				}
			}
			return $return;
		}
		
		/**
		 * returns count of all user groups
		 * @return int
		 */
		public static function getGroupCount(){
			$g = ServiceProvider::get()->db->fetchRow('SELECT COUNT(*) count FROM '.ServiceProvider::get()->db->prefix.'usergroups');
			if($g) return $g['count'];
			else return -1;
		}
		
		/**
		 * Overriding the BaseModel save to do proper save
		 * @return boolean
		 */
		public function save(){
			if($this->id != ''){
				//update usergroup
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'usergroup SET
						name = \''.$this->name.'\'
					WHERE id="'.$this->sp->db->escape($this->id).'"');
			} else {
				//insert usergroup
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'usergroup 
								(`name`) VALUES 
								(\''.$this->sp->db->escape($this->name).'\');');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
			}
		}
		
		/**
		 *	Deletes the usergroup from the database
		 * @return boolean
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'usergroup WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			//TODO remove all links
			return $ok;
		}
		
		// setter
		private function setId($id) { $this->id = $id; return $this; }
		public function setName($name) { $this->name = $name; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
	}
?>