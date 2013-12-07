<?php
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/Contact.php');
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/ContactData.php');
	require_once($GLOBALS['config']['root'].'_services/Contacts/model/ContactDataItem.php');

	use at\foundation\core;
	
	class Contacts extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->name = 'Contacts';
			$this->ini_file = $GLOBALS['to_root'].'_services/Contacts/Contacts.ini';	
			parent::__construct();
        }
		
		public function render($args){
			if(isset($args['action'])) $action = $args['action'];
			switch($action){
				case 'view.list': return handleViewList($args); break;
				case 'view.detail': return handleViewDetail($args); break;
				case 'do.save': return handleSave($args); break;
				case 'do.delete': return handleDelete($args); break;
				case 'do.delete_contact_data': return handleDeleteContactData($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getLoggedInUser();
			$whereSQL = 'user_id = \''.$user->getId().'\'';
			if(isset($args['search']) && strlen($args['search']) > 2){
					$args['search'] = $this->sp->db->escape($args['search']);
					$whereSQL .= ' AND (`firstname` LIKE \'%'.$args['search'].'%\' OR `lastname` LIKE \'%'.$args['search'].'%\')';
			}
			$from = 0;
			if(isset($args['from']) && is_int($args['from']) && $args['from'] > 0) {
				$from = $args['from'];
			}
			
			$rows = -1;
			if(isset($args['rows']) && is_int($args['rows']) && $args['rows'] >= 0){
				$rows = $args['rows'];
			}
			
			$contacts = Contact::getContacts($whereSQL, $from, $rows);
			if(isset($args['mode']) && $args['mode'] == 'short') $view = new core\Template\SubViewDescriptor('contact_short_list');
			else $view = new core\Template\SubViewDescriptor('contact_list');
			foreach($contacts as $contact){
				$sv = $view->showSubView('list_item');
				$sv->addValue('fname', $contact->getFirstName());
				$sv->addValue('lname', $contact->getlastName());
				$sv->addValue('email', $contact->getEmail());
				$sv->addValue('phone', $contact->getPhone());
				$sv->addValue('image', ($contact->getImage() == '')?'blank.png':$contact->getImage());
			}
			return $view->render();
		}
		
		private function handleViewDetail($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user && isset($args['id'])){
				$contact = Contact::getContact($args['id']);
				if($contact && $contact->getOwnerId() == $user->getId()){
					$view = new core\Template\SubViewDescriptor('contact_list');
					$view->addValue('fname', $contact->getFirstName());
					$view->addValue('lname', $contact->getlastName());
					$view->addValue('email', $contact->getEmail());
					$view->addValue('address', $contact->getAddress());
					$view->addValue('pc', $contact->getPostCode());
					$view->addValue('city', $contact->getCity());
					$view->addValue('notes', $contact->getNotes());
					$view->addValue('ssnum', $contact->getSocialSecurityNumber());
					$view->addValue('phone', $contact->getPhone());
					$view->addValue('image', ($contact->getImage() == '')?'blank.png':$contact->getImage());
	
					$cd = $contact->getContactData();
					$cdk = $cd->getKeys();
					
					foreach($cdk as $key){
						$cdi = $cd->get($key);
						if($cdi){
							$sv = $view->addSubView('data_item');
							$sv->addValue('label', $cdi->getKey());
							$sv->addValue('value', $cdi->getValue());
						}
					}
					
					return $view->render();
				} else {
					return '';
				}
			} 
			
			return '';
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get contact
					$contact = Contact::getContact($args['id']);
					if(!$contact || $contact->getOwnerId() != $user->getId()) return false;
				} else {
					//create new contact
					$contact = new Contact();
				}
				
				if(isset($args['fname'])) $contact->setFirstName($args['fname']);
				if(isset($args['lname'])) $contact->setLastName($args['lname']);
				if(isset($args['address'])) $contact->setAddress($args['address']);
				if(isset($args['pc'])) $contact->setPostCode($args['pc']);
				if(isset($args['city'])) $contact->setCity($args['city']);
				if(isset($args['email'])) $contact->setCity($args['email']);
				if(isset($args['phone'])) $contact->setPhone($args['phone']);
				if(isset($args['notes'])) $contact->setNotes($args['notes']);
				if(isset($args['ssnum'])) $contact->setSocialSecurityNumber($args['ssnum']);
				
				if(isset($args['data'])){
					$cd = $contact->getContactData();
					foreach($data as $k => $d){
						$cdi = $cd->opt($k, $d, true);
						$cdi->setValue($d);
						$cdi->save();
					}
				}
				
				if($contact->getOwnerId() == '') $contact->setOwner($user->getId());
				
				$contact->save();
			} else {
				return false;
			}
		}
		
		private function handleDelete($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					$contact = Contact::getContact($args['id']);
					if($contact && $contact->getOwnerId() == $user->getId()) {
						return $contact->delete();
					}
				}
			}
			
			return false;
		}
		
		private function handleDeleteContactData($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					$cdi = ContactDataItem::getContactDataItem($args['id']);
					if($cdi && $cdi->getContact()->getOwnerId() == $user->getId()) {
						return $cdi->delete();
					}
				}
			}
				
			return false;
		}
		
	}
?>