<?php
    class Database extends AbstractService implements IService {
        private $temp;
        private $mysqli;
        private $querycount;
        
        function __construct(){
            $this->name = 'Database';
            $this->querycount = array('success' => 0, 'error' => 0);
            $this->ini_file = $GLOBALS['to_root'].'_core/Database/Database.ini';
            parent::__construct();
            $this->temp = array();
            
			$this->connect();
        }
        
        public function getSettings() { return $this->settings; }
        
		private function connect() {
			//init database
			if(isset($this->mysqli)) $this->mysqli->close();
			$this->mysqli = new mysqli($this->_setting('db.database_host'), $this->_setting('db.database_user'), $this->_setting('db.database_pwd'), $this->_setting('db.database_table'));
            if (mysqli_connect_errno()) {
				//TODO use foundation messages
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
			$this->mysqli->character_set_name('utf8');
		}
		
        public function render($args){
            /** query = $query
             *  type = (row, array, insert(bool))
             *  action=(query)
             *  temp = wird tempor�r gespeichert oder nicht
             */
            $temp1 = (isset($args['temp'])) ? !(!$args['temp']) : true;
            $action = (isset($args['action'])) ? $args['action'] : 'query';
            $query = (isset($args['query'])) ? $args['query'] : '';
            $type = (isset($args['type'])) ? $args['type'] : 'row';
            $r = (isset($args['return'])) ? $args['return'] : false;
            
            if($action == 'query') {
                if($this->queryInCache($query)) return $this->getQueryCache($query);
                else {
                	switch($type){
                		case 'row':
                			return $this->fetchRow($query);
                			break;
                		case 'bool':
                			return $this->FetchBool($query, $r);
                			break;
                		case 'array': 
						case 'all':
                			return $this->fetchAll($query);
                			break;
                		default:
                			return '';
                	}
                }/*
                if(isset($args['query'])){
                    if(!isset($args['type'])) $args['type'] = 'array';
                    $query = mysql_query($args['query']);
                    $id = mysql_insert_id();
                    
                    $query ? $this->querycount['success']++ : $this->querycount['error']++;
                    if($type == 'row' && $query){   
                        $a = mysql_fetch_assoc($query);
                        if($this->temp) $this->temp[$md5] = $a;
                        return $a;
                    } else if($type == 'bool') {
                    	//$this->debugVar('-'.$id);
                        return ($query) ? (($r) ? $id : true)  : false;
                    } else if($query && $type == 'array'){
                        $a = array();
                        while($row = mysql_fetch_assoc($query)){
                            $a[] = $row;
                        }
                        if($temp1) $this->temp[$md5] = $a;
                        return $a;
                    }
                } else return '';*/
            }
            return '';
        }
        
        /**
         * returns a single row of data from the Database using mysql
         * @param string $query
         */
        public function fetchRow($query){
       		$result = $this->mysqli->query($query);
       		
       		$result ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		if($result) {
				$a = $result->fetch_assoc();
				$this->cacheQuery($query, $a);
				return $a;
			} else {
				return null;
			}
        }
        
     	/**
         * returns all rows of a query inside an array
         * @param string $query
         */
        public function fetchAll($query){
       		$result = $this->mysqli->query($query);
       		
       		$result ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		if($result){
				if (method_exists('mysqli_result', 'fetch_all')) {
					$a = $result->fetch_all(MYSQLI_ASSOC);
				} else {
					for ($a = array(); $tmp = $result->fetch_assoc();) $a[] = $tmp;
				}
				
	       		$this->cacheQuery($query, $a);
	       		
	            return $a;
       		} else return array();
        }
        
   	 	/**
         * returnes a boolean if the query was successfull
         * @param string $query
         */
        public function fetchBool($query, $return){
       		$result = $this->mysqli->real_query($query);
       		$id = $this->mysqli->insert_id();
       		
       		$result ? $this->querycount['success']++ : $this->querycount['error']++;
       		
       		$this->cacheQuery($query, $result);
       		
       		return $result;
        }

        
        public function exists($query){
            $a = $this->fetchRow($query);
            if(isset($a) && $a != ""){
                $ak = array_keys($a);
               // print_r($ak);
                if(isset($a[$ak[0]])) return true;
                else return false;
            } else return false;
        }
        
        public function getQueryCount(){
            return $this->querycount;
        }
        
        /**
         * checks if QUery is cached
         * @param $query
         */
     	private function queryInCache($query){
        	$md5 = (isset($query)) ? md5($query) : '';
        	
            return (in_array($md5, $this->temp));
        }
        
        /**
         * returnes cached query
         * @param $query
         */
        private function getQueryCache($query){
        	$md5 = (isset($query)) ? md5($query) : '';
        	
            if(in_array($md5, $this->temp)) return $this->temp[$md5];
            else return '';
        }
        
        /**
         * saves Queryresult to cache
         */
        private function cacheQuery($query, $result){
        	if($this->temp) $this->temp[md5($query)] = 	$result;
        }
        
        /**
         * Function to lazily insert rows into a table.
         * @param string $table The name of the database table to insert into
         * @param array $data An associative array where the keys have the same name as the columns in the database table
         * @return boolean
         */
        function lazyInsert($table, $data){
        	$sql = 'SHOW COLUMNS FROM '.$table.';';//fetch all columns
        	$result = $this->mysqli->query($sql);
        	$colstring = '';
        	$valuestring = '';
        	while($column = $result->fetch_assoc()){
        		//create the field- and value string
        		$colstring .= '`'.$column['Field'].'`,';
        		if(isset($data[$column['Field']])) $valuestring .= '\''.$this->mysqli->real_escape_string($data[$column['Field']]).'\', ';
        		else $valuestring .= '\'\', ';
        	}
        	$values = substr($valuestring,0,-2);
        	$cols = substr($colstring,0,-1);
        	return mysql_query('INSERT INTO '.$table.' ('.$cols.') VALUES ('.$values.');');//insert the data
        }
        
        /**
        * Function to lazily update rows in a table.
        * @param string $table The name of the database table to insert into
        * @param string $where A sql WHERE statement excluding the WHERE
        * @param array $data An associative array where the keys have the same name as the columns in the database table.
        * @return boolean
        */
        function lazyUpdate($table, $where, $data){
        	$sql = 'SHOW COLUMNS FROM '.$this->mysqli->real_escape_string($table).';';//fetch all columns
        	$result = $this->mysqli->query($sql);
        	$set = '';
        	while($column = $result->fetch_assoc()){
        		//create the field- and value string
        		if(isset($data[$column['Field']])){
        			$set .= $column['Field'].'=\''.$this->mysqli->real_escape_string($data[$column['Field']]).'\', ';
        		}
        	}
        	$set = substr($set,0,-2);
        	return mysql_query('UPDATE '.$table.' SET '.$set.' WHERE '.$where.';');//insert the data
        }
        
        function reloadConfig() {
        	$this->loadConfigIni($this->ini_file, false);
            $this->connect();
        }
    }
?>
