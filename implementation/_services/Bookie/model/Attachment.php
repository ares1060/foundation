<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Attachment extends core\BaseModel {
	
		private $id;
		private $entryId;
		private $entry;
		/**
		 * @var DateTime
		 */
		private $date;
		private $file;
		private $fileType;
	
		public function __construct($entryId = -1, $date = null, $file = '', $fileType = '') {
			
			$this->entryId = $entryId;
			$this->date = (!$date)?new DateTime():$date;
			$this->file = $file;
			$this->fileType = $fileType;
			
            parent::__construct(ServiceProvider::get()->db->prefix.'bookie_receipts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		
        /**
         * @param int $attachmentId
         * @return Attachment The Attachment for the given id
         */
        public static function getAttachment($attachmentId) {
        	$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_attachments WHERE `id` = \''.ServiceProvider::getInstance()->db->escape($attachmentId).'\';');
        	if($a) {
        		$ao = new Attachment($a['entry_id'], $a['date'], $a['file'], $a['file_type']);
        		$ao->setId($a['id']);
        		return $ao;
        	}
        	return null;
        }
        
       	/**
       	 * Fetches all Attachments matching the parameters
       	 * @param string $insertSQL
       	 * @param int $from
       	 * @param int $rows
       	 */
		public static function getAttachments($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_attachmetns '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $a) {
				$ao = new Attachment($a['entry_id'], $a['date'], $a['file'], $a['file_type']);
				$ao->setId($a['id']);
				$out[] = $ao;
			}
			return $out;
		}
		
		public static function getAttachmentsForEntry($entryId) {
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_attachments WHERE `entry_id` = \''.ServiceProvider::getInstance()->db->escape($entryId).'\';');
			$out = array();
			foreach($result as $a) {
				$ao = new Attachment($a['entry_id'], $a['date'], $a['file'], $a['file_type']);
				$ao->setId($a['id']);
				$out[] = $ao;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_attachments 
								(`entry_id`, `date`, `file`, `file_type`) VALUES 
								(
									\''.$this->sp->db->escape($this->entryId).'\',
									\''.$this->sp->db->escape($this->date->format('Y-m-d')).'\',
									\''.$this->sp->db->escape($this->file).'\',
									\''.$this->sp->db->escape($this->fileType).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_attachments SET
						`entry_id` = \''.$this->sp->db->escape($this->entryId).'\', 
						`date` = \''.$this->sp->db->escape($this->date->format('Y-m-d')).'\', 
						`file` = \''.$this->sp->db->escape($this->file).'\',
						`file_type` = \''.$this->sp->db->escape($this->fileType).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the receipt data item from the database
		 */
		public function delete(){
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'bookie_attachments WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
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
		public function setFile($file) { $this->file = $file; return $this; }
		public function setFileType($fileType) { $this->fileType = $fileType; return $this; }

	
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
		public function getFile() { return $this->file; }
		public function getFileType() { return $this->fileType; }

	}
?>