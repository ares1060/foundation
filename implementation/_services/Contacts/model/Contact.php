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
		private $birthdate;
		
		/**
		 * @var DateTime
		 */
		private $lastContact;
		private $image;
		 
        private $contactData;
			
        public function __construct($owner  = -1, $firstName = '', $lastName = '', $address = '', $postCode = '', $city = '', $email = '', $phone = '', $notes = '', $socialSecurityNumber = '', $lastContact = null, $image = '', $birthdate = null) {
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
			$this->lastContact = (!$lastContact)?new DateTime('0000-00-00 00:00:00'):$lastContact;
			$this->birthdate = (!$birthdate)?new DateTime('0000-00-00'):$birthdate;
			$this->image = $image;
            parent::__construct(ServiceProvider::get()->db->prefix.'contact', array());
        }
		
		/**
		 * STATIC FUNCTIONS
		 */
		
		/**
		 * Fetches the contact with the given id
		 * @param int $id The ID of the contact
		 * @return Contact
		 */
		public static function getContact($id){
			$contact = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'contacts WHERE id =\''.ServiceProvider::getInstance()->db->escape($id).'\';');
			if($contact){
				$coo = new Contact($contact['user_id'], $contact['firstname'], $contact['lastname'], $contact['address'], $contact['pc'], $contact['city'], $contact['email'], $contact['phone'], $contact['notes'], $contact['ssnum'], new DateTime($contact['last_contact']), $contact['image'], new DateTime($contact['birthdate']));
				$coo->setId($contact['id']);
				return $coo;
			} else {
				return null;
			}
		}
		
		/**
		 * Fetches all contacts matching the given SQL where statement
		 * @param string $insertSQL An optional SQL string which inserted between the select and the order statement.
		 * @return Contact[] An array containing all fetched Contacts
		 */
		public static function getContacts($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).', '.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$contacts = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'contacts '.$insertSQL.' ORDER BY lastname ASC '.$limit.';');
			$out = array();
			foreach($contacts as $contact) {
				$coo = new Contact($contact['user_id'], $contact['firstname'], $contact['lastname'], $contact['address'], $contact['pc'], $contact['city'], $contact['email'], $contact['phone'], $contact['notes'], $contact['ssnum'], new DateTime($contact['last_contact']), $contact['image'], new DateTime($contact['birthdate']));
				$coo->setId($contact['id']);
				$out[] = $coo;
			}
			return $out;
		}
		
		public static function getLinkedContacts($linkTable, $entryId = -1, $onlyIds = false){
			if($entryId >= 0) $where = ' WHERE lt.entry_id = '.ServiceProvider::getInstance()->db->escape($entryId);
			else $where = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.ServiceProvider::getInstance()->db->escape($linkTable).' AS lt LEFT JOIN '.ServiceProvider::getInstance()->db->prefix.'contacts AS c ON c.id = lt.contact_id '.$where.';');
			$out = array();
			foreach($result as $entry) {
				if($onlyIds) $out[] = $entry['contact_id'];
				else {
					$coo = new Contact($entry['user_id'], $entry['firstname'], $entry['lastname'], $entry['address'], $entry['pc'], $entry['city'], $entry['email'], $entry['phone'], $entry['notes'], $entry['ssnum'], new DateTime($entry['last_contact']), $entry['image'], new DateTime($entry['birthdate']));
					$coo->setId($entry['contact_id']);
					$out[] = $coo;
				}
			}
			return $out;
		}
		
		/**
		* Fetches the number of available contacts
		* @return int
		*/
		public static function getContactCount($insertSQL = ''){
			$count = ServiceProvider::getInstance()->db->fetchRow('SELECT COUNT(*) as count FROM '.ServiceProvider::getInstance()->db->prefix.'contacts '.$insertSQL.';');
			if($count && isset($count['count'])){
				return $count['count'];
			} else {
				return 0;
			}
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
								(`user_id`, `firstname`, `lastname`, `address`, `pc`, `city`, `email`, `phone`, `notes`, `last_contact`, `ssnum`, `image`, `birthdate`) VALUES 
								(\''.ServiceProvider::get()->db->escape($this->ownerId).'\', 
									\''.ServiceProvider::get()->db->escape($this->firstName).'\', 
									\''.ServiceProvider::get()->db->escape($this->lastName).'\',
									\''.ServiceProvider::get()->db->escape($this->address).'\',
									\''.ServiceProvider::get()->db->escape($this->postCode).'\',
									\''.ServiceProvider::get()->db->escape($this->city).'\',
									\''.ServiceProvider::get()->db->escape($this->email).'\',
									\''.ServiceProvider::get()->db->escape($this->phone).'\',
									\''.ServiceProvider::get()->db->escape($this->notes).'\',
									\''.ServiceProvider::get()->db->escape($this->lastContact->format('Y-m-d h:i:s')).'\',
									\''.ServiceProvider::get()->db->escape($this->socialSecurityNumber).'\',
									\''.ServiceProvider::get()->db->escape($this->image).'\',
									\''.ServiceProvider::get()->db->escape($this->birthdate->format('Y-m-d')).'\'
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
						pc = \''.ServiceProvider::get()->db->escape($this->postCode).'\',
						city = \''.ServiceProvider::get()->db->escape($this->city).'\',
						email = \''.ServiceProvider::get()->db->escape($this->email).'\',
						phone = \''.ServiceProvider::get()->db->escape($this->phone).'\',
						notes = \''.ServiceProvider::get()->db->escape($this->notes).'\',
						last_contact = \''.ServiceProvider::get()->db->escape($this->lastContact->format('Y-m-d h:i:s')).'\',
						ssnum = \''.ServiceProvider::get()->db->escape($this->socialSecurityNumber).'\',
						image = \''.ServiceProvider::get()->db->escape($this->image).'\',
						birthdate = \''.ServiceProvider::get()->db->escape($this->birthdate->format('Y-m-d')).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.ServiceProvider::get()->db->prefix.'contacts WHERE id=\''.ServiceProvider::get()->db->escape($this->id).'\';');
			if($ok) $this->getContactData()->delete();
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
		/**
		 * @param DateTime $lastContact
		 */
		public function setLastContact($lastContact) { $this->lastContact = $lastContact; return $this; }
		public function setImage($image) { $this->image = $image; return $this; }
		public function setBirthdate($birthdate) { $this->birthdate = $birthdate; return $this; }
		
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
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
		/**
		 * @return DateTime
		 */
		public function getLastContact(){ return $this->lastContact; }
		/**
		 * @return ContactData
		 */
		public function getContactData(){ 
			if($this->contactData == null) $this->contactData = ContactData::getDataForContact($this->id);
			return $this->contactData;
		}
		public function getImage(){ return $this->image; }
		/**
		* @return DateTime
		*/
		public function getBirthdate(){
			return $this->birthdate;
		}
		
	
	}
	
?>