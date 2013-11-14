<?php
	session_start();
	
	use at\foundation\core;
	
	//setup autoloader for core classes and core services
	spl_autoload_register(function ($class) {
		$a = array();
		if(preg_match('/^at\\\\foundation\\\\core\\\\[^\\\\]+\\\\.*$/', $class, $a) > 0){
			$class = str_replace('\\', '/', str_replace('at\foundation\core\\', $GLOBALS['config']['root'].'_core/', $class));
			require_once $class . '.php';
		} else if(strpos($class, 'at\foundation\core\\') === 0){
			$class = str_replace('\\', '/', str_replace('at\foundation\core\\', $GLOBALS['config']['root'].'_core/_serviceprovider/', $class));
			require_once $class . '.php';
		}
	});
		
	if(empty($_SERVER['REQUEST_URI'])) {
	    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	}
	
	
//--------------- INIT GLOBALS ------------------//
	
    $GLOBALS['stat']['start'] = microtime(true);
	$GLOBALS['config']['default_language'] = 'de';
	$GLOBALS['config']['root'] = substr(dirname(__FILE__), 0, -5);	
	$GLOBALS['to_root'] = isset($to_root) ? $to_root : '';
	
	//get abs link to main
	$a = explode('/', $_SERVER['PHP_SELF']);
	array_pop($a);
	$folder = implode('/', $a).'/';
	$GLOBALS['abs_root'] = 'http://'.$_SERVER['HTTP_HOST'].$folder.$GLOBALS['to_root'];
	$GLOBALS['working_dir'] = 'spidernet/';
	$GLOBALS['testDatabase'] = true; // if true the services databases will be deleted before install
	
	$root = '';
	for($i=0;$i<((count(explode('/', $GLOBALS['config']['root']))-2)-(count(explode('/', $_SERVER['REQUEST_URI']))-2)-(count(explode('/', $_SERVER['DOCUMENT_ROOT']))-1))*(-1);$i++){
		$root .= '../';
	}
    
	$GLOBALS['config']['login'] = $GLOBALS['abs_root'].'_admincenter/login/';
	$GLOBALS['tpl']['root'] = '';
    
	$GLOBALS['extra_css'] = array();
	$GLOBALS['extra_js'] = array();
	
//--------------- SESSION ------------------//
	
	/* -- Save active and previous page in session -- */
	if(!isset($connector) || !$connector){
		if(!isset($_SESSION['history']['prev_page'])) $_SESSION['history']['prev_page'] = $to_root.'';
		if(isset($_SESSION['history']['active_page'])){
			if(isset($_SESSION['history']['prev_page']) && $_SESSION['history']['prev_page'] != $_SESSION['history']['active_page']) $_SESSION['history']['prev_page'] = $_SESSION['history']['active_page'];
		} else {
			$_SESSION['history']['prev_page'] = 'index.php';
		}
		$get = '';
		foreach($_GET as $k=>$g){
			$w = ($get == '') ? '?' : '&';
			$get .= $w.$k.'='.$g;
		}
		$_SESSION['history']['active_page'] = (substr($_SERVER['SCRIPT_FILENAME'], strlen($GLOBALS['config']['root']), strlen($_SERVER['SCRIPT_FILENAME'])-strlen($GLOBALS['config']['root']))).$get;
	}
	
	//some imports to speed up things a bit -> no autoload needed for these
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/CoreService.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/AbstractService.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/IService.php');
	require_once($GLOBALS['config']['root'].'_core/_serviceprovider/ServiceProvider.php');
	require_once($GLOBALS['config']['root'].'_core/Template/ViewDescriptor.php');
	require_once($GLOBALS['config']['root'].'_core/Template/SubViewDescriptor.php');
		
	//make sure a ServiceProvider instance is available
	$sp = core\ServiceProvider::getInstance();

	/* check session expiration */
	$sp->user->checkSessionExpiration();

	/* check authorization */
	if(isset($authorized) && is_array($authorized) && $authorized != array()) {
		$gr = ($sp->ref('User')->isLoggedIn()) ? $sp->ref('User')->getLoggedInUser()->getGroup()->getId() : -1;
		error_log('sdf');
		if(!in_array(strtolower(User::getUserGroupNameFromId($gr)), $authorized) && !in_array($gr, $authorized)) {header('Location: '.$GLOBALS['config']['login']); exit(0);}
	}
?>