<?php

	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;

	class Tag extends core\BaseModel {
		private $id;
		private $name;
		private $webname;
		private $ownerId;
		private $ownerUser;
		private $scope;

		function __construct($name = '', $webname = '', $userId = -1, $scope = ''){
			$this->name = $name;
			$this->ownerId = $userId;
			$this->scope = $this->parseScope($scope);
			parent::__construct(ServiceProvider::get()->db->prefix.'tags', array());
			$this->webname = $this->sp->txtfun->string2Web($webname);
		}	
		
		/**
		 * STATIC METHODS
		 */
		
		/**
		 * checks if Tag link exists with given name, service, param and userId
		 * @param string $name
		 * @param string $service
		 * @param string $param
		 * @param string userId
		 */
		public static function linkExists($name, $service, $param, $userId = -1){
			$r = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` t 
											LEFT JOIN `'.ServiceProvider::getInstance()->db->prefix.'tag_links` tl ON t.id = tl.id
											WHERE t.name="'.ServiceProvider::getInstance()->db->escape($name).'" 
											AND tl.service="'.ServiceProvider::getInstance()->db->escape($service).'"
											AND tl.param ="'.ServiceProvider::getInstance()->db->escape($param).'"
											AND (t.user_id = \'-1\''.(($userId >= 0)?' OR t.user_id = \''.ServiceProvider::getInstance()->db->escape($userId).'\'':'').');');
			if($r){
				return isset($r['name']);
			} else return false;
		}
		
		/**
		 * returns Tag by Id
		 * 
		 * @param int $id
		 */
		public static function getTag($id){
			if($id > 0){
				$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` WHERE id="'.ServiceProvider::getInstance()->db->escape($id).'"');
				if($a != ''){
					$t = new Tag($a['name'], $a['webname'], $a['user_id'], $a['scope']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Name
		 * 
		 * @param string $name
		 * @param int $userId
		 */
		public static function getTagByName($name, $userId = -1){
			if($name != ''){
				$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` t WHERE `name`=\''.ServiceProvider::getInstance()->db->escape($name).'\' AND (t.user_id = \'-1\''.(($userId >= 0)?' OR t.user_id = \''.ServiceProvider::getInstance()->db->escape($userId).'\'':'').');');
				if($a != ''){
					$t = new Tag($a['name'], $a['webname'], $a['user_id'], $a['scope']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Webname
		 * 
		 * @param string $webname
		 * @param int $userId
		 */
		public static function getTagByWebname($webname, $userId = -1){
			if($webname != ''){
				$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` t WHERE `webname`=\''.ServiceProvider::getInstance()->db->escape($webname).'\' AND (t.user_id = \'-1\''.(($userId >= 0)?' OR t.user_id = \''.ServiceProvider::getInstance()->db->escape($userId).'\'':'').');');
				if($a != ''){
					$t = new Tag($a['name'], $a['webname'], $a['user_id'], $a['scope']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes all connected Params By given Tag and service
		 * @param int $tagId
		 */
		public static function getParamsForTag($tagId, $service, $userId = -1){
			$a = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tag_links` WHERE tag_id="'.ServiceProvider::getInstance()->db->escape($tagId).'" AND service="'.ServiceProvider::getInstance()->db->escape($service).'"');
			if(is_array($a)) {
				$return = array();
				foreach($a as $a_){
					$return[] = $a_['param'];
				}
				return $return;
			} else return array();
		}

		/**
		* returnes Tags
		*
		* @param string $service
		* @param string $param
		* @param int $userId
		* @param boolean $doQuery If false the sql statment is returend instead of the result.
		*/
		public static function getTags($service='', $name = '', $userId = -1, $doQuery = true){
			$service = ($service != '') ? ' AND t.scope LIKE "%:'.ServiceProvider::getInstance()->db->escape($service).':%"' : '';
			$name = ($name != '') ? ' AND t.name LIKE "%'.ServiceProvider::getInstance()->db->escape($name).'%"' : '';
			$sql = 'SELECT '.((!$doQuery)?'t.id AS id':'*').' FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` t
												WHERE (t.user_id = \'-1\''.(($userId >= 0)?' OR t.user_id = \''.ServiceProvider::getInstance()->db->escape($userId).'\'':'').') '.$service.$name;
			if(!$doQuery) return $sql;
			$a = ServiceProvider::getInstance()->db->fetchAll($sql);
			if($a != ''){
				$out = array();
				foreach($a as $tag){
					$t = new Tag($tag['name'], $tag['webname'], $tag['user_id'], $tag['scope']);
					$t->setId($tag['id']);
					$out[] = $t;
				}
				return $out;
			} else {
				return array();
			}
		}
		
		/**
		 * returnes Tags
		 * 
		 * @param string $service
		 * @param string $param
		 * @param int $userId
		 * @param boolean $doQuery If false the sql statment is returend instead of the result.
		 */
		public static function getLinkedTags($service='', $param='', $userId = -1, $doQuery = true){
			$param = ($param != '') ? ' AND tl.param="'.ServiceProvider::getInstance()->db->escape($param).'"' : '';
			$service = ($service != '') ? ' AND tl.service="'.ServiceProvider::getInstance()->db->escape($service).'"' : '';
			$sql = 'SELECT '.((!$doQuery)?'t.id AS id':'*').' FROM `'.ServiceProvider::getInstance()->db->prefix.'tag_links` tl
										LEFT JOIN `'.ServiceProvider::getInstance()->db->prefix.'tags` t ON tl.tag_id = t.id 
										WHERE (t.user_id = \'-1\''.(($userId >= 0)?' OR t.user_id = \''.ServiceProvider::getInstance()->db->escape($userId).'\'':'').') '.$service.$param;

			if(!$doQuery) return $sql;
			$a = ServiceProvider::getInstance()->db->fetchAll($sql);
			if($a != ''){
				$out = array();
				foreach($a as $tag){
					$t = new Tag($tag['name'], $tag['webname'], $tag['user_id'], $tag['scope']);
					$t->setId($tag['id']);
					$out[] = $t;
				}
				return $out;
			} else {
				return array();
			}
		}

		/**
		 * Creates a SQL subselect for joining when filtering for tags
		 * @param string $service The name of the service
		 * @param string $param 
		 */
		public static function getSubSelectSQL($service, $param = '', $userId = -1) {
			return self::getTags($service, $param, $userId, false);
		}
		
		/**
		 * Returns tag count by tag id and service
		 * 
		 * @param int $tagId
		 * @param string $service
		 */
		public static function getLinkCount($tagId, $service='', $userID = -1){
			$service = ($service == '') ? '' : ' AND service="'.ServiceProvider::getInstance()->db->escape($service).'" ';
			$a = ServiceProvider::getInstance()->db->fetchAll('SELECT COUNT(*) AS count FROM `'.ServiceProvider::getInstance()->db->prefix.'tag_links` WHERE tag_id="'.ServiceProvider::getInstance()->db->escape($tag_id).'"'.$service);
			if($a != '' && isset($a['count'])){
				return $a['count'];
			} else return 0;
		}
		
		public static function getLinkCountByName($name, $service='', $param = '', $userID = -1, $exact = true){
			$service = ($service == '') ? '' : ' AND service="'.ServiceProvider::getInstance()->db->escape($service).'" ';
			$param = ($param == '') ? '' : ' AND param="'.ServiceProvider::getInstance()->db->escape($param).'" ';
			$a = ServiceProvider::getInstance()->db->fetchAll('SELECT COUNT(*) AS count FROM `'.ServiceProvider::getInstance()->db->prefix.'tag_links` tl LEFT JOIN '.ServiceProvider::getInstance()->db->prefix.'tags t ON tl.tag_id = t.id WHERE t.name LIKE "'.ServiceProvider::getInstance()->db->escape($name).((exact)?'':'%').'"'.$service.$param);
			if($a != '' && isset($a['count'])){
				return $a['count'];
			} else return 0;
		}
		
		/** 
		 * INSTANCE METHODS
		 */
		 
		/**
		 * Returns the number of links for this tag
		 * @param string $service
		 */
		public function getCount($service = ''){
			return self::getTagCount($this->id, $service, $userID = -1);
		}
		
		/**
		 * Creates a link to the given service and param
		 * @param string $service
		 * @param string $param
		 */
		public function link($service, $param){
			return $this->sp->db->fetchBool('INSERT INTO `'.ServiceProvider::getInstance()->db->prefix.'tag_links` 
				(`tag_id`, `service`, `param`) VALUES 
				("'.ServiceProvider::getInstance()->db->escape($this->id).'", 
				 "'.ServiceProvider::getInstance()->db->escape($service).'", 
				 "'.ServiceProvider::getInstance()->db->escape($param).'")');
		}
		
		/**
		 * removes all links to the given service and param
		 * @param string $service
		 * @param string $param
		 */
		public function unlink($service, $param){
			return $this->sp->db->fetchBool('DELETE FROM `'.$this->sp->db->prefix.'tag_links` 
												WHERE tag_id="'.$this->sp->db->escape($this->id).'"
												AND service="'.$this->sp->db->escape($service).'"
												AND param="'.$this->sp->db->escape($param).'"');	
		}
		
		/**
		 * Merges the links of the tag with the links of the given tag.
		 * The given tag is removed from the database after successfully merging.
		 * If the userId of the given tag deviates the userId of this tag will be set to -1
		 * @param tagId 
		 * @return boolean True if merging was successfull. False otherwise.
		 */
		public function merge($tagId){
			//TODO implement merging
		}
		
		private function parseScope($scope){
			if(strlen($scope) == 0) return array();
			$scope = trim($scope, ':');
			$parts = explode(':', $scope);
			return $parts;
		}
		
		/**
		* Overriding the BaseModel save to do proper save
		*/
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'tags
										(`user_id`, `name`, `webname`, `scope`) VALUES 
										(
											\''.$this->sp->db->escape($this->ownerId).'\', 
											\''.$this->sp->db->escape($this->name).'\', 
											\''.$this->sp->db->escape($this->webname).'\',
											\''.$this->sp->db->escape(':'.implode(':', $this->scope).':').'\'
										);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
		
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'tags SET
								`user_id` = \''.$this->sp->db->escape($this->ownerId).'\', 
								`webname` = \''.$this->sp->db->escape($this->webname).'\', 
								`name` = \''.$this->sp->db->escape($this->name).'\',
								`scope` = \''.$this->sp->db->escape(':'.implode(':', $this->scope).':').'\'
							WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the tag data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.$this->sp->db->prefix.'tags WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			return $ok;
		}
		
		public function addScope($scope){
			if(!in_array($scope, $this->scope)) $this->scope[] = $scope;
			return this;
		}
		
		public function removeScope($scope){
			if(in_array($scope, $this->scope)) unset($this->scope[array_search($scope, $this->scope)]);
			return this;
		}
		
		public function hasScope($scope){
			return in_array($scope, $this->scope);
		}
		
		
		/* getters */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getWebname() { return $this->webname; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
			return $this->ownerUser;
		}
		public function getOwnerId(){ return $this->ownerId; }
		public function getScope(){ return $this->scope; }
		
		/* setters */
		private function setId($id) { $this->id = $id; return $this;}
		public function setName($name) { $this->name = $name; return $this; }
		public function setWebname($webname) { $this->webname = $this->sp->txtfun->string2Web($webname); return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setScope($scope) { $this->scope = (is_array($scope))?$scope:$this->parseScope($scope); return $this; }
		
	}
?>