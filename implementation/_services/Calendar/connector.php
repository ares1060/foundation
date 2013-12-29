<?php 

$to_root = '../../';

require_once($to_root.'_core/foundation.php');

require_once('dhtmlx/connector/db_mysqli.php');
require_once('dhtmlx/connector/scheduler_connector.php');

$user = $sp->user->getLoggedInUser();

if($user && $user->getId() > 0){
	$conn = new SchedulerConnector($sp->db->mysqli, 'MySQLi');
	$conn->enable_log("log.txt",true);
	
	function setOwner($data){
		$user = at\foundation\core\ServiceProvider::getInstance()->user->getLoggedInUser();
		if ($data->get_value("owner_id") == '' || $data->get_value("owner_id") != $user->getId()){
			$data->set_value("owner_id", $user->getId());
		}
	}
	
	
	$conn->event->attach("beforeProcessing","setOwner");
	
	$conn->render_sql('SELECT * FROM '.$sp->db->prefix.'calendar_events WHERE owner_id = \''.$user->getId().'\'',"id","start_date,end_date,text,owner_id");
}

?>