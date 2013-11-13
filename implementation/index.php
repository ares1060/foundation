<?php
	/* Main includes (init the foundation) */
	$to_root = '';
    require_once($to_root.'_core/foundation.php');
    
    $main = new ViewDescriptor('main');
    
    echo $main->render();
?>