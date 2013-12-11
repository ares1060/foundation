<?php

	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;

	class Tag extends core\BaseModel {
		private $id;
		private $name;
		private $webname;
		
		function __construct($name = '', $webname = ''){
			$this->name = $name;
			$this->webname = $this->sp->txtfun->string2Web($webname);
			parent::__construct(ServiceProvider::get()->db->prefix.'tags', array());
		}	
		
		/**
		 * STATIC METHODS
		 */
		
		/**
		 * checks if Tag exists with given service and param
		 * @param string $name
		 * @param string $service
		 * @param string $param
		 */
		public static function tagExists($name, $service, $param){
			$r = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` t 
											LEFT JOIN `'.ServiceProvider::getInstance()->db->prefix.'tags_link` tl ON t.id = tl.id
											WHERE t.name="'.ServiceProvider::getInstance()->db->escape($name).'" 
											AND tl.service="'.ServiceProvider::getInstance()->db->escape($service).'"
											AND tl.param ="'.ServiceProvider::getInstance()->db->escape($param).'"');
			if($r != ''){
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
					$t = new Tag($a['name'], $a['webname']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Name
		 * 
		 * @param string $name
		 */
		public static function getTagByName($name){
			if($name != ''){
				$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` WHERE `name`="'.ServiceProvider::getInstance()->db->escape($name).'"');
				if($a != ''){
					$t = new Tag($a['name'], $a['webname']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes Tag by Webname
		 * 
		 * @param string $webname
		 */
		public static function getTagByWebname($webname){
			if($webname != ''){
				$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` WHERE `webname`="'.ServiceProvider::getInstance()->db->escape($webname).'"');
				if($a != ''){
					$t = new Tag($a['name'], $a['webname']);
					$t->setId($a['id']);
					return $t;
				} else return null;
			} else return null;
		}
		
		/**
		 * returnes all connected Params By given Tag and service
		 * @param TagsTag $tag
		 */
		public function getParamsForTag(TagsTag $tag, $service){
			$a = $this->mysqlArray('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags_link` WHERE t_id="'.ServiceProvider::getInstance()->db->escape($tag->getId()).'" AND service="'.ServiceProvider::getInstance()->db->escape($service).'"');
			if(is_array($a)) {
				$return = array();
				foreach($a as $a_){
					$return[] = $a_['param'];
				}
				return $return;
			} else return array();
		}
		
		/**
		 * returnes Tags By Service
		 * cached
		 * 
		 * @param unknown_type $service
		 * @param unknown_type $param
		 * @param boolean $doQuery If false the sql statemnt is returend instead of the result.
		 */
		public function getTagsByService($service, $param=''){
			if($service != ''){
				if($this->serviceTags == array() || !isset($this->serviceTags[$service])){
					$param = ($param != '') ? ' AND tl.param="'.ServiceProvider::getInstance()->db->escape($param).'"' : '';
					$a = $this->mysqlArray('SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags_link` tl
												LEFT JOIN `'.ServiceProvider::getInstance()->db->prefix.'tags` t ON tl.t_id = t.t_id 
												WHERE tl.service="'.ServiceProvider::getInstance()->db->escape($service).'" '.$param;);
					if($a != ''){
						$this->serviceTags[$service] = array();
						foreach($a as $tag){
							$this->serviceTags[$service][] = new TagsTag($tag['t_id'], $tag['name'], $tag['webname']);
						}
					} else {
						return array();
						break;
					}
				}
				return $this->serviceTags[$service];
			} else return array();
		}

		/**
		 * Creates a SQL subselect for joining when filtering for tags
		 * @param string $service The name of the service
		 * @param string $param 
		 */
		public static function getSubSelectSQL($service, $param) {
			return 'SELECT * FROM `'.ServiceProvider::getInstance()->db->prefix.'tags_link` tl
												LEFT JOIN `'.ServiceProvider::getInstance()->db->prefix.'tags` t ON tl.t_id = t.t_id 
												WHERE tl.service="'.ServiceProvider::getInstance()->db->escape($service).'" '.$param;
		}
		
		/**
		 * Returnes TagCount by Tag id
		 * 
		 * @param unknown_type $tag_id
		 */
		public function getTagCount($tag_id, $service=''){
			if($tag_id > 0){
				$service = ($service == '') ? '' : ' AND service="'.ServiceProvider::getInstance()->db->escape($service).'" ';
				$a = $this->mysqlRow('SELECT COUNT(*) myCount FROM `'.ServiceProvider::getInstance()->db->prefix.'tags_link` WHERE t_id="'.ServiceProvider::getInstance()->db->escape($tag_id).'"'.$service);
				if($a != ''){
					return $a['myCount'];
				} else return -1;
			} else return -1;
		}
		
		
		/** 
		 * INSTANCE METHODS
		 */
		 
		 
		
		
		/* getters */
		public function getId() { return $this->id; }
		public function getName() { return $this->name; }
		public function getWebname() { return $this->webname; }
		
		/* setters */
		private function setId($id) { $this->id = $id; return this;}
		public function setName($name) { $this->name = $name; return $this; }
		public function setWebname($webname) { $this->webname = $this->sp->txtfun->string2Web($webname); return $this; }
		
		
	}
?>