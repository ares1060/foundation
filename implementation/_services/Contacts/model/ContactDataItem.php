<?php

	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	
	class ContactDataItem extends core\BaseModel {
		private $id;
		private $contactId;
		private $contact;
		private $key;
		private $value;
		private $changed;
	   
		function __construct($contactId, $key, $value){
			$this->id = '';
			$this->contactId = $contactId;
			$this->key = $key;
			$this->value = $value;
			$this->changed = false;
			
			parent::__construct(ServiceProvider::get()->db->prefix.'contactdata', array());
		}
		
	/* STATIC HELPER */
	
		/**
		 *	Deletes all UserData entries for the given user id
		 * @return boolean
		 */
		public static function deleteDataForContact($id) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contactdata WHERE contact_id=\''.ServiceProvider::get()->db->escape($id).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'contactdata 
								(`contact_id`, `key`, `value`) VALUES 
								(\''.ServiceProvider::get()->db->escape($this->contactId).'\', 
									\''.ServiceProvider::get()->db->escape($this->key).'\', 
									\''.ServiceProvider::get()->db->escape($this->value).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else if($this->changed) {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'contactdata SET
						key = \''.ServiceProvider::get()->db->escape($this->fieldId).'\',
						contact_id = \''.ServiceProvider::get()->db->escape($this->userId).'\',
						value = \''.ServiceProvider::get()->db->escape($this->value).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contactdata WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
			return $ok;
		}
		
		//setter
		private function setId($id) { $this->id = $id; return $this; }
		public function __setId($id) { $this->id = $id; return $this; }
		public function setContact($id) { $this->contactId = $id; $this->contact=null; $this->changed = true; return $this; }
		public function setContactId($id) { $this->contactId = $id; $this->contact=null; $this->changed = true; return $this; }
		public function setKey($key) { $this->key = $key; $this->changed = true; return $this; }
		public function setValue($value) { $this->value = $value; $this->changed = true; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getValue() { return $this->value; }
		public function getContact() { 
			if($this->contact == null) Contact::getContact($this->contactId);
			return $this->contact;
		}
		public function getContactId() { return $this->contactId; }
		public function getKey() { return $this->key;}
	}
?>