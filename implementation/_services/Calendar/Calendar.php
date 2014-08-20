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
				case 'get.event': return $this->handleGetEvent($args); break;
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
				$view->addValue('event_duration', $user->getUserData()->opt('set.event_duration', '30')->getValue());
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
				
				$dateFrom = new DateTime();
				$dateFrom->setTime(0, 0, 0);
				if(isset($args['date_from']) && $args['date_from'] != ''){
					$args['date_from'] = $this->sp->db->escape($args['date_from']);
					$dateFrom = new DateTime($args['date_from']);
					$whereSQL .= ' AND (e.start_date >= \''.$args['date_from'].' 00:00:00\' OR (e.end_date >= \''.$args['date_from'].' 00:00:00\' AND e.start_date <= \''.$args['date_from'].' 00:00:00\'))';
				} else {
					$whereSQL .= ' AND (e.start_date >= \''.$now->format('Y-m-d').' 00:00:00\' OR (e.end_date >= \''.$now->format('Y-m-d').' 00:00:00\' AND e.start_date <= \''.$now->format('Y-m-d').' 00:00:00\'))';
				}
				
				if(isset($args['date_to']) && $args['date_to'] != ''){
					$args['date_to'] = $this->sp->db->escape($args['date_to']);
					$whereSQL .= ' AND e.start_date <= \''.$args['date_to'].' 24:59:59\'';
				} else if(isset($args['days'])){
					$whereSQL .= ' AND e.start_date <= \''.$to->format('Y-m-d').' 24:59:59\'';
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
					$header->addValue('date_from', $dateFrom->format('d.m.Y'));
					if($contacts) $header->showSubView('filter_contacts');
					
					$footer = $view->showSubView('footer');
					$footer->addValue('current_page', '0');
					$footer->addValue('events_per_page', $rows);
					$footer->addValue('pages', $pages);
				}
				
				$dayView = null;
				$multiDay = array();
				$singleDay = array();
				$days = array();
				
				//seperate event types
				foreach($events as $event){
					$date = new DateTime($event['start_date']);
					$dateTo = new DateTime($event['end_date']);

					if($dateFrom->diff($date)->invert == 0 && !in_array($date->format('Y-m-d'), $days)) $days[] = $date->format('Y-m-d');
					if($dateFrom->diff($dateTo)->invert == 0 && !in_array($dateTo->format('Y-m-d'), $days)) $days[] = $dateTo->format('Y-m-d');
					
					if($date->format('d.m.Y') != $dateTo->format('d.m.Y')) {
						$multiDay[] = $event;
						$dur = $date->diff($dateTo);
						for($d = 1; $d < $dur->days; $d++){
							$date = $date->add(new DateInterval('P1D'));
							if($dateFrom->diff($date)->invert == 0 && !in_array($date->format('Y-m-d'), $days)) $days[] = $date->format('Y-m-d');
						}
					} else $singleDay[] = $event;

				}

				sort($days);
				
				//create sub views
				foreach($days as $day){
					
					$dayTime = new DateTime($day);
					
					$dayView = $view->showSubView('day');
					$tNow = clone $now;
					if($day == $tNow->format('Y-m-d')) $dayView->addValue('day', 'Heute');
					else if($day == $tNow->add(new DateInterval('P1D'))->format('Y-m-d')) $dayView->addValue('day', 'Morgen');
					else $dayView->addValue('day', $this->sp->txtfun->fixDateLoc($dayTime->format('l')));
									
					$dayView->addValue('date', $this->sp->txtfun->fixDateLoc($dayTime->format('d. F Y')));

					foreach($singleDay as $event){
						$date = new DateTime($event['start_date']);
						
						if($date->format('Y-m-d') != $day) break;
						
						array_shift($singleDay);
						$dateTo = new DateTime($event['end_date']);

						$ev = $dayView->showSubView('event');
						$ev->addValue('id', $event['id']);
						$ev->addValue('url', '?page=calendar&tab=agenda#event/'.$event['id']);
						$ev->addValue('title', $event['text']);
						$ev->addValue('from_time', $date->format('H:i'));				
						$ev->addValue('to_time', $dateTo->format('H:i'));
					}
					
					$tMultiDay = array();
					foreach($multiDay as $mEvent){
						
						$mDate = new DateTime($mEvent['start_date']);
						$mDateTo = new DateTime($mEvent['end_date']);


						$ev = $dayView->showSubView('event');
						$ev->addValue('id', $mEvent['id']);
						$ev->addValue('title', $mEvent['text']);
							
						if($mDate->format('Y-m-d') == $day) {
							$ev->addValue('from_time', $dateTo->format('H:i'));
							$ev->addValue('to_time', '...');
							$tMultiDay[] = $mEvent;
						} else if($mDateTo->format('Y-m-d') != $day) {
							$ev->addValue('from_time', '...');
							$ev->addValue('to_time', '...');
							$tMultiDay[] = $mEvent;
						} else {
							$ev->addValue('from_time', '...');
							$ev->addValue('to_time', $date->format('H:i'));
						}
					}
					$multiDay = $tMultiDay;

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
				if($event['owner_id'] != $user->getId()) return '';
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
		
		private function handleGetEvent($args){
			$user = $this->sp->user->getLoggedInUser();
		
			if($user && $user->getId() > 0 && isset($args['id'])){
				$event = $this->sp->db->fetchRow('SELECT * FROM '.$this->sp->db->prefix.'calendar_events WHERE id =\''.$this->sp->db->escape($args['id']).'\';');
				if($event['owner_id'] != $user->getId()) return '';

				return $event;
			} else {
				return '';
			}
		}
		
	}
?>