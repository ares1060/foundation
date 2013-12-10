<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	require_once $GLOBALS['config']['root'].'_services/Contacts/model/InvoicePart.php';
	
	class Invoice extends core\BaseModel {
	
		private $id;
		private $entryId;
		private $entry;
		private $contactId;
		private $contact;
		private $altSrcAddress;
		private $altDstAddress;
		private $number;
		private $payDate;
		private $reminderDate;
		private $dunnings;
	
		public function __construct() {
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_invoices', array());
        }
		
		/**
		 * STATIC METHODS
		 */
	
		public static function getInvoices() {
		
		}
		
		public static function getInvoicesForEntry($entryId) {
		
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_invoices 
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
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_invoices SET
						
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
		public function setContact($contactId) { $this->contactId = $contactId; $this->contact = null; return $this; }
		public function setAltSrcAddress($address) { $this->altSrcAddress = $adress; return $this; }
		public function setAltDstAddress($address) { $this->altDstAddress = $adress; return $this; }
		public function setNumber($numer) { $this->number = $number; return $this; }
		public function setPayDate($date) { $this->date = $date; return $this; }
		public function setReminderDate($date) { $this->reminderDate = $date; return $this; }
		public function setDunnings($dunnings) { $this->dunnings = $dunnings; return $this; }
		
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
		 * @return Contact
		 */
		public function getContact(){ 
			if(!$this->contact == null) $this->contact = Contact::getContact($this->contactId);
			return $this->contact;
		}
		public function getContactId(){ return $this->contactId; }
		public function getAltSrcAddress(){ return $this->altSrcAddress; }
		public function getAltDstAddress(){ return $this->dstSrcAddress; }
		public function getNumber(){ return $this->number; }
		public function getPayDate(){ return $this->payDate; }
		public function getReminderDate(){ return $this->reminderDate; }
		public function getDunnings(){ return $this->dunnings; }
	}
?>