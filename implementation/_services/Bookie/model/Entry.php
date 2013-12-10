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
		private $date;
		private $state;
	
		public function __construct($owner = -1, $brutto = 0, $netto = 0, $taxType = '', $taxValue = 0, $date = '', $notes = '') {
            $this->ownerId = $owner;
			$this->brutto = $brutto;
			$this->netto = $netto;
			$this->taxType = $taxType;
			$this->taxValue = $taxValue;
			$this->date = $date;
			$this->notes = $notes;
			parent::__construct(ServiceProvider::get()->db->prefix.'bookie_entries', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
		public static function getEntries() {
		
		}
		
		public static function getEntry($entryId) {
		
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
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_entries SET
						
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
		 * Recalculates the netto value based on the brutto and tax value
		 * @return Entry Returns a reference to this instance of Entry
		 */
		public function recalcNetto(){
			$this->netto = $this->brutto / (1 + $this->taxValue)
			return this;
		}
		
		/**
		 * Recalculates the brutto value based on the netto and tax value
		 * @return Entry Returns a reference to this instance of Entry
		 */
		public function recalcBrutto(){
			$this->brutto = $this->netto * (1 + $this->taxValue);
			return this;
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
		public function getDate() { return $this->date; }
		public function getState() { return $this->state; }
	
	}
?>