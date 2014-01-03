<?php
	class TagsView extends TFCoreFunctions {
		private $config;
		private $dataHelper;
		
		function __construct($config, TagsHelper &$dataHelper){
        	parent::__construct();
        	$this->config = $config;
        	$this->dataHelper = $dataHelper;
        }


        
		/**
         * returnes rendered Template for Tags by Service and Param
         * 
         * @param $service
         * @param $param
         */
        public function getAdminTags($service, $param){
        	$tags = $this->dataHelper->getTagsByService($service, $param);
        	$tpl = new ViewDescriptor($this->tpl('service_admin_tags'));
        	$tpl->addValue('service', $service);
        	$tpl->addValue('param', $param);
        	
        	foreach($tags as $tag){
        		$t = new SubViewDescriptor('tag');
        		
        		$t->addValue('id', $tag->getId());
        		$t->addValue('service', $service);
        		$t->addValue('param', $param);
        		$t->addValue('name', $tag->getName());
        		
        		$tpl->addSubView($t);
        		unset($t);
        	}
        	return $tpl->render();
        }
	}
?>