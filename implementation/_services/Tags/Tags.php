<?php
	require_once 'model/TagsHelper.php';
	require_once 'model/Tag.php';
	require_once 'view/TagsView.php';
	
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class Tags extends AbstractService implements IService {

    	
        function __construct(){
        	$this->name = 'Tags';
        	$this->ini_file = $GLOBALS['config']['root'].'_services/Tags/Tags.ini';
            parent::__construct();
        }

		
        public function render($args) {
        	
			$action = isset($args['action']) ? $args['action'] : '';
        	
        	switch($action){
        		case 'view.list':
        			return $this->handleViewList($args);
        			break;
        		case 'view.cloud':
        			return $this->handleViewCloud($args);
        			break;
				case 'do.save_tags':
        			return $this->handleSaveTags($args);
        			break;
				case 'do.link_tag':
        			return $this->handleLinkTag($args);
        			break;
        		case 'do.unlink_tag':
        			return $this->handleUnlinkTag($args);
        			break;
        	}
            return '';
        }
		
		private function handleViewList($args){
			$service = (isset($args['service']))?$args['service']:'';
			$param = (isset($args['param']))?$args['param']:'';
			
			$tags = Tag::getTagsByService($service, $param);
        	$tpl = new core\Template\ViewDescriptor($this->settings->tpl_service_tags);
        	
        	foreach($tags as $tag){
        		$t = $tpl->showSubView('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('name', $tag->getName());
        		$t->addValue('webname', $tag->getWebname());
        		
        		unset($t);
        	}
        	return $tpl->render();
		}
		
		private function handleViewCloud($args){
			$service = (isset($args['service']))?$args['service']:'';
			$param = (isset($args['param']))?$args['param']:'';
					
			$tags = Tag::getTagsByService($service, $param);
        	
        	shuffle($tags);
        	
        	$count = array();
        	$max_count = 0;
        	
        	foreach($tags as $tag){
        		$c = $tag->getCount($service);
        		$count[$tag->getId()] = $c;
        		if($c > $max_count) $max_count = $c;
        	}
        	
        	$tpl = new core\Template\ViewDescriptor($this->settings->tpl_service_tag_cloud);

        	foreach($tags as $tag){
        		$t = $tpl->showSubView('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('name', $tag->getName());
        		$t->addValue('size', round($this->settings->max_tag_cloud_size/($max_count/$count[$tag->getId()])));
        		
        		unset($t);
        	}
        	return $tpl->render();
		}
		
		private function handleSaveTags($args){
			if($this->checkRight('administer_tags', $args['service'])) {
				
			}
		}
		
		private function handleLinkTag($args){
			if(!isset($args['service']) || !isset($args['name'])) return false;
			if($this->checkRight('administer_tags', $args['service'])) {
				$tag = Tag::getTagByName($args['name']);
				if($tag == null) {
					$tag = new Tag($args['name'], (isset($args['webname']))?$args['webname']:$args['name']);
					$tag->save();
				}
				
				return $tag->link($args['service'], $args['param']);

			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return false;
			}
		}
		
		private function handleUnlinkTag($args){
			if(!isset($args['service']) || !isset($args['name'])) return false;
			if($this->checkRight('administer_tags', $args['service'])) {
				$tags = Tag::getTagsByService($args['service'], (isset($args['param']))?$args['service']:'');
				
				foreach($tags as $tag){
					$tag->unlink($service, $param);
				}
				
				return true;
			} else {
				$this->_msg($this->_('You are not authorized', 'rights'), Messages::ERROR);
        		return array();
			}
		}
        
		/**
		 * Creates a SQL subselect for joining when filtering for tags
		 * @param string $service The name of the service
		 * @param string $param
		 */
		public function getSubSelectSQL($service, $param = '', $userId = -1) {
			return Tag::getSubSelectSQL($service, $param, $userId);
		}
		
        /* =========  Getter ====== */
        /**
         * returnes Tag by given webname
         * @param $name
         */
        public function getTagByWebname($webname, $userId = -1) {
        	return Tag::getTagByWebname($webname, $userId);
        }
		
		/* ===========================  RIGHTS  ===========================  */
		private function allowUser($service, $u_id){
			return $this->sp->ref('Rights')->authorizeUser('Tags', 'administer_tags', $u_id, $service);
		}
    }
?>