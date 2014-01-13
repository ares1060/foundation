<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class InvoicePart extends core\BaseModel {
	
		private $id;
		private $invoiceId;
		private $invoice;
		/**
		 * @var DateTime
		 */
		private $date;
		private $notes;
		private $amount;
	
		public function __construct($invoiceId = -1, $date = null, $notes = '', $amount = '') {

			$this->id = '';
			$this->invoiceId = $invoiceId;
			$this->date = (!$date)?new DateTime():$date;
			$this->notes = $notes;
			$this->amount = $amount;
		
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_invoice_parts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
	
		/**
		 * Fetches all InvoiceParts for the given invoiceId and returns them in an array
		 * @param int $invoiceId The id of the Invoice
		 * @return InvoicePart[] An array containing all InvoiceParts for the given invoiceId
		 */
		public static function getPartsForInvoice($invoiceId) {
			$result = ServiceProvider::get()->db->fetchAll('SELECT * FROM `'.ServiceProvider::get()->db->prefix.'bookie_invoice_parts` WHERE `invoice_id` = \''.ServiceProvider::get()->db->escape($invoiceId).'\';');
			$out = array();
			foreach($result as $ip) {
				$ivp = new InvoicePart($ip['invoice_id'], new DateTime($ip['date']), $ip['notes'], $ip['amount']);
				$ivp->setId($ip['id']);
				$out[] = $ivp;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_invoice_parts 
								(`invoice_id`, `notes`, `amount`, `date`) VALUES 
								(
									\''.$this->sp->db->escape($this->invoiceId).'\',
									\''.$this->sp->db->escape($this->notes).'\',
									\''.$this->sp->db->escape($this->amount).'\',
									\''.$this->sp->db->escape($this->date->format('Y-m-d')).'\'
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
						`invoice_id` = \''.$this->sp->db->escape($this->invoiceId).'\',
						`notes` = \''.$this->sp->db->escape($this->notes).'\',
						`amount` = \''.$this->sp->db->escape($this->amount).'\',
						`date` = \''.$this->sp->db->escape($this->date->format('Y-m-d')).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the invoice part data item from the database
		 */
		public function delete(){
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'bookie_invoice_parts WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setInvoice($invoiceId) { $this->invoiceId = $invoiceId; $this->invoice = null; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setDate($date) { $this->date = $date; return $this; }
		public function setAmount($amount) { $this->amount = $amount; return $this; }
	
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
		/**
		 * @return DateTime
		 */
		public function getDate() { return $this->date; }
		public function getAmount() { return $this->amount; }
	}
?>