<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Receipt extends core\BaseModel {
	
		private $entryId;
		private $entry;
		private $date;
		private $number;
		private $account;
	
		public function __construct() {
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_receipts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
		public static function getAllReceipts() {
		
		}
		
		public static function getReceiptsForEntry($entryId) {
		
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
								() VALUES 
								(
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
						
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setEntry($entryId) { $this->entryId = $entryId; $this->entry = null; return $this; }
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
		public function getDate() { return $this->date; }
		public function getNumber() { return $this->number; }
		public function getAccount() { return $this->account; }

	}
?>