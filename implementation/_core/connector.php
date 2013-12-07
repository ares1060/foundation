<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    $authorized = array();
    $connector = true;
    
    $GLOBALS['connector_to_root'] = (isset($_POST['to_root'])) ? $_POST['to_root'] : $to_root; // possibility to set new root if ajax file is in other folder
    $GLOBALS['connector_to_root'] = (isset($_GET['to_root'])) ? $_GET['to_root'] : $GLOBALS['connector_to_root'];
    
	require_once($to_root.'_core/foundation.php');
	
	/**
	 * HINTS:
	 * 	.) AUTHORIZATION FOR PAGE:
	 * 		$authorized ... Array |Êwill be checked in theFoundation.php
	 * 		insert authorized user-groups
	 * 		if $authorized = array() anyone will be authorized to view the page
	 * 
	 *  .) MESSAGES:
	 *  	msg service -> will be added to the page further down 
	 *  	@see: _core/Messages/Messages.php
	 *  
	 *  .) CSS and JS to header:
	 *  	use $GLOBALS['extracss'] and $GLOBALS['extrajs'] in Template to add extra CSS and JS to the header
	 */
	error_reporting(E_ALL ^ E_NOTICE);
    
    $args = array();
    $args = (isset($_POST['args'])) ? array_merge($_POST['args'], $args) : $args;
    $args = (isset($_GET['args'])) ? array_merge($_GET['args'], $args) : $args;

    if(isset($args['working_dir'])) $GLOBALS['working_dir'] = $args['working_dir'];
    if(isset($args['to_root'])) $GLOBALS['to_root'] =  $args['to_root'];
	if(isset($args['template'])) $sp->ref('Template')->setTemplate($args['template']);
	
    $service_name = (isset($_GET['service_name'])) ? $_GET['service_name'] : ((isset($_POST['service_name'])) ? $_POST['service_name'] : '');
	
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('content-type: application/json; charset=utf-8');
    
// 	error_log('TF:Connector: start');
	if(isset($GLOBALS['session_expired']) && $GLOBALS['session_expired'] === true) {
		// handles session expiration for ajax requests
		echo json_encode(array('content'=>'session_expired', 'msg'=>''));
	} else {
		if(!isset($args['noMsg'])) {
	    	echo json_encode(array('content'=>$sp->render($service_name, $args), 
	    							'msg'=>$sp->render('Messages', array('action'=>'viewType', 'type'=>'error/info')),
	    							'debug'=>$sp->render('Messages', array('action'=>'viewType', 'type'=>'debug'))));	
	    } else {
	  		echo json_encode(array('content'=>$sp->render($service_name, $args)));
	    }
	}
// 	error_log('TF:Connector: end');
	
?>