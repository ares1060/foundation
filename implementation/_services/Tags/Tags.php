<?php
	require_once $GLOBALS['config']['root'].'_services/Tags/model/Tag.php';
	
	use at\foundation\core;
	
	/**
     * Description
     * @author author
     * @version: version
     * @name: name
     * 
     * @requires: Services required
     */
    class Tags extends core\AbstractService implements core\IService {

    	
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
				case 'view.linker':
					return $this->handleViewLinker($args);
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
			$name = (isset($args['name']))?$args['name']:'';
			
			$user = $this->sp->user->getLoggedInUser();
			
			if(!isset($args['param'])) $tags = Tag::getTags($service, $name, ($user)?$user->getId():-1);
			else $tags = Tag::getLinkedTags($service, $param, ($user)?$user->getId():-1);

        	if(isset($args['mode']) && $args['mode'] == 'json'){
        		$out = array();
        		
        		foreach($tags as $tag){
        			$oi = array(
        				'id' => $tag->getId(),
        				'name' => $tag->getName()
        			);
        			$out[] = $oi;
        		}
        		
        		return $out;
        	} else {
        		$tpl = new core\Template\ViewDescriptor('_services/Tags/tag_list');
        		
        		foreach($tags as $tag){
        			$t = $tpl->showSubView('tag');
        		
        			$t->addValue('id', $tag->getId());
        			$t->addValue('service', $service);
        			$t->addValue('name', $tag->getName());
        			$t->addValue('webname', $tag->getWebname());
        			
        			if($tag !== end($tags)){
        				$t->showSubView('delim');
        			}
        		}
        		
        		return $tpl->render();
        	}
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
		
		private function handleViewLinker($args){
			$service = (isset($args['service']))?$args['service']:'';
			$param = (isset($args['param']))?$args['param']:'';
			$name = (isset($args['name']))?$args['name']:'';
				
			$user = $this->sp->user->getLoggedInUser();

			$tpl = new core\Template\ViewDescriptor('_services/Tags/tag_linker');
			
			if($param != ''){
				$tags = Tag::getLinkedTags($service, $param, ($user)?$user->getId():-1);
			
				$t = array();
				
				foreach($tags as $tag){
					$t[] = $tag->getName();
				}
				
				$tpl->addValue('tags', implode(',', $t));
			}
			
			$tpl->addValue('service', $service);
			$tpl->addValue('param', $param);
			
			return $tpl->render();
		}
		
		private function handleSaveTags($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user) {
				//get all currently linked tags
				$tags = Tag::getLinkedTags($args['service'], $args['param'], $user->getId());
				$newTags = explode(',', $args['tags']);
				$tagIds = array();
				
				//for each new tag
				foreach($newTags as $tagName){
					$tag = Tag::getTagByName($tagName, $user->getId());
					//check if tag already exists
					if($tag){
						$tag->addScope($args['service']);
					} else {
						//else create new tag
						$tag = new Tag($tagName, $tagName, $user->getId(), $args['service']);
						$tag->setScope($args['service']);
					}
					$tag->save();
					
					$tag->link($args['service'], $args['param']);
					$tagIds[] = $tag->getId();
				}
					
				//unlink old tags
				foreach($tags as $tag){
					if(!in_array($tag->getId(), $tagIds)){
						$tag->unlink($args['service'], $args['param']);
					}
				}
				
				return true;
			}
			
			return false;
		}
		
		private function handleLinkTag($args){
			if(!isset($args['service']) || !isset($args['name'])) return false;
			
			$tag = Tag::getTagByName($args['name']);
			if($tag == null) {
				$tag = new Tag($args['name'], (isset($args['webname']))?$args['webname']:$args['name']);
				$tag->save();
			}
			
			return $tag->link($args['service'], $args['param']);

		}
		
		private function handleUnlinkTag($args){
			if(!isset($args['service']) || !isset($args['name'])) return false;
			
			$tags = Tag::getTagsByService($args['service'], (isset($args['param']))?$args['service']:'');
			
			foreach($tags as $tag){
				$tag->unlink($service, $param);
			}
			
			return true;
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