<?php
	use at\foundation\core;
	use at\foundation\core\User\model\User;
	use at\foundation\core\ServiceProvider;
	
	class Category extends core\BaseModel {
		
		private $id;
		private $taxId;
		private $name;
	
		public function __construct($name = '', $taxId = '') {
			$this->name = $name;
			$this->taxId = $taxId;
			parent::__construct(ServiceProvider::get()->db->prefix.'bookie_categories', array());
        }
		
		/**
		 * STATIC METHODS
		 */
		 
        /**
         * Fetches all matching Categories from the database and returns them in an array
         * @param string $insertSQL
         * @param int $from
         * @param int $rows
         * @return Category[]
         */
		public static function getCategories($insertSQL = '', $from = 0, $rows = -1){
			if($from >= 0 && $rows >= 0) $limit = ' LIMIT '.ServiceProvider::getInstance()->db->escape($from).','.ServiceProvider::getInstance()->db->escape($rows);
			else $limit = '';
			$result = ServiceProvider::getInstance()->db->fetchAll('SELECT *, c.id as id FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_categories AS c '.$insertSQL.' '.$limit.';');
			$out = array();
			foreach($result as $cat) {
				$co = new Category($cat['name'], $cat['taxid']);
				$co->setId($cat['id']);
				$out[] = $co;
			}
			return $out;
		}
		
		/**
		 * Fetches the Category with the given ID from the database
		 * @param int $categoryId The id of the Category to be fetched
		 * @return Category|NULL
		 */
		public static function getCategory($categoryId) {
			$acc = ServiceProvider::getInstance()->db->fetchRow('SELECT * FROM '.ServiceProvider::getInstance()->db->prefix.'bookie_categories WHERE id =\''.ServiceProvider::getInstance()->db->escape($categoryId).'\';');
			if($acc){
				$co = new Category($cat['name'], $cat['taxid']);
				$co->setId($cat['id']);
				return $co;
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
				$succ = $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.'bookie_categories
								(`name`, `taxid`) VALUES 
								(
									\''.$this->sp->db->escape($this->name).'\',
									\''.$this->sp->db->escape($this->taxId).'\'
								);');
				if($succ) {
					$this->id = $this->sp->db->getInsertedID();
					return true;
				} else {
					return false;
				}
				
			} else {
				//update
				return $this->sp->db->fetchBool('UPDATE '.ServiceProvider::get()->db->prefix.'bookie_categories SET
						`name` = \''.$this->sp->db->escape($this->name).'\', 
						`taxid` = \''.$this->sp->db->escape($this->taxId).'\'
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
		public function setName($name) { $this->name = $name; return $this; }
		public function setTaxId($taxId) { $this->taxId = $taxId; return $this; }
		
		
		public function getId(){ return $this->id; }
		public function getName() { return $this->name; }
		public function getTaxId() { return $this->taxId; }
		
	}
?>