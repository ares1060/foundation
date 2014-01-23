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
				$view->addValue('start_view', (isset($args['tab']))?$args['tab']:$user->getUserData()->opt('set.calendar_start_view', 'month')->getValue());
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
				$tags = false;
				$contacts = false;
				$whereSQL = 'WHERE e.owner_id = \''.$this->sp->db->escape($user->getId()).'\'';
				
				
				$days = 4;
				if(isset($args['days'])) $days = $args['days']+1;
				
				$now = new DateTime();
				$now->setTime(0, 0, 0);
				$nowDay = $now->format('d');
				
				$to = new DateTime();
				$to->setTime(0, 0, 0);
				$to->add(new DateInterval('P'.$days.'D'));
				
				if(isset($args['search']) && strlen($args['search']) > 2){
					$args['search'] = $this->sp->db->escape($args['search']);
				
					//TODO tagging still hacky
					$whereSQL = 'LEFT JOIN '.$this->sp->db->prefix.'tag_links tl ON e.id = tl.param AND tl.service = \'Calendar\' LEFT JOIN '.$this->sp->db->prefix.'tags t ON t.id = tl.tag_id '.$whereSQL;
					$whereSQL .= ' AND (e.text LIKE \'%'.$args['search'].'%\' OR t.name LIKE \''.$args['search'].'%\')';
					$tags = true;
				}
				
				if(isset($args['date_from']) && $args['date_from'] != ''){
					$args['date_from'] = $this->sp->db->escape($args['date_from']);
					$whereSQL .= ' AND e.start_date >= \''.$args['date_from'].'\'';
				} else {
					$whereSQL .= ' AND e.start_date >= \''.$now->format('Y-m-d').'\'';
				}
				
				if(isset($args['date_to']) && $args['date_to'] != ''){
					$args['date_to'] = $this->sp->db->escape($args['date_to']);
					$whereSQL .= ' AND e.start_date <= \''.$args['date_to'].'\'';
				} else if(isset($args['days'])){
					$whereSQL .= ' AND e.start_date <= \''.$to->format('Y-m-d').'\'';
				}
				
				$from = 0;
				if(isset($args['from']) && $args['from'] > 0) {
					$from = $this->sp->db->escape($args['from']);
				}
					
				$rows = -1;
				if(isset($args['rows']) && $args['rows'] >= 0){
					$rows = $this->sp->db->escape($args['rows']);
				}		
				
				if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.$this->sp->db->escape($from).','.$this->sp->db->escape($rows);
				else $limit = '';		
				
				//TODO: generalize this so that services can infuse filter criteria generically
				if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0){
					$values = '';
					foreach($args['contact_filter'] as $val){
						$values .= $this->sp->db->escape($val).',';
					}
					$values = substr($values, 0, -1);
					$whereSQL = 'JOIN '.$this->sp->db->prefix.'calendar_events_contacts AS c ON c.entry_id = e.id '.$whereSQL;
					$whereSQL .= ' AND c.contact_id IN ('.$values.')';
					$contacts = true;
				}
				
				if($tags || $contacts) $whereSQL.= ' GROUP BY e.id';
				
				$events = $this->sp->db->fetchAll('SELECT *, e.id AS id FROM '.$this->sp->db->prefix.'calendar_events AS e '.$whereSQL.' ORDER BY e.start_date'.$limit);
				$count = $this->sp->db->fetchRow('SELECT SUM(tc.count) AS count FROM (SELECT COUNT(*) AS count FROM '.$this->sp->db->prefix.'calendar_events AS e '.$whereSQL.') AS tc;');
				
				
				if($rows < 0) $rows = max(1, count($events));
				$pages = ceil($count['count'] / $rows);
				
				$view->addValue('pages', $pages);
				if(isset($args['mode']) && $args['mode'] == 'wrapped'){
					$header = $view->showSubView('header');
					if($contacts) $header->showSubView('filter_contacts');
					
					$footer = $view->showSubView('footer');
					$footer->addValue('current_page', '0');
					$footer->addValue('events_per_page', $rows);
					$footer->addValue('pages', $pages);
				}
				
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