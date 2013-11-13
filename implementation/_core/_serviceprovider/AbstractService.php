<?php
	
	namespace at\foundation\core;
	use at\foundation\core;
	
	/**
	 * Class Service
	 *
	 */
	abstract class AbstractService extends CoreService{
        /**
         * name of the service
         * @var string
         */
    	protected $name;
        
        /**
         * Array for configuration data
         * @var string[]
         */
        protected $config;

        /**
         * configuration file path
         * @var string
         */
    	protected $config_file;  
        
        public function __construct() {
        	parent::__construct();
            $this->config = array();

            // load ini config 
            if(isset($this->ini_file) && $this->ini_file != '') $this->loadConfig($this->ini_file, $this->name);
            
            // load localization
            if(isset($this->config['loc_folder']) && isset($this->sp->loc)) $this->sp->loc->loadLocalizationFile($this->config['loc_folder'], $this->name);
            
            if($this->_setting('loc.loc_folder') != null){
            	$setting = (strpos($this->_setting('loc.loc_folder'), $GLOBALS['config']['root']) === false) ?  $GLOBALS['config']['root'].$this->_setting('loc.loc_folder') : $this->_setting('loc.loc_folder');
				
            	//exception for localization (it loads systems core localization file
            	$name = ($this->name == 'Localization') ? 'core' : $this->name;

            	if(isset($this->sp->loc)) $this->sp->loc->loadLocalizationFile($setting, $name);
            	
            	// preload Localization File -> fil will be loaded after initialization
            	else core\Localization\Localization::preloadLocalizationFolder($setting, $name);
            }
        }
        
        
        /**
         * 
         * Loads File with Htmlwrapper service
         * @param string $file
         */
        protected function loadFile($file){ 
        	return $this->sp->render('Filehandler', array('action'=>'load', 'file'=>$file));
       	}       	
        
        /**
         * Generates POT file for this Service by searching through the sourcecode
         * Feature for developers only
         * 
         * @see Localization generatePOTFile();
         */
        protected function generatePOT() {
        	$this->sp->ref('Localization')->generatePOTFile($this->name);
        }
        
    }
?>
