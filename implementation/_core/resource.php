<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    $authorized = array();
    $connector = true;
    
    $GLOBALS['connector_to_root'] = (isset($_POST['to_root'])) ? $_POST['to_root'] : $to_root; // possibility to set new root if ajax file is in other folder
    $GLOBALS['connector_to_root'] = (isset($_GET['to_root'])) ? $_GET['to_root'] : $GLOBALS['connector_to_root'];
    
	require_once($to_root.'_core/foundation.php');
	
	error_reporting(E_ALL ^ E_NOTICE);
	
	if(isset($_REQUEST['type'])){
		if($_REQUEST['type'] == 'image'){
			ini_set("memory_limit","128M");
			
			$sp->ref('Image')->render($_REQUEST);
		}
	}
	
    //TODO: find requested resource
	
	//TODO: modify headery accordingly
	
	//TODO: output resource
	
?>