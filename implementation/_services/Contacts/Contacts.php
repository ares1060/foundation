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
				case 'view.list': return $this->handleViewList($args); break;
				case 'view.detail': return $this->handleViewDetail($args); break;
				case 'view.form': return $this->handleViewForm($args); break;
				case 'do.save': return $this->handleSave($args); break;
				case 'do.delete': return $this->handleDelete($args); break;
				case 'do.delete_contact_data': return $this->handleDeleteContactData($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getLoggedInUser();
			$whereSQL = 'WHERE user_id = \''.$user->getId().'\'';
			if(isset($args['search']) && strlen($args['search']) > 2){
					$args['search'] = $this->sp->db->escape($args['search']);
					$whereSQL .= ' AND (`firstname` LIKE \'%'.$args['search'].'%\' OR `lastname` LIKE \'%'.$args['search'].'%\')';
			}
			$from = 0;
			if(isset($args['from']) && $args['from'] > 0) {
				$from = $this->sp->db->escape($args['from']);
			}
			
			$rows = -1;
			if(isset($args['rows']) && $args['rows'] >= 0){
				$rows = $this->sp->db->escape($args['rows']);
			}
			
			$contacts = Contact::getContacts($whereSQL, $from, $rows);
			if(isset($args['mode']) && $args['mode'] == 'short'){
				$view = new core\Template\ViewDescriptor('_services/Contacts/contact_shortlist');
				foreach($contacts as $contact){
					$sv = $view->showSubView('row');
					$sv->addValue('id', $contact->getId());
					$svn = $sv->showSubView('nameonly');
					$svn->addValue('firstname', $contact->getFirstName());
					$svn->addValue('lastname', $contact->getlastName());
					$sv->addValue('email', $contact->getEmail());
					$sv->addValue('phone', $contact->getPhone());
					$sv->addValue('action_icon', (isset($args['actionicon']))?$args['actionicon']:'glyphicon glyphicon-filter');
					$sv->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
				}
			} else {
				$view = new core\Template\ViewDescriptor('_services/Contacts/contact_list');	
				if($rows < 0) $rows = count($contacts);
				$pages =  ceil(Contact::getContactCount($whereSQL) / $rows);
				$view->addValue('pages', $pages);
				if(isset($args['mode']) && $args['mode'] == 'wrapped'){
					$view->showSubView('header');
					$footer = $view->showSubView('footer');
					$footer->addValue('current_page', '0');
					$footer->addValue('contacts_per_page', $rows);
					$footer->addValue('pages', $pages);
				}
				$third = ceil($rows / 3);
				$count = 0;
				$fc = '';
				foreach($contacts as $contact){
					if($count % $third == 0) {
						if(isset($col)) $col->addValue('content', $colContent);
						$col = $view->showSubView('col');
						$colContent = '';
					}
					
					if($fc != strtoupper(substr($contact->getlastName(),0, 1))){
						$fc = strtoupper(substr($contact->getlastName(),0, 1));
						$svh = new core\Template\SubViewDescriptor('segment_header');
						$svh->setParent($view);
						$svh->updateQualifiedName($col->getQualifiedName());
						$svh->addValue('label', $fc);
						$colContent .= $svh->render();
					}
					
					$sv = new core\Template\SubViewDescriptor('row');
					$sv->setParent($view);
					$sv->updateQualifiedName($col->getQualifiedName());
					$sv->addValue('id', $contact->getId());
					$sv->addValue('firstname', $contact->getFirstName());
					$sv->addValue('lastname', $contact->getlastName());
					$sv->addValue('email', $contact->getEmail());
					$sv->addValue('phone', $contact->getPhone());
					$sv->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
					$colContent .= $sv->render();
					$count++;
				}
			}
			if(isset($col)) $col->addValue('content', $colContent);
			
			return $view->render();
		}
		
		private function handleViewDetail($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user && isset($args['id'])){
				$contact = Contact::getContact($args['id']);
				if($contact && $contact->getOwnerId() == $user->getId()){
					$view = new core\Template\ViewDescriptor('_services/Contacts/contact_detail');
					$view->addValue('id', $contact->getId());
					$view->addValue('firstname', $contact->getFirstName());
					$view->addValue('lastname', $contact->getlastName());
					$view->addValue('email', $contact->getEmail());
					$view->addValue('address', $contact->getAddress());
					$view->addValue('pc', $contact->getPostCode());
					$view->addValue('city', $contact->getCity());
					$view->addValue('notes', $contact->getNotes());
					$view->addValue('ssnum', $contact->getSocialSecurityNumber());
					$view->addValue('phone', $contact->getPhone());
					$view->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
						
					$cd = $contact->getContactData();
					$cdk = $cd->getKeys();
					
					foreach($cdk as $key){
						$cdi = $cd->get($key);
						if($cdi){
							$sv = $view->showSubView('data_item');
							$sv->addValue('id', $cdi->getId());
							$sv->addValue('key', $cdi->getKey());
							$sv->addValue('value', $cdi->getValue());
						}
					}
					
					return $view->render();
				} else {
					return 'not for you';
				}
			} 
			
			return 'wut';
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				$view = new core\Template\ViewDescriptor('_services/Contacts/contact_detail');
				if(isset($args['id'])) $contact = Contact::getContact($args['id']);
				if($contact && $contact->getOwnerId() == $user->getId()){
					$view->addValue('id', $contact->getId());
					$view->addValue('firstname', $contact->getFirstName());
					$view->addValue('lastname', $contact->getlastName());
					$view->addValue('email', $contact->getEmail());
					$view->addValue('address', $contact->getAddress());
					$view->addValue('pc', $contact->getPostCode());
					$view->addValue('city', $contact->getCity());
					$view->addValue('notes', $contact->getNotes());
					$view->addValue('ssnum', $contact->getSocialSecurityNumber());
					$view->addValue('phone', $contact->getPhone());
					$view->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
		
					$cd = $contact->getContactData();
					$cdk = $cd->getKeys();
						
					foreach($cdk as $key){
						$cdi = $cd->get($key);
						if($cdi){
							$sv = $view->showSubView('data_item');
							$sv->addValue('id', $cdi->getId());
							$sv->addValue('key', $cdi->getKey());
							$sv->addValue('value', $cdi->getValue());
						}
					}
						
					
				} else {
					$view->addValue('image', urlencode($this->settings->default_image));
					$view->addValue('title', 'Neuer Kontakt');
				}
				
				return $view->render();
			}
				
			return 'wut';
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
				
				if(isset($args['firstname'])) $contact->setFirstName($args['firstname']);
				if(isset($args['lastname'])) $contact->setLastName($args['lastname']);
				if(isset($args['address'])) $contact->setAddress($args['address']);
				if(isset($args['pc'])) $contact->setPostCode($args['pc']);
				if(isset($args['city'])) $contact->setCity($args['city']);
				if(isset($args['email'])) $contact->setEmail($args['email']);
				if(isset($args['phone'])) $contact->setPhone($args['phone']);
				if(isset($args['notes'])) $contact->setNotes($args['notes']);
				if(isset($args['ssnum'])) $contact->setSocialSecurityNumber($args['ssnum']);
				if(isset($args['image'])) {
					if(file_exists($GLOBALS['config']['root'].$this->settings->upload_folder.$args['image'])){
						//move file from upload folder to image folder
						if(rename($GLOBALS['config']['root'].$this->settings->upload_folder.$args['image'], $GLOBALS['config']['root'].$this->settings->image_folder.$args['image'])){
							$contact->setImage($args['image']);
						}
					}
				}
				
				if($contact->getOwnerId() < 0) $contact->setOwner($user->getId());
				
				$ok = $contact->save();
				
				if($ok && isset($args['data'])){
					$cd = $contact->getContactData();
					$hk = array();
					//save values
					foreach($args['data'] as $k => $d){
						$cdi = $cd->opt($k, $d, true);
						$cdi->setValue($d);
						$cdi->save();
						$hk[] = $k;
					}
					
					//remove old values
					$keys = $cd->getKeys();
					foreach($keys as $k){
						if(!in_array($k, $hk)){
							$cd->del($k);
						}
					}
				}
								
				return $ok;
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