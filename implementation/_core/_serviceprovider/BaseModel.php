<?php

	namespace at\foundation\core;

	public class BaseModel {

		/**
		 * @var array
		 */
		protected $values
		
		/**
		 * @var string
		 */
		protected $table
	
		/**
		 * @var ServiceProvider
		 */
		protected $sp
	
		public function __construct($table, $values) {
			$this->values = $values;
			$this->table = $table;
			$this->sp = ServiceProvider::getInstance();
		}
		
		public function __get($name) {
			if(isset($this->values[$name])) return $this->values[$name];
			else return null;
		}
		
		public function __set($name, $value) {
			if(isset($this->values[$name])){
				$this->values[$name] = $value;
			}
		}
		
		public function save() {
			if(isset($this->values['id']) && $this->values['id'] != '') $this->sp->db->lazyUpdate($this->table, 'id = \''.$this->values['id'].'\'', $this->$values);
			else  $this->sp->db->lazyInsert($this->table, $this->$values);
		}
	
	}

?>