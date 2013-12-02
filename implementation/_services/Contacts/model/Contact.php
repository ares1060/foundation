<?php

	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	require_once $GLOBALS['config']['root'].'_services/Contacts/model/ContactData.php';
	require_once $GLOBALS['config']['root'].'_services/Contacts/model/ContactDataItem.php';
	
	class Contact extends core\BaseModel {
	
        private $id;
		private $ownerId;
		private $ownerUser;
		private $firstName;
        private $lastName;
		private $address;
		private $postCode;
		private $city;
		private $email;
		private $phone;
		private $notes;
		private $socialSecurityNumber;
		private $lastContact;
		 
        private $contactData;
			
        public function __construct($owner  = -1, $firstName = '', $lastName = '', $address = '', $postCode = '', $city = '', $email = '', $phone = '', $notes = '', $socialSecurityNumber = '') {
			$this->ownerId = $owner;
			$this->firstName = $firstName;
			$this->lastName = $lastName;
			$this->address = $address;
			$this->postCode = $postCode;
			$this->city = $city;
			$this->email = $email;
			$this->phone = $phone;
			$this->notes = $notes;
			$this->socialSecurityNumber = $socialSecurityNumber;
			$this->lastContact = '';
            parent::__construct(ServiceProvider::get()->db->prefix.'contact', array());
        }
		
		/**
		 * STATIC FUNCTIONS
		 */
		
		/**
		 * Fetches the contact with the given id
		 * @param int $id The ID of the contact
		 */
		public static function getContact($id){
			$contacts = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'contacts '.$whereSQL.';');

		}
		
		/**
		 * Fetches all contacts matching the given SQL where statement
		 * @param string $whereSQL An optional SQL WHERE statement.
		 * @return Contact[] An array containing all fetched Contacts
		 */
		public static function getContacts($whereSQL = ''){
			if($whereSQL != '' && strpos($whereSQL, 'WHERE') === FALSE) $whereSQL = 'WHERE '.$whereSQL;
			$contacts = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'contacts '.$whereSQL.';');
			$out = array();
			foreach($contacts as $contact) {
				$coo = new Contact($contact['user_id'], $contact['firstname'], $contact['lastname'], $contact['address'], $contact['pc'], $contact['city'], $contact['email'], $contact['phone'], $contact['notes'], $contact['ssnum']);
				$coo->setId($contact['id']);
				$out[] = $coo;
			}
			return $out;
		}
		
		/**
		 * INSTANCE FUNCTIONS
		 */
		
		/**
		 * Overriding the BaseModel save to do proper save
		 */
		public function save(){
			if($this->id == ''){
				//insert
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'contacts 
								(`user_id`, `firstname`, `lastname`, `address`, `pc`, `city`, `email`, `phone`, `notes`, `last_contact`, `ssnum`) VALUES 
								(\''.ServiceProvider::get()->db->escape($this->ownerId).'\', 
									\''.ServiceProvider::get()->db->escape($this->firstName).'\', 
									\''.ServiceProvider::get()->db->escape($this->lastName).'\',
									\''.ServiceProvider::get()->db->escape($this->address).'\',
									\''.ServiceProvider::get()->db->escape($this->postalCode).'\',
									\''.ServiceProvider::get()->db->escape($this->city).'\',
									\''.ServiceProvider::get()->db->escape($this->email).'\',
									\''.ServiceProvider::get()->db->escape($this->phone).'\',
									\''.ServiceProvider::get()->db->escape($this->notes).'\',
									\''.ServiceProvider::get()->db->escape($this->lastContact).'\',
									\''.ServiceProvider::get()->db->escape($this->ssnum).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'contacts SET
						user_id= \''.ServiceProvider::get()->db->escape($this->ownerId).'\',
						firstname = \''.ServiceProvider::get()->db->escape($this->firstName).'\',
						lastname = \''.ServiceProvider::get()->db->escape($this->lastName).'\',
						address = \''.ServiceProvider::get()->db->escape($this->address).'\',
						pc = \''.ServiceProvider::get()->db->escape($this->postalCode).'\',
						city = \''.ServiceProvider::get()->db->escape($this->city).'\',
						email = \''.ServiceProvider::get()->db->escape($this->email).'\',
						phone = \''.ServiceProvider::get()->db->escape($this->phone).'\',
						notes = \''.ServiceProvider::get()->db->escape($this->notes).'\',
						last_contact = \''.ServiceProvider::get()->db->escape($this->lastContact).'\',
						ssnum = \''.ServiceProvider::get()->db->escape($this->socialSecurityNumber).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contacts WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
			return $ok;
		}
		
		/**
		 * GETTER & SETTER
		 */ 
		
		private function setId($id) { $this->id = $id; return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setFirstName($firstName) { $this->firstName = $firstName; return $this; }
		public function setLastName($lastName) { $this->lastName = $lastName; return $this; }
		public function setAddress($address) { $this->address = $address; return $this; }
		public function setPostCode($postCode) { $this->postCode = $postCode; return $this; }
		public function setCity($city) { $this->city = $city; return $this; }
		public function setEmail($email) { $this->email = $email; return $this; }
		public function setPhone($phone) { $this->phone = $phone; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		public function setSocialSecurityNumber($socialSecurityNumber) { $this->socialSecurityNumber = $socialSecurityNumber; return $this; }
		public function setLastContact($lastContact) { $this->lastContact = $lastContact; return $this; }
		
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser();
			return $this->ownerUser;
		}
		public function getOwnerId(){ return $this->ownerId; }
		public function getFirstName(){ return $this->firstName; }
		public function getLastName(){ return $this->lastName; }
		public function getAddress(){ return $this->address; }
		public function getPostCode(){ return $this->postCode; }
		public function getCity(){ return $this->city; }
		public function getEmail(){ return $this->email; }
		public function getPhone(){ return $this->phone; }
		public function getNotes(){ return $this->notes; }
		public function getSocialSecurityNumber(){ return $this->socialSecurityNumber; }
		public function getLastContact(){ return $this->lastContact; }
		public function getContactData(){ return $this->socialSecurityNumber; }
		
	
	}
	
?>