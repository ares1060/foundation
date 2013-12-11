<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Post extends core\BaseModel {
		
		private $id;
		private $ownerUser;
		private $ownerId;
		private $text;
		private $title;
		private $date;
	
		public function __construct($owner = -1, $text='', $title='', $date='') {
            $this->ownerId = $owner;
			$this->text = $text;
			$this->title = $title;
			$this->date = $date;
			parent::__construct(ServiceProvider::get()->db->prefix.'blogpost', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
		public static function getPosts() {
		
		}
		
		public static function getPost($postId) {
		
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'blogposts 
								() VALUES 
								(
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'blogposts SET
						
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the contact data item from the database
		 */
		public function delete(){
			
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setOwner($id) { $this->ownerId = $id; $this->ownerUser = null; return $this; }
		public function setText($text) { $this->text = $text; return $this; }
		public function setTitle($title) { $this->title = $title; return $this; }
		public function setDate($date) { $this->date = $date; return $this; }	
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getOwner(){ 
			if(!$this->ownerUser == null) $this->ownerUser = User::getUser($this->ownerId);
			return $this->ownerUser;
		}
		public function getOwnerId(){ return $this->ownerId; }
		public function getText() { return $this->text; }
		public function getTitle() { return $this->title; }
		public function getDate() { return $this->date; }
	
	}
?>