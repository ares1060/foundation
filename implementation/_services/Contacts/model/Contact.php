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
        private $title;
		private $address;
		private $postCode;
		private $city;
		private $country;
		private $email;
		private $phone;
		private $notes;
		private $company;
		private $uid;
		private $socialSecurityNumber;
		private $birthdate;
		
		/**
		 * @var DateTime
		 */
		private $lastContact;
		private $image;
		 
        private $contactData;
			
        public function __construct($owner  = -1, $firstName = '', $lastName = '', $title = '', $address = '', $postCode = '', $city = '', $country = '', $email = '', $phone = '', $notes = '', $socialSecurityNumber = '', $lastContact = null, $image = '', $birthdate = null, $company = '', $uid='') {
			$this->ownerId = $owner;
			$this->firstName = $firstName;
			$this->lastName = $lastName;
			$this->title = $title;
			$this->address = $address;
			$this->postCode = $postCode;
			$this->city = $city;
			$this->country = $country;
			$this->email = $email;
			$this->phone = $phone;
			$this->notes = $notes;
			$this->socialSecurityNumber = $socialSecurityNumber;
			$this->lastContact = $lastContact;
			$this->birthdate = $birthdate;
			$this->image = $image;
			$this->uid = $uid;
			$this->company = $company;
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
				$coo = new Contact($contact['user_id'], $contact['firstname'], $contact['lastname'], $contact['title'], $contact['address'], $contact['pc'], $contact['city'], $contact['country'], $contact['email'], $contact['phone'], $contact['notes'], $contact['ssnum'], ($contact['last_contact'] != '0000-00-00 00:00:00')?new DateTime($contact['last_contact']):null, $contact['image'], ($contact['birthdate'] != '0000-00-00')?new DateTime($contact['birthdate']):null, $contact['company'], $contact['uid']);
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
			$contacts = ServiceProvider::getInstance()->db->fetchAll('SELECT *, c.id AS id FROM '.ServiceProvider::getInstance()->db->prefix.'contacts AS c '.$insertSQL.' ORDER BY lastname ASC '.$limit.';');
			$out = array();
			foreach($contacts as $contact) {
				$coo = new Contact($contact['user_id'], $contact['firstname'], $contact['lastname'], $contact['title'], $contact['address'], $contact['pc'], $contact['city'], $contact['country'], $contact['email'], $contact['phone'], $contact['notes'], $contact['ssnum'], ($contact['last_contact'] != '0000-00-00 00:00:00')?new DateTime($contact['last_contact']):null, $contact['image'], ($contact['birthdate'] != '0000-00-00')?new DateTime($contact['birthdate']):null, $contact['company'], $contact['uid']);
				$coo->setId($contact['id']);
				$out[] = $coo;
			}
			return $out;
		}
		
		public static function getLinkedContacts($linkTable, $entryId = -1, $onlyIds = false){
			if($entryId >= 0) $where = ' WHERE lt.entry_id = '.ServiceProvider::getInstance()->db->escape($entryId);
			else $where = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.ServiceProvider::getInstance()->db->escape($linkTable).'_contacts AS lt LEFT JOIN '.ServiceProvider::getInstance()->db->prefix.'contacts AS c ON c.id = lt.contact_id '.$where.';');
			$out = array();
			foreach($result as $entry) {
				if($onlyIds) $out[] = $entry['contact_id'];
				else {
					$coo = new Contact($entry['user_id'], $entry['firstname'], $entry['lastname'], $entry['title'], $entry['address'], $entry['pc'], $entry['city'], $entry['country'], $entry['email'], $entry['phone'], $entry['notes'], $entry['ssnum'], ($entry['last_contact'] != '0000-00-00 00:00:00')?new DateTime($contact['last_contact']):null, $entry['image'], ($entry['birthdate'] != '0000-00-00')?new DateTime($entry['birthdate']):null, $entry['company'], $entry['uid']);
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
			$count = ServiceProvider::getInstance()->db->fetchRow('SELECT COUNT(*) as count FROM '.ServiceProvider::getInstance()->db->prefix.'contacts AS c '.$insertSQL.';');
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
								(`user_id`, `firstname`, `lastname`, `title`, `address`, `pc`, `city`, `country`, `email`, `phone`, `notes`, `last_contact`, `ssnum`, `image`, `birthdate`, `company`, `uid`) VALUES 
								(\''.ServiceProvider::get()->db->escape($this->ownerId).'\', 
									\''.ServiceProvider::get()->db->escape($this->firstName).'\', 
									\''.ServiceProvider::get()->db->escape($this->lastName).'\',
									\''.ServiceProvider::get()->db->escape($this->title).'\',
									\''.ServiceProvider::get()->db->escape($this->address).'\',
									\''.ServiceProvider::get()->db->escape($this->postCode).'\',
									\''.ServiceProvider::get()->db->escape($this->city).'\',
									\''.ServiceProvider::get()->db->escape($this->country).'\',
									\''.ServiceProvider::get()->db->escape($this->email).'\',
									\''.ServiceProvider::get()->db->escape($this->phone).'\',
									\''.ServiceProvider::get()->db->escape($this->notes).'\',
									\''.ServiceProvider::get()->db->escape((!$this->lastContact)?'0000-00-00 00:00:00':$this->lastContact->format('Y-m-d H:i:s')).'\',
									\''.ServiceProvider::get()->db->escape($this->socialSecurityNumber).'\',
									\''.ServiceProvider::get()->db->escape($this->image).'\',
									\''.ServiceProvider::get()->db->escape((!$this->birthdate)?'0000-00-00':$this->birthdate->format('Y-m-d')).'\',
									\''.ServiceProvider::get()->db->escape($this->company).'\',
									\''.ServiceProvider::get()->db->escape($this->uid).'\'
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
						title = \''.ServiceProvider::get()->db->escape($this->title).'\',
						address = \''.ServiceProvider::get()->db->escape($this->address).'\',
						pc = \''.ServiceProvider::get()->db->escape($this->postCode).'\',
						city = \''.ServiceProvider::get()->db->escape($this->city).'\',
						country = \''.ServiceProvider::get()->db->escape($this->country).'\',
						email = \''.ServiceProvider::get()->db->escape($this->email).'\',
						phone = \''.ServiceProvider::get()->db->escape($this->phone).'\',
						notes = \''.ServiceProvider::get()->db->escape($this->notes).'\',
						last_contact = \''.ServiceProvider::get()->db->escape((!$this->lastContact)?'0000-00-00 00:00:00':$this->lastContact->format('Y-m-d H:i:s')).'\',
						ssnum = \''.ServiceProvider::get()->db->escape($this->socialSecurityNumber).'\',
						image = \''.ServiceProvider::get()->db->escape($this->image).'\',
						company = \''.ServiceProvider::get()->db->escape($this->company).'\',
						uid = \''.ServiceProvider::get()->db->escape($this->uid).'\',
						birthdate = \''.ServiceProvider::get()->db->escape((!$this->birthdate)?'0000-00-00':$this->birthdate->format('Y-m-d')).'\'
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
		public function setTitle($title) { $this->title = $title; return $this; }
		public function setAddress($address) { $this->address = $address; return $this; }
		public function setPostCode($postCode) { $this->postCode = $postCode; return $this; }
		public function setCity($city) { $this->city = $city; return $this; }
		public function setCountry($country) { $this->country = $country; return $this; }
		public function setEmail($email) { $this->email = $email; return $this; }
		public function setPhone($phone) { $this->phone = $phone; return $this; }
		public function setNotes($notes) { $this->notes = $notes; return $this; }
		public function setSocialSecurityNumber($socialSecurityNumber) { $this->socialSecurityNumber = $socialSecurityNumber; return $this; }
		/**
		 * @param DateTime $lastContact
		 */
		public function setLastContact($lastContact) { $this->lastContact = $lastContact; return $this; }
		public function setImage($image) { $this->image = $image; return $this; }
		public function setUID($uid) { $this->uid = $uid; return $this; }
		public function setCompany($company) { $this->company = $company; return $this; }
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
		public function getTitle(){ return $this->title; }
		public function getAddress(){ return $this->address; }
		public function getPostCode(){ return $this->postCode; }
		public function getCity(){ return $this->city; }
		public function getCountry(){ return $this->country; }
		public function getEmail(){ return $this->email; }
		public function getPhone(){ return $this->phone; }
		public function getNotes(){ return $this->notes; }
		public function getCompany(){ return $this->company; }
		public function getUID(){ return $this->uid; }
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