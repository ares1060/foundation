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
				case 'view.form': return $this->handleViewForm($args); break; 
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
		
		private function handleViewForm($args){
			$user = $this->sp->user->getLoggedInUser();
				
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/event_form');
				$event = $this->sp->db->fetchRow('SELECT * FROM '.$this->sp->db->prefix.'calendar_events WHERE id =\''.$this->sp->db->escape($args['id']).'\';');
				$view->addValue('id', $event['id']);
				
				$event['start_date'] = new DateTime($event['start_date']);
				$view->addValue('date_from', $event['start_date']->format('d.m.Y H:i'));
				
				$event['end_date'] = new DateTime($event['end_date']);
				$view->addValue('date_to', $event['end_date']->format('d.m.Y H:i'));
				$view->addValue('text', $event['text']);
				return $view->render();
			} else {
				return '';
			}
		}
		
	}
?>