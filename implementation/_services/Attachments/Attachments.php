<?php
	require_once($GLOBALS['config']['root'].'_services/Attachments/model/Attachment.php');

	use at\foundation\core;
	
	class Attachments extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->name = 'Attachments';
			$this->ini_file = $GLOBALS['to_root'].'_services/Attachments/Attachments.ini';	
			parent::__construct();
        }
		
		public function render($args){
			if(isset($args['action'])) $action = $args['action'];
			switch($action){
				case 'view.list': return $this->handleViewList($args); break;
				case 'view.count': return $this->handleViewCount($args); break;
				case 'view.linker': return $this->handleViewLinker($args); break;
				case 'do.save_attachments': return $this->handleSave($args); break;
				case 'do.add_attachment': return $this->handleAddAttachment($args); break;
				case 'do.remove_attachment': return $this->handleRemoveAttachment($args); break;
				case 'do.remove_all_attachments': return $this->handleRemoveAllAttachments($args); break;
				default: return 'mooh!'; break;
			}
		}
		
		private function checkAuth($service='', $param=''){
			if($service != ''){
				$serv = $this->sp->ref($service);
				if($serv && method_exists($serv, 'checkAttachmentAuth')){
					return $serv->checkAttachmentAuth($param);
				} else {
					return true;
				}
			} else {
				return true;
			}
		}
		
		private function handleViewCount($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();
							
			if($this->checkAuth($args['service'], $args['param'])){
				return Attachment::getAttachmentCount($args['service'], $args['param']);
			} else {
				return 0;
			}
		}
		
		private function handleViewList($args){				
			$view = new core\Template\ViewDescriptor('_services/Attachments/attachment_list');
			$view->addValue('param', $args['param']);
			if($this->checkAuth($args['service'], $args['param'])){
				$att = Attachment::getAttachments($args['service'], $args['param']);
				if(count($att) > 0){
					foreach($att as $a){
						$aiv = $view->showSubView('item');
						
						$aiv->addValue('id', $a->getId());
						$aiv->addValue('url', $a->getFile());
						$aiv->addValue('file_name', ($a->getFileName() != '')?$a->getFileName():$a->getFile());
						$ext = explode('.', $a->getFile());
						$ext = array_pop($ext);
						$aiv->addValue('thumb', urlencode(($ext == 'jpg' || $ext == 'png' || $ext == 'gif')?$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$a->getFile():$this->sp->tpl->getTemplateDir().'/img/attachment_dummy.png'));
					}
				}
			}
			if(isset($args['mode']) && $args['mode'] == 'full') {
				$header = $view->showSubView('header');
				$header->addValue('service', $args['service']);
				$header->addValue('param', $args['param']);
				
				$footer = $view->showSubView('footer');
				$footer->addValue('service', $args['service']);
				$footer->addValue('param', $args['param']);
			}
			return $view->render();
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getSuperUserForLoggedInUser();

			
		}
		
		private function handleSave($args){
			if($this->checkAuth($args['service'], $args['param'])){
				
				
				
			} else {
				return false;
			}
		}
		
		private function handleAddAttachment($args){
			if(isset($args['service']) && isset($args['param']) && isset($args['file'])){
				if($this->checkAuth($args['service'], $args['param'])){
					if(file_exists($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'])){
						//move file from upload folder to image folder
						if(rename($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'], $GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$args['file'])){
							$ao = new Attachment($args['service'], $args['param']);
							$ao->setFile($args['file']);
							$ao->setFileName(isset($args['file_name'])?$args['file_name']:$args['file']);
							if($ao->save()){
								return $ao->getId();
							}
						}
					} 
				}
				
				return false;
									
			} else {
				return false;
			}
		}
		
		private function handleRemoveAttachment($args){
			if(isset($args['aid'])){
				//get attachment
				$att = Attachment::getAttachment($args['aid']);
				if(!$att) return true;
				
				if($this->checkAuth($att->getService(), $att->getParam())){
					$this->sp->fh->deleteFile($GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$att->getFile());
					return $att->delete();
				}
	
			}
			
			return false;
		}
		
		private function handleRemoveAllAttachments($args){
			if(isset($args['service']) && isset($args['param'])){
				if($this->checkAuth($args['service'], $args['param'])){
					$atts = Attachment::getAttachments($args['service'], $args['param']);
					if(!$atts || count($atts) == 0) return true;
					
					$ok = true;
					foreach ($atts as $att){
						$this->sp->fh->deleteFile($GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$att->getFile());
						$ok &= $att->delete();
					}
					
					return $ok;
				}
				return false;
			}
				
			return false;
		}
		
	}
?>