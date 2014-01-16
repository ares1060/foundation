<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Attachment extends core\BaseModel {
	
		private $id;
		private $param;
		private $service;
		/**
		 * @var DateTime
		 */
		private $date;
		private $file;
		private $fileType;
	
		public function __construct($service='', $param = '', $date = null, $file = '', $fileType = '') {
			$this->service = $service;
			$this->param = $param;
			$this->date = (!$date)?new DateTime():$date;
			$this->file = $file;
			$this->fileType = $fileType;
			
            parent::__construct(ServiceProvider::get()->db->prefix.'attachments', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		
        /**
         * @param int $attachmentId
         * @return Attachment The Attachment for the given id
         */
        public static function getAttachment($attachmentId) {
        	$a = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'attachments WHERE `id` = \''.ServiceProvider::getInstance()->db->escape($attachmentId).'\';');
        	if($a) {
        		$ao = new Attachment($a['service'], $a['param'], $a['date'], $a['file'], $a['file_type']);
        		$ao->setId($a['id']);
        		return $ao;
        	}
        	return null;
        }
        
       	/**
       	 * Fetches all Attachments matching the service and param
       	 * @param string $service
       	 * @param string $param
       	 */
		public static function getAttachments($service = '', $param = ''){
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'attachments WHERE service = \''.ServiceProvider::getInstance()->db->escape($service).'\' AND param = \''.ServiceProvider::getInstance()->db->escape($param).'\';');
			$out = array();
			foreach($result as $a) {
				$ao = new Attachment($a['service'], $a['param'], $a['date'], $a['file'], $a['file_type']);
				$ao->setId($a['id']);
				$out[] = $ao;
			}
			return $out;
		}
		
		public static function getAttachmentCount($service = '', $param = ''){
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT COUNT(*) as count FROM '.ServiceProvider::getInstance()->db->prefix.'attachments WHERE service = \''.ServiceProvider::getInstance()->db->escape($param).'\' AND param = \''.ServiceProvider::getInstance()->db->escape($param).'\';');
			if(isset($result['count'])) return $result['count'];
			else return 0;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'attachments 
								(`service`, `param`, `date`, `file`, `file_type`) VALUES 
								(
									\''.$this->sp->db->escape($this->service).'\',
									\''.$this->sp->db->escape($this->param).'\',
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
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'attachments SET
						`service` = \''.$this->sp->db->escape($this->service).'\', 
						`param` = \''.$this->sp->db->escape($this->param).'\', 
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
			return $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'attachments WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setService($service) { $this->service = $service; return $this; }
		public function setParam($param) { $this->param = $param; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setDate($date) { $this->date = $date; return $this; }
		public function setFile($file) { $this->file = $file; return $this; }
		public function setFileType($fileType) { $this->fileType = $fileType; return $this; }

	
		public function getId(){ return $this->id; }
		public function getService(){ return $this->service; }
		public function getParam(){ return $this->param; }
		/**
		 * @return DateTime
		 */
		public function getDate() { return $this->date; }
		public function getFile() { return $this->file; }
		public function getFileType() { return $this->fileType; }

	}
?>