<?php

/**
 * Delete a directory and all its content
 *
 */
function deleteDir($dirPath) { 
    if (! is_dir($dirPath)) { 
        throw new InvalidArgumentException('$dirPath must be a directory'); 
    } 
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') { 
        $dirPath .= '/'; 
    } 
    $files = glob($dirPath . '*', GLOB_MARK); 
    foreach ($files as $file) { 
        if (is_dir($file)) { 
            self::deleteDir($file); 
        } else { 
            unlink($file); 
        } 
    } 
    rmdir($dirPath); 
} 


/**
 * XML-RPC protocol support
 *
 */
class XMLRPC_Server extends IXR_Server {

	/**
	 * Register all of the XMLRPC methods that XMLRPC server understands.
	 *
	 * Sets up server and method property. Passes XMLRPC
	 * methods through the 'xmlrpc_methods' filter to allow plugins to extend
	 * or replace XMLRPC methods.
	 *
	 * @since 1.5.0
	 *
	 * @return wp_xmlrpc_server
	 */
	function __construct() {
		$this->methods = array(
		
			// Blogger API
			'blogger.getUsersBlogs' => 'this:blogger_getUsersBlogs',
			'blogger.getUserInfo' => 'this:blogger_getUserInfo',
			'blogger.deletePost' => 'this:blogger_deletePost',
		
			// MetaWeblog API (with MT extensions to structs)
			'metaWeblog.newPost' => 'this:mw_newPost',
			'metaWeblog.editPost' => 'this:mw_editPost',
			'metaWeblog.getPost' => 'this:mw_getPost',
			'metaWeblog.getRecentPosts' => 'this:mw_getRecentPosts',
			'metaWeblog.getCategories' => 'this:mw_getCategories',
			'metaWeblog.newMediaObject' => 'this:mw_newMediaObject',

			// MetaWeblog API aliases for Blogger API
			'metaWeblog.deletePost' => 'this:blogger_deletePost',
			'metaWeblog.getUserInfo' => 'this:blogger_getUserInfo',
			'metaWeblog.getUsersBlogs' => 'this:blogger_getUsersBlogs',

			// Wordpress needed additions
			'wp.newCategory'		=> 'this:wp_newCategory',
			'wp.deleteCategory'		=> 'this:wp_deleteCategory',
			'wp.uploadFile'			=> 'this:mw_newMediaObject'

		);
	}

	function serve_request() {
		$this->IXR_Server($this->methods);
	}

	/**
	 * Check user's credentials.
	 *
	 * @since 1.5.0
	 *
	 * @param string $user_login User's username.
	 * @param string $user_pass User's password.
	 * @return bool Whether authentication passed.
	 * @deprecated use wp_xmlrpc_server::login
	 * @see wp_xmlrpc_server::login
	 */
	function login($user_login, $user_pass) {
		if ($user_login!=username && $user_pass!=password) {
			$this->error = new IXR_Error(403, ('Bad login/pass combination.'));
			return false;
		}
		return true;
	}

	/**
	 * Checks if the method received at least the minimum number of arguments.
	 *
	 * @since 3.4.0
	 *
	 * @param string|array $args Sanitize single string or array of strings.
	 * @param int $count Minimum number of arguments.
	 * @return boolean if $args contains at least $count arguments.
	 */
	protected function minimum_args( $args, $count ) {
		if ( count( $args ) < $count ) {
			$this->error = new IXR_Error( 400, ( 'Insufficient arguments passed to this XML-RPC method.' ) );
			return false;
		}

		return true;
	}
	
	protected function _convert_date( $date ) {
		return new IXR_Date( date( 'Ymd\TH:i:s', $date) );
	}
	
	/**
	 * Retrieve user's data.
	 *
	 * Gives your client some info about you, so you don't have to.
	 *
	 * @since 1.5.0
	 *
	 * @param array $args Method parameters.
	 * @return array
	 */
	function blogger_getUserInfo($args) {

		$username = $args[1];
		$password  = $args[2];
		if (!$user = $this->login($username, $password)) return $this->error;

		$struct = array(
			'nickname'  => nickname,
			'userid'    => username,
			'url'       => siteURL,
			'lastname'  => lastname,
			'firstname' => firstname
		);

		return $struct;
	}

	/**
	 * Retrieve blogs that user owns.
	 *
	 * Will make more sense once we support multiple blogs.
	 *
	 * @since 1.5.0
	 *
	 * @param array $args Method parameters.
	 * @return array
	 */
	function blogger_getUsersBlogs($args) {
		
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;
		
		$struct = array(
			'isAdmin'  => true,
			'url'      => blogURL,
			'blogid'   => '1',
			'blogName' => blogName,
			'xmlrpc'   => blogURL.'API/xmlrpc/'
		);

		return array($struct);

	}
	
	function get_categories() {
		
		$result=array();
		
		// Try to open the comment directory
		$catDir = '../../BG/CATS/';
		if ($handle = opendir($catDir)) {

			// Loop directory content
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {

					// File url
					$result[] = $entry;

				}
			}
			closedir($handle);
			
		}
		
		return $result;
	}
	
	/*
	 * Standard arguments.
	 *
	 * @return A list of categories
	 *
	 */
	function mw_getCategories($args) {
		
		$username = $args[1];
		$password = $args[2];
		if (!$this->login($username, $password)) return $this->error;

		$categories_struct = array();

		if ( $cats = $this->get_categories() ) {
			foreach ( $cats as $cat ) {
				$struct=array();
				$struct['categoryId'] = $cat;
				$struct['parentId'] = null;
				$struct['description'] = $cat;
				$struct['categoryDescription'] = $cat;
				$struct['categoryName'] = $cat;
				//$struct['htmlUrl'] = esc_html(get_category_link($cat->term_id));
				//$struct['rssUrl'] = esc_html(get_category_feed_link($cat->term_id, 'rss2'));

				$categories_struct[] = $struct;
			}
		}

		return $categories_struct;
		
	}
	
	function sanitize_url($title) {
		
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		$urlTitle = str_replace($search, $replace, $title);
		$urlTitle = preg_replace('/[^a-zA-Z0-9.]/i', '-', $urlTitle);
		$urlTitle = preg_replace('/(\-)+/i', '-', $urlTitle);
		return $urlTitle;
		
	}
	
	/**
	 * Create a new post.
	 *
	 * The 'content_struct' argument must contain:
	 *  - title
	 *  - description
	 *  - mt_excerpt
	 *  - mt_text_more
	 *  - mt_keywords
	 *  - mt_tb_ping_urls
	 *  - categories
	 *
	 * Also, it can optionally contain:
	 *  - wp_slug
	 *  - wp_password
	 *  - wp_page_parent_id
	 *  - wp_page_order
	 *  - wp_author_id
	 *  - post_status | page_status - can be 'draft', 'private', 'publish', or 'pending'
	 *  - mt_allow_comments - can be 'open' or 'closed'
	 *  - mt_allow_pings - can be 'open' or 'closed'
	 *  - date_created_gmt
	 *  - dateCreated
	 *  - wp_post_thumbnail
	 *
	 * @since 1.5.0
	 *
	 * @param array $args Method parameters. Contains:
	 *  - blog_id
	 *  - username
	 *  - password
	 *  - content_struct
	 *  - publish
	 * @return int
	 */
	function mw_newPost($args) {
	
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;
		
		// Loading directories
		$bgDir = '../..'; mkdir($bgDir);
		$catDir = $bgDir.'/CATS'; mkdir($catDir);
		$allDir = $bgDir.'/ALL'; mkdir($allDir);
		
		// Load data
		$content = $args[3];
		
		$title = $content['title'];
		$description = $content['description'];
		$shortDescription = $content['mt_excerpt'];
		$categories = $content['categories'];

        if($shortDescription=='' || $shortDescription==NULL) {
            $shortDescription_start = min(stripos($description,'<p>'),stripos($description,'<p '));
            $shortDescription_end = stripos($description,'</p>');
            if($shortDescription_start !== FALSE && $shortDescription_end !== FALSE) {
                $shortDescription = substr($description, $shortDescription_start, 4+$shortDescription_end-$shortDescription_start);
            }
        }
		
		if($args[0] != 1 && $args[0] != '1') {
		
			// EDIT AN EXISTING POST
			$currentYear = date('Y'); 
			$uniqueID = $args[0];
			$filePath = $allDir.'/'.$uniqueID;
			if (!file_exists($filePath))
				return new IXR_Error(404, ('Invalid post ID.'));
			
			$urlTitle = file_get_contents($filePath);
			
		} else {
			
			// ADD A NEW POST
			$randID = rand(100,999);
			$currentYear = date('Y'); 
			$uniqueID = str_pad((PHP_INT_MAX-time()),10,'0',STR_PAD_LEFT).'-'.$randID;
			
			$urlTitle = $this->sanitize_url(html_entity_decode($title));
			$urlTitle = $currentYear.'/'.trim($urlTitle,'-').'-'.$randID;
			
			// add a link in the ALL directory
			file_put_contents($allDir.'/'.$uniqueID, $urlTitle);
		}
		
		// build final template
		$template = file_get_contents('template.html');
		$template = str_replace("{post-title}",$title,$template);
		$template = str_replace("{post-body}",$description,$template);
		
		// write files
		mkdir($bgDir.'/'.$currentYear);
		mkdir($bgDir.'/'.$urlTitle);
		file_put_contents($bgDir.'/'.$urlTitle.'/index.html', $template);
		file_put_contents($bgDir.'/'.$urlTitle.'/title.txt', $title);
		file_put_contents($bgDir.'/'.$urlTitle.'/description.txt', $description);
		file_put_contents($bgDir.'/'.$urlTitle.'/short-description.txt', $shortDescription);
		file_put_contents($bgDir.'/'.$urlTitle.'/unique-identifier.txt', $uniqueID);
		file_put_contents($bgDir.'/'.$urlTitle.'/list-of-categories.txt', implode(',',$categories));
		
		// publish: add a 'published.txt' file
		if($args[4] == 1) {
			@file_put_contents($bgDir.'/'.$urlTitle.'/published.txt', '1');
		} else {
			@unlink($bgDir.'/'.$urlTitle.'/published.txt');
		}
		
		// categories: clear previous categories
		if ( $cats = $this->get_categories() ) {
			foreach ( $cats as $cat ) {
				@unlink($catDir.'/'.$cat.'/'.$uniqueID);
			}
		}
		
		// categories: add a link in CAT folders
		foreach($categories as $cat) {
			mkdir($catDir.'/'.$cat);
			file_put_contents($catDir.'/'.$cat.'/'.$uniqueID, $urlTitle);
		}
	
		// return the identifier
		return $uniqueID;
	}
	
	/*
	 * Post ID as argument 0.
	 * As usual for argument 1 and 2.
	 *
	 * @return struct
	 * ¦ datetime dateCreated (ISO.8601) 
	 * ¦ int userid 
	 * ¦ int page_id 
	 * ¦ string page_status 
	 * ¦ string description 
	 * ¦ string title 
	 * ¦ string link 
	 * ¦ string permaLink 
	 * ¦ array categories
	 * ¦ string Category Name 
	 * ¦ ... 
	 * ¦ string excerpt 
	 * ¦ ...
	*/
	function mw_getPost($args) {
	
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;
		
		$bgDir = '../..'; mkdir($bgDir);
		$catDir = $bgDir.'/CATS'; mkdir($catDir);
		$allDir = $bgDir.'/ALL'; mkdir($allDir);

		$uniqueID = $args[0]; $filePath = $allDir.'/'.$uniqueID;
		if (!file_exists($filePath))
			return new IXR_Error(404, ('Invalid post ID.'));

		$urlTitle = file_get_contents($filePath);
				
		if(file_exists($bgDir.'/'.$urlTitle.'/published.txt')) {
			$status = "publish";
		} else {
			$status = "draft";
		}

		$title = file_get_contents($bgDir.'/'.$urlTitle.'/title.txt');
		$description = file_get_contents($bgDir.'/'.$urlTitle.'/description.txt');
		$shortDescription = file_get_contents($bgDir.'/'.$urlTitle.'/short-description.txt');
		$link = blogURL.$urlTitle;
		$cDate = $this->_convert_date(filectime($bgDir.'/'.$urlTitle.'/unique-identifier.txt'));
		$mDate = $this->_convert_date(filemtime($bgDir.'/'.$urlTitle.'/unique-identifier.txt'));
		$categories = explode(",",file_get_contents($bgDir.'/'.$urlTitle.'/list-of-categories.txt'));
	
		$struct = array(
			'dateCreated' => $cDate,
			'userid' => 'me',
			'postid' => (string) $uniqueID,
			'description' => $description,
			'title' => $title,
			'link' => $link,
			'permaLink' => $link,
			'content' => $description,
			'categories' => $categories,
			'mt_excerpt' => $shortDescription,
			'post_status' => $status,
			'date_modified' => $mDate
		);
		
		return $struct;

	}
	
	function mw_editPost($args) {
		return $this->mw_newPost($args);
	}
	
	function blogger_deletePost($args) {
		
		$username = $args[2];
		$password  = $args[3];
		if (!$this->login($username, $password)) return $this->error;
		
		// Loading directories
		$bgDir = '../..'; mkdir($bgDir);
		$catDir = $bgDir.'/CATS'; mkdir($catDir);
		$allDir = $bgDir.'/ALL'; mkdir($allDir);
				
		// EDIT AN EXISTING POST
		$currentYear = date('Y'); 
		$uniqueID = $args[1];
		$filePath = $allDir.'/'.$uniqueID;
		if (!file_exists($filePath))
			return new IXR_Error(404, ('Invalid post ID.'));
		
		$urlTitle = file_get_contents($filePath);
		
		// categories: clear categories
		if ( $cats = $this->get_categories() ) {
			foreach ( $cats as $cat ) {
				@unlink($catDir.'/'.$cat.'/'.$uniqueID);
			}
		}
		
		// delete files
		mkdir($bgDir.'/'.$currentYear);
		mkdir($bgDir.'/'.$urlTitle);
		deleteDir($bgDir.'/'.$urlTitle);
			
		// delete link in the ALL directory
		@unlink($allDir.'/'.$uniqueID);
		
		// everything was fine
		return true;
	}
	
	function wp_newCategory($args) {
		
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;
		
		// Loading directories
		$bgDir = '../..'; mkdir($bgDir);
		$catDir = $bgDir.'/CATS'; mkdir($catDir);
		
		// Creating the directory
		mkdir($catDir.'/'.$args[0]);
		
		return true;
	}
	
	function wp_deleteCategory($args) {
		
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;
		
		// Loading directories
		$bgDir = '../..'; mkdir($bgDir);
		$catDir = $bgDir.'/CATS'; mkdir($catDir);
		
		// Deleting the directory
		deleteDir($catDir.'/'.$args[0]);
		
		// NOTE:
		// This doesn't remove the category from referenced posts
		// Deleting a category is therefore not recommeded if you have
		// any post pointing to it.
		return true;
	}

	function mw_newMediaObject($args) {
		
		$username = $args[1];
		$password  = $args[2];
		if (!$this->login($username, $password)) return $this->error;

		// Retreiving data
		$data = $args[3];
		$name = $this->sanitize_url($data['name']);
		$type = $data['type'];
		$bits = $data['bits'];
		
		// Loading directories
		$bgDir = '../..'; mkdir($bgDir);
		$datDir = $bgDir.'/DATA'; mkdir($datDir);
		$datTypeDir = $datDir.'/'.$this->sanitize_url($type); mkdir($datTypeDir);
		$datTypeDir = $datTypeDir.'/'.date('Y'); mkdir($datTypeDir);


		// Writing the file
		file_put_contents($datTypeDir.'/'.$name, $bits);
		
		// Sending OK status
		$struct = array(
			'file' => $name,
			'url'  => blogURL.'DATA/'.$this->sanitize_url($type).'/'.date('Y').'/'.$name,
			'type' => $type
		);
		
		return $struct;

	}
	
}
