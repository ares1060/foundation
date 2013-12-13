<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Receipt extends core\BaseModel {
	
		private $entryId;
		private $entry;
		/**
		 * @var DateTime
		 */
		private $date;
		private $number;
		private $account;
	
		public function __construct($entryId = -1, $date = null, $number = '', $account = '') {
			
			$this->entryId = $entryId;
			$this->date = (!$date)?new DateTime():$date;
			$this->number = $number;
			$this->account = $account;
			
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_receipts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
       	/**
       	 * Fetches all Receipts matching the parameters
       	 * @param string $insertSQL
       	 * @param int $from
       	 * @param int $rows
       	 */
		public static function getReceipts($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_receipts '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $receipt) {
				$ro = new Receipt($receipt['entry_id'], $receipt['date'], $receipt['number'], $receipt['account']);
				$ro->setId($receipt['id']);
				$out[] = $ro;
			}
			return $out;
		}
		
		public static function getReceiptsForEntry($entryId) {
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_receipts WHERE `entry_id` = \''.ServiceProvider::getInstance()->db->escape($entryId).'\';');
			$out = array();
			foreach($result as $receipt) {
				$ro = new Receipt($receipt['entry_id'], $receipt['date'], $receipt['number'], $receipt['account']);
				$ro->setId($receipt['id']);
				$out[] = $ro;
			}
			return $out;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_receipts 
								(`entry_id`, `date`, `number`, `account`) VALUES 
								(
									\''.$this->sp->db->escape($this->entryId).'\',
									\''.$this->sp->db->escape($this->date->format('Y-m-d')).'\',
									\''.$this->sp->db->escape($this->number).'\',
									\''.$this->sp->db->escape($this->account).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_receipts SET
						`entry_id` = \''.$this->sp->db->escape($this->entryId).'\', 
						`date` = \''.$this->sp->db->escape($this->date->format('Y-m-d')).'\', 
						`number` = \''.$this->sp->db->escape($this->number).'\',
						`account` = \''.$this->sp->db->escape($this->account).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'bookie_receipts WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setEntry($entryId) { $this->entryId = $entryId; $this->entry = null; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setDate($date) { $this->date = $date; return $this; }
		public function setNumber($number) { $this->number = $number; return $this; }
		public function setAccount($account) { $this->account = $account; return $this; }

	
		public function getId(){ return $this->id; }
		/**
		 * @return Entry
		 */
		public function getEntry(){ 
			if(!$this->entry == null) $this->entry = Entry::getEntry($this->entryId);
			return $this->entry;
		}
		public function getEntryId(){ return $this->entryId; }
		/**
		 * @return DateTime
		 */
		public function getDate() { return $this->date; }
		public function getNumber() { return $this->number; }
		public function getAccount() { return $this->account; }

	}
?>