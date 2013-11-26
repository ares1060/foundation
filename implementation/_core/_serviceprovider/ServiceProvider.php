<?php
    
	/**
	 * @package at\foundation\core\ServiceProvider
	 */

	namespace at\foundation\core;
	use at\foundation\core;
	
	require_once($GLOBALS['config']['root'].'_core/Settings/Settings.php');
	require_once($GLOBALS['config']['root'].'_core/Messages/Messages.php');
	require_once($GLOBALS['config']['root'].'_core/Database/Database.php');
	require_once($GLOBALS['config']['root'].'_core/User/User.php');
    require_once($GLOBALS['config']['root'].'_core/FileHandler/FileHandler.php');
    require_once($GLOBALS['config']['root'].'_core/Template/Template.php');
    require_once($GLOBALS['config']['root'].'_core/Localization/Localization.php');
    require_once($GLOBALS['config']['root'].'_core/Rights/Rights.php');
    
    class ServiceProvider {
        /**
         * @var at\foundation\core\Database\Database
         */
    	public $db;
    
        /**
         * @var at\foundation\core\FileHandler\FileHandler
         */
    	public $fh;
	
		/**
		* @var at\foundation\core\Localization\Localization
		*/
        public $loc;
        
		/**
		 * @var at\foundation\core\Messages\Messages
		 */
		public $msg;
		
		/**
		 * @var at\foundation\core\User\User
		 */
		public $user;
		
		/**
		 * @var at\foundation\core\Template\Template
		 */
		public $tpl;

		/**
		* @var at\foundation\core\Rights\Rights
		*/
		public $rights;
		
		/**
		 * @var at\foundation\core\TextFunctions\TextFunctions
		 */
		public $txtfun;
		
		/**
		 * @var at\foundation\core\Mail\Mail
		 */
		public $mail;
		
		/**
		 * @var at\foundation\core\Settings\Settings
		 */
		public $settings;
		
		/**
		 * @var array
		 */
        private $services;

		/**
		 * @var ServiceProvider
		 */
        private static $instance = null;
		
        const VERSION = '0.03A R7';
        
        private function __construct() {
        	
        	$GLOBALS['ServiceProvider'] = $this;
			self::$instance = $this;
			$this->services = array();
			$this->settings = new Settings\Settings();
			$this->services['settings'] =& $this->settings;
			$this->loc = new Localization\Localization();
			$this->services['localization'] =& $this->loc;
			$this->db = new Database\Database();
			$this->services['database'] =& $this->db;
            $this->txtfun = new TextFunctions\TextFunctions();
            $this->services['textfunctions'] =& $this->txtfun;
            $this->msg = new Messages\Messages();
			$this->services['messages'] =& $this->msg;
  			$this->fh = new Filehandler\Filehandler();
            $this->services['filehandler'] =& $this->fh;
            $this->user = new User\User();
            $this->services['user'] =& $this->user;
            $this->tpl = new Template\Template();
			$this->services['template'] =& $this->tpl;
            $this->rights = new Rights\Rights();
            $this->services['rights'] =& $this->rights;
            $this->mail = new Mail\Mail();
            $this->services['mail'] =& $this->mail;
            $this->templates = array();
    
            $this->loc->loadPreloadedFiles();
            
            // check if installation is valid 
            if((!isset($GLOBALS['setup']) || !$GLOBALS['setup']) && $this->db->fetchBool('SHOW TABLES like "'.$this->db->prefix.'rights"') === false) {
            	// goto setup
            	header('Location: '.$GLOBALS['abs_root'].'_admincenter/setup/');
            }       
        }
        
		/**
		 * Returns a reference to the class of the requested Service.
		 * @param $name The name of the Service.
		 * @return IService
		 */
    	public function ref($name){
    		
    		if(!isset($this->services[strtolower($name)])){
				if(is_file($GLOBALS['config']['root'].'_services/'.$name.'/'.$name.'.php')){
					
					require_once($GLOBALS['config']['root'].'_services/'.$name.'/'.$name.'.php');
					if(class_exists($name)){
						$class = new $name();
						$this->services[strtolower($name)] = $class;
					} else {
						$this->__(str_replace('{@pp:service}', $name, $this->_('SERVICE_NOT_FOUND')));
						$class = null;
					}
				} else if(isset($this->services['Localization'])){
					$this->_msg(str_replace('{@pp:service}', $name, $this->_('SERVICE_NOT_FOUND')));
					$class = null;
				} else {
					$class = null;
				}
				if(isset($_SESSION['User']) && isset($_SESSION['User']['loggedInUser'])){
					$this->rights->getUserRights(@$_SESSION['User']['loggedInUser']->getId(), $name);
				}
			} else {
				$class = $this->services[strtolower($name)];
			}
			return $class;
		}
        
        // ----------------------------     Services wrapper
        public function render($name, $args) {
        	$ref = $this->ref($name);
        	return ($ref != null) ? $ref->render($args) : '';
       	}

        private function _($str, $service='core'){ return $this->data('Localization', array('str'=>$str, 'service'=>$service));}
        private function __($str, $type=Messages\Messages::DEBUG_ERROR, $service='core'){ $this->msg->run(array('message'=>$str, 'type'=>$type));}
        
        /**
         * Setup function for creating necessary tables, folders and files
         */
       	public function setup(){
       		return true;
       	}
		
		/**
		 * @return ServiceProvider
		 */
		public static function getInstance(){
			if(self::$instance == null) new ServiceProvider();
			return self::$instance;
		}
		
		/**
		 * @return ServiceProvider
		 */
		public static function get(){
			return self::getInstance();
		}
        
		
		/**
		 * Magic function for getting various internal values
		 */
		public function __get($name){
			$ref = $this->ref($name);
			if($ref) return $ref;
			
			$trace = debug_backtrace();
			trigger_error(
				'Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
    }
?>