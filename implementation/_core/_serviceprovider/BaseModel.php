<?php

	namespace at\foundation\core;
	
	/**
	 *	This is a Helper class for quickly enabling objectoriented model based access to a database.
	 */
	class BaseModel {

		/**
		 * @var array
		 */
		protected $values;
		
		/**
		 * @var string
		 */
		protected $table;
	
		/**
		 * @var ServiceProvider
		 */
		protected $sp;
	
		public function __construct($table, $values) {
			$this->values = $values;
			$this->table = $table;
			$this->sp = ServiceProvider::getInstance();
		}
		
		/**
		 *	Fetches the row with the given id from the given table and returns a BaseModel instance
		 *  @param $table string The name of the table to fetch the data from
		 *  @param $id int The id of the database entry to get
		 *	@return BaseModel The BaseModel containing the data or null
		 */
		public static function getItem($table, $id){
			$val = $this->sp->dp->fetchRow('SELECT * FROM \''.ServiceProvider::get()->db->escape($table).'\' WHERE id=\''.ServiceProvider::get()->db->escape($id).'\' LIMIT 0,1;');
			if($val != null) {
				return new BaseModel($table, $val);
			} else return null;
		}
		
		/**
		 *	Fetches all items from the given table and returns an array with BaseModel instances
		 *  @param $table string The name of the table to fetch the data from
		 *  @param $from int The row from which to fetch the entries
		 *  @param $rows int The numer of rows to fetch
		 *	@return array.<BaseModel> The array of resulting BaseModels
		 */
		public static function getItems($table, $from = 0, $rows = -1){
			if($from >= 0 && $rows > 0) $limit = ' LIMIT '.ServiceProvider::get()->db->escape($from).','.ServiceProvider::get()->db->escape($rows);
			else $limit = '';
			$vals = $this->sp->dp->fetchAll('SELECT * FROM \''.ServiceProvider::get()->db->escape($table).'\''.$limit.';');
			$a = array();
			
			foreach($vals as &$val){
				array_push($a, new BaseModel($table, $val));
			}
			unset($val);
			
			return $a;
		}
		
		/**
		 *	Fetches all items from the given sql. Saving is supported but may yield unexpected results.
		 *	@return array.<BaseModel> The array of resulting BaseModels
		 */
		public static function getItemsSql($table, $sql){
			$vals = $this->sp->dp->fetchAll($sql);
			$a = array();
			
			foreach($vals as &$val){
				array_push($a, new BaseModel($table, $val));
			}
			unset($val);
			
			return $a;
		}
		
		public function __get($name) {
			if(isset($this->values[$name])) return $this->values[$name];
			else return null;
		}
		
		public function __set($name, $value) {
			if($name != 'id' && isset($this->values[$name])){
				$this->values[$name] = $value;
			}
		}
		
		/**
		 * Saves the current state of the model to the database by inserting or updating a record
		 * @return boolean Returns true if successfully saved otherwise false.
		 */
		public function save() {
			if(!$this->table || $this->table == '') return false;
			if(isset($this->values['id']) && $this->values['id'] != ''){
				$vals = $this->values; //copies array
				unset($vals['id']);
				return $this->sp->db->lazyUpdate($this->table, 'id = \''.$this->values['id'].'\'', $vals);
			} else {
				$suc = $this->sp->db->lazyInsert($this->table, $this->$values);
				if($suc > 0){
					$this->values['id'] = $suc;
					return true;
				} else { 
					return false;
				}
			}
		}
		
		/**
		 * Deletes the corresponding entry from the database
		 * @return boolean Returns true if there is no corresponding entry left
		 */
		public function delete(){
			if($this->table == '' || isset($this->values['id']) || $this->values['id'] == '') return true;
			if($this->sp->db->fetchBool('DELETE FROM '.$this->table.' WHERE id = \''.$this->values['id'].'\';')){
				unset($this->values['id']);
				return true;
			} else {
				return false;
			}
		}
	
	}

?>