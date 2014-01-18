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
				case 'view.agenda': return $this->handleViewAgenda($args); break;
				case 'view.form': return $this->handleViewForm($args); break; 
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewOverview($args){
			$user = $this->sp->user->getLoggedInUser();
			
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_overview');
				$view->addValue('uid', $user->getId());
				$view->addValue('contact_id', (isset($args['contact_filter'])?$args['contact_filter']:''));
				$view->addValue('start_view', $user->getUserData()->opt('set.calendar_start_view', 'month')->getValue());
				$view->addValue('first_hour', $user->getUserData()->opt('set.first_hour', '6')->getValue());
				return $view->render();
			} else {
				return '';
			}
		}
		
		private function handleViewAgenda($args){
			$user = $this->sp->user->getLoggedInUser();
				
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_agenda');
				
				$days = 3;
				if(isset($args['days'])) $days = $args['days'];
				
				$now = new DateTime();
				$now->setTime(0, 0, 0);
				$nowDay = $now->format('d');
				
				$to = new DateTime();
				$to->setTime(0, 0, 0);
				$to->sub(new DateInterval('P'.$days.'D'));
				
				$events = $this->sp->db->fetchAll('SELECT * FROM '.$this->sp->db->prefix.'calendar_events WHERE start_date >= \''.$now->format('Y-m-d').'\' AND start_date >= \''.$to->format('Y-m-d').'\' ORDER BY start_date;');
				
				$day = '';
				$dayView = null;
				foreach($events as $event){
					$date = new DateTime($event['start_date']);
					if($day != $date->format('d')) {
						$day = $date->format('d');
						$dayView = $view->showSubView('day');
						if($date->format('d') == $nowDay) $dayView->addValue('day', 'Heute');
						else if($date->format('d') == $nowDay+1) $dayView->addValue('day', 'Morgen');
						else $dayView->addValue('day', $this->sp->txtfun->fixDateLoc($date->format('l')));
						
						$dayView->addValue('date', $this->sp->txtfun->fixDateLoc($date->format('d. F Y')));
					}
					$ev = $dayView->showSubView('event');
					$ev->addValue('id', $event['id']);
					$ev->addValue('title', $event['text']);
					$ev->addValue('from_time', $date->format('H:i'));
					$dateTo = new DateTime($event['end_date']);
					$ev->addValue('to_time', $dateTo->format('H:i'));

				}
				
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