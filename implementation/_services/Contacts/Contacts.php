<?php
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/Contact.php');
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/ContactData.php');
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/ContactDataItem.php');

	class Image extends AbstractService implements IService {
		
		function __construct(){
			$this->name = 'Contacts';
			$this->ini_file = $GLOBALS['to_root'].'_services/Contacts/Contacts.ini';	
			parent::__construct();
        }
		
	}
?>