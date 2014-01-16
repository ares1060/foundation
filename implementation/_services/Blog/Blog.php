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
			$whereSQL = 'WHERE 1=1';
			$tags = false;
			$contacts = false;
			
			if($this->settings->journal_mode == "private"){
				if(!$user) return '';
				$whereSQL .= ' AND p.user_id = \''.$this->sp->db->escape($user->getId()).'\'';
			} else if(isset($args['author'])){
				$whereSQL .= ' AND p.user_id = \''.$this->sp->db->escape($args['author']).'\'';
			}
			
			if(isset($args['search']) && strlen($args['search']) > 2){
				$args['search'] = $this->sp->db->escape($args['search']);
				
				//TODO tagging still hacky
				$whereSQL = 'LEFT JOIN '.$this->sp->db->prefix.'tag_links tl ON p.id = tl.param AND tl.service = \'Blog\' LEFT JOIN '.$this->sp->db->prefix.'tags t ON t.id = tl.tag_id '.$whereSQL;
				$whereSQL .= ' AND (`title` LIKE \'%'.$args['search'].'%\' OR `text` LIKE \'%'.$args['search'].'%\' OR t.name LIKE \''.$args['search'].'%\')';
				$tags = true;
			}
			
			if(isset($args['date_from']) && $args['date_from'] != ''){
				$args['date_from'] = $this->sp->db->escape($args['date_from']);
				$whereSQL .= ' AND p.date >= \''.$args['date_from'].'\'';
			}
				
			if(isset($args['date_to']) && $args['date_to'] != ''){
				$args['date_to'] = $this->sp->db->escape($args['date_to']);
				$whereSQL .= ' AND p.date <= \''.$args['date_to'].'\'';
			}
			
			
			$from = 0;
			if(isset($args['from']) && $args['from'] > 0) {
				$from = $this->sp->db->escape($args['from']);
			}
			
			$rows = -1;
			if(isset($args['rows']) && $args['rows'] >= 0){
				$rows = $this->sp->db->escape($args['rows']);
			}
			
			//TODO: generalize this so that services can infuse filter criteria generically
			if(isset($args['contact_filter']) && is_array($args['contact_filter']) && count($args['contact_filter']) > 0){
				$values = '';
				foreach($args['contact_filter'] as $val){
					$values .= $this->sp->db->escape($val).',';
				}
				$values = substr($values, 0, -1);
				$whereSQL = 'JOIN '.$this->sp->db->prefix.'blog_posts_contacts AS c ON c.entry_id = p.id '.$whereSQL;
				$whereSQL .= ' AND c.contact_id IN ('.$values.')';
				$contacts = true;
			}
			
			if($tags || $contacts) $whereSQL.= ' GROUP BY p.id';
			
			$whereSQL .= ' ORDER BY p.date DESC, p.id DESC';
			
			$posts = Post::getPosts($whereSQL, $from, $rows);

			$view = new core\Template\ViewDescriptor('_services/Blog/post_list');	
			if($rows < 0) $rows = count($posts);
			$pages =  ceil(Post::getPostCount($whereSQL) / $rows);
			$view->addValue('pages', $pages);
			if(isset($args['mode']) && $args['mode'] == 'wrapped'){
				$header = $view->showSubView('header');
				if($contacts) $header->showSubView('filter_contacts');
				
				$footer = $view->showSubView('footer');
				$footer->addValue('current_page', '0');
				$footer->addValue('posts_per_page', $rows);
				$footer->addValue('pages', $pages);
			}
			
			foreach($posts as $post){
				$sv = $view->showSubView('row');

				$sv->addValue('id', $post->getId());
				$sv->addValue('date', $this->sp->txtfun->fixDateLoc($post->getDate()->format('d. F Y, H:i')));
				$sv->addValue('title', $post->getTitle());
				$sv->addValue('text', nl2br($post->getText()));
					
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

					$view->addValue('id', $post->getId());
					$view->addValue('title', $post->getTitle());
					$view->addValue('text', $post->getText());
					$view->addValue('date', $post->getDate()->format('d.m.Y H:i'));
					
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
					if(!$post || $post->getAuthorId() != $user->getId()) return false;
				} else {
					//create new post
					$post = new Post();
				}
			
				if(isset($args['text'])) $post->setText($args['text']);
				if(isset($args['title'])) $post->setTitle($args['title']);
				if(isset($args['date'])) $post->setDate(new DateTime($args['date']));
				
				if($post->getAuthorId() < 0) $post->setAuthor($user->getId());
				
				$ok = $post->save();
				
				if($ok){
					return $post->getId();
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
		
		public function checkAttachmentAuth($param){
			$user = $this->sp->user->getLoggedInUser();
			if($user){
				$post = Post::getPost($param);
				if($post && $post->getAuthorId() == $user->getId()) return true;
			}
			return false;
		}
		
	}
?>