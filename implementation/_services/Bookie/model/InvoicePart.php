<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class InvoicePart extends core\BaseModel {
	
		private $id;
		private $invoiceId;
		private $invoice;
		private $date;
		private $notes;
		private $value;
	
		public function __construct() {
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_invoice_parts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
	
		public static function getPartsForInvoice($invoiceId) {
			
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_invoice_parts 
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
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_invoice_parts SET
						
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
		public function setInvoice($invoiceId) { $this->invoiceId = $invoiceId; $this->invoice = null; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		public function setDate($date) { $this->date = $date; return $this; }
		public function setValue($value) { $this->value = $value; return $this; }
	
		public function getId(){ return $this->id; }
		/**
		 * @return Invoice
		 */
		public function getInvoice(){ 
			if(!$this->invoice == null) $this->invoice = Invoice::getInvoice($this->invoiceId);
			return $this->invoice;
		}
		public function getInvoiceId(){ return $this->invoiceId; }
		public function getNotes() { return $this->notes; }
		public function getDate() { return $this->date; }
		public function getValue() { return $this->value; }
	}
?>