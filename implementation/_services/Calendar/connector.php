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
	
	$whereSQL = 'WHERE e.owner_id = \''.$user->getId().'\'';
	
	//TODO: generalize this so that services can infuse filter criteria generically
	if(isset($_REQUEST['contact_filter']) && $_REQUEST['contact_filter'] != ''){
		$values = '';
		$cf = explode(',', $_REQUEST['contact_filter']);
		foreach($cf as $val){
			$values .= $sp->db->escape($val).',';
		}
		$values = substr($values, 0, -1);
		$whereSQL = 'JOIN '.$sp->db->prefix.'calendar_events_contacts AS c ON c.entry_id = e.id '.$whereSQL;
		$whereSQL .= ' AND contact_id IN ('.$values.')';
	}
	
	
	$conn->render_sql('SELECT *, e.id AS id FROM '.$sp->db->prefix.'calendar_events e '.$whereSQL,"id","start_date,end_date,text,owner_id");
}

?>