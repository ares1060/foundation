<?php
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Entry.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Invoice.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/InvoicePart.php');
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Receipt.php');


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
				$whereSQL .= ' AND `netto` >= '.$args['amount_from'];
			}
			
			if(isset($args['amount_to']) && $args['amount_to'] != ''){
				$args['amount_to'] = $this->sp->db->escape($args['amount_to']);
				$whereSQL .= ' AND `netto` <= '.$args['amount_to'];
			}
			
			if(isset($args['date_from']) && $args['date_from'] != ''){
				$args['date_from'] = $this->sp->db->escape($args['date_from']);
				$whereSQL .= ' AND `date` >= \''.$args['date_from'].'\'';
			}
				
			if(isset($args['date_to']) && $args['date_to'] != ''){
				$args['date_to'] = $this->sp->db->escape($args['date_to']);
				$whereSQL .= ' AND `date` <= \''.$args['date_to'].'\'';
			}
			
			if(isset($args['dunned'])){
				$whereSQL .= ' AND `dunnings` != \'\'';
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
			
			$view->addValue('total_in', $totals['brutto_in']);
			$view->addValue('total_out', $totals['brutto_out']);
			$view->addValue('total', $totals['brutto_out'] + $totals['brutto_in']);

			foreach($entries as $entry){
				$sv = $view->showSubView('row');

				$sv->addValue('id', $entry->getId());
				$sv->addValue('date', $entry->getDate()->format('d. l Y'));
				$sv->addValue('type', ($entry->getBrutto() <= 0)?'out':'in');
				$sv->addValue('amount', $entry->getBrutto());
				$sv->addValue('state', $entry->getState());
				
				if($entry->getTaxValue() > 0){
					$svt = $sv->showSubView('taxinfo');
					$svt->addValue('tax_label', $entry->getTaxType());
					$svt->addValue('tax_amount', $entry->getTaxAmount());
				}
			}
			
			return $view->render();
		}
		
		private function handleViewForm($args){
			
		}
		
		private function handleSave($args){
			
		}
		
		private function handleDelete($args){
			
		}
		
	}
?>