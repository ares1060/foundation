<?php
	require_once($GLOBALS['config']['root'].'_services/Bookie/model/Attachment.php');

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
				default: return 'mooh!'; break;
			}
		}
		
		private function handleViewCount($args){
			$user = $this->sp->user->getLoggedInUser();
							
			//TODO: get auth
			return Attachment::getAttachmentCount($args['service'], $args['param']);
		}
		
		private function handleViewList($args){
			$user = $this->sp->user->getLoggedInUser();
							
			//TODO: get auth
			$att = Attachment::getAttachments($args['service'], $args['param']);
			if(count($att) > 0){
				foreach($att as $a){
					$aiv = $view->showSubView('item');
					
					$aiv->addValue('url', $a->getFile());
					$ext = explode('.', $a->getFile());
					$ext = array_pop($ext);
					$aiv->addValue('thumb', urlencode(($ext == 'jpg' || $ext == 'png' || $ext == 'gif')?$GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$a->getFile():$this->sp->tpl->getTemplateDir().'/img/attachment_dummy.png'));
				}
			}
			
			return $view->render();
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getLoggedInUser();

			
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				
				
				
			} else {
				return false;
			}
		}
		
		private function handleAddAttachment($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['service']) && isset($args['param']) && isset($args['file'])){
					
					//TODO: get auth
					
					if(file_exists($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'])){
						//move file from upload folder to image folder
						if(rename($GLOBALS['config']['root'].$this->settings->upload_folder.$args['file'], $GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$args['file'])){
							$ao = new Attachment($args['service'], $args['param']);
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
					
					//TODO: check auth
						
					$this->sp->fh->deleteFile($GLOBALS['config']['root'].$this->settings->attachment_folder.(($this->settings->service_subdir == 1)?$att->getService().'/':'').$att->getFile());
					return $att->delete();
		
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
	}
?>