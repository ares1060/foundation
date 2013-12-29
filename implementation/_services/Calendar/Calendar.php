<?php

	use at\foundation\core;
	
	class Calendar extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->name = 'Calendar';
			$this->ini_file = $GLOBALS['to_root'].'_services/Calendar/Calendar.ini';	
			parent::__construct();
        }
		
		public function render($args){
			if(isset($args['action'])) $action = $args['action'];
			switch($action){
				case 'view.overview': return $this->handleViewOverview($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewOverview($args){
			$user = $this->sp->user->getLoggedInUser();
			
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_overview');
				$view->addValue('uid', $user->getId());
				return $view->render();
			} else {
				return '';
			}
		}
		
	}
?>