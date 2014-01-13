<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	require_once $GLOBALS['config']['root'].'_services/Bookie/model/InvoicePart.php';
	
	class Invoice extends core\BaseModel {
	
		private $id;
		private $entryId;
		private $entry;
		private $altSrcAddress;
		private $altDstAddress;
		private $number;
		/**
		 * @var DateTime
		 */
		private $payDate;
		/**
		* @var DateTime
		*/
		private $reminderDate;
		private $dunnings;
	
		/**
		 * @param int $entryId
		 * @param string $altSrcAddress
		 * @param string $altDstAddress
		 * @param string $number
		 * @param DateTime $payDate
		 * @param DateTime $reminderDate
		 * @param string $dunnings
		 */
		public function __construct($entryId = -1, $altSrcAddress = '', $altDstAddress = '', $number = '', $payDate = null, $reminderDate = null, $dunnings = '') {
            
			$this->entryId = $entryId;
			$this->altSrcAddress = $altSrcAddress;
			$this->altDstAddress = $altDstAddress;
			$this->number = $number;
			$this->payDate = (!$payDate)?new DateTime():$payDate;
			$this->reminderDate = (!$reminderDate)?null:$reminderDate;
			$this->dunnings = $dunnings;
			
			parent::__construct(ServiceProvider::get()->db->prefix.'bookie_invoices', array());
        }
		
		/**
		 * STATIC METHODS
		 */
	
		/**
		 * Fetches an array of invoices matching the given parameters
		 * @param $insertSQL additional SQL code to be inserted between select from and limit;
		 * @param $from The row from withc to select 
		 * @param $rows The number of rows to select
		 * @return Invoice[] An array containing the resulting Invoices
		 */
		public static function getInvoices($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_invoices '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $invoice) {
				$ivo = new Invoice($invoice['entry_id'], $invoice['alt_src_adr'], $invoice['alt_dst_adr'], $invoice['number'], new DateTime($invoice['pay_date']), ($invoice['reminder_date'] == '0000-00-00 00:00:00')?null:new DateTime($invoice['reminder_date']), $invoice['dunnings']);
				$ivo->setId($invoice['id']);
				$out[] = $ivo;
			}
			return $out;
		}
		
		/**
		 * Fetches all invoices for a given Bookie Entry id
		 * @param int $entryId The id of the linked Bookie Entry
		 * @return Invoice[]
		 */
		public static function getInvoicesForEntry($entryId) {
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_invoices WHERE `entry_id` = \''.ServiceProvider::getInstance()->db->escape($entryId).'\';');
			$out = array();
			foreach($result as $invoice) {
				$ivo = new Invoice($invoice['entry_id'], $invoice['alt_src_adr'], $invoice['alt_dst_adr'], $invoice['number'], new DateTime($invoice['pay_date']), ($invoice['reminder_date'] == '0000-00-00 00:00:00')?null:new DateTime($invoice['reminder_date']), $invoice['dunnings']);
				$ivo->setId($invoice['id']);
				$out[] = $ivo;
			}
			return $out;
		}
		
		/**
		 * Fetches the Invoice with the given id from the database
		 * @param int $invoiceId The id of the Invoice
		 * @return Invoice|NULL
		 */
		public static function getInvoice($invoiceId) {
			$invoice = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_invoices WHERE id =\''.ServiceProvider::getInstance()->db->escape($invoiceId).'\';');
			if($invoice){
				$ivo = new Invoice($invoice['entry_id'], $invoice['alt_src_adr'], $invoice['alt_dst_adr'], $invoice['number'], new DateTime($invoice['pay_date']), ($invoice['reminder_date'] == '0000-00-00 00:00:00')?null:new DateTime($invoice['reminder_date']), $invoice['dunnings']);
				$ivo->setId($invoice['id']);
				return $ivo;
			} else {
				return null;
			}
		}
		
		/**
		 * Counts the number of invoices between the given dates
		 * @param DateTime $fromDate
		 * @param DateTime $toDate
		 */
		public static function getInvoiceCount($fromDate, $toDate) {
			$count = ServiceProvider::getInstance()->db->fetchRow('SELECT COUNT(*) AS count FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_invoices AS i LEFT JOIN '.ServiceProvider::getInstance()->db->prefix.'bookie_entries AS e ON e.id = i.entry_id WHERE e.date >= \''.ServiceProvider::getInstance()->db->escape($fromDate->format('Y-m-d')).'\' AND e.date <= \''.ServiceProvider::getInstance()->db->escape($toDate->format('Y-m-d')).'\';');
			if($count && isset($count['count'])){
				return $count['count'];
			} else {
				return 0;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_invoices 
								(`entry_id`, `alt_dst_adr`, `alt_src_adr`, `number`, `pay_date`, `reminder_date`, `dunnings`) VALUES 
								(
									\''.$this->sp->db->escape($this->entryId).'\',
									\''.$this->sp->db->escape($this->altDstAddress).'\',
									\''.$this->sp->db->escape($this->altSrcAddress).'\',
									\''.$this->sp->db->escape($this->number).'\',
									\''.$this->sp->db->escape($this->payDate->format('Y-m-d')).'\',
									\''.$this->sp->db->escape(($this->reminderDate)?$this->reminderDate->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
									\''.$this->sp->db->escape($this->dunnings).'\'
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
						`entry_id` = \''.$this->sp->db->escape($this->entryId).'\',
						`alt_dst_adr` = \''.$this->sp->db->escape($this->altDstAddress).'\',
						`alt_src_adr` = \''.$this->sp->db->escape($this->altSrcAddress).'\',
						`number` = \''.$this->sp->db->escape($this->number).'\',
						`pay_date` = \''.$this->sp->db->escape($this->payDate->format('Y-m-d')).'\',
						`reminder_date` = \''.$this->sp->db->escape(($this->reminderDate)?$this->reminderDate->format('Y-m-d H:i:s'):'0000-00-00 00:00:00').'\',
						`dunnings` = \''.$this->sp->db->escape($this->dunnings).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the invoice data item from the database
		 */
		public function delete(){
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'bookie_invoices WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
		}
		 
		/**
		 * Adds a dunning to the list of dunnings as a date Y-m-d
		 * @param DateTime $date
		 */
		public function addDunning($date=null){
			if(!$date) $date = new DateTime();
			if($this->dunnings != '') $this->dunnings .= ',';
			$this->dunnings .= $date->format('Y-m-d');
		}
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setEntry($entryId) { $this->entryId = $entryId; $this->entry = null; return $this; }
		public function setAltSrcAddress($address) { $this->altSrcAddress = $address; return $this; }
		public function setAltDstAddress($address) { $this->altDstAddress = $address; return $this; }
		public function setNumber($number) { $this->number = $number; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setPayDate($date) { $this->date = $date; return $this; }
		/**
		* @param DateTime $date
		*/
		public function setReminderDate($date) { $this->reminderDate = $date; return $this; }
		public function setDunnings($dunnings) { $this->dunnings = $dunnings; return $this; }
		
		public function getId(){ return $this->id; }
		/**
		 * @return Entry
		 */
		public function getEntry(){ 
			if($this->entry == null) $this->entry = Entry::getEntry($this->entryId);
			return $this->entry;
		}
		public function getEntryId(){ return $this->entryId; }
		
		public function getAltSrcAddress(){ return $this->altSrcAddress; }
		public function getAltDstAddress(){ return $this->altDstAddress; }
		public function getNumber(){ return $this->number; }
		/**
		 * @return DateTime
		 */
		public function getPayDate(){ return $this->payDate; }
		/**
		* @return DateTime
		*/
		public function getReminderDate(){ return $this->reminderDate; }
		public function getDunnings(){ return $this->dunnings; }
		public function getDunningCount(){ return count(explode(',', $this->dunnings)); }
	}
?>