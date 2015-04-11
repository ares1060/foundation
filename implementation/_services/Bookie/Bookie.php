<?php
	use at\foundation\core\ServiceProvider;

	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Entry.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Invoice.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/InvoicePart.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Receipt.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Category.php');


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
				case 'do.delete': return $this->handleDelete($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		public static function buildWhereSQLQuery($args){
			$sp = ServiceProvider::getInstance();
			$user = $sp->user->getSuperUserForLoggedInUser();
			
			$whereSQL = 'LEFT JOIN '.$sp->db->prefix.'bookie_invoices inv ON e.id = inv.entry_id WHERE e.user_id = \''.$user->getId().'\' AND e.deleted != \'1\'';
			$tags = false;
			$contacts = false;
				
			if(isset($args['search']) && strlen($args['search']) > 2){
				$args['search'] = $sp->db->escape($args['search']);
			
				//TODO tagging still hacky
				$whereSQL = 'LEFT JOIN '.$sp->db->prefix.'tag_links tl ON e.id = tl.param AND tl.service = \'Bookie\' LEFT JOIN '.$sp->db->prefix.'tags t ON t.id = tl.tag_id '.$whereSQL;
				//$whereSQL = 'LEFT JOIN '.$sp->db->prefix.'bookie_categories c ON e.category_id = c.id '.$whereSQL;
				$whereSQL .= ' AND (`notes` LIKE \'%'.$args['search'].'%\' OR t.name LIKE \''.$args['search'].'%\' )';
				$tags = true;
			}
				
			if(isset($args['state']) && is_array($args['state']) && count($args['state']) > 0){
				$values = '';
				foreach($args['state'] as $val){
					$values .= '\''.$sp->db->escape($val).'\',';
				}
				$values = substr($values, 0, -1);
				$whereSQL .= ' AND `state` IN ('.$values.')';
			}
				
			if(isset($args['account']) && is_array($args['account']) && count($args['account']) > 0){
				$values = '';
				foreach($args['account'] as $val){
					$values .= '\''.$sp->db->escape($val).'\',';
				}
				$values = substr($values, 0, -1);
				$whereSQL .= ' AND `account_id` IN ('.$values.')';
			}
				
			if(isset($args['category'])){
				$whereSQL .= ' AND `category_id` = \''.$sp->db->escape($args['category']).'\'';
			}
				
				
				
			$from = false;
			if(isset($args['amount_from']) && $args['amount_from'] != ''){
				$from = $sp->db->escape($args['amount_from']);
			}
				
			$to = false;
			if(isset($args['amount_to']) && $args['amount_to'] != ''){
				$to = $sp->db->escape($args['amount_to']);
			}
				
			if($from !== false && $to !== false){
				if($from > $to) {
					$tmp = $from;
					$from = $to;
					$to = $tmp;
				}
			}
				
			if($from !== false){
				$whereSQL .= ' AND `brutto` >= '.$from;
			}
				
			if($to !== false){
				$whereSQL .= ' AND `brutto` <= '.$to;
			}
				
			if(!isset($args['date_from']) && isset($args['mode']) && $args['mode'] == 'wrapped') $args['date_from'] = date('Y').'-01-01';
				
			if(isset($args['date_from']) && $args['date_from'] != ''){
				$args['date_from'] = $sp->db->escape($args['date_from']);
				$whereSQL .= ' AND ((`date` >= \''.$args['date_from'].'\' AND (inv.pay_date IS NULL OR inv.pay_date = \'0000-00-00\')) OR (inv.pay_date >= \''.$args['date_from'].'\' AND inv.pay_date != \'0000-00-00\'))';
			}
			
			if(isset($args['date_to']) && $args['date_to'] != ''){
				$args['date_to'] = $sp->db->escape($args['date_to']);
				$whereSQL .= ' AND ((`date` <= \''.$args['date_to'].'\' AND (inv.pay_date IS NULL OR inv.pay_date = \'0000-00-00\')) OR (inv.pay_date <= \''.$args['date_to'].'\' AND inv.pay_date != \'0000-00-00\'))';
			}
				
			//TODO: generalize this so that services can infuse filter criteria generically
			if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0){
				$values = '';
				foreach($args['contact_filter'] as $val){
					$values .= $sp->db->escape($val).',';
				}
				$values = substr($values, 0, -1);
				$whereSQL = 'JOIN '.$sp->db->prefix.'bookie_entries_contacts AS c ON c.entry_id = e.id '.$whereSQL;
				$whereSQL .= ' AND contact_id IN ('.$values.')';
				$contacts = true;
			}
				
			if($tags || $contacts) $whereSQL.= ' GROUP BY e.id';
				
			$whereSQL .= ' ORDER BY e.date DESC, e.id DESC';
			
			return $whereSQL;
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			
			$whereSQL = self::buildWhereSQLQuery($args);

			$from = 0;
			if(isset($args['from']) && $args['from'] > 0) {
				$from = $this->sp->db->escape($args['from']);
			}
			
			$rows = -1;
			if(isset($args['rows']) && $args['rows'] >= 0){
				$rows = $this->sp->db->escape($args['rows']);
			}
			
			$entries = Entry::getEntries($whereSQL, $from, $rows);

			if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0) $contacts = true;
			else $contacts = false;
			
			$lastYear = new DateTime();
			$lastMonth = $this->clamp($lastYear->format('m') - 1, 1, 12, true);
			$lastQuarter = $this->clamp($this->getQuarter($lastYear) - 1, 1, 4, true);
			$lastYear = $lastYear->format('Y') - 1;
			
			$view = new core\Template\ViewDescriptor('_services/Bookie/entry_list');	
			if($rows < 0) $rows = count($entries);
			$pages =  ceil(Entry::getEntryCount($whereSQL) / $rows);

			$view->addValue('pages', $pages);
			if(isset($args['mode']) && $args['mode'] == 'wrapped'){
				$header = $view->showSubView('header');
				if($contacts) $header->showSubView('filter_contacts');
				$header->addValue('last_year', $lastYear);
				$header->addValue('llast_year', $lastYear-1);
				$header->addValue('lllast_year', $lastYear-2);
				$header->addValue('last_quarter', $lastQuarter);
				$header->addValue('llast_quarter', $this->clamp($lastQuarter - 1, 1, 4, true));
				$header->addValue('lllast_quarter', $this->clamp($lastQuarter - 2, 1, 4, true));
				$header->addValue('llllast_quarter', $this->clamp($lastQuarter - 3, 1, 4, true));
				$header->addValue('last_month', $lastMonth);
				$header->addValue('llast_month', $this->clamp($lastMonth - 1, 1, 12, true));
				$header->addValue('lllast_month', $this->clamp($lastMonth - 2, 1, 12, true));
				$months = array('Dezember', 'J&auml;nner', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
				$header->addValue('last_month_name', $months[$lastMonth]);
				$header->addValue('llast_month_name', $months[$this->clamp($lastMonth - 1, 1, 12, true)]);
				$header->addValue('lllast_month_name', $months[$this->clamp($lastMonth - 2, 1, 12, true)]);
				$header->addValue('last_month_year', ($lastMonth==12)?$lastYear:$lastYear+1);
				$header->addValue('llast_month_year', ($lastMonth > 1)?$lastYear+1:$lastYear);
				$header->addValue('lllast_month_year', ($lastMonth > 2)?$lastYear+1:$lastYear);
				$header->addValue('last_quarter_year', ($lastQuarter==4)?$lastYear:$lastYear+1);
				$header->addValue('llast_quarter_year', ($lastQuarter > 1)?$lastYear+1:$lastYear);
				$header->addValue('lllast_quarter_year', ($lastQuarter > 2)?$lastYear+1:$lastYear);
				$header->addValue('llllast_quarter_year', ($lastQuarter > 3)?$lastYear+1:$lastYear);
				$header->addValue('filter_preset_date_from', '01.01.'.date('Y'));
				
				
				$cats = Category::getCategories('ORDER BY c.name ASC');
				if($cats){
					foreach($cats as $cat){
						$cv = $header->showSubView('category_option');
						$cv->addValue('id', $cat->getId());
						$cv->addValue('name', $cat->getName());
					}
				}
				
				
				$footer = $view->showSubView('footer');
				$footer->addValue('current_page', '0');
				$footer->addValue('entries_per_page', $rows);
				$footer->addValue('pages', $pages);
			}
			
			$totals = Entry::getEntrySums($whereSQL);
			
			$subValuesView = $view->showSubView('sub_values'.($user->getUserData()->opt('set.taxes', false, false)->getValue()?'':'_notax'));
			
			$subValuesView->addValue('total_in', number_format($totals['brutto_in'], 2, ',', '.'));
			$subValuesView->addValue('total_out', number_format($totals['brutto_out'], 2, ',', '.'));
			
			$subValuesView->addValue('netto_in', number_format($totals['netto_in'], 2, ',', '.'));
			$subValuesView->addValue('netto_out', number_format($totals['netto_out'], 2, ',', '.'));
			
			$subValuesView->addValue('tax_in', number_format($totals['brutto_in'] - $totals['netto_in'], 2, ',', '.'));
			$subValuesView->addValue('tax_out', number_format($totals['brutto_out'] - $totals['netto_out'], 2, ',', '.'));
			
			$tv = $totals['brutto_out'] + $totals['brutto_in'];
			$view->addValue('total_class', ($tv < 0)?'out':'in');
			$view->addValue('total', number_format($tv, 2, ',', '.'));

			//cash sum
			$sum = Entry::getEntrySums('WHERE e.user_id = \''.$user->getId().'\' AND e.deleted = \'\' AND e.account_id = \'2\'');
			if($sum['brutto_in'] + $sum['brutto_out'] < 0) $view->showSubView('cashwarning');
			
			foreach($entries as $entry){ /* @var $entry Entry */
				$sv = $view->showSubView('row');
				
				$sv->addValue('id', $entry->getId());
				$sv->addValue('account', ($entry->getAccount())?$entry->getAccount()->getName():'NULL');
				$sv->addValue('date', $this->sp->txtfun->fixDateLoc($entry->getDate()->format('d. F Y')));
				$sv->addValue('type', ($entry->getBrutto() <= 0)?'out':'in');
				$sv->addValue('amount', number_format($entry->getBrutto(), 2, ',', '.'));
				$sv->addValue('state', $entry->getState());
				$sv->addValue('include', ($entry->getInclude())?'':'moot');
				$sv->addValue('notes', nl2br($entry->getNotes()));
				
				if($entry->getTaxValue() > 0 && $entry->getTaxCountry() == 0){
					$svt = $sv->showSubView('taxinfo');
					$svt->addValue('tax_label', ($entry->getTaxType() == 'Umsatzsteuer')?'USt.':(($entry->getTaxType() == 'Vorsteuer')?'VSt.':$entry->getTaxType()));
					$svt->addValue('tax_amount', number_format($entry->getTaxAmount(), 2, ',', '.'));
				}
				
				if($entry->getBrutto() <= 0 && $entry->getCategoryId() > 0){
					$csv = $sv->showSubView('category');
					$csv->addValue('category', $entry->getCategory()->getName());
				}
				
				//check if invoice
				$inv = Invoice::getInvoicesForEntry($entry->getId());
				if($inv && count($inv) > 0){
					$sv->hideSubView('action_delete');
					$inv = $inv[0];
					$isv = $sv->showSubView('pdf');
					$isv->addValue('id', $inv->getId());
					
					$iidsv = $sv->showSubView('invoice_id');
					$iidsv->addValue('invoice_id', $inv->getNumber());
					
					//check for dunnings
					if($entry->getState() != 'payed'){
						$now = new DateTime();
						$diff = $now->diff($entry->getDate())->days;
						if($diff > max(1, $user->getUserData()->opt('set.dunning_interval', 14)->getValue())){
							$dc = floor($diff / max(1, $user->getUserData()->opt('set.dunning_interval', 14)->getValue()));
							if($inv->getDunningCount() < $dc){
								//its a new dunning
								$dv = $sv->showSubView('dunning');
								$dv->addValue('id', $inv->getId());
								$dv->addValue('count', $inv->getDunningCount() + 1);
								$sv->addValue('state', 'delayed');
								$entry->setState('delayed');
								$entry->save();
							} else {
								$dv = $sv->showSubView('dunning_small');
								$dv->addValue('id', $inv->getId());
								$dv->addValue('count', $inv->getDunningCount());
							}
						}
					}
				} else {
					//if($entry->getAccountId() == 2 && $entry->getDate()->diff(new DateTime())->days != 0) $sv->hideSubView('action_delete');
					$sv->showSubView('action_delete');
				}
					
			}
			
			return $view->render();
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			$view = new core\Template\ViewDescriptor('_services/Bookie/entry_form');
			$view->addValue('useruid', $user->getUserData()->opt('set.uid', '', false)->getValue());
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
					$view->addValue('include', $entry->getInclude()?'checked="checked"':'');
					$view->addValue('state_'.$entry->getState(), ' selected="selected"');
					
					$inv = Invoice::getInvoicesForEntry($entry->getId());
					if($inv && count($inv) > 0){
						$inv = $inv[0];
						$invv = $view->showSubView('invoice');
						

						$invv->addValue('number', $inv->getNumber());
						$invv->addValue('altdstaddr', $inv->getAltDstAddress());
						$invv->addValue('altsrcaddr', $inv->getAltSrcAddress());
						if($inv->getReminderDate()) $invv->addValue('reminder', $inv->getReminderDate()->format('d.m.Y H:i'));
						if($inv->getPayDate()) $invv->addValue('paydate', $inv->getPayDate()->format('d.m.Y'));
						
						$view->addValue('title', 'Rechnung editieren');
						
						//handle invoice parts
						$parts = InvoicePart::getPartsForInvoice($inv->getId());
						$num = 0;
						foreach($parts as $part){
							$siv = $view->showSubView('invoice_item');
							$siv->addValue('amount', str_replace('.', ',', ($part->getTaxValue() > 0 && $part->getNetto() != 0) ? $part->getNetto() : $part->getBrutto()));
							$siv->addValue('tax_value',  ($part->getTaxValue() != 0 && $part->getNetto() != 0) ? $part->getTaxValue() * 100 : '-1');
							$siv->addValue('notes', $part->getNotes());
							$siv->addValue('dom_id', 'invoice_item_'.$part->getid());
							$siv->addValue('number', ++$num);
						}
						
						$tv = $view->showSubView('entry_type_in');
						$tv->addValue('include', $entry->getInclude()?'checked="checked"':'');
						
						$view->showSubView('add_invoice_item');
						
						$iviv = $view->showSubView('invoice_item');
						$iviv->addValue('dom_id', 'empty_invoice_item');
						
						if($user->getUserData()->opt('set.taxes', '0')->getValue() == '1' || $user->getUserData()->opt('set.uid', '')->getValue() != '') $tax = $view->showSubView('tax_input');
						
					} else {
						$sel = $view->showSubView('entry_type_selection');
						$sel->addValue('type_'.(($entry->getBrutto() >= 0)?'in':'out').'_checked', 'checked');
						$sel->addValue('include', $entry->getInclude()?'checked="checked"':'');
						
						//if($user->getUserData()->opt('set.taxes', '0')->getValue() == '1') $tax = $view->showSubView('tax_input');
						$tax = $view->showSubView('tax_input');
						
						if($entry->getProjectedDisposal()) $view->addValue('projected_disposal', $entry->getDate()->diff($entry->getProjectedDisposal())->y);
						if($entry->getDisposal()) $view->addValue('disposal', $entry->getDisposal()->format('d.m.Y'));
					}
					
					if($tax){
						$tax->addValue('netto', str_replace('.', ',',$entry->getNetto()));
						$tax->addValue('uid', $entry->getUID());
						$tax->addValue('tax_type', $entry->getTaxType());
						$tax->addValue('tax_value', $entry->getTaxValue()*100);
						$tax->addValue('tax_country_'.$entry->getTaxCountry(), ' selected="selected"');
					}
					
					$accs = Account::getAccountsForUser($user->getId());
					if($accs){
						foreach($accs as $acc){
							$av = $view->showSubView('account_option');
							$av->addValue('id', $acc->getId());
							$av->addValue('name', $acc->getName());
							if($acc->getId() == $entry->getAccountId()) $av->addValue('selected', ' selected="selected"');
						}
					}
					
					$cats = Category::getCategories('ORDER BY c.name ASC');
					if($cats){
						foreach($cats as $cat){
							$cv = $view->showSubView('category_option');
							$cv->addValue('id', $cat->getId());
							$cv->addValue('name', $cat->getName());
							if($cat->getId() == $entry->getCategoryId()) $cv->addValue('selected', ' selected="selected"');
						}
					}
					
					return $view->render();
				} else {
					return 'not for you';
				}
			} else {
				
				if(isset($args['mode']) && $args['mode'] == 'invoice'){
					$view->addValue('title', 'Neue Rechnung');
					$invv = $view->showSubView('invoice');
				
					$invcount = Invoice::getInvoiceCount(new DateTime('01-01-'.date('Y')), new DateTime('31-12-'.date('Y')), $user->getId()) + 1;
					$invv->addValue('number', $user->getUserData()->opt('set.invoice_prefix', 'WMR_')->getValue().date('Y').'_'.str_pad($invcount, 6, "0", STR_PAD_LEFT));
					
					$view->showSubView('add_invoice_item');
					$iviv = $view->showSubView('invoice_item');
					$iviv->addValue('dom_id', 'empty_invoice_item');
					
					$tv = $view->showSubView('entry_type_in');
					$tv->addValue('include', 'checked="checked"');
					if($user->getUserData()->opt('set.taxes', '0')->getValue() == '1') {
						$tax = $view->showSubView('tax_input');
						$tax->addValue('tax_type', 'Umsatzsteuer');
						$tax->addValue('tax_value', 20);
					} else if($user->getUserData()->opt('set.uid', '')->getValue() != '') {
						$tax = $view->showSubView('tax_input');
						$tax->addValue('tax_type', '');
						$tax->addValue('tax_value', 0);
					}
				} else {
					$view->addValue('title', 'Neuer Eintrag');
					$tv = $view->showSubView('entry_type_selection');
					$tv->addValue('include', 'checked="checked"');
					if($user->getUserData()->opt('set.taxes', '0')->getValue() == '1') {
						$tax = $view->showSubView('tax_input');
						$tax->addValue('tax_type', 'Vorsteuer');
						$tax->addValue('tax_value', 20);
					} else if($user->getUserData()->opt('set.uid', '')->getValue() != '') {
						$tax = $view->showSubView('tax_input');
						$tax->addValue('tax_type', '');
						$tax->addValue('tax_value', 0);
					}
					$view->addValue('state_payed', ' selected="selected"');
				}
				
				$view->addValue('date', date('d.m.Y'));
				
				$accs = Account::getAccountsForUser($user->getId());
				if($accs){
					foreach($accs as $acc){
						$av = $view->showSubView('account_option');
						$av->addValue('id', $acc->getId());
						$av->addValue('name', $acc->getName());
						if($acc->getId() == $user->getUserData()->opt('set.default_account','1')->getValue()) $av->addValue('selected', ' selected="selected"');
					}
				}
				
				$cats = Category::getCategories('ORDER BY c.name ASC');
				if($cats){
					foreach($cats as $cat){
						$cv = $view->showSubView('category_option');
						$cv->addValue('id', $cat->getId());
						$cv->addValue('name', $cat->getName());
					}
				}
				
				return $view->render();
			}
			
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
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
				
				/*if($entry->getAccountId() == 2 && $entry->getDate()->diff(new DateTime())->days != 0){
					//can't edit old cash entry
					$entry->save();
					return $entry->getId(); 
				}*/
				
				if(isset($args['date']) /*&& $entry->getAccountId() != 2*/) $entry->setDate(new DateTime($args['date']));
				if(isset($args['include'])) $entry->setInclude($args['include']);
				if(isset($args['brutto'])) $entry->setBrutto($args['brutto']);
				if(isset($args['category']) && $entry->getBrutto() < 0) $entry->setCategory($args['category']);
				
				if(isset($args['account'])){
					$acc = Account::getAccount($args['account']);
					if($acc->getOwnerID() == -1 || $acc->getOwnerID() == $user->getId()) $entry->setAccount($args['account']);
				}
				
				
				//Eigenes Personal - 14
				//Versicherung - 5
				//PKW - 6
				//Reisekosten Inland - 8
				//Zinsen und ähnliche Aufwendungen - 9
				//Spenden und Trinkgelder - 12
				//Eigene Pflichtversicherungseiträge - 10
				//Reisekosten Ausland - 19
				//Anschaffung PKW - 20
				//Privatentnahme - 21
				
				if($user->getUserData()->opt('set.taxes', '0')->getValue() == '1' || $user->getUserData()->opt('set.uid', '')->getValue() != ''){ 
					if(!in_array($entry->getCategoryId(), array(5,6,8,9,10,12,14,19,20,21))){
						if(isset($args['tax_country'])) $entry->setTaxCountry($args['tax_country']);
						if($entry->getTaxCountry() == 0){
							if(isset($args['tax_type'])) $entry->setTaxType($args['tax_type']);
							if(isset($args['tax_value'])) $entry->setTaxValue($args['tax_value']);
						} else if ($entry->getTaxCountry() == 1 && $entry->getBrutto() < 0) { 
							//set tax to 20% if EU and buy and tax_value is 0
							$entry->setTaxType(($entry->getBrutto() > 0)?'Umsatzsteuer':'Vorsteuer');
							$entry->setTaxValue(($args['tax_value'] <= 0)?0.2:$args['tax_value']);
						} else {
							$entry->setTaxType('');
							$entry->setTaxValue(0);
						}
						if(isset($args['tax_uid'])) $entry->setUID($args['tax_uid']);
						
						//check if entry tax configuration is valid
						if($entry->getBrutto() > 0){ //income
							if($entry->getUID() == '' && ($entry->getTaxCountry() > 0 || $entry->getBrutto() > 10000)) return false; //if the income is from outside the home country or > 10000 an UID is mandatory 
						}
						
						
					} else if ($entry->getCategoryId() == 8 && $user->getUserData()->opt('set.taxes', '0')->getValue() == '1') { //Reisekosten Inland
						$entry->setTaxType('Vorsteuer');
						$entry->setTaxValue(0.1);
					} else {
						$entry->setTaxType('');
						$entry->setTaxValue(0);
					}
				} else {
					$entry->setTaxType('');
					$entry->setTaxValue(0);
				}
				
				$entry->recalcNetto();
				
				
				if(isset($args['projected_disposal'])) {
					$years = max(3, $args['projected_disposal']);
					if($entry->getCategoryId() == 20) $years = 8;
					$pd = new DateTime($args['date']);
					$entry->setProjectedDisposal($pd->add(new DateInterval('P'.$years.'Y')));
				}
				if(isset($args['disposal'])) $entry->setDisposal(new DateTime($args['disposal']));
				
				//check category and disposal
				if(($entry->getCategoryId() == 1 || $entry->getCategoryId() == 20) && $entry->getNetto() < -400 && $entry->getProjectedDisposal() == null) return false;
				
				if($entry->getOwnerId() < 0) $entry->setOwner($user->getId());
				
				$ok = $entry->save();
				
				if($ok && isset($args['type']) && $args['type'] == 'invoice'){
					// save invoice stuff
					$inv = Invoice::getInvoicesForEntry($entry->getId());
					if($inv && count($inv) > 0) $inv = $inv[0];
					else if(!isset($args['id'])) $inv = new Invoice($entry->getId());
					else $inv = null;
					
					$invcount = Invoice::getInvoiceCount(new DateTime('01-01-'.date('Y')), new DateTime('31-12-'.date('Y')), $user->getId()) + 1;
					
					if($inv){
						if(isset($args['altdstaddr'])) $inv->setAltDstAddress($args['altdstaddr']);
						if(isset($args['altsrcaddr'])) $inv->setAltSrcAddress($args['altsrcaddr']);
						//if(isset($args['reminder'])) $inv->setReminderDate(new DateTime(($args['reminder'] == '')?'00-00-0000 00:00:00':$args['reminder']));
						if(isset($args['paydate'])) $inv->setPayDate(($args['paydate'] == '')?null:new DateTime($args['paydate']));
						//if(isset($args['number'])) $inv->setNumber($args['number']);
						if($inv->getNumber() == '') $inv->setNumber($user->getUserData()->opt('set.invoice_prefix', 'WMR_')->getValue().date('Y').'_'.str_pad($invcount, 6, "0", STR_PAD_LEFT));
						
						$inv->save();
						$nettoSum = 0;
						
						//handle invoice parts
						if(isset($args['parts'])){
							$oldParts = InvoicePart::getPartsForInvoice($inv->getId());
							foreach($args['parts'] as $part){
								$netto = $part['amount'];
								$brutto = $part['amount'];
								if($part['tax_value'] > 0) {
									$brutto = $part['amount'] * (1 + $part['tax_value']);
								} else {
									$netto = $part['amount'] / (1 + $entry->getTaxValue());
								}
								$nettoSum += $netto;
								$ip = new InvoicePart($inv->getId(), null, $part['notes'], $brutto, $netto, $part['tax_value']);
								$ip->save();
							}
							
							foreach($oldParts as $opart){
								$opart->delete();
							}
						} else {
							InvoicePart::deletePartsForInvoice($inv->getId());
						}
						$entry->setNetto($nettoSum);
						$entry->save();
					}
				}
				
				$this->logAction('save', $entry->data());
				
				if($ok) return $entry->getId();
				else return false;
			} else {
				return false;
			}
		}
		
		private function handleDelete($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get contact
					$entry = Entry::getEntry($args['id']);
					if(!$entry) return true;
					if($entry->getOwnerId() != $user->getId()) return false;
					//if($entry->getAccountId() == 2 && $entry->getDate()->diff(new DateTime())->days != 0) return false; //can't delete cash entry
					//if($entry->getBrutto() >= 0 && count(Invoice::getInvoicesForEntry($entry->getId())) > 0) return false; //can't delete invoice 
					$entry->setDeleted(true);
					/*$ok = $entry->delete();
					if($ok) {
						$invoices = Invoice::getInvoicesForEntry($entry->getId());
						foreach ($invoices as $inv){
							$inv->delete();
						}
					}*/
					
					$this->logAction('delete', $entry->data());
					
					return $entry->save();
				}
			}
			return false;
		}
		
		public function checkAttachmentAuth($param){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
			if($user){
				$entry = Entry::getEntry($param);
				if($entry && $entry->getOwnerId() == $user->getId()) return true;
				else if(!$entry) return true;
			}
			return false;
		}
		
		private function logAction($action, $data){
			$date = new DateTime();
			$fileName = 'user'.$this->sp->user->getLoggedInUser()->getId().'_'.$date->format('Y-m').'.log';
			$file = fopen($GLOBALS['config']['root'].$this->settings->log_folder.$fileName, 'a');
			fwrite($file, "\r\n".$action.' -> '.json_encode($data));
			fclose($file);
		}
		
		private function getQuarter($date){
			$q = ceil($date->format('m') / 3);
			return $q;
		}
		
		private function clamp($number, $min, $max, $overlap){
		    $diff = $max - $min + 1;
		    
		    if($number > $max) {
		        if(!$overlap) $number = $max;
		        else $number = $min + ($number - $min) % $diff;
		    } else if($number < $min) {
		        if(!$overlap) $number = $min;
		        else $number = abs(($max - $number) % $diff - $max);
		    }
		    
		    return $number;
		}
		
	}
?>