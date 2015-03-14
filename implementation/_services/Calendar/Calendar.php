<?php

	use at\foundation\core\TextFunctions\TextFunctions;
	use at\foundation\core\Template\SubViewDescriptor;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
	require_once($GLOBALS['config']['root'].'_services/Calendar/model/Event.php');
	
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
				case 'view.week': return $this->handleViewWeek($args); break;
				case 'view.form': return $this->handleViewForm($args); break; 
				case 'do.save': return $this->handleSave($args); break; 
				case 'do.delete': return $this->handleDelete($args); break; 
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewOverview($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_overview');
				$view->addValue('uid', $user->getId());
				$view->addValue('contact_id', (isset($args['contact_filter'])?$args['contact_filter']:''));
				$view->addValue('start_view', (isset($args['tab']))?$args['tab']:$user->getUserData()->opt('set.calendar_start_view', 'month')->getValue());
				$fh = explode(':', $user->getUserData()->opt('set.first_hour', '6')->getValue());
				$view->addValue('first_hour', $fh[0]*1);
				$view->addValue('event_duration', $user->getUserData()->opt('set.event_duration', '30')->getValue());
				return $view->render();
			} else {
				return '';
			}
		}
		
		public static function buildWhereSQLQuery($args){
			$sp = ServiceProvider::getInstance();
			$user = $sp->user->getSuperUserForLoggedInUser();
			$tags = false;
			$contacts = false;
			
			$whereSQL = 'WHERE e.owner_id = \''.$sp->db->escape($user->getId()).'\'';
			
			$days = 4;
			if(isset($args['days'])) $days = $args['days']+1;
			
			$now = new DateTime();
			$now->setTime(0, 0, 0);
			
			$to = new DateTime();
			$to->setTime(0, 0, 0);
			$to->add(new DateInterval('P'.$days.'D'));
			
			if(isset($args['search']) && strlen($args['search']) > 2){
				$args['search'] = $sp->db->escape($args['search']);
			
				//TODO tagging still hacky
				$whereSQL = 'LEFT JOIN '.$sp->db->prefix.'tag_links tl ON e.id = tl.param AND tl.service = \'Calendar\' LEFT JOIN '.$sp->db->prefix.'tags t ON t.id = tl.tag_id '.$whereSQL;
				$whereSQL .= ' AND (e.text LIKE \'%'.$args['search'].'%\' OR t.name LIKE \''.$args['search'].'%\')';
				$tags = true;
			}
			
			if(isset($args['date_from']) && $args['date_from'] != ''){
				$args['date_from'] = $sp->db->escape($args['date_from']);
				$whereSQL .= ' AND (e.start_date >= \''.$args['date_from'].' 00:00:00\' OR (e.end_date >= \''.$args['date_from'].' 00:00:00\' AND e.start_date <= \''.$args['date_from'].' 00:00:00\'))';
			} else {
				$whereSQL .= ' AND (e.start_date >= \''.$now->format('Y-m-d').' 00:00:00\' OR (e.end_date >= \''.$now->format('Y-m-d').' 00:00:00\' AND e.start_date <= \''.$now->format('Y-m-d').' 00:00:00\'))';
			}
			
			if(isset($args['date_to']) && $args['date_to'] != ''){
				$args['date_to'] = $sp->db->escape($args['date_to']);
				$whereSQL .= ' AND e.start_date <= \''.$args['date_to'].' 24:59:59\'';
			} else if(isset($args['days'])) {
				$whereSQL .= ' AND e.start_date <= \''.$to->format('Y-m-d').' 24:59:59\'';
			}
			
			//TODO: generalize this so that services can infuse filter criteria generically
			if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0){
				$values = '';
				foreach($args['contact_filter'] as $val){
					$values .= $sp->db->escape($val).',';
				}
				$values = substr($values, 0, -1);
				$whereSQL = 'JOIN '.$sp->db->prefix.'calendar_events_contacts AS c ON c.entry_id = e.id '.$whereSQL;
				$whereSQL .= ' AND c.contact_id IN ('.$values.')';
				$contacts = true;
			}
			
			if($tags || $contacts) $whereSQL.= ' GROUP BY e.id';
		
			return $whereSQL;
		}
		
		private function handleViewAgenda($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
				
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_agenda');
				
				if(isset($args['date_from']) && $args['date_from'] != '') {
					$dateFrom = new DateTime($args['date_from']);
				} else {
					$dateFrom = new DateTime();
					$dateFrom->setTime(0, 0, 0);
				}
				
				
				$whereSQL = Calendar::buildWhereSQLQuery($args);
				
				$from = 0;
				if(isset($args['from']) && $args['from'] > 0) {
					$from = $this->sp->db->escape($args['from']);
				}
				
				$rows = -1;
				if(isset($args['rows']) && $args['rows'] >= 0){
					$rows = $this->sp->db->escape($args['rows']);
				}
				
				$now = new DateTime();
				$now->setTime(0, 0, 0);
				$nowDay = $now->format('d');
								
				$events = Event::getEvents($whereSQL.' ORDER BY e.start_date', $from, $rows);
				$count = $this->sp->db->fetchRow('SELECT SUM(tc.count) AS count FROM (SELECT COUNT(*) AS count FROM '.$this->sp->db->prefix.'calendar_events AS e '.$whereSQL.') AS tc;');
				
				if($rows < 0) $rows = min(10, count($events));
				$pages = ceil($count['count'] / $rows);
				
				$view->addValue('pages', $pages);
				if(isset($args['mode']) && $args['mode'] == 'wrapped'){
					$header = $view->showSubView('header');
					$header->addValue('date_from', $dateFrom->format('d.m.Y'));
					if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0) $header->showSubView('filter_contacts');
					
					$footer = $view->showSubView('footer');
					$footer->addValue('current_page', '0');
					$footer->addValue('events_per_page', $rows);
					$footer->addValue('pages', $pages);
					$footer->addValue('event_duration', $user->getUserData()->opt('set.event_duration', '30')->getValue());
				}
				
				$dayView = null;
				$multiDay = array();
				$singleDay = array();
				$days = array();
				
				//seperate event types
				foreach($events as $event){
					$date = clone $event->getStartDate();
					$dateTo = clone $event->getEndDate();

					if($dateFrom->diff($date)->invert == 0 && !in_array($date->format('Y-m-d'), $days)) $days[] = $date->format('Y-m-d');
					if($dateFrom->diff($dateTo)->invert == 0 && !in_array($dateTo->format('Y-m-d'), $days)) $days[] = $dateTo->format('Y-m-d');
					
					if($date->format('d.m.Y') != $dateTo->format('d.m.Y') || $event->getWholeDay()) {
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

					$tMultiDay = array();
					foreach($multiDay as $mEvent){
					
						$mDate = $mEvent->getStartDate();
						$mDateTo = $mEvent->getEndDate();
					
					
						$ev = $dayView->showSubView('event');
						$ev->addValue('id', $mEvent->getId());
						$ev->addValue('title', $mEvent->getText());
					
						$ev->addValue('time_sep', '-');
						$ev->addValue('url', '?page=calendar&tab=agenda#event/'.$mEvent->getId());
						
						$fromTime = $mDate->format('H:i');
						$toTime = $mDateTo->format('H:i');
					
						if($mDate->format('Y-m-d') == $day && $fromTime != '00:00') {
							$ev->addValue('from_time', $fromTime);
							$ev->addValue('time_sep', '~');
							$ev->addValue('to_time', '');
							$tMultiDay[] = $mEvent;
						} else if($mDateTo->format('Y-m-d') != $day || $fromTime == '00:00') {
							$ev->addValue('from_time', 'Ganzer Tag');
							$ev->addValue('time_sep', '');
							$ev->addValue('to_time', '');
							if($mDateTo->format('Y-m-d') != $day) $tMultiDay[] = $mEvent;
						} else {
							$ev->addValue('from_time', '');
							$ev->addValue('time_sep', '~');
							$ev->addValue('to_time', $toTime);
						}
						
						if($mEvent->getColor() != ''){
							$tv = new SubViewDescriptor('tag');
							$tv->addValue('color', $mEvent->getColor());
							$ev->addSubView($tv);
						}
					}
					$multiDay = $tMultiDay;
					
					foreach($singleDay as $event){
						$date = clone $event->getStartDate();
						
						if($date->format('Y-m-d') != $day) break;
						
						array_shift($singleDay);
						$dateTo = clone $event->getEndDate();

						$ev = $dayView->showSubView('event');
						$ev->addValue('id', $event->getId());
						$ev->addValue('url', '?page=calendar&tab=agenda#event/'.$event->getId());
						$ev->addValue('title', $event->getText());
						$ev->addValue('from_time', $date->format('H:i'));				
						$ev->addValue('to_time', $dateTo->format('H:i'));
						$ev->addValue('time_sep', '-');
						
						if($event->getColor() != ''){
							$tv = new SubViewDescriptor('tag');
							$tv->addValue('color', $event->getColor());
							$ev->addSubView($tv);
						}
					}

				}
				
				return $view->render();
			} else {
				return '';
			}
		}
		
		private function handleViewWeek($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
		
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/calendar_week');
				$whereSQL = 'WHERE e.owner_id = \''.$this->sp->db->escape($user->getId()).'\'';

				$now = new DateTime();
				$now->setTime(0, 0, 0);
				$nowWeekDay = $now->format('w') - 1;
				
				($nowWeekDay >= 0)?$now->sub(new DateInterval('P'.$nowWeekDay.'D')):$now->sub(new DateInterval('P'.abs($nowWeekDay-5).'D'));
				
				$to = clone $now;
				$to->setTime(0, 0, 0);
				$to->add(new DateInterval('P6D'));
				
				$dateFrom = new DateTime();
				$dateFrom->setTime(0, 0, 0);
				if(isset($args['date_from']) && $args['date_from'] != ''){
					$dateFrom = new DateTime($args['date_from']);

					$now = new DateTime($args['date_from']);
					$now->setTime(0, 0, 0);
					
					$to = new DateTime($args['date_from']);
					$to->setTime(0, 0, 0);
					$to->add(new DateInterval('P6D'));
				} else {
					$args['date_from'] = $now->format('Y-m-d');
				}
				
				$args['date_to'] = $to->format('Y-m-d');
				
				$whereSQL = Calendar::buildWhereSQLQuery($args).' ORDER BY e.start_date';
				
				$events = Event::getEvents($whereSQL);				
				
				$today = new DateTime();
				$today = $today->format('d.m.Y');
				
				$todayFrom = clone $now;
				
				$multidaycounts = array(0,0,0,0,0,0,0);
				
				foreach($events as $event){
					if($event->getWholeDay()) {
						$md = min($event->getEndDate()->format('z'), $to->format('z')) - $now->format('z');
						for($i = max($now->format('z'), $event->getStartDate()->format('z')) - $now->format('z'); $i <= $md; $i++){
							$multidaycounts[$i]++;
						}
					}
				}
				
				$parallelMultiDayEvents = max($multidaycounts);
				$usedMultidayLevels = array();
				$multidayLevelsZIndex = array();
				for($i = 0; $i < $parallelMultiDayEvents; $i++){
					$usedMultidayLevels[$i] = false;
					$multidayLevelsZIndex[$i] = -1;
				}
				
				$events = array_reverse($events);
				
				function dateToRow($date) {
					return ($date->format('H') + round($date->format('i') / 60) * 0.5);
				}
				
				for($i = 0; $i < 7; $i++){
					$dv = new SubViewDescriptor('day');
					$dv->addValue('day', $this->sp->txtfun->fixDateLoc($now->format('l')));
					$dv->addValue('day_short', substr($this->sp->txtfun->fixDateLoc($now->format('l')), 0, 2));
					$dv->addValue('date', $now->format('d.m.'));
					$dv->addValue('date_full', $now->format('Y-m-d'));
					
					if($today == $now->format('d.m.Y')) {
						$nowTime = new DateTime();
						$dv->addValue('today', ' today');
						$tiv = $dv->showSubView('time');
						$tiv->addValue('top', ($nowTime->format('H') + round($nowTime->format('i') / 0.06) / 1000) + 2);
						$tiv->addValue('zindex', count($event));
					}
					
					$overlapEvents = array();
					
					$colCount = 0;
					$eventRange = array_fill(0, 48, 0);
					$todaysEvents = array();
					
					//seperate event types
					while($event = array_pop($events)) {
						if($event->getStartDate()->format('z') * 1 > $now->format('z') * 1) {
							$events[] = $event;
							break;
						}
						$ev = new SubViewDescriptor('item');
						
						$ev->addValue('zindex', count($events) + 1);
						
						$ev->addValue('id', $event->getId());
						
						$mask = null;
						if($event->getWholeDay()) {
							//a whole day event
							$ev->addValue('start_hour', 1);
							$ev->addValue('height', 1 / $parallelMultiDayEvents);
							
							for($m = 0; $m < $parallelMultiDayEvents; $m++){
								if(!$usedMultidayLevels[$m] || $usedMultidayLevels[$m] < $event->getStartDate()->format('z')) {
									if($multidayLevelsZIndex[$m] > 0) {
										$multidayLevelsZIndex[$m]--;
										$ev->addValue('zindex', $multidayLevelsZIndex[$m]);
									} else $multidayLevelsZIndex[$m] = count($events) + 1;
									$ev->addValue('start_hour', 1 + $m / $parallelMultiDayEvents);
									$usedMultidayLevels[$m] = $event->getEndDate()->format('z');
									break;
								}
							}
							
							$ev->addValue('colspan', min($event->getEndDate()->format('z'), $to->format('z')) - max($event->getStartDate()->format('z'), $now->format('z')) + 1);
							$ev->addValue('type', 'multiday');
							$ev->addValue('from_to', $event->getStartDate()->format('d.m.') .' - '. $event->getEndDate()->format('d.m.'));
						} else if($event->getStartDate()->format('z') != $now->format('z')) {
							//event started before today
							$ev->addValue('start_hour', 2);
							if($event->getEndDate()->format('z') == $now->format('z')){
								$ev->addValue('height', dateToRow($event->getEndDate()));
								$ev->addValue('overlap', 'overlap top');
								$length = dateToRow($event->getEndDate()) * 2;
							} else {
								$ev->addValue('height', 24);
								$ev->addValue('overlap', 'overlap top bottom');
								$overlapEvents[] = $event;
								$length = 48;
							}
							$ev->addValue('from_to', $event->getStartDate()->format('d.m. H:i') .' - '. $event->getEndDate()->format('d.m. H:i'));
							
							$mask = array_fill(0, $length, 1);
							$todaysEvents[] = array(
								'view' => $ev,
								'from' => 0,
								'length' => $length
							);
						} else {
							//event starts today
							$ev->addValue('start_hour', dateToRow($event->getStartDate()) + 2);
							$from = dateToRow($event->getStartDate()) * 2;
								
							if($event->getStartDate()->format('z') == $event->getEndDate()->format('z')){
								$ev->addValue('height', max(.5, dateToRow($event->getEndDate()) - dateToRow($event->getStartDate())));
								$ev->addValue('from_to', $event->getStartDate()->format('H:i') .'-'. $event->getEndDate()->format('H:i'));
								$length = dateToRow($event->getEndDate()) * 2 - $from;
							} else {
								$ev->addValue('height', 24 - dateToRow($event->getStartDate()));
								$ev->addValue('overlap', 'overlap bottom');
								$overlapEvents[] = $event;
								$ev->addValue('from_to', $event->getStartDate()->format('d.m. H:i') .' - '. $event->getEndDate()->format('d.m. H:i'));
								$length = 48 - $from;
							}
							
							
							if($length > 0) $mask = array_fill($from, $length, 1);
							$todaysEvents[] = array(
								'view' => $ev,
								'from' => $from,
								'length' => $length
							);
						}
						
						if($mask) {
							foreach($mask as $index => $value){
								$eventRange[$index] += 1;
							}
						}
						
						$ev->addValue('text', $event->getText());
						
						if($event->getColor() != ''){
							$tv = new SubViewDescriptor('tag');
							$tv->addValue('color', $event->getColor());
							$ev->addSubView($tv);
						}
						
						$dv->addSubView($ev);
					}
					
					//count events today and set their width accordingly
					$shift = 0;
					while($event = array_shift($todaysEvents)) {
						if($event['length'] > 0) $cols = max(array_slice($eventRange, $event['from'], $event['length']));
						else $cols = 0;
						if($cols > 1) {
							$event['view']->addValue('colspan', 1 / $cols);
							$event['view']->addValue('left', 100 * $shift / $cols .'%');
							$shift++;
						} else {
							$shift = 0;
						}
					}
					
					
					$view->addSubView($dv);
					$now->add(new DateInterval('P1D'));
					
					$events = array_merge($events, $overlapEvents);
				}
					
				if(isset($args['mode']) && $args['mode'] == 'wrapped'){
					$header = $view->showSubView('header');
					$header->addValue('date_from', $todayFrom->format('Y-m-d'));
						
					$footer = $view->showSubView('footer');
					$footer->addValue('event_duration', $user->getUserData()->opt('set.event_duration', '30')->getValue());
				}
				
				return $view->render();
			} else {
				return '';
			}
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
				
			if($user && $user->getId() > 0){
				$view = new core\Template\ViewDescriptor('_services/Calendar/event_form');
				if(isset($args['id'])){
					$event = Event::getEvent($args['id']);
					if($event->getOwnerId() != $user->getId()) return '';
					$view->addValue('id', $event->getId());
				
					$view->addValue('date_from', $event->getStartDate()->format('d.m.Y H:i'));
				
					$view->addValue('date_to', $event->getEndDate()->format('d.m.Y H:i'));
					$view->addValue('text', $event->getText());
					$view->addValue('color', $event->getColor());
					$view->addValue('whole_day', ($event->getWholeDay())?' checked="checked"':'');
					
					$jbv = $view->showSubView('journal_button');
					$jbv->addValue('id', $event->getId());
				}
				return $view->render();
			} else {
				return '';
			}
		}
		
		private function handleGetEvent($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
		
			if($user && $user->getId() > 0 && isset($args['id'])){
				$event = $this->sp->db->fetchRow('SELECT * FROM '.$this->sp->db->prefix.'calendar_events WHERE id =\''.$this->sp->db->escape($args['id']).'\';');
				if($event['owner_id'] != $user->getId()) return '';

				return $event;
			} else {
				return '';
			}
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get event
					$event = Event::getEvent($args['id']);
					if(!$event || $event->getOwnerId() != $user->getId()) return false;
				} else {
					//create new event
					$event = new Event();
					$event->setOwner($user->getId());
				}
				
				if(isset($args['text'])) $event->setText($args['text']);
				if(isset($args['color'])) $event->setColor($args['color']);
				if(isset($args['start_date'])) $event->setStartDate(new DateTime($args['start_date']));
				if(isset($args['end_date'])) $event->setEndDate(new DateTime($args['end_date']));
				if(isset($args['whole_day']) && $args['whole_day']) $event->setWholeDay(true);
				else $event->setWholeDay(false);
				
				$ok = $event->save();
				
				if($ok) return $event->getId();
				else return false;
			} else {
				return false;
			}
		}
		
		private function handleDelete($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get contact
					$event = Event::getEvent($args['id']);
					if(!$event) return true;
					if($event->getOwnerId() != $user->getId()) return false;

					//TODO clean up linked contacts
					
					return $event->delete();
				}
			}
			return false;
		}
		
	}
?>