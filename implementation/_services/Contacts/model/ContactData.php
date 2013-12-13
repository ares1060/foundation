<?php

	use at\foundation\core;
	use at\foundation\core\ServiceProvider;

	
	class ContactData extends core\BaseModel {
		private $id;
		private $contactId;
		private $contact;
		private $data;
		private $keys;
	   
		function __construct($contactId){
			$this->contactId = $contactId;
			
			$qry = ServiceProvider::get()->db->fetchAll('SELECT * FROM `'.ServiceProvider::get()->db->prefix.'contactdata` AS cd 
															WHERE cd.contact_id = \''.ServiceProvider::get()->db->escape($contactId).'\';');
															
			$this->data = array();
			$this->keys = array();
			if($qry != array()) {
				foreach($qry as $d){
					$di = new ContactDataItem($d['contact_id'], $d['key'], $d['value']);
					$di->__setId($d['id']);
					$this->data[$d['key']] = $di;
					$this->keys[] = $d['key'];
				}
			}		
			
			parent::__construct('', array());
		}
		
	/* STATIC HELPER */
	
		/**
		 * returns the ContactData for the given Contact
		 * @param $id
		 * @return ContactData
		 */
		public static function getDataForContact($id){
			return new ContactData($id);
		}
	
		/**
		 *	Deletes all ContactData entries for the given contact id
		 * @return boolean
		 */
		public static function deleteDataForContact($id) {
			return ServiceProvider::get()->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contactdata WHERE contact_id=\''.ServiceProvider::get()->db->escape($id).'\';');
		}
	
	/* INSTANCE FUNCTIONS */
		
		/**
		 * Returns the ContactDataItem for the given key
		 * @param string $key The key name of the data item
		 * @param bool $create If true an empty ContactDataItem is created if it doesn't already exist.
		 * @return ContactDataItem The resulting contact data item or null
		 */
		public function get($key, $create=false){
			if(isset($this->data[$key])) return $this->data[$key];
			else if($create){
				$cdi = new ContactDataItem($this->contactId, $key, '');
				$this->data[$key] = $cdi;
				$this->keys[] = $key;
				return $cdi;
			} else {
				return null;
			}
		}
		
		/**
		 * Returns the ContactDataItem for the given name
		 * @see get()
		 * @param string $key The name of the corresponding ContactDataField
		 * @param object $default The default value of the field which is returned if it doesn't exist
		 * @param bool $create If true a ContactDataItem containing the value given in $default is created if it doesn't already exist.
		 * @return ContactDataItem The resulting user data item or null
		 */
		public function opt($key, $default='', $create=false){
			if(isset($this->data[$key])) return $this->data[$key];
			else if($create) {
				$cdi = new ContactDataItem($this->contactId, $key, $default);
				$this->data[$key] = $cdi;
				$this->keys[] = $key;
				return $cdi;
			} else {
				return null;
			}
		}
		
		/**
		 * Deletes the ContactDataItem with the given key
		 * @param string $key
		 */
		public function del($key){
			if(isset($this->data[$key])) {
				$ok = $this->data[$key]->delete();
				if($ok) {
					unset($this->data[$key]);
					unset($this->keys[array_search($key, $this->keys)]);
				}
				return $ok;
			}
			else return true;
		}
		
		/**
		 * Returns true if the contact has a ContactDataItem for the given key
		 * @param string $key The name of the corresponding UserDataField
		 * @return bool True if available
		 */
		public function has($key){
			return isset($this->data[$key]);
		}
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			//save all UserDataItems
			foreach($this->data as &$cdi) {
				$cdi->save();
			}
			unset($cdi);
		}
		
		/**
		 *	Deletes all the UserData
		 */
		public function delete(){
			$this->data = array();
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contactdata WHERE contact_id=\''.$this->sp->db->escape($this->contactId).'\';');
			return $ok;
		}
		
		//setter
		private function setId($Id) { $this->id = $id; return $this; }
		public function setContact($id) { $this->contactId = $id; $this->contact=null; return $this; }
		public function setContactId($id) { $this->contactId = $id; $this->contact=null; return $this; }
		public function setValue($value) { $this->value = $value; return $this; }
		
		// getter
		public function getId() { return $this->id; }
		public function getValue() { return $this->value; }
		public function getContact() { 
			if($this->contact == null) Contact::getContact($this->contactId);
			return $this->contact;
		}
		public function getContactId() { return $this->contactId; }
		public function getKeys() { return $this->keys; }
	}
?>