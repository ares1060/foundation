<?php
	/* Main includes (init the foundation) */
	$to_root = '../';
    require_once($to_root.'_core/foundation.php');
    
	use at\foundation\core;
	use at\foundation\core\Template;
    
	$sp->tpl->setTemplate('admin');
	
    $main = new Template\ViewDescriptor('main');
    
    echo $main->render();
	
	echo '<br>runtime: '.(microtime(true)-$GLOBALS['stat']['start']);
?>