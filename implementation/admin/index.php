<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    require_once($to_root.'_core/foundation.php');
    
	use at\foundation\core;
	use at\foundation\core\Template;
    
	$sp->user->login('tester', 'asdf');
	
	$sp->tpl->setTemplate('admin');
	
    $main = new Template\ViewDescriptor('main');
    $main->addValue('username', $sp->user->getLoggedInUser()->getNick());
	
	$menuItem = $main->showSubView('menuitem');
	$menuItem->addValues(array(
		'label' => '<span class="glyphicon glyphicon-user"></span> Userverwaltung',
		'href' => '?service=User',
		'active' => (isset($_GET['service']) && $_GET['service'] == 'User')?'active':''
	));
	
	$content = 'Willkommen im Admin';
	
	if(isset($_GET['service'])){
		$action = 'admin.main';
		if(isset($_GET['action']) && $_GET['action'] != '') $action = 'admin.'.$_GET['action'];  
		$ref = $sp->ref($_GET['service']);
		if($ref){
			$content = $ref->render(array('action' => $action));
		} else {
			$content = 'unknown service!';
		}
	}
	
	$main->addValue('content', $content);
	
    echo $main->render();
	
	echo '<br>runtime: '.(microtime(true)-$GLOBALS['stat']['start']);
?>