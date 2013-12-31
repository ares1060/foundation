<?php
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Entry.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Invoice.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/InvoicePart.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Receipt.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Attachment.php');


	use at\foundation\core;
	
	class Bookie extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->name = 'Bookie';
			$this->ini_file = $GLOBALS['to_root'].'_services/Bookie/Bookie.ini';	
			parent::__construct();
        }
		
		public function render($args){
			if(isset($args['action'])) $action = $args['action'];
			switch($action){
				case 'view.list': return $this->handleViewList($args); break;
				case 'view.form': return $this->handleViewForm($args); break;
				case 'do.save': return $this->handleSave($args); break;
				case 'do.add.attachment': return $this->handleAddAttachment($args); break;
				case 'do.remove.attachment': return $this->handleRemoveAttachment($args); break;
				case 'do.delete': return $this->handleDelete($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getLoggedInUser();
			$whereSQL = 'WHERE user_id = \''.$user->getId().'\'';
			if(isset($args['search']) && strlen($args['search']) > 2){
					$args['search'] = $this->sp->db->escape($args['search']);
					$whereSQL .= ' AND (`notes` LIKE \'%'.$args['search'].'%\')';
					//TODO tagging
			}
			
			if(isset($args['state']) && is_array($args['state']) && count($args['state']) > 0){
				$values = '';
				foreach($args['state'] as $val){
					$values .= '\''.$this->sp->db->escape($val).'\',';
				}
				$values = substr($values, 0, -1);
				$whereSQL .= ' AND `state` IN ('.$values.')';
			}
			
			if(isset($args['amount_from']) && $args['amount_from'] != ''){
				$args['amount_from'] = $this->sp->db->escape($args['amount_from']);
				$whereSQL .= ' AND `brutto` >= '.$args['amount_from'];
			}
			
			if(isset($args['amount_to']) && $args['amount_to'] != ''){
				$args['amount_to'] = $this->sp->db->escape($args['amount_to']);
				$whereSQL .= ' AND `brutto` <= '.$args['amount_to'];
			}
			
			if(isset($args['date_from']) && $args['date_from'] != ''){
				$args['date_from'] = $this->sp->db->escape($args['date_from']);
				$whereSQL .= ' AND `date` >= \''.$args['date_from'].'\'';
			}
				
			if(isset($args['date_to']) && $args['date_to'] != ''){
				$args['date_to'] = $this->sp->db->escape($args['date_to']);
				$whereSQL .= ' AND `date` <= \''.$args['date_to'].'\'';
			}
			
			$from = 0;
			if(isset($args['from']) && $args['from'] > 0) {
				$from = $this->sp->db->escape($args['from']);
			}
			
			$rows = -1;
			if(isset($args['rows']) && $args['rows'] >= 0){
				$rows = $this->sp->db->escape($args['rows']);
			}
			
			if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0){
				$values = '';
				foreach($args['contact_filter'] as $val){
					$values .= $this->sp->db->escape($val).',';
				}
				$values = substr($values, 0, -1);
				$whereSQL = 'JOIN '.$this->sp->db->prefix.'bookie_entry_contacts AS c ON c.entry_id = e.id '.$whereSQL;
				$whereSQL .= ' AND contact_id IN ('.$values.') GROUP BY e.id';
			}
			
			$whereSQL .= ' ORDER BY e.date DESC, e.id DESC';
			
			$entries = Entry::getEntries($whereSQL, $from, $rows);

			$view = new core\Template\ViewDescriptor('_services/Bookie/entry_list');	
			if($rows < 0) $rows = count($entries);
			$pages =  ceil(Entry::getEntryCount($whereSQL) / $rows);
			$view->addValue('pages', $pages);
			if(isset($args['mode']) && $args['mode'] == 'wrapped'){
				$view->showSubView('header');
				$footer = $view->showSubView('footer');
				$footer->addValue('current_page', '0');
				$footer->addValue('entries_per_page', $rows);
				$footer->addValue('pages', $pages);
			}
			
			$totals = Entry::getEntrySums($whereSQL);
			
			$view->addValue('total_in', number_format($totals['brutto_in'], 2, ',', '.'));
			$view->addValue('total_out', number_format($totals['brutto_out'], 2, ',', '.'));
			$view->addValue('total', number_format($totals['brutto_out'] + $totals['brutto_in'], 2, ',', '.'));

			foreach($entries as $entry){
				$sv = $view->showSubView('row');

				$sv->addValue('id', $entry->getId());
				$sv->addValue('date', $entry->getDate()->format('d. F Y'));
				$sv->addValue('type', ($entry->getBrutto() <= 0)?'out':'in');
				$sv->addValue('amount', number_format($entry->getBrutto(), 2, ',', '.'));
				$sv->addValue('state', $entry->getState());
				$sv->addValue('notes', $entry->getNotes());
				
				if($entry->getTaxValue() > 0){
					$svt = $sv->showSubView('taxinfo');
					$svt->addValue('tax_label', $entry->getTaxType());
					$svt->addValue('tax_amount', number_format($entry->getTaxAmount(), 2, ',', '.'));
				}
				
				$contacts = $entry->getContacts();
				if(count($contacts) > 0){
					foreach($contacts as $contact){
						$svc = $sv->showSubView('contact');
						$svc->addValue('firstname', $contact->getFirstName());
						$svc->addValue('lastname', $contact->getLastName());
						$svc->addValue('id', $contact->getId());
						$svc->addValue('image', urlencode(($contact->getImage() == '')?$this->sp->ref('Contacts')->settings->default_image:$this->sp->ref('Contacts')->settings->image_folder.$contact->getImage()));
					}
				}
				
				//check if invoice
				$inv = Invoice::getInvoicesForEntry($entry->getId());
				if($inv && count($inv) > 0){
					$isv = $sv->showSubView('pdf');
					$isv->addValue('id', $inv[0]->getId());
				}
				
				$att = Attachment::getAttachmentsForEntry($entry->getId());
				if(count($att) > 0){
					$av = $sv->showSubView('attachments');
					$av->addValue('id', $entry->getId());
					foreach($att as $a){
						$aiv = $av->showSubView('item');
						
						$aiv->addValue('url', $a->getFile());
						$aiv->addValue('thumb', $GLOBALS['to_root'].'_templates/'.$this->sp->tpl->getTemplate().'/img/attachment_dummy.png');
					}
				}
					
			}
			
			return $view->render();
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getLoggedInUser();
			$view = new core\Template\ViewDescriptor('_services/Bookie/entry_form');
			if($user && isset($args['id'])){
				//edit form
				$entry = Entry::getEntry($args['id']);
				if($entry && $entry->getOwnerId() == $user->getId()){
					$view->addValue('title', 'Eintrag editieren');
					$view->addValue('id', $entry->getId());
					$view->addValue('brutto', str_replace('.', ',',$entry->getBrutto()));
					$view->addValue('netto', str_replace('.', ',',$entry->getNetto()));
					$view->addValue('tax_type', $entry->getTaxType());
					$view->addValue('tax_value', $entry->getTaxValue()*100);
					$view->addValue('date', $entry->getDate()->format('d.m.Y'));
					$view->addValue('notes', $entry->getNotes());
					$view->addValue('state_'.$entry->getState(), ' selected="selected"');
					$view->addValue('type_'.(($entry->getBrutto() >= 0)?'in':'out').'_checked', 'checked');
					
					$contacts = $entry->getContacts();
					$contactRndr = '';
					if(count($contacts) > 0){
						$cv = new core\Template\ViewDescriptor('_services/Contacts/contact_shortlist');
						foreach($contacts as $contact){
							$svc = $cv->showSubView('row');
							$svcn = $svc->showSubView('nameonly');
							$svcn->addValue('firstname', $contact->getFirstName());
							$svcn->addValue('lastname', $contact->getLastName());
							$svc->addValue('action_icon', 'glyphicon glyphicon-remove');
							$svc->addValue('id', $contact->getId());
							$svc->addValue('image', urlencode(($contact->getImage() == '')?$this->sp->ref('Contacts')->settings->default_image:$this->sp->ref('Contacts')->settings->image_folder.$contact->getImage()));
							$contactRndr .= $svc->render();
						}
					}
					
					$view->addValue('contacts', $contactRndr);
					
					
					$inv = Invoice::getInvoicesForEntry($entry->getId());
					if($inv && count($inv) > 0){
						$inv = $inv[0];
						$invv = $view->showSubView('invoice');
						

						$invv->addValue('number', $inv->getNumber());
						$invv->addValue('altdstaddr', $inv->getAltDstAddress());
						$invv->addValue('altsrcaddr', $inv->getAltSrcAddress());
						$invv->addValue('reminder', $inv->getReminderDate()->format('d.m.Y'));
						$invv->addValue('paydate', $inv->getPayDate()->format('d.m.Y'));
						
						$view->addValue('title', 'Rechnung editieren');
					}
					
					return $view->render();
				} else {
					return 'not for you';
				}
			} else {

				if(isset($args['mode']) && $args['mode'] == 'invoice'){
					$view->addValue('title', 'Neue Rechnung');
					$invv = $view->showSubView('invoice');
				
					$invcount = Invoice::getInvoiceCount(new DateTime('01-01-'.date('Y')), new DateTime('31-12-'.date('Y'))) + 1;
					$invv->addValue('number', 'WMR_'.date('Y').'_'.str_pad($invcount, 6, "0", STR_PAD_LEFT));
				} else {
					$view->addValue('title', 'Neuer Eintrag');
				}
				
				return $view->render();
			}
			
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get entry
					$entry = Entry::getEntry($args['id']);
					if(!$entry || $entry->getOwnerId() != $user->getId()) return false;
				} else {
					//create new entry
					$entry = new Entry();
				}
			
				if(isset($args['notes'])) $entry->setNotes($args['notes']);
				if(isset($args['state'])) $entry->setState($args['state']);
				if(isset($args['tax_type'])) $entry->setTaxType($args['tax_type']);
				if(isset($args['tax_value'])) $entry->setTaxValue($args['tax_value']);
				if(isset($args['brutto'])) $entry->setBrutto($args['brutto']);
				if(isset($args['netto'])) $entry->setNetto($args['netto']);
				if(isset($args['date'])) $entry->setDate(new DateTime($args['date']));
				
				if($entry->getOwnerId() < 0) $entry->setOwner($user->getId());
				
				$ok = $entry->save();
				
				if($ok && isset($args['contacts'])) {
					$oldCids = $entry->getContactIds();
					$cids = array();
					//save values
					foreach($args['contacts'] as $cid){
						if(!in_array($cid, $oldCids)){
							$entry->addContact($cid);
						}
						$cids[] = $cid;
					}
						
					//remove old values
					foreach($oldCids as $cid){
						if(!in_array($cid, $cids)){
							$entry->removeContact($cid);
						}
					}
				}
				
				if($ok && isset($args['type']) && $args['type'] == 'invoice'){
					// save invoice stuff
					$inv = Invoice::getInvoicesForEntry($entry->getId());
					if($inv && count($inv) > 0) $inv = $inv[0];
					else $inv = new Invoice($entry->getId());
					
					if(isset($args['altdstaddr'])) $inv->setAltDstAddress($args['altdstaddr']);
					if(isset($args['altsrcaddr'])) $inv->setAltSrcAddress($args['altsrcaddr']);
					if(isset($args['reminder'])) $inv->setReminderDate(new DateTime($args['reminder']));
					if(isset($args['paydate'])) $inv->setPayDate(new DateTime($args['paydate']));
					if(isset($args['number'])) $inv->setNumber($args['number']);
					
					$inv->save();
				}
				
				return $ok;
			} else {
				return false;
			}
		}
		
		private function handleAddAttachment($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id']) && isset($args['file'])){
					//get entry
					$entry = Entry::getEntry($args['id']);
					if(!$entry || $entry->getOwnerId() != $user->getId()) return 'no right';
					
					if(file_exists($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'])){
						//move file from upload folder to image folder
						if(rename($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'], $GLOBALS['config']['root'].$this->settings->attachment_folder.$args['file'])){
							$ao = new Attachment($entry->getId());
							$ao->setFile($args['file']);
							if($ao->save()){
								return $ao->getId();
							} else {
								return false;
							}
						}
					} 
					
					return false;
										
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		private function handleRemoveAttachment($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['aid'])){
					//get attachment
					$att = Attachment::getAttachment($args['aid']);
					if(!$att) return true;
					//get entry
					$entry = $att->getEntry();
					if(!$entry || $entry->getOwnerId() != $user->getId()) return false;
						
					$this->sp->fh->deleteFile($GLOBALS['config']['root'].$this->settings->attachment_folder.$att->getFile());
					return $att->delete();
		
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		
		private function handleDelete($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get contact
					$entry = Entry::getEntry($args['id']);
					if(!entry) return true;
					if($entry->getOwnerId() != $user->getId()) return false;
					$ok = $entry->delete();
					if($ok) {
						$invoices = Invoice::getInvoicesForEntry($entry->getId());
						foreach ($invoices as $inv){
							$inv->delete();
						}
						
						$att = Attachment::getAttachmentsForEntry($entry->getId());
						if(count($att) > 0){
							foreach($att as $a){
								$this->sp->fh->deleteFile($GLOBALS['config']['root'].$this->settings->attachment_folder.$a->getFile());
								$a->delete();
							}
						}
					}
					
					return $ok;
				}
			}
			return false;
		}
		
	}
?>