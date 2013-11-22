<?php

	use at\foundation\core;

	class HelloWorld extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->ini_file = $GLOBALS['to_root'].'_services/HelloWorld/HelloWorld.ini';	
			parent::__construct();
        }
        
        public function render($args){ 
			if(isset($args['message'])) return $args['message'];
			return $this->settings->message;
        }
		
		public function setup(){
        	
        }

	}
?>