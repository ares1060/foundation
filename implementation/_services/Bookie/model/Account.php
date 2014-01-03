<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Account extends core\BaseModel {
		
		private $id;
		private $ownerUser;
		private $ownerId;
		private $notes;
		private $name;
	
		public function __construct($owner = -1, $notes = '', $name = '') {
            $this->ownerId = $owner;
			$this->notes = $notes;
			$this->name = $name;
			parent::__construct(ServiceProvider::get()->db->prefix.'bookie_accounts', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
        /**
         * Fetches all matching Accounts from the database and returns them in an array
         * @param string $insertSQL
         * @param int $from
         * @param int $rows
         * @return Account[]
         */
		public static function getAccounts($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).','.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT *, a.id as id FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_accounts AS a '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $acc) {
				$ao = new Account($acc['user_id'], $acc['notes'], $entry['name']);
				$ao->setId($acc['id']);
				$out[] = $ao;
			}
			return $out;
		}
		
		/**
		 * Fetches the Account with the given ID from the database
		 * @param int $accountId The id of the Account to be fetched
		 * @return Account|NULL
		 */
		public static function getAccount($accountId) {
			$acc = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_accounts WHERE id =\''.ServiceProvider::getInstance()->db->escape($accountId).'\';');
			if($acc){
				$ao = new Account($acc['user_id'], $acc['notes'], $entry['name']);
				$ao->setId($acc['id']);
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_accounts
								(`user_id`, `notes`, `name`) VALUES 
								(
									\''.$this->sp->db->escape($this->ownerId).'\', 
									\''.$this->sp->db->escape($this->notes).'\', 
									\''.$this->sp->db->escape($this->name).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_accounts SET
						`user_id` = \''.$this->sp->db->escape($this->ownerId).'\', 
						`notes` = \''.$this->sp->db->escape($this->notes).'\', 
						`name` = \''.$this->sp->db->escape($this->name).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the entry data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.$this->sp->db->prefix.'bookie_accounts WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			return $ok;
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		public function setName($name) { $this->name = $name; return $this; }
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
			return $this->ownerUser;
		}
		public function getOwnerId(){ return $this->ownerId; }
		public function getNotes() { return $this->notes; }
		public function getName() { return $this->name; }
		
	}
?>