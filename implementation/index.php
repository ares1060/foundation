<?php
	/* Main includes (init the foundation) */
	$to_root = '';
    require_once($to_root.'_core/foundation.php');
    
	use at\foundation\core;
	use at\foundation\core\Template;
	use at\foundation\core\User;
    
    $main = new Template\ViewDescriptor('main');
        
    echo $main->render();
    
    $sp->user->login('tester', 'asdf');
    
    echo '<br>Welcome: '.$sp->user->getLoggedInUser()->getNick();
    
	$bm = core\BaseModel::getItem('pp_user', '19');
	$bm->email = 'ehehehe@lol.com';
	$bm->save();
	$bm->email = 'yadada@lol.com';
	$bm->save();
	
	echo $sp->db->getLastError();
    
	echo '<br>runtime: '.(microtime(true)-$GLOBALS['stat']['start']);
?>