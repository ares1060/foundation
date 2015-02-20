<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
		
	class Event extends core\BaseModel {
	
		private $id;
		/**
		 * @var DateTime
		 */
		private $start;
		/**
		* @var DateTime
		*/
		private $end;
		
		private $ownerId;
		private $ownerUser;
		private $eventGroupUid;
		private $text;
		private $color;
		private $wholeDay;
	
		/**
		 * @param DateTime $start
		 * @param DateTime $end
		 * @param string $text
		 * @param string $color
		 * @param int $owner
		 * @param string $eventGroupUid
		 */
		public function __construct($start = null, $end = null, $text = '', $color = '', $owner = '', $wholeDay = false, $eventGroupUid = '') {
            
			$this->start = (!$start)?null:$start;
			$this->end = (!$end)?null:$end;
			$this->text = $text;
			$this->color = $color;
			$this->ownerId = $owner;
			$this->wholeDay = $wholeDay;
			$this->eventGroupUid = $eventGroupUid;
			
			parent::__construct(ServiceProvider::get()->db->prefix.'calendar_events', array());
        }
		
		/**
		 * STATIC METHODS
		 */
	
		/**
		 * Fetches an array of Events matching the given parameters
		 * @param $insertSQL additional SQL code to be inserted between select from and limit;
		 * @param $from The row from withc to select 
		 * @param $rows The number of rows to select
		 * @return Event[] An array containing the resulting Events
		 */
		public static function getEvents($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			
			//echo 'SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'calendar_events '.$insertSQL.' '.$limit.';';
			
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT *, e.id AS id FROM '.ServiceProvider::getInstance()->db->prefix.'calendar_events AS e '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $event) {
				$ev = new Event(
					($event['start_date'] == '0000-00-00 00:00:00')?null:new DateTime($event['start_date']), 
					($event['end_date'] == '0000-00-00 00:00:00')?null:new DateTime($event['end_date']),
					$event['text'],
					$event['color'],
					$event['owner_id'],
					$event['whole_day'],
					$event['event_group_id']
				);
				$ev->setId($event['id']);
				$out[] = $ev;
			}
			return $out;
		}
		
		/**
		 * Fetches the Event with the given id from the database
		 * @param int $eventId The id of the Event
		 * @return Event|NULL
		 */
		public static function getEvent($eventId) {
			$event = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'calendar_events WHERE id =\''.ServiceProvider::getInstance()->db->escape($eventId).'\';');
			if($event){
				$ev = new Event(
					($event['start_date'] == '0000-00-00 00:00:00')?null:new DateTime($event['start_date']), 
					($event['end_date'] == '0000-00-00 00:00:00')?null:new DateTime($event['end_date']),
					$event['text'],
					$event['color'],
					$event['owner_id'],
					$event['whole_day'],
					$event['event_group_id']
				);
				$ev->setId($event['id']);
				return $ev;
			} else {
				return null;
			}
		}
		
		/**
		 * INSTANCE METHODS
 		 */
		 
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'calendar_events 
								(`id`, `start_date`, `end_date`, `text`, `color`, `owner_id`, `whole_day`, `event_group_id`) VALUES 
								(
									\''.$this->sp->db->escape($this->id).'\',
									\''.$this->sp->db->escape(($this->start)?$this->start->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
									\''.$this->sp->db->escape(($this->end)?$this->end->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
									\''.$this->sp->db->escape($this->text).'\',
									\''.$this->sp->db->escape($this->color).'\',
									\''.$this->sp->db->escape($this->ownerId).'\',
									\''.$this->sp->db->escape($this->wholeDay).'\',
									\''.$this->sp->db->escape($this->eventGroupUid).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'calendar_events SET
						`start_date` = \''.$this->sp->db->escape(($this->start)?$this->start->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
						`end_date` = \''.$this->sp->db->escape(($this->end)?$this->end->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
						`text` = \''.$this->sp->db->escape($this->text).'\',
						`color` = \''.$this->sp->db->escape($this->color).'\',
						`owner_id` = \''.$this->sp->db->escape($this->ownerId).'\',
						`whole_day` = \''.$this->sp->db->escape($this->wholeDay).'\',
						`event_group_id` = \''.$this->sp->db->escape($this->eventGroupUid).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the invoice data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'calendar_events WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
			//TODO: delete link entries
			return $ok;
		}
		
		public function data() {
			$out = array(
				'id' => $this->id,
				'start' => ($this->start)?$this->start->format('d.m.Y H:i:s'):'',
				'end' => (($this->end)?$this->end->format('d.m.Y H:i:s'):''),
				'text' => $this->text,
				'color' => $this->color,
				'whole_day' => $this->wholeDay,
				'eventGroupId' => $this->eventGroupUid
			);
			return $out;
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setStartDate($date) { $this->start = $date; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setEndDate($date) {
			$this->end = $date; return $this;
		}
		public function setText($text) { $this->text = $text; return $this; }
		public function setColor($color) { $this->color = $color; return $this; }
		public function setEventGroupId($id) { $this->eventGroupUid = $id; return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setWholeDay($day) { $this->wholeDay = $day; return $this; }
		
		public function getId(){ return $this->id; }
		/**
		 * @return DateTime
		 */
		public function getStartDate(){ return $this->start; }
		/**
		* @return DateTime
		*/
		public function getEndDate(){ return $this->end; }
		public function getText(){ return $this->text; }
		public function getColor(){ return $this->color; }
		public function getWholeDay(){ return $this->wholeDay; }
		public function getEventGroupId(){ return $this->eventGroupUid; }
		
		public function getOwner(){
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
			return $this->ownerUser;
		}
		public function getOwnerId(){
			return $this->ownerId;
		}
	}
?>