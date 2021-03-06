<?php
	
	namespace at\foundation\core\Messages;
	use at\foundation\core;
	use at\foundation\core\ServiceProvider;
	use at\foundation\core\Template\ViewDescriptor;
	use at\foundation\core\Template\SubViewDescriptor;
	
	/**
	 * Message Service | stores and displays messages
	 * @author Matthias Eigner
	 * @version: 0.1r2
     * @name: Messages
     */
    class Messages extends core\AbstractService implements core\IService  {
        
    	private $messages; // just needed if config[save_messages_in_session] = false
        
        const ERROR = 0;
        const INFO = 1;
        const DEBUG = 2; // should be used for testung
        const DEBUG_ERROR = 3; // should be used for testung
        const RUNTIME = 4; // runtime information (number of db-queries, runtime of script etc)
        const RUNTIME_ERROR = 5; // runtime errors 
        
        function __construct(){
            $this->name = 'Messages';
        	$this->ini_file = $GLOBALS['to_root'].'_core/Messages/Messages.ini';
        	
        	parent::__construct();
            $this->messages = array();
        }
        
        /**
         * $args['message'] ... message
         * $args['type'] ... type
         */
        public function render($args) {
            $action = (isset($args['action'])) ? $args['action'] : '';
            
			if($action == 'div') {
            	
                return $this->renderDiv($args);
            } if($action == 'viewType'){
            	
            	if(isset($args['type'])){
            		return $this->renderType($args['type']);
            	}
            } else if(isset($args['message']) && isset($args['type'])) {
                return $this->addMsg($args['message'], $args['type']);
            } else {
                return $this->renderMe();
            }
			
			/*else {
                $a = array('type'=> Messages::DEBUG_ERROR, 
                			'message'=>str_replace(array('{@pp:service}'), array('Messages'), $this->_('INTERNAL_ERROR')),
                			'id'=>count($this->messages)+1);
               	$this->messages[] = $a;
               	unset($a);
                return false;
            }*/
        }
        
        public function setup(){
        	return true;
        }
        
        /**
         * 
         * Adds a message to the message stack
         * @param string $message
         * @param string $type
         */
        public function addMsg($message, $type){
			       // 	print_r($this->messages);
        	if($this->settings->msg_save_messages_in_session) {
				$m = array('type'=>$type, 
							'time'=>microtime(true), 
							'message'=>$message, 
							'id'=>isset($_SESSION['messages']) ? count($_SESSION['messages']) : 0);
				if(isset($_SESSION['messages'])) $_SESSION['messages'][] = $m;
				else $_SESSION['messages'] = array($m);
// 				error_log('TF:Messsage: new message in Session ['.$message.']');
				unset($m);
			} else {
				$m = array('type'=>$type, 
							'time'=>microtime(true), 
							'message'=>$message, 
							'id'=>count($this->messages));
				$this->messages[] = $m;
				unset($m);
			}
			
			if($type == self::ERROR || $type == self::DEBUG_ERROR || $type == self::RUNTIME_ERROR){
				error_log('TheFoundation: '.$message);
			}
			
			return true;
        }        
        
        public function renderDiv($args){
        	if(isset($args['msg']) && isset($args['msg']['type']) &&isset($args['msg']['msg'])){
        		$replace = new ViewDescriptor($this->settings->tpl.tpl_message);
        		$sv = new SubViewDescriptor('message');
                $sv->addValue('type', $args['msg']['type']);
                $sv->addValue('time', '');
                $sv->addValue('m_id', '');
                $sv->addValue('message', $args['msg']['msg']);
                $replace->addSubView($sv);
                return $replace->render();	
        	}
        }
        
        /**
         * Renders active and session stored Messages
         */
        public function renderMe(){
            $replace = new ViewDescriptor($this->settings->tpl_message);

            /* -- render active Messages --- */
            foreach($this->messages as $key=>$message) {
            	if(($message['type'] == self::ERROR && $this->settings->msg_display_error) ||
            		($message['type'] == self::INFO && $this->settings->msg_display_info) ||
             		($message['type'] == self::DEBUG && $this->settings->msg_display_debug) ||
	             	($message['type'] == self::RUNTIME && $this->settings->msg_display_runtime)) {
             			
             		$sv = new SubViewDescriptor('message');
            		$sv->addValue('type', $message['type']);
           			$sv->addValue('time', ($message['type'] == Messages::DEBUG || $message['type'] == Messages::DEBUG_ERROR) ? '('.round(($message['time']-$GLOBALS['stat']['start'])*1000,4).')' : '');
         			$sv->addValue('message',  $message['message']);
   					$sv->addValue('m_id',  $message['id']);
        			$replace->addSubView($sv);

             		unset($this->messages[$key]);
             	}
			}
			error_log('TF:Messsage: Render Me');
			/* -- render $_SESSION Messages from previous Site --- */
			if(isset($_SESSION['messages'])){
				foreach($_SESSION['messages'] as $key=>$message) {
	            	if(($message['type'] == self::ERROR && $this->settings->msg_display_error) ||
	            		($message['type'] == self::INFO && $this->settings->msg_display_info) ||
	             		($message['type'] == self::DEBUG && $this->settings->msg_display_debug) ||
		             	($message['type'] == self::RUNTIME && $this->settings->msg_display_runtime)) {
	             			
	             		$sv = new SubViewDescriptor('message');
	            		$sv->addValue('type', $message['type']);
	           			$sv->addValue('time', ($message['type'] == self::DEBUG || $message['type'] == self::DEBUG_ERROR) ? '('.round(($message['time']-$GLOBALS['stat']['start'])*1000,4).')' : '');
	         			$sv->addValue('message',  $message['message']);
	   					$sv->addValue('m_id',  $message['id']);
	        			$replace->addSubView($sv);
	        			
	        			unset($_SESSION['messages'][$key]);
	      			}
				}
			}
			
			return $replace->render();
        }
        
        /**
         * Renders all messages of one type
         * @param string $type
         */
        public function renderType($type) {
        	$type = explode('/', $type);
        	$typear = array();
        	foreach($type as $t) $typear[] = $this->getTypeID($t);
        	
        	if($this->settings->msg_display_debug) $typear[] = self::DEBUG;
        	if($this->settings->msg_display_runtime) $typear[] = self::RUNTIME;
        	if($this->settings->msg_display_runtime_error) $typear[] = self::RUNTIME_ERROR;        	
			
        	//$_SESSION['messages'] = array();
        	if(count($typear) > 0) {
        		$replace = new ViewDescriptor($this->settings->tpl_message);
        		/* -- render active Messages --- */
	            foreach($this->messages as $key=>$message) {
						
	            	if((in_array($message['type'], $typear))) {
	            		$sv = new SubViewDescriptor('message');
	            		$sv->addValue('type', $message['type']);
	           			$sv->addValue('time', ($message['type'] == self::DEBUG || $message['type'] == self::DEBUG_ERROR) ? '('.round(($message['time']-$GLOBALS['stat']['start'])*1000,4).')' : '');
	         			$sv->addValue('message',  $message['message']);
	   					$sv->addValue('m_id',  $message['id']);
	        			
	   					$replace->addSubView($sv);
	        			
	        			unset($sv);
	        			unset($this->messages[$key]);
	      			}
	        	}
	        	/* -- render $_SESSION Messages from previous Site --- */
				if(isset($_SESSION['messages'])){
					$count=0;
						
					foreach($_SESSION['messages'] as $key=>$message) {
						if((in_array($message['type'], $typear))) {
							$count++;
		            		$sv = new SubViewDescriptor('message');
		            		$sv->addValue('type', $message['type']);
		           			$sv->addValue('time', ($message['type'] == self::DEBUG || $message['type'] == self::DEBUG_ERROR) ? '('.round(($message['time']-$GLOBALS['stat']['start'])*1000,4).')' : '');
		         			$sv->addValue('message',  $message['message']);
		   					$sv->addValue('m_id',  $message['id']);
		        			
		   					$replace->addSubView($sv);

// 							error_log('TF:Messsage: deleted message from Session ['.$message['message'].']');
		   					unset($sv);
		        			unset($_SESSION['messages'][$key]);
		            	}
					}
				}
// 				error_log('TF:Messages: '.$count);
// 				error_log('TF:Messages:'.$replace->render());
				return $replace->render();
        	} else return 'asdf';
        }
        
        /**
         * 
         * Returnes Type Id for given string
         * @param string $type
         */
        private function getTypeID($type){
        	switch($type){
        		case 'debug':
        			return self::DEBUG;
        			break;
        		case 'info':
        			return self::INFO;
        			break;
        		case 'error':
        			return self::ERROR;
        			break;
        		case 'debug_error':
        			return self::DEBUG_ERROR;
        			break;
        		case 'runtime':
        			return self::RUNTIME;
        			break;
        		case 'runtime_error':
        			return self::RUNTIME_ERROR;
        			break;
        		default:
        			return -1;
        			break;
        	}
        }
        
    }
?>