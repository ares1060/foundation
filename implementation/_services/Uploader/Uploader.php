<?php
	use at\foundation\core;

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
	
	}
?>