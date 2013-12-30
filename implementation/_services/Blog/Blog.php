<?php
	require_once($GLOBALS['config']['root'].'_services/Blog/model/Post.php');

	use at\foundation\core;
	
	class Blog extends core\AbstractService implements core\IService {
		
		function __construct(){
			$this->name = 'Blog';
			$this->ini_file = $GLOBALS['to_root'].'_services/Blog/Blog.ini';	
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
					$whereSQL .= ' AND (`title` LIKE \'%'.$args['search'].'%\' OR `text` LIKE \'%'.$args['search'].'%\')';
					//TODO tagging
			}
			
			
			$from = 0;
			if(isset($args['from']) && $args['from'] > 0) {
				$from = $this->sp->db->escape($args['from']);
			}
			
			$rows = -1;
			if(isset($args['rows']) && $args['rows'] >= 0){
				$rows = $this->sp->db->escape($args['rows']);
			}
			
			$whereSQL .= ' ORDER BY p.date DESC, p.id DESC';
			
			$posts = Post::getPosts($whereSQL, $from, $rows);

			$view = new core\Template\ViewDescriptor('_services/Blog/post_list');	
			if($rows < 0) $rows = count($posts);
			$pages =  ceil(Post::getPostCount($whereSQL) / $rows);
			$view->addValue('pages', $pages);
			if(isset($args['mode']) && $args['mode'] == 'wrapped'){
				$view->showSubView('header');
				$footer = $view->showSubView('footer');
				$footer->addValue('current_page', '0');
				$footer->addValue('entries_per_page', $rows);
				$footer->addValue('pages', $pages);
			}
			
			foreach($posts as $post){
				$sv = $view->showSubView('row');

				$sv->addValue('id', $post->getId());
				$sv->addValue('date', $post->getDate()->format('d. F Y, h:i'));
				$sv->addValue('title', $post->getTitle());
				$sv->addValue('text', $post->getText());
					
			}
			
			return $view->render();
		}
		
		private function handleViewForm($args){
			$user = $this->sp->user->getLoggedInUser();
			$view = new core\Template\ViewDescriptor('_services/Blog/post_form');
			if($user && isset($args['id'])){
				//edit form
				$post = Post::getPost($args['id']);
				if($post && $post->getAuthorId() == $user->getId()){
					$view->addValue('form_title', 'Eintrag bearbeiten');

					$view->addValue('title', $post->getTitle());
					$view->addValue('text', $post->getText());
					$view->addValue('date', $post->getDate()->format('d.m.Y h:i:s'));
					
					//TODO contacts linking
					
					return $view->render();
				} else {
					return 'not for you';
				}
			} else {

				$view->addValue('form_title', 'Neuer Eintrag');
				
				return $view->render();
			}
			
		}
		
		private function handleSave($args){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				if(isset($args['id'])){
					//get post
					$post = Post::getPost($args['id']);
					if(!$post || $post->getOwnerId() != $user->getId()) return false;
				} else {
					//create new post
					$post = new Post();
				}
			
				if(isset($args['text'])) $post->setText($args['text']);
				if(isset($args['title'])) $post->setTitle($args['title']);
				if(isset($args['date'])) $post->setDate(new DateTime($args['date']));
				
				if($post->getAuthorId() < 0) $post->setAuthor($user->getId());
				
				$ok = $post->save();
				
				if($ok && isset($args['contacts'])) {
					/*$oldCids = $entry->getContactIds();
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
					}*/
					//TODO implement generic contact linking
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
					//get contact
					$post = Post::getPost($args['id']);
					if(!$post) return true;
					if($post->getAuthorId() != $user->getId()) return false;
					$ok = $post->delete();
					if($ok) {
						//clean up
					}
					
					return $ok;
				}
			}
			return false;
		}
		
	}
?>