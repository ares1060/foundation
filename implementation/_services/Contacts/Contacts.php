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
				case 'get.links': return $this->handleGetLinks($args); break;
				case 'view.links': return $this->handleViewLinks($args); break;
				case 'view.detail': return $this->handleViewDetail($args); break;
				case 'view.form': return $this->handleViewForm($args); break;
				case 'do.save': return $this->handleSave($args); break;
				case 'do.delete': return $this->handleDelete($args); break;
				case 'do.delete_contact_data': return $this->handleDeleteContactData($args); break;
				case 'view.contact_linker': return $this->handleViewLinker($args); break;
				case 'do.save_contact_links': return $this->handleLinks($args); break;
				case 'do.link_contact': return $this->handleLink($args); break;
				case 'do.delete_contact_link': return $this->handleDeleteLink($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getLoggedInUser();
			$whereSQL = 'WHERE user_id = \''.$user->getId().'\'';
			if(isset($args['search']) && strlen($args['search']) > 2){
					$args['search'] = trim($args['search']);
					$firstSpace = strpos($args['search'], ' ');
					$lastSpace = strrpos($args['search'], ' ');
					$whereSQL .= ' AND (
						`company` 
							LIKE \''.$this->sp->db->escape(($firstSpace !== False)?substr($args['search'], 0, $firstSpace):$args['search']).'%\' 
						OR
						`firstname` 
							LIKE \''.$this->sp->db->escape(($lastSpace !== False)?substr($args['search'], 0, $lastSpace):$args['search']).'%\' '
						.(($lastSpace !== False)?'AND':'OR').' 
						`lastname` 
							LIKE \''.$this->sp->db->escape(($lastSpace !== False)?substr($args['search'], $lastSpace+1):$args['search']).'%\'
						)';
			}
			
			if(isset($args['ids']) && $args['ids'] != '' && is_array($args['ids']) && count($args['ids']) > 0){
				$ids = array();
				foreach($args['ids'] as $id){
					$ids[] = $this->sp->db->escape($id);
				}
				$whereSQL .= ' AND id IN ('.implode(',', $ids).')';
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
					if(isset($args['css_class'])) $sv->addValue('class', $args['css_class']);
					$sv->addValue('id', $contact->getId());
					$svn = $sv->showSubView('nameonly');
					$svn->addValue('firstname', $contact->getFirstName().(($contact->getCompany() == '')?'<br/>':''));
					$svn->addValue('lastname', $contact->getLastName());
					$svn->addValue('title', $contact->getTitle());
					$svn->addValue('company', $contact->getCompany().(($contact->getCompany() == '')?'':'<br/>'));
					$sv->addValue('email', $contact->getEmail());
					$sv->addValue('phone', $contact->getPhone());
					$sv->addValue('uid', $contact->getUID());
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
					
					if($fc != strtoupper(substr($contact->getLastName(),0, 1))){
						$fc = strtoupper(substr($contact->getLastName(),0, 1));
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
					$sv->addValue('lastname', $contact->getLastName());
					$sv->addValue('title', $contact->getTitle());
					$sv->addValue('company', $contact->getCompany());
					$sv->addValue('email', $contact->getEmail());
					$sv->addValue('phone', $contact->getPhone());
					$sv->addValue('uid', $contact->getUID());
					$sv->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
					
					 if($user->getUserData()->opt('set.has_bookie', false)->getValue()) $sv->showSubView('has_bookie')->addValue('id', $contact->getId());
					
					$colContent .= $sv->render();
					$count++;
				}
			}
			if(isset($col)) $col->addValue('content', $colContent);
			
			return $view->render();
		}
		
		private function handleViewLinks($args){
			$user = $this->sp->user->getLoggedInUser();
			$sm = (isset($args['mode']) && $args['mode'] == 'simple');
			$sh = (isset($args['mode']) && $args['mode'] == 'short');
			if($sm) $view = new core\Template\ViewDescriptor('_services/Contacts/contact_simplelinks');
			else if ($sh) $view = new core\Template\ViewDescriptor('_services/Contacts/contact_shortlist');
			else $view = new core\Template\ViewDescriptor('_services/Contacts/contact_links');

			if($user && isset($args['entry_id']) && isset($args['link_table'])){
				if($this->checkLinkingAuth($args['link_table'], $args['entry_id'])){
					$contacts = Contact::getLinkedContacts($args['link_table'], $args['entry_id']);
					if(count($contacts) > 0){
						if($sm) $view->showSubView('header');
						foreach($contacts as $contact){
							if(isset($svc) && $svc) $svc->showSubView('divider');
							$svc = $view->showSubView('row');
							if($sh){
								$svn = $svc->showSubView('nameonly');
								$svn->addValue('firstname', $contact->getFirstName().(($contact->getCompany() == '')?'<br/>':''));
								$svn->addValue('lastname', $contact->getLastName());
								$svn->addValue('lastname', $contact->getTitle());
								$svn->addValue('company', $contact->getCompany().(($contact->getCompany() == '')?'':(($sm)?' ':'<br/>')));
								$svc->addValue('action_icon', (isset($args['actionicon']))?$args['actionicon']:'glyphicon glyphicon-remove');
							} else {
								$svc->addValue('firstname', $contact->getFirstName().(($contact->getCompany() == '')?(($sm)?' ':'<br/>'):''));
								$svc->addValue('lastname', $contact->getLastName());
								$svc->addValue('title', $contact->getTitle());
								$svc->addValue('company', $contact->getCompany().(($contact->getCompany() == '')?'':(($sm)?' ':'<br/>')));
							}
							$svc->addValue('uid', $contact->getUID());
							$svc->addValue('id', $contact->getId());
							$svc->addValue('image', urlencode(($contact->getImage() == '')?$this->settings->default_image:$this->settings->image_folder.$contact->getImage()));
						}
					}
				}
			}

			return $view->render();
			
		}
		
		private function checkLinkingAuth($linkTable, $entryId){
			$user = $this->sp->user->getLoggedInUser();
			$entry = $this->sp->db->fetchRow('SELECT * FROM '.$this->sp->db->prefix.$this->sp->db->escape($linkTable).' WHERE id = \''.$entryId.'\';');
			if((isset($entry['owner_id']) || isset($entry['author_id']) || isset($entry['user_id'])) && $user){
				if(isset($entry['owner_id']) && $entry['owner_id'] == $user->getId()) return true;
				else if(isset($entry['author_id']) && $entry['author_id'] == $user->getId()) return true;
				else if(isset($entry['user_id']) && $entry['user_id'] == $user->getId()) return true;
				else return false;
			}
			else return true;
		}
		
		private function handleViewDetail($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user && isset($args['id'])){
				$contact = Contact::getContact($args['id']);
				if($contact && $contact->getOwnerId() == $user->getId()){
					$view = new core\Template\ViewDescriptor('_services/Contacts/contact_detail');
					$view->addValue('id', $contact->getId());
					$view->addValue('firstname', $contact->getFirstName());
					$view->addValue('lastname', $contact->getLastName());
					$view->addValue('title', $contact->getTitle());
					$view->addValue('email', $contact->getEmail());
					$view->addValue('address', $contact->getAddress());
					$view->addValue('pc', $contact->getPostCode());
					$view->addValue('city', $contact->getCity());
					$view->addValue('country', $contact->getCountry());
					$view->addValue('notes', $contact->getNotes());
					$view->addValue('ssnum', $contact->getSocialSecurityNumber());
					$view->addValue('phone', $contact->getPhone());
					$view->addValue('uid', $contact->getUID());
					$view->addValue('company', $contact->getCompany());
					$view->addValue('birthdate', ($contact->getBirthdate())?$contact->getBirthdate()->format('d.m.Y'):'');
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
					
					$view->showSubView('attachments');
					
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
					$view->addValue('lastname', $contact->getLastName());
					$view->addValue('title', $contact->getTitle());
					$view->addValue('email', $contact->getEmail());
					$view->addValue('address', $contact->getAddress());
					$view->addValue('pc', $contact->getPostCode());
					$view->addValue('city', $contact->getCity());
					$view->addValue('country', $contact->getCountry());
					$view->addValue('notes', $contact->getNotes());
					$view->addValue('ssnum', $contact->getSocialSecurityNumber());
					$view->addValue('phone', $contact->getPhone());
					$view->addValue('company', $contact->getCompany());
					$view->addValue('uid', $contact->getUID());
					$view->addValue('birthdate', ($contact->getBirthdate())?$contact->getBirthdate()->format('d.m.Y'):'');
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
						
					$view->showSubView('attachments');
					
				} else {
					$view->addValue('image', urlencode($this->settings->default_image));
					$view->addValue('form_title', 'Neuer Kontakt');
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
				if(isset($args['title'])) $contact->setTitle($args['title']);
				if(isset($args['address'])) $contact->setAddress($args['address']);
				if(isset($args['pc'])) $contact->setPostCode($args['pc']);
				if(isset($args['city'])) $contact->setCity($args['city']);
				if(isset($args['country'])) $contact->setCountry($args['country']);
				if(isset($args['email'])) $contact->setEmail($args['email']);
				if(isset($args['phone'])) $contact->setPhone($args['phone']);
				if(isset($args['notes'])) $contact->setNotes($args['notes']);
				if(isset($args['ssnum'])) $contact->setSocialSecurityNumber($args['ssnum']);
				if(isset($args['uid'])) $contact->setUID($args['uid']);
				if(isset($args['company'])) $contact->setCompany($args['company']);
				if(isset($args['birthdate'])) $contact->setBirthdate(($args['birthdate'] == '')?null:new DateTime($args['birthdate']));
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
						
						$ok = $contact->delete();
						
						if($ok && isset($args['linktables'])){
							foreach($args['linktables'] as $lt){
								$this->sp->db->fetchBool('DELETE FROM '.$this->sp->db->prefix.$this->sp->db->escape($lt).'_contacts WHERE contact_id=\''.$this->sp->db->escape($args['id']).'\';');
							}
						}
						
						return $ok;
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
		
		private function handleViewLinker($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				//check if arguments are available
				// - entry_id (opt)
				// - link_table
				if(isset($args['link_table'])){
							
					$view = new core\Template\ViewDescriptor('_services/Contacts/contact_linker');
					$view->addValue('link_table', $args['link_table']);
					$view->addValue('label', (isset($args['form_label']))?$args['form_label']:'Kontakte');
								
					if(isset($args['entry_id']) && $args['entry_id'] != ''){
						$contacts = Contact::getLinkedContacts($args['link_table'], $args['entry_id']);
						$contactRndr = '';
						if(count($contacts) > 0){
							$cv = new core\Template\ViewDescriptor('_services/Contacts/contact_shortlist');
							foreach($contacts as $contact){
								$svc = $cv->showSubView('row');
								$svcn = $svc->showSubView('nameonly');
								$svcn->addValue('firstname', $contact->getFirstName().(($contact->getCompany() == '')?'<br/>':''));
								$svcn->addValue('lastname', $contact->getLastName());
								$svcn->addValue('title', $contact->getTitle());
								$svcn->addValue('company', $contact->getCompany().(($contact->getCompany() == '')?'':'<br/>'));
								$svc->addValue('action_icon', 'glyphicon glyphicon-remove');
								$svc->addValue('uid', $contact->getUID());
								$svc->addValue('id', $contact->getId());
								$svc->addValue('image', urlencode(($contact->getImage() == '')?$this->sp->ref('Contacts')->settings->default_image:$this->sp->ref('Contacts')->settings->image_folder.$contact->getImage()));
								$contactRndr .= $svc->render();
							}
						}
						
						$view->addValue('contacts', $contactRndr);
					}
					
					return $view->render();
				}
			}
				
			return false;
		}
		
		private function handleLinks($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['entry_id']) && isset($args['link_table'])){
					return $this->saveLinks(isset($args['contact_ids'])?$args['contact_ids']:array(), $args['link_table'], $args['entry_id']);
				}
			}
				
			return false;
		}
		
		public function saveLinks($contactIds, $linkTable, $entryId){
			$user = $this->sp->user->getLoggedInUser();
			if(!$user) return false;
			if(!$this->checkLinkingAuth($linkTable, $entryId)) return false;
			$oldCids = Contact::getLinkedContacts($linkTable, $entryId, true);
			$cids = array();
			//save values
			foreach($contactIds as $cid){
				if(!in_array($cid, $oldCids)){
					$co = Contact::getContact($cid);
					if($co && $co->getOwnerId() == $user->getId()) $this->sp->db->fetchBool('INSERT INTO '.$this->sp->db->prefix.$this->sp->db->escape($linkTable).'_contacts (`entry_id`,`contact_id`) VALUES (\''.$this->sp->db->escape($entryId).'\', \''.$this->sp->db->escape($cid).'\');');
				}
				$cids[] = $cid;
			}
				
			//remove old values
			foreach($oldCids as $cid){
				if(!in_array($cid, $cids)){
					$this->sp->db->fetchBool('DELETE FROM '.$this->sp->db->prefix.$this->sp->db->escape($linkTable).'_contacts WHERE entry_id=\''.$this->sp->db->escape($entryId).'\' AND contact_id=\''.$this->sp->db->escape($cid).'\';');
				}
			}
			
			return true;
		}
		
		private function handleLink($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				//check if all arguments are available
				// - contact_id
				// - entry_id
				// - link_table
			}
				
			return false;
		}
		
		private function handleDeleteLink($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				//check if all arguments are available
				// - contact_id
				// - entry_id (opt)
				// - link_table (opt)
			}
				
			return false;
		}
		
		public function getLinkedContacts($linktable, $entryId){
			return Contact::getLinkedContacts($linktable, $entryId);
		}
		
		public function checkAttachmentAuth($param){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				$contact = Contact::getContact($param);
				if($contact && $contact->getOwnerId() == $user->getId()) return true;
				else if(!$contact) return true;
			}
			return false;
		}
		
	}
?>