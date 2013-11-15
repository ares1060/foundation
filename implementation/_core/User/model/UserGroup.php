<?php
	namespace at\foundation\core\User\model;
	use at\foundation\core;
	
	class UserGroup extends core\BaseModel  {
		private $id;
		private $name;
		
		private static $groups = array();
		
		function __construct($name = ''){
			$this->id = '';
			$this->name = $name = '';
			parent::__construct(ServiceProvider::get()->db->prefix.'userdata', array());
		}

	/* STATIC HELPER */
		
		/**
		 *	Fetches the given group from the database
		 *	@return UserGroup
		 */
		public static function getGroup($id){
			if(!isset(self::$groups[$id])) {
				$g = ServiceProvider::get()->db->fetchRow('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup WHERE id="'.mysqli_real_escape_string($id).'"');
				if($g != array()){
					self::$groups[$id] = (new UserGroup($g['name']))
						->setId($g['id']);
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
		 */
		public static function getGroups($page=-1, $perPage=-1) {
			$return = array();
        	
			$all = self::getGroupCount(-1, -1);
        	
			$from = ($page-1)*(ServiceProvider::get()->groups->settings->perpage_user_group);
			if($from > $all) $from = 0;
			
			$limit = ($page == -1) ? '' : 'LIMIT '.mysqli_real_escape_string($from).', '.mysqli_real_escape_string(ServiceProvider::get()->groups->settings->perpage_user_group).';';
			
			$gs = ServiceProvider::get()->db->fetchAll('SELECT * FROM '.ServiceProvider::get()->db->prefix.'usergroup '.$limit);
			if($gs != array()){
				foreach($gs as $g) {
					$return[] = (new UserGroup($g['name']))
						->setId($g['id']);
				}
			}
			return $return;
		}
		
		/**
		 * returns count of all user groups
		 */
		public static function getGroupCount(){
			$g = ServiceProvider::get()->db->fetchRow('SELECT COUNT(*) count FROM '.ServiceProvider::get()->db->prefix.'usergroups');
			if($g) return $g['count'];
			else return -1;
		}
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id != ''){
				//update usergroup
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'usergroup SET
						name = \''.$this->name.'\'
					WHERE id="'.mysqli_real_escape_string($this->id).'"');
			} else {
				//insert usergroup
				$succ = $this->db->fetchBoolean('INSERT INTO '.$this->sp->db->prefix.'usergroup 
								(`name`) VALUES 
								(\''.mysqli_real_escape_string($this->name).'\');');
				if($succ) {
					$this->id = mysqli_insert_id();
					return true;
				} else {
					return false;
				}
			}
		}
		
		/**
		 *	Deletes the usergroup from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'usergroup WHERE id=\''.mysqli_real_escape_string($this->id).'\';');
			//TODO remove all links
			return $ok;
		}
		
		// setter
		private function setId() { $this->id = $id; return $this; }
		public function setName($name) { $this->name = $name; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
	}
?>