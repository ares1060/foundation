<?php
	
	namespace at\foundation\core\User\view;
	use at\foundation\core\User\model\UserDataField;

	use at\foundation\core;
	use at\foundation\core\Messages\Messages;
	use at\foundation\core\User\User;
	use at\foundation\core\User\model;
	use at\foundation\core\Template\ViewDescriptor;
	use at\foundation\core\Template\SubViewDescriptor;
	
	class UserAdminView extends core\CoreService{
		protected $name;

		function __construct($settings){
			parent::__construct();
			$this->setSettingsCore($settings);
			$this->name = 'User';
		}
		/* ======   INTERFACE ADMINCENTER ======= */
		/**
		 * returnes renderes Dropdown of all Visible User groups
		 * @param unknown_type $id
		 */
		public function tplGetGroupDropdown($id){
			
			$out = '<select name="group" id="group" class="form-control">';
			
        	$groups = model\UserGroup::getGroups();

        	foreach($groups as $group){
        		if($this->checkRight('administer_group', $group->getId()) || $sel==$group->getId()) $out .= '<option value="'.$group->getId().'" '.(($id==$group->getId())?'selected="selected"':'').'>'.$group->getName().'</option>';
        	}
			
			$out .= '</select>';
        	
        	return $out;
		}
		/**
		 * returnes rendered Status Dropdown
		 * @param unknown_type $status
		 */
		public function tplGetStatusDropdown($status=-1) {

			$out = '<select name="status" id="status" class="form-control">';

        	
        	$out .= '<option value="'.User::STATUS_ACTIVE.'" '.(($status==User::STATUS_ACTIVE)?'selected="selected"':'').'>'.$this->_('_Status: Active', 'core').'</option>';
        	$out .= '<option value="'.User::STATUS_BLOCKED.'" '.(($status==User::STATUS_BLOCKED)?'selected="selected"':'').'>'.$this->_('_Status: Blocked', 'core').'</option>';
        	$out .= '<option value="'.User::STATUS_DELETED.'" '.(($status==User::STATUS_DELETED)?'selected="selected"':'').'>'.$this->_('_Status: Deleted', 'core').'</option>';
        	$out .= '<option value="'.User::STATUS_HAS_TO_ACTIVATE.'" '.(($status==User::STATUS_HAS_TO_ACTIVATE)?'selected="selected"':'').'>'.$this->_('_Status: Has to activate', 'core').'</option>';
			
			$out .= '</select>';
			
			return $out;
        }
		
		/* ======   Template Profile ======= */
		public function tplProfile() {
			if($this->sp->user->isLoggedIn()){
				$view = new ViewDescriptor($this->settings->usercenter_profile);
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		
		public function tplProfileData() {
			if($this->sp->user->isLoggedIn()){
				$view = new ViewDescriptor($this->settings->usercenter_profile_data);
			
				$u = $this->sp->user->getLoggedInUser();

				if($u != null){
					$view->addValue('nick', $u->getNick());
					$view->addValue('email', $u->getEmail());
					
					return $view->render();
				} else return 'error';
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
	
		public function tplProfileNotifications() {
			if($this->sp->user->isLoggedIn()){
				$view = new ViewDescriptor($this->settings->usercenter_profile_notification);
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}	
		}
	
		public function tplProfilePrivacy() {
			if($this->sp->user->isLoggedIn()){
				$view = new ViewDescriptor($this->settings->usercenter_profile_privacy);
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		/* ======   Usercenter ======= */
		public function tplUsercenter() {
			if($this->sp->user->isLoggedIn() && $this->checkRight('usercenter')){
				$view = new ViewDescriptor($this->settings->usercenter_main);
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return $this->_('You are not authorized', 'core');
			}
		}
		/* ======   User ======= */
		public function tplUserList($page=1) {
			if($this->sp->user->isLoggedIn() && $this->checkRight('usercenter')){
				if($page < -1) $page = 0;
        		
				$view = new ViewDescriptor($this->settings->usercenter_list_user);
				
        		$view->addValue('pagina_active', $page);
        		$view->addValue('pagina_count', ceil(model\User::getAllUserCount(-1, -1)/$this->settings->perpage_user));
        			
        		$user = model\User::getUsers($page);
				
        		foreach($user as $u){
        			$stpl = new SubViewDescriptor('user');
        			
        			$stpl->addValue('id', $u->getId());
        			$stpl->addValue('nick', $u->getNick());
        			$stpl->addValue('email', $u->getEmail());
        			$stpl->addValue('status', $u->getStatus());
        			$stpl->addValue('group', $u->getGroup()->getName());
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			
        			/*if(($this->checkRight('administer_group', $u->getGroup()->getId()) || $this->checkRight('edit_user', $u->getId()))) {
        				$sub = new SubViewDescriptor('edit');
        				$sub->addValue('id', $u->getId());
        				
        				$stpl->addSubView($sub);
        				unset($sub);
        			}*/
        			
        			$view->addSubView($stpl);
        			unset($stpl);
        		}
				
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}	
		}
		public function tplUserEdit($id) {
			$user = model\User::getUser($id);
			if($this->sp->user->isLoggedIn() && $this->checkRight('usercenter') &&  ($this->checkRight('edit_user', $user->getId()) || $this->checkRight('administer_group', $user->getGroup()->getId()))){
				$view = new ViewDescriptor($this->settings->usercenter_edit_user);
				
				$view->addValue('id', $user->getId());
        		$view->addValue('nick', $user->getNick());
        		$view->addValue('email', $user->getEMail());
        		$view->addValue('group', $this->tplGetGroupDropdown($user->getGroup()->getId()));
        		$view->addValue('groupId', $user->getGroup()->getId());
				
        		$view->addValue('status', $this->tplGetStatusDropdown($user->getStatus()));
        		
				$ud = $user->getUserData();
				$fn = UserDataField::getUserDataFields();
				$settings = '';
				foreach($fn as $n){ /* @var $n UserDataField */
					$view->addValue('userdata_'.$n->getName(), $ud->opt($n->getName(), '', true)->getValue());
					
					if(strpos($n->getName(), 'set.') === 0) {
						$udi = $ud->opt($n->getName(), '', false);
						$sv = new SubViewDescriptor('setting_'.(($n->getType() == User::DATA_TYPE_TEXT)?'text':(($n->getType() == User::DATA_TYPE_CHECKBOX)?'checkbox':'input')));
						$sv->setParent($view);
						$sv->addValue('label', $n->getInfo());
						$sv->addValue('name', $n->getName());
						
						if($n->getType() == User::DATA_TYPE_CHECKBOX){
							if($udi->getValue() == 1) $sv->addValue('value', 'checked="checked"');
							else $sv->addValue('value', '');
						} else {
							$sv->addValue('value', $udi->getValue());
						}
						
						$settings .= $sv->render();
						//$sv->updateQualifiedName($view->getQualifiedName());
					}
				}
				
				$view->addValue('settings', $settings);
        		
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}
		}
		public function tplUserNew() {
			if($this->sp->user->isLoggedIn() && $this->checkRight('usercenter') &&  ($this->checkRight('administer_user'))){
				$view = new ViewDescriptor($this->settings->usercenter_new_user);
				
        		$view->addValue('group', $this->tplGetGroupDropdown(-1));
        		$view->addValue('status', $this->tplGetStatusDropdown(-1));
				
        		//TODO: Userdata
        		
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}
		}
		/* ======   Userdata ======= */
		public function tplUserData($page=1){
			if($this->sp->user->isLoggedIn() && $this->checkRight('usercenter')){
				if($page < -1) $page = 0;
        		
				$view = new ViewDescriptor($this->settings->usercenter_userdata);
				
        		$view->addValue('pagina_active', $page);
        		$view->addValue('pagina_count', ceil(model\User::getAllUserDataCount(-1, -1)/$this->settings->perpage_user_data));
        			
        		$user = model\UserDataField::getUserDataField($page);
        		$usergroups = model\UserGroup::getGroups();

        		foreach($usergroups as $ug){
        			$t = new SubViewDescriptor('usergroups_header');
        			$t->addValue('id', $ug->getId());
        			$t->addValue('name', $ug->getName());
        			
        			$view->addSubView($t);
        			unset($t);
        		}
        		
        		foreach($user as $u){
        			$stpl = new SubViewDescriptor('userdata');
        			
        			$stpl->addValue('id', $u->getId());
        			$stpl->addValue('name', $u->getName());
        			$stpl->addValue('group', $u->getGroup()->getName());
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			$stpl->addValue('type', $u->getType());
        			$stpl->addValue('info', $u->getInfo());
        			$stpl->addValue('vis_register', ($u->getVisibleAtRegister()) ? 'yes' : 'no');
        			$stpl->addValue('vis_edit', ($u->getVisibleAtEdit()) ? 'yes' : 'no');
        			$stpl->addValue('vis_login', ($u->getVisibleAtLogin()) ? 'yes' : 'no');
        			
        			$stpl->addValue('group_id', $u->getGroup()->getId());
        			
        			foreach($usergroups as $ug){
        				$t = new SubViewDescriptor('userdata_group');
        				$t->addValue('enabled', ($u->usedByGroup($ug->getId())) ? 'ja' : 'nein');
        				
        				$stpl->addSubView($t);
        				unset($t);
        			}
        			
        			$view->addSubView($stpl);
        			unset($stpl);
        		}
				
				return $view->render();
			} else {
				$this->_msg($this->_('You are not authorized', 'core'), Messages::ERROR);
        		return '';
			}	
			return 'userdata_new';
		}
		
	}
?>