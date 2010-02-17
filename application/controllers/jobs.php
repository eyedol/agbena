<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This controller is used to list/ view and edit reports
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Reports Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Jobs_Controller extends Jobsmain_Controller {

	var $logged_in;
	
	function __construct()
	{
		parent::__construct();

		// Javascript Header
		$this->template->header->validator_enabled = TRUE;
		
		//include footer form js file
        $footerjs = new View('footer_form_js');
		
		// Pack the javascript using the javascriptpacker helper
		$myPacker = new javascriptpacker($footerjs , 'Normal', false, false);
		$footerjs = $myPacker->pack();
        $this->template->header->js = $footerjs;
		
		// Is the Admin Logged In?
		$this->logged_in = Auth::instance()->logged_in()
		     ? TRUE
		     : FALSE;
	}

	/**
	 * Displays all reports.
	 */
	public function index() 
	{
		$this->template->header->this_page = 'jobs';
		$this->template->content = new View('jobs');
		
		// Filter By Category
		$category_filter = ( isset($_GET['c']) && !empty($_GET['c']) )
			? "category_id = ".$_GET['c'] : " 1=1 ";
		
		// Pagination
		$pagination = new Pagination(array(
				'query_string' => 'page',
				'items_per_page' => (int) Kohana::config('settings.items_per_page'),
				'total_items' => ORM::factory('job')
					->join('job_category', 'job.id', 'job_category.job_id')
					->where('job_active', '1')
					->where($category_filter)
					->count_all()
				));

		$jobs = ORM::factory('job')
				->select('DISTINCT job.*')
				->join('job_category', 'job.id', 'job_category.job_id')
				->where('job_active', '1')
				->where($category_filter)
				->groupby('job.id')
				->orderby('job_dateadd', 'desc')
				->find_all( (int) Kohana::config('settings.items_per_page'), 
					$pagination->sql_offset);
		
		$this->template->content->jobs = $jobs;
		
		//Set default as not showing pagination. Will change below if necessary.
		$this->template->content->pagination = ''; 
		
		// Pagination and Total Num of Report Stats
		if($pagination->total_items == 1) {
			$plural = '';
		} else {
			$plural = 's';
		}
		if ($pagination->total_items > 0) {
			$current_page = ($pagination->sql_offset/ (int) Kohana::config('settings.items_per_page')) + 1;
			$total_pages = ceil($pagination->total_items/ (int) Kohana::config('settings.items_per_page'));
			
			if($total_pages > 1) { // If we want to show pagination
				$this->template->content->pagination_stats = '(Showing '
                     .$current_page.' of '.$total_pages
                     .' pages of '.$pagination->total_items.' job'.$plural.')';
				
                $this->template->content->pagination = $pagination;
			} else { // If we don't want to show pagination
				$this->template->content->pagination_stats = '('.$pagination->total_items.' job'.$plural.')';
			}
		} else {
			$this->template->content->pagination_stats = '('.$pagination->total_items.' job'.$plural.')';
		}
		
		/*$icon_html = array();
		$icon_html[1] = "<img src=\"".url::base()."media/img/image.png\">"; //image
		$icon_html[2] = "<img src=\"".url::base()."media/img/video.png\">"; //video
		$icon_html[3] = ""; //audio
		$icon_html[4] = ""; //news
		$icon_html[5] = ""; //podcast
		
		//Populate media icon array
		$this->template->content->media_icons = array();
		foreach($jobs as $job) {
			$job_id = $job->id;
			if(ORM::factory('media')
               ->where('job_id', $job_id)->count_all() > 0) {
				$medias = ORM::factory('media')
                          ->where('job_id', $job_id)->find_all();
				
				//Modifying a tmp var prevents Kohona from throwing an error
				$tmp = $this->template->content->media_icons;
				$tmp[$job_id] = '';
				
				foreach($medias as $media) {
					$tmp[$job_id] .= $icon_html[$media->media_type];
					$this->template->content->media_icons = $tmp;
				}
			}
		}*/
		
		// Category Title, if Category ID available
		$category_id = ( isset($_GET['c']) && !empty($_GET['c']) )
			? $_GET['c'] : "0";
		$category = ORM::factory('j_category')
			->find($category_id);
		$this->template->content->job_category_title = ( $category->loaded ) ?
			$category->job_category_title : "";
		
		//include footer form js file
        $footerjs = new View('footer_form_js');
		
		// Pack the javascript using the javascriptpacker helper
		$myPacker = new javascriptpacker($footerjs , 'Normal', false, false);
		$footerjs = $myPacker->pack();
        $this->template->header->js .= $footerjs;
	} 
	
	/**
	 * Submits a new report.
	 */
	public function submit($id = false, $saved = false)
	{
		$this->template->header->this_page = 'jobs_submit';
		$this->template->content = new View('jobs_submit');
		
		// setup and initialize form field names
		$form = array
		(
			'job_title' => '',
			'job_description' => '',
			'latitude' => '',
			'longitude' => '',
			'location_name' => '',
			'country_id' => '',
			'job_category' => array(),
			'person_first' => '',
			'person_last' => '',
            'person_email' => '',
            
		);
		
		//	copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
        $form_error = FALSE;

        if ($saved == 'saved')
		{
			$form_saved = TRUE;
		}
		else
		{
			$form_saved = FALSE;
		}

		
		// Initialize Default Values
		/*$form['job_date'] = date("m/d/Y",time());
		$form['job_hour'] = "12";
		$form['job_minute'] = "00";
        $form['job_ampm'] = "pm";
        // initialize custom field array
		$form['custom_field'] = $this->_get_custom_form_fields($id,'',true);
                //GET custom forms
		$forms = array();
		foreach (ORM::factory('form')->find_all() as $custom_forms)
		{
			$forms[$custom_forms->id] = $custom_forms->form_title;
		}
		$this->template->content->forms = $forms;*/

		
		// check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory(array_merge($_POST,$_FILES));
			
			 //  Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('job_title', 'required', 'length[3,200]');
			$post->add_rules('job_description', 'required');
			
			// Validate for maximum and minimum latitude values
			$post->add_rules('latitude', 'required', 'between[-90,90]');
			$post->add_rules('longitude', 'required', 'between[-180,180]');
			$post->add_rules('location_name', 'required', 'length[3,200]');
			$post->add_rules('person_first','required', 'length[3,100]');
			$post->add_rules('person_last','required', 'length[3,100]');
			$post->add_rules('person_email','required', 'email', 'length[3,100]');
			
			//XXX: Hack to validate for no checkboxes checked
			if (!isset($_POST['job_category'])) {
				$post->job_category = "";
				$post->add_error('job_category', 'required');
			}
			else
			{
				$post->add_rules('job_category.*', 'required', 'numeric');
			}
			
						
			// Validate Personal Information
			if (!empty($_POST['person_first']))
			{
				$post->add_rules('person_first', 'length[3,100]');
			}
			
			if (!empty($_POST['person_last']))
			{
				$post->add_rules('person_last', 'length[3,100]');
			}
			
			if (!empty($_POST['person_email']))
			{
				$post->add_rules('person_email', 'email', 'length[3,100]');
			}
			
			// Test to see if things passed the rule checks
			if ($post->validate())
			{
				// STEP 1: SAVE LOCATION
				$location = new Location_Model();
				$location->location_name = $post->location_name;
				$location->latitude = $post->latitude;
				$location->longitude = $post->longitude;
				$location->location_date = date("Y-m-d H:i:s",time());
				$location->save();
				
				// STEP 2: SAVE job
				$job = new Job_Model();
                $job->location_id = $location->id;
              
				$job->user_id = 0;
				$job->job_title = $post->job_title;
				$job->job_description = $post->job_description;
				
				$job_date=explode("/",$post->job_dateadd);
				
				// The $_POST['date'] is a value posted by form in mm/dd/yyyy format
				$job_date=$job_date[2]."-".$job_date[0]."-".$job_date[1];
					
				$job->job_dateadd = date("Y-m-d H:i:s",time());
				$job->save();
				
				// STEP 3: SAVE CATEGORIES
				foreach($post->job_category as $item)
				{
					$job_category = new Job_Category_Model();
					$job_category->job_id = $job->id;
					$job_category->j_category_id = $item;
					$job_category->save();
				}
				
				// STEP 5: SAVE PERSONAL INFORMATION
				$person = new Job_Person_Model();
				$person->location_id = $location->id;
				$person->job_id = $job->id;
				$person->person_first = $post->person_first;
				$person->person_last = $post->person_last;
				$person->person_email = $post->person_email;
				$person->person_date = date("Y-m-d H:i:s",time());
				$person->save();
				
				url::redirect('jobs/thanks');
			}
	
			// No! We have validation errors, we need to show the form again, with the errors
			else   
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('report'));
				$form_error = TRUE;
			}
		}

		
		// Retrieve Country Cities
		$default_country = Kohana::config('settings.default_country');
		$this->template->content->cities = $this->_get_cities($default_country);
		$this->template->content->multi_country = Kohana::config('settings.multi_country');

                $this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->categories = $this->_get_categories($form['job_category']);


		// Javascript Header
		$this->template->header->map_enabled = TRUE;
		$this->template->header->datepicker_enabled = TRUE;
		$this->template->header->js = new View('reports_submit_js');
		$this->template->header->js->default_map = Kohana::config('settings.default_map');
		$this->template->header->js->default_zoom = Kohana::config('settings.default_zoom');
		if (!$form['latitude'] || !$form['latitude'])
		{
			$this->template->header->js->latitude = Kohana::config('settings.default_lat');
			$this->template->header->js->longitude = Kohana::config('settings.default_lon');
		}
		else
		{
			$this->template->header->js->latitude = $form['latitude'];
			$this->template->header->js->longitude = $form['longitude'];
		}
		//include footer form js file
        $footerjs = new View('footer_form_js');
		
		// Pack the javascript using the javascriptpacker helper
		$myPacker = new javascriptpacker($footerjs , 'Normal', false, false);
		$footerjs = $myPacker->pack();
        $this->template->header->js .= $footerjs;
	}
	
	 /**
	 * Displays a report.
	 * @param boolean $id If id is supplied, a report with that id will be
	 * retrieved.
	 */
	public function view($id = false)
	{
		$this->template->header->this_page = 'jobs';
		$this->template->content = new View('jobs_view');
		
		// Load Akismet API Key (Spam Blocker)
		$api_akismet = Kohana::config('settings.api_akismet');
		
		if ( !$id )
		{
			url::redirect('jobsmain');
		}
		else
		{
			$job = ORM::factory('job', $id);
			
			if ( $job->id == 0 )	// Not Found
			{
				url::redirect('jobsmain');
			}

			// Comment Post?
			// Setup and initialize form field names
			$form = array
			(
				'comment_author' => '',
				'comment_description' => '',
				'comment_email' => '',
				'comment_ip' => '',
				'captcha' => '',
				'comment' => 'Submit',
			);

			$captcha = Captcha::factory(); 
			$errors = $form;
			$form_error = FALSE;
			$form_sent = FALSE;
			
			// Check, has the form been submitted, if so, setup validation
			if ($_POST)
			{
				// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
				$post = Validation::factory($_POST);

				// Add some filters
				$post->pre_filter('trim', TRUE);
				 
				// Add some rules, the input field, followed by a list of checks, carried out in order
				$post->add_rules('comment_author', 'required', 'length[3,100]');
				$post->add_rules('comment_description', 'required');
				$post->add_rules('comment_email', 'required','email', 'length[4,100]');
				$post->add_rules('captcha', 'required', 'Captcha::valid');
				
				
				
				// Test to see if things passed the rule checks
				if ($post->validate())
				{
					// Yes! everything is valid
					
					if ($api_akismet != "")
					{ // Run Akismet Spam Checker
						$akismet = new Akismet();

						// comment data
						$comment = array(
							'author' => $post->comment_author,
							'email' => $post->comment_email,
							'website' => "",
							'body' => $post->comment_description,
							'user_ip' => $_SERVER['REMOTE_ADDR']
						);

						$config = array(
							'blog_url' => url::site(),
							'api_key' => $api_akismet,
							'comment' => $comment
						);

						$akismet->init($config);

						if($akismet->errors_exist()) 
						{
							if($akismet->is_error('AKISMET_INVALID_KEY'))
							{
								// throw new Kohana_Exception('akismet.api_key');
							}
							elseif($akismet->is_error('AKISMET_RESPONSE_FAILED')) 
							{
								// throw new Kohana_Exception('akismet.server_failed');
							}
							elseif($akismet->is_error('AKISMET_SERVER_NOT_FOUND')) 
							{
								// throw new Kohana_Exception('akismet.server_not_found');
							}
							// If the server is down, we have to post 
							// the comment :(
							// $this->_post_comment($comment);
							$comment_spam = 0;
						}
						else {
							if($akismet->is_spam()) 
							{
								$comment_spam = 1;
							}
							else {
								$comment_spam = 0;
							}
						}
					}
					else
					{ // No API Key!!
						$comment_spam = 0;
					}
					 
						$comment = new Job_Comment_Model();
						$comment->job_id = $id;
						$comment->comment_author = strip_tags($post->comment_author);
						$comment->comment_description = strip_tags($post->comment_description);
						$comment->comment_email = strip_tags($post->comment_email);
						$comment->comment_ip = $_SERVER['REMOTE_ADDR'];
						$comment->comment_date = date("Y-m-d H:i:s",time());
					
						// Activate comment for now
						if ($comment_spam == 1)
						{
							$comment->comment_spam = 1;
							$comment->comment_active = 0;
						}
						else
						{
							$comment->comment_spam = 0;
							$comment->comment_active = 1;
						} 
						$comment->save();
					
						// Notify Admin Of New Comment
						$send = notifications::notify_admins(
							"[".Kohana::config('settings.site_name')."] ".
							Kohana::lang('notifications.admin_new_comment.subject'),
							Kohana::lang('notifications.admin_new_comment.message')
							."\n\n'".strtoupper($job->job_title)."'"
							."\n".url::base().'jobs/view/'.$id
							);
					
						// Redirect
						url::redirect('jobs/view/'.$id);
					}

				// No! We have validation errors, we need to show the form again, with the errors
				else   
				{
					// repopulate the form fields
					$form = arr::overwrite($form, $post->as_array());

					// populate the error fields, if any
					$errors = arr::overwrite($errors, $post->errors('comments'));
					$form_error = TRUE;
				}
			}
			
			$this->template->content->job_id = $job->id;
			$this->template->content->job_title = $job->job_title;
			$this->template->content->job_description = nl2br($job->job_description);
			$this->template->content->job_location = $job->location->location_name;
			$this->template->content->job_latitude = $job->location->latitude;
			$this->template->content->job_longitude = $job->location->longitude;
			$this->template->content->job_date = date('M j Y', strtotime($job->job_dateadd));
			$this->template->content->job_time = date('H:i', strtotime($job->job_dateadd));
			$this->template->content->job_category = $job->job_category;
			
			
			$this->template->content->job_verified = $job->job_verified; 

			// Retrieve Comments (Additional Information)
			$job_comments = array(); 
			if ($id)
			{
				$job_comments = ORM::factory('job_comment')
                                     ->where('job_id',$id)
                                     ->where('comment_active','1')
									 ->where('comment_spam','0')
                                     ->orderby('comment_date', 'asc')
                                     ->find_all();
			}

			$this->template->content->job_comments = $job_comments;
		}
		
		// Add Neighbors
		$this->template->content->job_neighbors = $this->_get_neighbors($job->location->latitude, 
                                                              $job->location->longitude);
				
		// Get RSS News Feeds
		$this->template->content->feeds = ORM::factory('feed_item')
                                          ->limit('5')
                                          ->orderby('item_date', 'desc')
                                          ->find_all();
		
		// Create object of the video embed class
		$video_embed = new VideoEmbed();
		$this->template->content->videos_embed = $video_embed;
		
		// Javascript Header
		$this->template->header->map_enabled = TRUE;
		$this->template->header->photoslider_enabled = TRUE;
		$this->template->header->videoslider_enabled = TRUE;
		$this->template->header->js = new View('jobs_view_js');
		$this->template->header->js->job_id = $job->id;
		$this->template->header->js->default_map = Kohana::config('settings.default_map');
		$this->template->header->js->default_zoom = Kohana::config('settings.default_zoom');
		$this->template->header->js->latitude = $job->location->latitude;
		$this->template->header->js->longitude = $job->location->longitude;
		
		//include footer form js file
        $footerjs = new View('footer_form_js');
		
		
        $this->template->header->js .= $footerjs;
		// Pack the javascript using the javascriptpacker helper
		$myPacker = new javascriptpacker($this->template->header->js, 'Normal', false, false);
		$this->template->header->js = $myPacker->pack();

		// Forms
        $this->template->content->form = $form;
		$this->template->content->captcha = $captcha;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_sent = $form_sent;
		
		// If the Admin is Logged in - Allow for an edit link
		$this->template->content->logged_in = $this->logged_in;
	}
	
	/**
	 * Report Thanks Page
	 */
	function thanks()
	{
		$this->template->header->this_page = 'jobs_submit';
		$this->template->content = new View('jobs_submit_thanks');
	}
	
	/**
	 * Apply job page.
	 */
	public function apply( $id = false ) {
		$this->template->header->this_page = 'job_apply';
		$this->template->content = new View('job_apply');
		
		if ( !$id )
		{
			url::redirect('jobsmain');
		}
		else
		{
			$job = ORM::factory('job', $id);
			
			$person = ORM::factory('job_person')->where('job_id',$id)->find();
			
			if ( $job->id == 0 )	// Not Found
			{
				url::redirect('jobsmain');
			}
		
			// Setup and initialize form field names
        	$form = array (
				'contact_name' => '',
				'contact_email' => '',
				'contact_phone' => '',
				'contact_subject' => '',			
				'contact_message' => '',
				'captcha' => ''
				);

        	// Copy the form as errors, so the errors will be stored with keys
        	// corresponding to the form field names
			$captcha = Captcha::factory();
        	$errors = $form;
        	$form_error = FALSE;
        	$form_sent = FALSE;
		
			// Check, has the form been submitted, if so, setup validation
			if ($_POST)
			{
				// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
				$post = Validation::factory($_POST);

				// Add some filters
				$post->pre_filter('trim', TRUE);
	
				// Add some rules, the input field, followed by a list of checks, carried out in order
				$post->add_rules('contact_name', 'required', 'length[3,100]');
				$post->add_rules('contact_email', 'required','email', 'length[4,100]');
				$post->add_rules('contact_subject', 'required', 'length[3,100]');
				$post->add_rules('contact_message', 'required');
				$post->add_rules('captcha', 'required', 'Captcha::valid');
			
				// Test to see if things passed the rule checks
				if ($post->validate())
				{
					$form_sent = $this->_send_application($post, $person->person_email,$id);
				
            	}
            	// No! We have validation errors, we need to show the form again, with the errors
            	else
            	{
                	// repopulate the form fields
                	$form = arr::overwrite($form, $post->as_array());

                	// populate the error fields, if any
                	$errors = arr::overwrite($errors, $post->errors('contact'));
                	$form_error = TRUE;
            	}
			}
        }
		
		$this->template->content->job_title = $job->job_title;
		$this->template->content->job_description = nl2br($job->job_description);
		$this->template->content->job_id = $id;	
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_sent = $form_sent;
		$this->template->content->captcha = $captcha;
	}
		
	/**
	 * Report Rating.
	 * @param boolean $id If id is supplied, a rating will be applied to selected report
	 */
	public function rating($id = false)
	{
		$this->template = "";
		$this->auto_render = FALSE;
		
		if (!$id)
		{
			echo json_encode(array("status"=>"error", "message"=>"ERROR!"));
		}
		else
		{
			if (!empty($_POST['action']) AND !empty($_POST['type'])) 
			{
				$action = $_POST['action'];
				$type = $_POST['type'];
				
				// Is this an ADD(+1) or SUBTRACT(-1)?
				if ($action == 'add') 
				{
					$action = 1;
				}
				elseif ($action == 'subtract') 
				{
					$action = -1;
				}
				else 
				{
					$action = 0;
				}
				
				if (!empty($action) AND ($type == 'original' OR $type == 'comment'))
				{
					// Has this IP Address rated this post before?
					if ($type == 'original') 
					{
						$previous = ORM::factory('rating')
                                    ->where('job_id',$id)
                                    ->where('rating_ip',$_SERVER['REMOTE_ADDR'])
                                    ->find();
					}
					elseif ($type == 'comment') 
					{
						$previous = ORM::factory('rating')
                                    ->where('comment_id',$id)
                                    ->where('rating_ip',$_SERVER['REMOTE_ADDR'])
                                    ->find();
					}
					
					// If previous exits... update previous vote
					$rating = new Rating_Model($previous->id);

					// Are we rating the original post or the comments?
					if ($type == 'original') 
					{
						$rating->job_id = $id;
					}
					elseif ($type == 'comment') 
					{
						$rating->comment_id = $id;
					}

					$rating->rating = $action;
					$rating->rating_ip = $_SERVER['REMOTE_ADDR'];
					$rating->rating_date = date("Y-m-d H:i:s",time());
					$rating->save();
					
					// Get total rating and send back to json
					$total_rating = $this->_get_rating($id, $type);
					
					echo json_encode(array("status"=>"saved", "message"=>"SAVED!", "rating"=>$total_rating));
				}
				else
				{
					echo json_encode(array("status"=>"error1", "message"=>"ERROR!"));
				}
			}
			else
			{
				echo json_encode(array("status"=>"error2", "message"=>"ERROR!"));
			}
		}
	}
		
	/*
	 * Retrieves Cities
	 */
	private function _get_cities()
	{
		$cities = ORM::factory('city')->orderby('city', 'asc')->find_all();
		$city_select = array('' => Kohana::lang('ui_main.reports_select_city'));
		
		foreach ($cities as $city) 
		{
			$city_select[$city->city_lon.",".$city->city_lat] = $city->city;
		}
		
		return $city_select;
	}

	/*
	 * Retrieves Categories
	 */	
	private function _get_categories($selected_categories)
	{
		// Count categories to determine column length
		$categories_total = ORM::factory('j_category')
                            ->where('job_category_visible', '1')
                            ->count_all();

		$this->template->content->categories_total = $categories_total;

		$categories = array();

		foreach (ORM::factory('j_category')
                 ->where('job_category_visible', '1')
                 ->find_all() as $category)
		{
			// Create a list of all categories
			$categories[$category->id] = array($category->job_category_title, $category->job_category_color);
		}

		return $categories;
	}
	
	/*
	 * Retrieves Total Rating For Specific Post
	 * Also Updates The job & Comment Tables (Ratings Column)
	 */
	private function _get_rating($id = false, $type = NULL)
	{
		if (!empty($id) AND ($type == 'original' OR $type == 'comment'))
		{
			if ($type == 'original') 
			{
				$which_count = 'job_id';
			} 
			elseif ($type == 'comment') 
			{
				$which_count = 'comment_id';
			}
			else 
			{
				return 0;
			}
			
			$total_rating = 0;
			
			// Get All Ratings and Sum them up
			foreach(ORM::factory('rating')
                    ->where($which_count,$id)
                    ->find_all() as $rating)
			{
				$total_rating += $rating->rating;
			}
			
			// Update Counts
			if ($type == 'original') 
			{
				$job = ORM::factory('job', $id);
				if ($job->loaded==true)
				{
					$job->job_rating = $total_rating;
					$job->save();
				}
			} 
			elseif ($type == 'comment') 
			{
				$comment = ORM::factory('comment', $id);
				if ($comment->loaded==true)
				{
					$comment->comment_rating = $total_rating;
					$comment->save();
				}
			}
			
			return $total_rating;
		} 
		else 
		{
			return 0;
		}
	}
	
	/*
	* Retrieves Neighboring jobs
	*/
	private function _get_neighbors($latitude = 0, $longitude = 0)
	{
		$proximity = new Proximity($latitude, $longitude, 100); // Within 100 Miles ( or Kms ;-) )
		
		// Generate query from proximity calculator
		$radius_query = "location.latitude >= '" . $proximity->minLat . "' 
                         AND location.latitude <= '" . $proximity->maxLat . "' 
                         AND location.longitude >= '" . $proximity->minLong . "'
                         AND location.longitude <= '" . $proximity->maxLong . "'
                         AND job_active = 1";
		
		$neighbors = ORM::factory('job')
                     ->join('location', 'job.location_id', 'location.id','INNER')
                     ->select('job.*')
                     ->where($radius_query)
                     ->limit('5')
                     ->find_all();
		
		return $neighbors;
	}
	
	private function _get_job_app_form() {
		
		$form = array ('captcha' => '',
			'contact_name' => '',
			'contact_email' => '',
			'contact_phone' => '',
			'contact_subject' => '',			
			'contact_message' => '',
			'comment' => 'Submit',
			'apply' => 'Apply'
		);
		
		// Load Akismet API Key (Spam Blocker)
		$api_akismet = Kohana::config('settings.api_akismet');
		
		$captcha = Captcha::factory();
		
		//  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;

		//has form been submitted, if so setup validation
		if($_POST)
		{

			$post = Validation::factory($_POST);

			//Trim whitespaces
			$post->pre_filter('trim', TRUE);

			//Add validation rules
			$post->add_rules('contact_name', 'required', 'length[3,100]');
			$post->add_rules('contact_email', 'required','email', 'length[4,100]');
			$post->add_rules('contact_subject', 'required', 'length[3,100]');
			$post->add_rules('contact_message', 'required');
			$post->add_rules('captcha', 'required', 'Captcha::valid');
			
			if( $post->validate() ) { 
				if($api_akismet != "" ) {
					// Run Akismet Spam Checker
						$akismet = new Akismet();

						// comment data
						$jobapply = array(
							'contact_name' => $post->contact_name,
							'contact_email' => $post->contact_email,
							'contact_subject' => $post->contact_subject,
							'contact_message' => $post->contact_message,
						);

						$config = array(
							'blog_url' => url::site(),
							'api_key' => $api_akismet,
							'jobapply' => $jobapply
						);

						$akismet->init($config);

						if($akismet->errors_exist()) 
						{
							if($akismet->is_error('AKISMET_INVALID_KEY'))
							{
								// throw new Kohana_Exception('akismet.api_key');
							}
							elseif($akismet->is_error('AKISMET_RESPONSE_FAILED')) 
							{
								// throw new Kohana_Exception('akismet.server_failed');
							}
							elseif($akismet->is_error('AKISMET_SERVER_NOT_FOUND')) 
							{
								// throw new Kohana_Exception('akismet.server_not_found');
							}
							// If the server is down, we have to post 
							// the comment :(
							// $this->_post_comment($comment);
							$jobapply_spam = 0;
						}
						else {
							if($akismet->is_spam()) 
							{
								$jobapply_spam = 1;
							}
							else {
								$jobapply_spam = 0;
							}
						}
					}
					else
					{ // No API Key!!
						$feedback_spam = 0;
					}
				$this->_dump_feedback($post);
			}
			else
	        {
				// repopulate the form fields
	            $form = arr::overwrite($form, $post->as_array());

	            // populate the error fields, if any
	            $errors = arr::overwrite($errors, $post->errors('feedback'));
				$form_error = TRUE;
			}
		}
		$this->template->footer->js = new View('footer_form_js');
		$this->template->footer->form = $form;
		$this->template->footer->captcha = $captcha;
		$this->template->footer->errors = $errors;
		$this->template->footer->form_error = $form_error;
	}
	
	private function _send_application($post,$person_email,$id) {
		
		$site_email = Kohana::config('settings.site_email');
		
		$message = "Sender: " . $post->contact_name . "<br />";
		$message .= "Email: " . $post->contact_email . "<br />";
		$message .= "Phone: " . $post->contact_phone . "<br /><br />";
		$message .= "Message: \n" . $post->contact_message . "<br /><br /><br />";
		$message .= "~~~~~~~~~~~~~~~~~~~~~~\n";
		$message .= "This is a reponse to the job placement below<br /><br />";
		$message . url::base()."jobs/view/$id";
		
		$to = $person_email;
		$from = $post->contact_email;
		$subject = $post->contact_subject;
		
		//email details
		if( email::send( $to, $from, $subject, $message, TRUE ) == 1 )
		{
			return TRUE;
		}
		else 
		{
			return FALSE;
		}
	}
        
} // End Reports
