<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Post extends core\BaseModel {
		
		private $id;
		private $authorUser;
		private $authorId;
		private $text;
		private $title;
		/**
		 * @var DateTime
		 */
		private $date;
	
		public function __construct($author = -1, $text='', $title='', $date=null) {
            $this->authorId = $author;
			$this->text = $text;
			$this->title = $title;
			$this->date = (!$date)?new DateTime():$date;
			parent::__construct(ServiceProvider::get()->db->prefix.'blog_post', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
		public static function getPosts($insertSQL = '', $from = 0, $rows = -1) {
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).','.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT *, p.id as id FROM '.ServiceProvider::getInstance()->db->prefix.'blog_posts AS p '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $post) {
				$po = new Post($post['user_id'], $post['text'], $post['title'], new DateTime($post['date']));
				$po->setId($post['id']);
				$out[] = $po;
			}
			return $out;
		}
		
		public static function getPost($postId) {
			$post = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'blog_posts WHERE id = \''.ServiceProvider::getInstance()->db->escape($postId).'\';');
			if($post) {
				$po = new Post($post['user_id'], $post['text'], $post['title'], new DateTime($post['date']));
				$po->setId($post['id']);
				return $po;
			}
			return null;
		}
		
		public static function getPostCount($insertSQL = ''){
			$result = ServiceProvider::getInstance()->db->fetchRow('SELECT COUNT(*) AS count FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_posts AS p '.$insertSQL.';');
			return $result['count'];
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'blog_posts 
								(`user_id`, `date`, `title`, `text`) VALUES 
								(
									\''.$this->sp->db->escape($this->authorId).'\',
									\''.$this->sp->db->escape($this->date->format('Y-m-d H:i:s')).'\',
									\''.$this->sp->db->escape($this->title).'\',
									\''.$this->sp->db->escape($this->text).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'blog_posts SET
						`user_id` = \''.$this->sp->db->escape($this->authorId).'\',
						`date` = \''.$this->sp->db->escape($this->date->format('Y-m-d H:i:s')).'\',
						`title` = \''.$this->sp->db->escape($this->title).'\',
						`text` = \''.$this->sp->db->escape($this->text).'\'
					WHERE id="'.ServiceProvider::get()->db->escape($this->id).'"');
			}
			return true;
		}
		
		/**
		 *	Deletes the post data item from the database
		 */
		public function delete(){
			$ok = $this->sp->db->fetchBool('DELETE FROM '.$this->sp->db->prefix.'blog_posts WHERE id=\''.$this->sp->db->escape($this->id).'\';');
			return $ok;
		}
		 
		 
		/**
		 * GETTER & SETTER
		 */
		private function setId($id) { $this->id = $id; return $this; }
		public function setAuthor($id) { $this->authorId = $id; $this->authorUser = null; return $this; }
		public function setText($text) { $this->text = $text; return $this; }
		public function setTitle($title) { $this->title = $title; return $this; }
		/**
		 * @param DateTime $date
		 */
		public function setDate($date) { $this->date = $date; return $this; }	
		
		
		public function getId(){ return $this->id; }
		/**
		 * @return at/foundation/_core/User/model/User
		 */
		public function getAuthor(){ 
			if(!$this->authorUser == null) $this->authorUser = User::getUser($this->authorId);
			return $this->authorUser;
		}
		public function getAuthorId(){ return $this->authorId; }
		public function getText() { return $this->text; }
		public function getTitle() { return $this->title; }
		/**
		 * @return DateTime
		 */
		public function getDate() { return $this->date; }
	
	}
?>