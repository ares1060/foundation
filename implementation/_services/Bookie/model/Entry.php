<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	require_once $GLOBALS['config']['root'].'_services/Contacts/model/Receipt.php';
	require_once $GLOBALS['config']['root'].'_services/Contacts/model/Invoice.php';
	
	class Entry extends core\BaseModel {
		
		private $id;
		private $ownerUser;
		private $ownerId;
		private $notes;
		private $brutto;
		private $netto;
		private $taxType;
		private $taxValue;
		/**
		 * @var DateTime
		 */
		private $date;
		private $state;
	
		public function __construct($owner = -1, $brutto = 0, $netto = 0, $taxType = '', $taxValue = 0, $date = null, $notes = '') {
            $this->ownerId = $owner;
			$this->brutto = $brutto;
			$this->netto = $netto;
			$this->taxType = $taxType;
			$this->taxValue = $taxValue;
			$this->date = (!$date)?new DateTime():$date;
			$this->notes = $notes;
			parent::__construct(ServiceProvider::get()->db->prefix.'bookie_entries', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
        /**
         * Fetches all matching Entries from the database and returns them in an array
         * @param string $insertSQL
         * @param int $from
         * @param int $rows
         * @return Entry[]
         */
		public static function getEntries($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_entries '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $entry) {
				$eo = new Entry($entry['user_id'], $entry['brutto'], $entry['netto'], $entry['tax_type'], $entry['tax_value'], new DateTime($entry['date']), $entry['notes']);
				$eo->setId($entry['id']);
				$out[] = $eo;
			}
			return $out;
		}
		
		/**
		 * Fetches the Entry with the given ID from the database
		 * @param int $entryId The id of the Entry to be fetched
		 * @return Entry|NULL
		 */
		public static function getEntry($entryId) {
			$entry = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_entries WHERE id =\''.ServiceProvider::getInstance()->db->escape($entryId).'\';');
			if($contact){
				$eo = new Entry($entry['user_id'], $entry['brutto'], $entry['netto'], $entry['tax_type'], $entry['tax_value'], new DateTime($entry['date']), $entry['notes']);
				$eo->setId($entry['id']);
				return $eo;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_entries 
								(`user_id`, `notes`, `brutto`, `netto`, `tax_type`, `tax_value`, `date`, `state`) VALUES 
								(
									\''.$this->sp->db->escape($this->ownerId).'\', 
									\''.$this->sp->db->escape($this->notes).'\', 
									\''.$this->sp->db->escape($this->brutto).'\', 
									\''.$this->sp->db->escape($this->netto).'\', 
									\''.$this->sp->db->escape($this->tax_type).'\', 
									\''.$this->sp->db->escape($this->tax_value).'\', 
									\''.$this->sp->db->escape($this->date->format('Y-m-d')).'\', 
									\''.$this->sp->db->escape($this->state).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_entries SET
						`user_id` = \''.$this->sp->db->escape($this->ownerId).'\', 
						`notes` = \''.$this->sp->db->escape($this->notes).'\', 
						`brutto` = \''.$this->sp->db->escape($this->brutto).'\', 
						`netto` = \''.$this->sp->db->escape($this->netto).'\', 
						`tax_type` = \''.$this->sp->db->escape($this->tax_type).'\', 
						`tax_value` = \''.$this->sp->db->escape($this->tax_value).'\', 
						`date` = \''.$this->sp->db->escape($this->date->format('Y-m-d')).'\', 
						`state` = \''.$this->sp->db->escape($this->state).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'bookie_entries WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
		}
		 
		/**
		 * Recalculates the netto value based on the brutto and tax value
		 * @return Entry Returns a reference to this instance of Entry
		 */
		public function recalcNetto(){
			$this->netto = $this->brutto / (1 + $this->taxValue);
			return $this;
		}
		
		/**
		 * Recalculates the brutto value based on the netto and tax value
		 * @return Entry Returns a reference to this instance of Entry
		 */
		public function recalcBrutto(){
			$this->brutto = $this->netto * (1 + $this->taxValue);
			return $this;
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setBrutto($value, $recalcNetto = false) { $this->brutto = $brutto; return (recalcNetto)?$this->recalcNetto():$this; }
		public function setNetto($value, $recalcBrutto = false) { $this->netto = $netto; return (recalcBrutto)?$this->recalcBrutto():$this; }
		public function setTaxType($type) { $this->taxType = $type; return $this; }
		public function setTaxValue($value) { $this->taxValue = $type; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setDate($date) { $this->date = $date; return $this; }
		public function setState($state) { $this->state = $state; return $this; }
		
		
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
			return $this->ownerUser;
		}
		public function getOwnerId(){ return $this->ownerId; }
		public function getBrutto() { return $this->brutto; }
		public function getNetto() { return $this->netto; }
		public function getTaxType() { return $this->taxType; }
		public function getTaxValue() { return $this->taxValue; }
		public function getNotes() { return $this->notes; }
		/**
		 * @return DateTime
		 */
		public function getDate() { return $this->date; }
		public function getState() { return $this->state; }
	
	}
?>