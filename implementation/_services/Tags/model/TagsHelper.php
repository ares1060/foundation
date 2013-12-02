<?php
	class TagsHelper extends TFCoreFunctions{
		
		private $serviceTags;
		protected $name;
		
		function __construct(){
			parent::__construct();
			$this->serviceTags = array();
			$this->name = 'Tags';
		}

		
		/* ===========================  GETTER  ===========================  */

		
	
		
		/* ===========================  SETTER  ===========================  */

		
		/**
		 * Adds Tag To Service and Parameter
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function addTag($tag_name, $service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$tag = $this->getTagByName($tag_name);
				if($tag == null) $tag = $this->getTag($this->newTag($tag_name));
				
				if($this->mysqlInsert('INSERT INTO `'.ServiceProvider::getInstance()->db->prefix.'tags_link` 
												(`t_id`, `service`, `param`) VALUES 
												("'.ServiceProvider::getInstance()->db->escape($tag->getId()).'", 
												 "'.ServiceProvider::getInstance()->db->escape($service).'", 
												 "'.ServiceProvider::getInstance()->db->escape($param).'")') !== false) {
					
					//$this->_msg($this->_('_tag add success'), Messages::INFO);
					return true;
				} else {
					//$this->_msg($this->_('_tag add error'), Messages::ERROR);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/* ===========================  DELETER  ===========================  */
		/**
		 * deletes all Tags from Service
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteServiceTags($service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$tags = $this->getTagsByService($service, $param);
				
				foreach($tags as $tag) $this->deleteTagFromService($tag, $service, $param);
				
				return true;
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/**
		 * Deletes Tag From Service 
		 * notice $tag has to be a TagsTag object -> user $delteTagFromServiceById or $delteTagFromServiceByName
		 * 
		 * @param TagsTag $tag
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromService(TagsTag $tag, $service, $param){
			if($this->checkRight('administer_tags', $service)) {
				$count = $this->getTagCount($tag->getId());
				$error = !$this->mysqlDelete('DELETE FROM `'.ServiceProvider::getInstance()->db->prefix.'tags_link` 
													WHERE t_id="'.ServiceProvider::getInstance()->db->escape($tag->getId()).'"
													AND service="'.ServiceProvider::getInstance()->db->escape($service).'"
													AND param="'.ServiceProvider::getInstance()->db->escape($param).'"');	
				
				if(!$error && $count == 1){
					$error = !$error && !$this->mysqlDelete('DELETE FROM `'.ServiceProvider::getInstance()->db->prefix.'tags` 
													WHERE t_id="'.ServiceProvider::getInstance()->db->escape($tag->getId()).'"');
				}
				
				if($error){
					//$this->_msg($this->_('_tag delete error'), Messages::ERROR);
					return false;
				} else {
					//$this->_msg($this->_('_tag delete success'), Messages::INFO);
					return false;
				}
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
		
		/**
		 * deletes Tag From Service By Id
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromServiceById($tag_id, $service, $param){
			return $this->deleteTagFromService($this->getTag($tag_id), $service, $param);
		}
		
		/**
		 * deletes Tag From Service By Name
		 * 
		 * @param unknown_type $tag_name
		 * @param unknown_type $service
		 * @param unknown_type $param
		 */
		public function deleteTagFromServiceByName($tag_name, $service, $param){
			return $this->deleteTagFromService($this->getTagByName($tag_name), $service, $param);
		}
		
	}
?>