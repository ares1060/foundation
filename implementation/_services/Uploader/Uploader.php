<?php
	use at\foundation\core;

	/**
	* @author Immanuel Bauer
	* based on code from Moxiecode Systems AB
	* Released under GPL License.
	**/
	
	class Uploader extends core\AbstractService implements core\IService {
	
		private $name;
		private $value;
		private $label;
		private $id;
		private $max_file_size;
		private $max_uploads;
		private $type;
	
		function __construct(){
			$this->label = '';
			$this->max_uploads = 5;
			$this->max_file_size = 100000;
		}
		
		public function render($args) {	
			$action = '';
			if(isset($args['action'])) $action = $args['action'];	
        	switch($action){
        		case 'do.upload': return $this->handleUpload(); break;
        		case 'view.file_input': return $this->renderFileInput(); break;
        		case 'view.ftp': return $this->renderFTP(); break;
        		default: return $this->renderDialog($args); break;
        	}
		}
		
		private function renderDialog($args){
			$vd = new core\Template\ViewDescriptor('_services/Uploader/upload');
			
			if($this->max_uploads == 1) $vd->removeSubView('upload_more_than_one');
			
			$vd->addValue('id', $this->id);
			$vd->addValue('name', $this->name);
			$vd->addValue('label', $this->label);
			$vd->addValue('type', $this->type);
			$vd->addValue('value', $this->value);
			$vd->addValue('max_file_size', $this->max_file_size);
			$vd->addValue('max_uploads', $this->max_uploads);
				
			return $vd->render();
		}
		
		private function renderFileInput($args){
			$vd = new core\Template\ViewDescriptor('_services/Uploader/input');
			
			if(isset($args['id'])) $vd->addValue('id', $args['id']);
			if(isset($args['label'])) $vd->addValue('label', $args['label']);
			
			return $vd->render();
		}
		
		private function renderFTP($args){
			if(is_dir($GLOBALS['config']['root'].$this->settings->ftp_folder)){
				$count=0;
				$vd = new core\Template\ViewDescriptor('_services/Uploader/ftp');
				if ($handle = opendir($GLOBALS['config']['root'].$this->settings->ftp_folder)) {
					$types = '*';
					if(isset($args['types'])) $types = $args['types'];
					while (false !== ($file = readdir($handle))) {
						if ($file != '.' && $file != '..') {
							if(preg_match("/\." . $types . "$/i", $file)){
								$sv = new SubViewDescriptor('file');
								$sv->addValue('name', $file);
								$tpl->addSubView($sv);
								unset($sv);
								$count++;
							}
						}
					}
					closedir($handle);
				}
				if($count == 0) $vd->showSubView('no_files');
				return $vd->render();
			} else return 'ERR:1';
	
		}
		
		public function handleUpload(){
			//$log = fopen($GLOBALS['config']['root'].'uploadlog.txt', 'a');
		
			// Get parameters
			$chunk = isset($_REQUEST['chunk']) ? $_REQUEST['chunk'] : 0;
			$chunks = isset($_REQUEST['chunks']) ? $_REQUEST['chunks'] : 0;
			$fileName = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
		
			$tmpFolder = $GLOBALS['config']['root'].$this->settings->tmp_folder;
			
			//$headers = apache_request_headers();
			//foreach ($headers as $header => $value) {
			//    fwrite($log, "\r\n".$header.':'.$value);
			//}
		
			//fwrite($log, "\r\nuploading file:".$fileName.'('.$chunk.' of '.$chunks.')');
		
			// Clean the fileName for security reasons
			$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		
			// Make sure the fileName is unique but only if chunking is disabled
			if ($chunks < 2 && file_exists($tmpFolder . $fileName)) {
				$ext = strrpos($fileName, '.');
				$fileName_a = substr($fileName, 0, $ext);
				$fileName_b = substr($fileName, $ext);
		
				$count = 1;
				while (file_exists($tmpFolder . $fileName_a . '_' . $count . $fileName_b))
				$count++;
		
				$fileName = $fileName_a . '_' . $count . $fileName_b;
			}
		
			// Look for the content type header
			if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
			if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];
		
			// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
			if (strpos($contentType, "multipart") !== false) {
				//fwrite($log, "\r\nmode: multipart ".(isset($_FILES['Filedata']['tmp_name'])?'found':'not found'));
				if (isset($_FILES['Filedata']['tmp_name']) && is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
					//fwrite($log, "\r\ncopy data");
		
					// Open temp file
					$out = fopen($tmpFolder . $fileName, $chunk == 1 ? "wb" : "ab");
					if ($out) {
						//fwrite($log, "\r\ntmp found");
						// Read binary input stream and append it to temp file
						$in = fopen($_FILES['Filedata']['tmp_name'], "rb");
		
						if ($in) {
							while ($buff = fread($in, 4096))
							fwrite($out, $buff);
						} else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
						//fwrite($log, "\r\ncopied data");
						fclose($in);
						fclose($out);
						@unlink($_FILES['Filedata']['tmp_name']);
					} else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				} else die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file.'.$_FILES['Filedata']['tmp_name'].'"}, "id" : "id"}');
			} else {
				//fwrite($log, "\r\nmode: stream");
				//fwrite($log, "\r\ndata: ".file_get_contents('php://input'));
				// Open temp file
				$out = fopen($tmpFolder . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen("php://input", "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
						fwrite($out, $buff);
					} else die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
					fclose($in);
					fclose($out);
				} else die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		
			// Return JSON-RPC response
			die('{"jsonrpc" : "2.0", "result" : "null", "id" : "id", "fileName" : "'.$fileName.'"}');
		
		}
		
		public function getUploads() {
			$r = array();
			//print_r($_FILES);
			//print_r($_POST);
			if(isset($_POST['action']) && $_POST['action'] == 'upload'){
				switch($_POST['selected_type']){
					case 'html':
						if(isset($_FILES['files']) && isset($_FILES['files']['name']) && $_FILES['files']['name'][0] != ''){
							for($i=0; $i<count($_FILES['files']['name']) ; $i++){
								switch($_FILES['files']['error'][$i]){
									case 1: //size extends upload_max_filesize directive in php.ini
										$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
										break;
									case 2: //size extends MAX_FILE_SIZE
										$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
										break;
									case 3: //The uploaded file was only partially uploaded.
										$this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_UPLOAD_PARTIALLY')));
										break;
									case 4: // no file uploaded - just debug error
										$this->__($this->_('ERROR_NO_FILE_UPLOADED'), Messages::DEBUG_ERROR);
										break;
									case 6: //Missing a temporary folder
										$this->__($this->_('ERROR_MISSING_TEMP_FOLDER'), Messages::DEBUG_ERROR);
										break;
									case 7: //cant write at disk
										$this->__($this->_('ERROR_CANT_WRITE_DISK'), Messages::DEBUG_ERROR);
										break;
									case 8: //php extension stopped upload
										$this->__($this->_('ERROR_EXTENTION_STOPPED_UPLOAD'), Messages::DEBUG_ERROR);
										break;
									case 0: //ok
										$r[] = array('name'=>$_FILES['files']['name'][$i],
					        								 'tmp_name'=>$_FILES['files']['tmp_name'][$i], 
					        								 'error'=>$_FILES['files']['error'][$i], 
					        								 'size'=>$_FILES['files']['size'][$i],
					        								 'type'=>$_FILES['files']['type'][$i]);
										break;
								}
							}
						}
						break;
					case 'flash':
						if(is_dir($this->config['tmpFolder'])){
							$count=0;
							if ($handle = opendir($this->config['tmpFolder'])) {
								while (false !== ($file = readdir($handle))) {
									if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
										//if(preg_match("/\." . $types . "$/i", $file)){
										$size = filesize($this->config['tmpFolder'].$file);
										$type = pathinfo($this->config['tmpFolder'].$file);
										/*if($size > $_POST['MAX_FILE_SIZE']){			//size extends MAX_FILE_SIZE
										 $this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
										} else {*/
										$count++;
										$r[] = array('name'=>$file,
						        					'tmp_name'=>$this->config['tmpFolder'].$file,
						        					'error'=>0,
						        					'size'=>$size,
						        					'type'=>$type['extension']);
										//}
										//	}
									}
								}
								closedir($handle);
							}
							if($count==0) $this->__($this->_('NO_UPLOADS'));
						}
						break;
					case 'ftp':
						if(is_dir($this->config['ftpFolder'])){
							$count=0;
							if ($handle = opendir($this->config['ftpFolder'])) {
								while (false !== ($file = readdir($handle))) {
									if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
										//if(preg_match("/\." . $types . "$/i", $file)){
										$size = filesize($this->config['ftpFolder'].$file);
										$type = pathinfo($this->config['ftpFolder'].$file);
										/*if($size > $_POST['MAX_FILE_SIZE']){			//size extends MAX_FILE_SIZE
										 $this->__(str_replace('{@pp:file}', $_FILES['files']['name'][$i], $this->_('ERROR_MAX_FILE_SIZE')));
										} else {*/
										$count++;
										$r[] = array('name'=>$file,
										        					'tmp_name'=>$this->config['ftpFolder'].$file,
										        					'error'=>0,
										        					'size'=>$size,
										        					'type'=>$type['extension']);
										//}
										//	}
									}
								}
								closedir($handle);
							}
							if($count==0) $this->__($this->_('NO_UPLOADS'));
						}
						break;
					default:
						$this->__(str_replace('{@pp:service}', $this->name, $this->_('WRONG_PARAMETER', 'core'))); //Error Message Internal Error
					break;
				}
		
			}
			return $r;
		}
	
	}
?>