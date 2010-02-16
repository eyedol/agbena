<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reports Controller.
 * This controller will take care of adding and editing jobs in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Reports Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Jobs_Controller extends Admin_Controller
{
	function __construct()
	{
		parent::__construct();
	
		$this->template->this_page = 'jobs';
	}
	
	
	/**
	* Lists the reports.
    * @param int $page
    */
	function index($page = 1)
	{
		$this->template->content = new View('admin/jobs');
		$this->template->content->title = 'Jobs';
		
		
		if (!empty($_GET['status']))
		{
			$status = $_GET['status'];
			
			if (strtolower($status) == 'a')
			{
				$filter = 'job_active = 0';
			}
			elseif (strtolower($status) == 'v')
			{
				$filter = 'job_verified = 0';
			}
			else
			{
				$status = "0";
				$filter = '1=1';
			}
		}
		else
		{
			$status = "0";
			$filter = "1=1";
		}
		
		// Get Search Keywords (If Any)
		if (isset($_GET['k']))
		{
			$keyword_raw = $_GET['k'];
			$filter .= " AND (".$this->_get_searchstring($keyword_raw).")";
		}
		else
		{
			$keyword_raw = "";
		}
		
		// check, has the form been submitted?
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
	    if ($_POST)
	    {
			$post = Validation::factory($_POST);
			
	         //  Add some filters
	        $post->pre_filter('trim', TRUE);

	        // Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('action','required', 'alpha', 'length[1,1]');
			$post->add_rules('job_id.*','required','numeric');
			
			if ($post->validate())
	        {
				if ($post->action == 'a')		// Approve Action
				{
					foreach($post->job_id as $item)
					{
						$update = new Job_Model($item);
						if ($update->loaded == true) {
							$update->job_active = '1';
							
							// Tag this as a report that needs to be sent out as an alert
							$update->job_alert_status = '1';
							$update->save();
							
							$verify = new Job_Verify_Model();
							$verify->job_id = $item;
							$verify->job_verified_status = '1';
							$verify->user_id = $_SESSION['auth_user']->id;			// Record 'Verified By' Action
							$verify->job_verified_date = date("Y-m-d H:i:s",time());
							$verify->save();
						}
					}
					$form_action = "APPROVED";
				}
				elseif ($post->action == 'u') 	// Unapprove Action
				{
					foreach($post->job_id as $item)
					{
						$update = new Job_Model($item);
						if ($update->loaded == true) {
							$update->job_active = '0';
							$update->save();
							
							$verify = new Job_Verify_Model();
							$verify->job_id = $item;
							$verify->job_verified_status = '0';
							$verify->user_id = $_SESSION['auth_user']->id;			// Record 'Verified By' Action
							$verify->job_verified_date = date("Y-m-d H:i:s",time());
							$verify->save();
						}
					}
					$form_action = "UNAPPROVED";
				}
				elseif ($post->action == 'v')	// Verify Action
				{
					foreach($post->job_id as $item)
					{
						$update = new Job_Model($item);
						$verify = new Job_Verify_Model();
						if ($update->loaded == true) {
							if ($update->job_verified == '1')
							{
								$update->job_verified = '0';
								$verify->job_verified_status = '0';
							}
							else {
								$update->job_verified = '1';
								$verify->job_verified_status = '2';
							}
							$update->save();
							
							$verify->job_id = $item;
							$verify->user_id = $_SESSION['auth_user']->id;			// Record 'Verified By' Action
							$verify->job_verified_date = date("Y-m-d H:i:s",time());
							$verify->save();
						}
					}
					$form_action = "VERIFIED";
				}
				elseif ($post->action == 'd')	//Delete Action
				{
					foreach($post->job_id as $item)
					{
						$update = new Job_Model($item);
						if ($update->loaded == true) {
							$job_id = $update->id;
							$location_id = $update->location_id;
							$update->delete();
							
							// Delete Location
							ORM::factory('location')->where('id',$location_id)->delete_all();
							
							// Delete Categories
							ORM::factory('job_category')->where('job_id',$job_id)->delete_all();
							
							// Delete Translations
							ORM::factory('job_lang')->where('job_id',$job_id)->delete_all();
							
							
							// Delete Sender
							ORM::factory('job_person')->where('job_id',$job_id)->delete_all();
							
							// Delete Comments
							ORM::factory('job_comment')->where('job_id',$job_id)->delete_all();
						}					
					}
					$form_action = "DELETED";
				}
				$form_saved = TRUE;
			}
			else
			{
				$form_error = TRUE;
			}
			
		}
		
		
		// Pagination
		$pagination = new Pagination(array(
			'query_string'    => 'page',
			'items_per_page' => (int) Kohana::config('settings.items_per_page_admin'),
			'total_items'    => ORM::factory('job')
				->where($filter)
				->join('location', 'job.location_id', 'location.id','INNER')
				->count_all()
		));

		$jobs = ORM::factory('job')
			->where($filter)->orderby('job_dateadd', 'desc')
			->join('location', 'job.location_id', 'location.id','INNER')
			->find_all((int) Kohana::config('settings.items_per_page_admin'), $pagination->sql_offset);
		
		//GET countries
		$countries = array();
		foreach (ORM::factory('country')->orderby('country')->find_all() as $country)
		{
			// Create a list of all categories
			$this_country = $country->country;
			if (strlen($this_country) > 35)
			{
				$this_country = substr($this_country, 0, 35) . "...";
			}
			$countries[$country->id] = $this_country;
		}
		
		$this->template->content->countries = $countries;		
		$this->template->content->jobs = $jobs;
		$this->template->content->pagination = $pagination;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		
		// Total Reports
		$this->template->content->total_items = $pagination->total_items;
		
		// Status Tab
		$this->template->content->status = $status;
		
		// Javascript Header
		$this->template->js = new View('admin/jobs_js');		
	}
	
	
	/**
	* Edit a job
    * @param bool|int $id The id no. of the report
    * @param bool|string $saved
    */
	function edit( $id = false, $saved = false )
	{
		$this->template->content = new View('admin/jobs_edit');
		$this->template->content->title = 'Create A Job';
		
		// setup and initialize form field names
		$form = array
	    (
	        'location_id'      => '',
			'locale'		   => '',
			'job_title'      => '',
	        'job_description'    => '',
	        'job_hour'      => '',
			'latitude' => '',
			'longitude' => '',
			'location_name' => '',
			'country_id' => '',
			'job_category' => array(),
			'person_first' => '',
			'person_last' => '',
			'person_email' => '',
			'job_active' => '',
			'job_verified' => '',
			'job_source' => '',
			'job_information' => ''
	    );
		
		//  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
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
		$form['locale'] = Kohana::config('locale.language');
		//$form['latitude'] = Kohana::config('settings.default_lat');
		//$form['longitude'] = Kohana::config('settings.default_lon');
		$form['country_id'] = Kohana::config('settings.default_country');
		$form['job_date'] = date("m/d/Y",time());
		$form['job_hour'] = date('g');
		$form['job_minute'] = date('i');
		$form['job_ampm'] = date('a');		
		
		// Locale (Language) Array
		$this->template->content->locale_array = Kohana::config('locale.all_languages');
		
        // Create Categories
        $this->template->content->job_categories = $this->_get_job_categories();	
		$this->template->content->new_job_categories_form = $this->_new_job_categories_form_arr();
		

        // Get Countries
		$countries = array();
		foreach (ORM::factory('country')->orderby('country')->find_all() as $country)
		{
			// Create a list of all categories
			$this_country = $country->country;
			if (strlen($this_country) > 35)
			{
				$this_country = substr($this_country, 0, 35) . "...";
			}
			$countries[$country->id] = $this_country;
		}
		$this->template->content->countries = $countries;
		
		// Retrieve thumbnail photos (if edit);
		//XXX: fix _get_thumbnails
		$this->template->content->job = $this->_get_thumbnails($id);
		
		// Are we creating this report from SMS/Email/Twitter?
		// If so retrieve message
		if ( isset($_GET['mid']) && !empty($_GET['mid']) ) {
			
			$message_id = $_GET['mid'];
			$service_id = "";
			$message = ORM::factory('message', $message_id);
			
			if ($message->loaded == true && $message->message_type == 1)
			{
				$service_id = $message->reporter->service_id;
				
				// Has a report already been created for this Message?
				if ($message->job_id != 0) {
					// Redirect to report
					url::redirect('admin/jobs/edit/'. $message->job_id);
				}

				$this->template->content->show_messages = true;
				$job_description = $message->message;
				if (!empty($message->message_detail))
				{
					$job_description .= "\n\n~~~~~~~~~~~~~~~~~~~~~~~~~\n\n"
					 	. $message->message_detail;
				}
				$form['job_description'] = $job_description;
				
				$form['job_hour'] = date('h', strtotime($message->message_date));
				$form['job_minute'] = date('i', strtotime($message->message_date));
				$form['job_ampm'] = date('a', strtotime($message->message_date));
				$form['person_first'] = $message->reporter->reporter_first;
				$form['person_last'] = $message->reporter->reporter_last;
				
				// Retrieve Last 5 Messages From this account
				$this->template->content->all_messages = ORM::factory('message')
					->where('reporter_id', $message->reporter_id)
					->orderby('message_date', 'desc')
					->limit(5)
					->find_all();
			}else{
				$message_id = "";
				$this->template->content->show_messages = false;
			}
		}else{
			$this->template->content->show_messages = false;
		}
		
		// Are we creating this report from a Newsfeed?
		if ( isset($_GET['fid']) && !empty($_GET['fid']) )
		{
			$feed_item_id = $_GET['fid'];
			$feed_item = ORM::factory('feed_item', $feed_item_id);
			
			if ($feed_item->loaded == true)
			{				
				// Has a report already been created for this Feed item?
				if ($feed_item->job_id != 0)
				{
					// Redirect to report
					url::redirect('admin/jobs/edit/'. $feed_item->job_id);
				}
				
				$form['job_title'] = $feed_item->item_title;
				$form['job_description'] = $feed_item->item_description;
				$form['job_hour'] = date('h', strtotime($feed_item->item_date));
				$form['job_ampm'] = date('a', strtotime($feed_item->item_date));
				
				// News Link
				$form['job_news'][0] = $feed_item->item_link;
				
				// Does this newsfeed have a geolocation?
				if ($feed_item->location_id)
				{
					$form['location_id'] = $feed_item->location_id;
					$form['latitude'] = $feed_item->location->latitude;
					$form['longitude'] = $feed_item->location->longitude;
					$form['location_name'] = $feed_item->location->location_name;
				}
			}
			else
			{
				$feed_item_id = "";
			}
		}
	
		// check, has the form been submitted, if so, setup validation
	    if ($_POST)
	    {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory(array_merge($_POST,$_FILES));

	         //  Add some filters
	        $post->pre_filter('trim', TRUE);

	        // Add some rules, the input field, followed by a list of checks, carried out in order
	        // $post->add_rules('locale','required','alpha_dash','length[5]');
			$post->add_rules('location_id','numeric');
			$post->add_rules('message_id','numeric');
			$post->add_rules('job_title','required', 'length[3,200]');
			$post->add_rules('job_description','required');
			$post->add_rules('latitude','required','between[-90,90]');		// Validate for maximum and minimum latitude values
			$post->add_rules('longitude','required','between[-180,180]');	// Validate for maximum and minimum longitude values
			$post->add_rules('location_name','required', 'length[3,200]');
			$post->add_rules('person_first','required', 'length[3,100]');
			$post->add_rules('person_last','required', 'length[3,100]');
			$post->add_rules('person_email','required', 'email', 'length[3,100]');
			
			//XXX: Hack to validate for no checkboxes checked
			if (!isset($_POST['job_category'])) {
				$post->job_category = "";
				$post->add_error('job_category','required');
			}
			else
			{
				$post->add_rules('job_category.*','required','numeric');
			}

			// Validate only the fields that are filled in	
	        if (!empty($_POST['job_news']))
			{
	        	foreach ($_POST['job_news'] as $key => $url) {
					if (!empty($url) AND !(bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED))
					{
						$post->add_error('job_news','url');
					}
	        	}
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
			
			$post->add_rules('job_active','required', 'between[0,1]');
			$post->add_rules('job_verified','required', 'length[0,1]');
			$post->add_rules('job_source','alpha', 'length[1,1]');
			$post->add_rules('job_information','numeric', 'length[1,1]');
			
			// Test to see if things passed the rule checks
	        if ($post->validate())
	        {
                // Yes! everything is valid
				$location_id = $post->location_id;
				// STEP 1: SAVE LOCATION
				$location = new Location_Model($location_id);
				$location->location_name = $post->location_name;
				$location->latitude = $post->latitude;
				$location->longitude = $post->longitude;
				$location->location_date = date("Y-m-d H:i:s",time());
				$location->save();
				
				// STEP 2: SAVE job
				$job = new Job_Model($id);
				$job->location_id = $location->id;
				$job->user_id = $_SESSION['auth_user']->id;
				$job->job_title = $post->job_title;
				$job->job_description = $post->job_description;
				
				// Is this new or edit?
				if ($id)	// edit
				{
					$job->job_datemodify = date("Y-m-d H:i:s",time());
				}
				else 		// new
				{
					$job->job_dateadd = date("Y-m-d H:i:s",time());
				}
				// Is this an Email, SMS, Twitter submitted report?
                //XXX: We may get rid of job_mode altogether... ???
                //$_POST
				if(!empty($service_id))
				{
					if ($service_id == 1)
					{ // SMS
						$job->job_mode = 2;
					}
					elseif ($service_id == 2)
					{ // Email
						$job->job_mode = 3;
					}
					elseif ($service_id == 3)
					{ // Twitter
						$job->job_mode = 4;
					}
					elseif ($service_id == 4)
					{ // Laconica
						$job->job_mode = 5;
					}
				}
				// job Evaluation Info
				$job->job_active = $post->job_active;
				$job->job_verified = $post->job_verified;
				//Save
				$job->save();
				
				// Record Approval/Verification Action
				$verify = new Job_Verify_Model();
				$verify->job_id = $job->id;
				$verify->user_id = $_SESSION['auth_user']->id;			// Record 'Verified By' Action
				$verify->job_verified_date = date("Y-m-d H:i:s",time());
				if ($post->job_active == 1)
				{
					$verify->job_verified_status = '1';
				}
				elseif ($post->job_verified == 1)
				{
					$verify->job_verified_status = '2';
				}
				elseif ($post->job_active == 1 && $post->job_verified == 1)
				{
					$verify->job_verified_status = '3';
				}
				else
				{
					$verify->job_verified_status = '0';
				}
				$verify->save();
				
				
				// STEP 3: SAVE CATEGORIES
				ORM::factory('Job_Category')->where('job_id',$job->id)->delete_all();		// Delete Previous Entries
				foreach($post->job_category as $item)
				{
					$job_category = new Job_Category_Model();
					$job_category->job_id = $job->id;
					$job_category->j_category_id = $item;
					$job_category->save();
				}
				
				// STEP 5: SAVE PERSONAL INFORMATION
				ORM::factory('Job_Person')->where('job_id',$job->id)->delete_all();		// Delete Previous Entries
	            $person = new Job_Person_Model();
				$person->location_id = $location->id;
				$person->job_id = $job->id;
				$person->person_first = $post->person_first;
				$person->person_last = $post->person_last;
				$person->person_email = $post->person_email;
				$person->person_date = date("Y-m-d H:i:s",time());
				$person->save();
				
				// SAVE AND CLOSE?
				if ($post->save == 1)		// Save but don't close
				{
					url::redirect('admin/jobs/edit/'. $job->id .'/saved');
				}
				else 						// Save and close
				{
					url::redirect('admin/jobs/');
				}
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
		else
		{
			if ( $id )
			{
				// Retrieve Current job
				$job = ORM::factory('job', $id);
				if ($job->loaded == true)
				{
					// Retrieve Categories
					$job_category = array();
					foreach($job->job_category as $category) 
					{ 
						
						$job_category[] = $category->j_category_id;
					}
					
					// Retrieve Media
					$job_news = array();
		
					// Combine Everything
					$job_arr = array
				    (
					    'location_id' => $job->location->id,
						'locale' => $job->locale,
						'job_title' => $job->job_title,
						'job_description' => $job->job_description,
						'latitude' => $job->location->latitude,
						'longitude' => $job->location->longitude,
						'location_name' => $job->location->location_name,
						'country_id' => $job->location->country_id,
						'job_category' => $job_category,
						'person_first' => $job->job_person->person_first,
						'person_last' => $job->job_person->person_last,
						'person_email' => $job->job_person->person_email,
						'job_active' => $job->job_active,
						'job_verified' => $job->job_verified,
						
				    );
					
					// Merge To Form Array For Display
					$form = arr::overwrite($form, $job_arr);
				}
				else
				{
					// Redirect
					url::redirect('admin/jobs/');
				}		
				
			}
		}
	
		$this->template->content->id = $id;
		$this->template->content->form = $form;
	    $this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		
		// Retrieve Previous & Next Records
		$previous = ORM::factory('job')->where('id < ', $id)->orderby('id','desc')->find();
		$previous_url = ($previous->loaded ? 
				url::base().'admin/jobs/edit/'.$previous->id : 
				url::base().'admin/jobs/');
		$next = ORM::factory('job')->where('id > ', $id)->orderby('id','desc')->find();
		$next_url = ($next->loaded ? 
				url::base().'admin/jobs/edit/'.$next->id : 
				url::base().'admin/jobs/');
		$this->template->content->previous_url = $previous_url;
		$this->template->content->next_url = $next_url;
		
		// Javascript Header
		$this->template->map_enabled = TRUE;
        $this->template->colorpicker_enabled = TRUE;
		$this->template->js = new View('admin/jobs_edit_js');
		$this->template->js->default_map = Kohana::config('settings.default_map');
		$this->template->js->default_zoom = Kohana::config('settings.default_zoom');
		
		if (!$form['latitude'] || !$form['latitude'])
		{
			$this->template->js->latitude = Kohana::config('settings.default_lat');
			$this->template->js->longitude = Kohana::config('settings.default_lon');
		}
		else
		{
			$this->template->js->latitude = $form['latitude'];
			$this->template->js->longitude = $form['longitude'];
		}
		
		// Inline Javascript
		$this->template->content->date_picker_js = $this->_date_picker_js();
        $this->template->content->color_picker_js = $this->_color_picker_js();
        $this->template->content->new_category_toggle_js = $this->_new_category_toggle_js();
	}


	/**
	* Download Reports in CSV format
    *
    
	function download()
	{
		$this->template->content = new View('admin/reports_download');
		$this->template->content->title = 'Download Reports';
		
		$form = array(
			'data_point'      => '',
			'data_include'      => '',
			'from_date'    => '',
			'to_date'    => ''
		);
		$errors = $form;
		$form_error = FALSE;
		
		// check, has the form been submitted, if so, setup validation
	    if ($_POST)
	    {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory($_POST);

	         //  Add some filters
	        $post->pre_filter('trim', TRUE);

	        // Add some rules, the input field, followed by a list of checks, carried out in order
	        $post->add_rules('data_point.*','required','numeric','between[1,4]');
			$post->add_rules('data_include.*','numeric','between[1,5]');
			$post->add_rules('from_date','date_mmddyyyy');
			$post->add_rules('to_date','date_mmddyyyy');
			
			// Validate the report dates, if included in report filter
			if (!empty($_POST['from_date']) || !empty($_POST['to_date']))
			{	
				// Valid FROM Date?
				if (empty($_POST['from_date']) || (strtotime($_POST['from_date']) > strtotime("today"))) {
					$post->add_error('from_date','range');
				}
				
				// Valid TO date?
				if (empty($_POST['to_date']) || (strtotime($_POST['to_date']) > strtotime("today"))) {
					$post->add_error('to_date','range');
				}
				
				// TO Date not greater than FROM Date?
				if (strtotime($_POST['from_date']) > strtotime($_POST['to_date'])) {
					$post->add_error('to_date','range_greater');
				}
			}
			
			// Test to see if things passed the rule checks
	        if ($post->validate())
	        {
				// Add Filters
				$filter = " ( 1=1";
				// Report Type Filter
				foreach($post->data_point as $item)
				{
					if ($item == 1) {
						$filter .= " OR job_active = 1 ";
					}
					if ($item == 2) {
						$filter .= " OR job_verified = 1 ";
					}
					if ($item == 3) {
						$filter .= " OR job_active = 0 ";
					}
					if ($item == 4) {
						$filter .= " OR job_verified = 0 ";
					}
				}
				$filter .= ") ";
				
				// Report Date Filter
				if (!empty($post->from_date) && !empty($post->to_date)) 
				{
					$filter .= " AND ( job_date >= '" . date("Y-m-d H:i:s",strtotime($post->from_date)) . "' AND job_date <= '" . date("Y-m-d H:i:s",strtotime($post->to_date)) . "' ) ";					
				}
				
				// Retrieve reports
				$jobs = ORM::factory('job')->where($filter)->orderby('job_dateadd', 'desc')->find_all();
				
				// Column Titles
				$report_csv = "#,job TITLE,job DATE";
				foreach($post->data_include as $item)
				{
					if ($item == 1) {
						$report_csv .= ",LOCATION";
					}
					if ($item == 2) {
						$report_csv .= ",DESCRIPTION";
					}
					if ($item == 3) {
						$report_csv .= ",CATEGORY";
                                        }
                                        if ($item == 4) {
                                                $report_csv .= ",LATITUDE";
                                        }
                                        if($item == 5) {
                                                $report_csv .= ",LONGITUDE";
                                        }
				}
				$report_csv .= ",APPROVED,VERIFIED";
				$report_csv .= "\n";
				
				foreach ($jobs as $job)
				{
					$report_csv .= '"'.$job->id.'",';
					$report_csv .= '"'.htmlspecialchars($job->job_title).'",';
					$report_csv .= '"'.$job->job_date.'"';
					
					foreach($post->data_include as $item)
					{
						if ($item == 1) {
                                                        $report_csv .= ',"'.htmlspecialchars($job->location->location_name).'"';
						}
						if ($item == 2) {
							$report_csv .= ',"'.htmlspecialchars($job->job_description).'"';
						}
						if ($item == 3) {
							$report_csv .= ',"';
							foreach($job->job_category as $category) 
							{ 
								$report_csv .= htmlspecialchars($category->category->category_title) . ", ";
							}
							$report_csv .= '"';
                                                }
                                                if ($item == 4) {
                                                        $report_csv .= ',"'.htmlspecialchars($job->location->latitude).'"';
                                                }
                                                if ($item == 5) {
                                                        $report_csv .= ',"'.htmlspecialchars($job->location->longitude).'"';
                                                }
					}
					if ($job->job_active) {
						$report_csv .= ",YES";
					}
					else
					{
						$report_csv .= ",NO";
					}
					if ($job->job_verified) {
						$report_csv .= ",YES";
					}
					else
					{
						$report_csv .= ",NO";
					}
					$report_csv .= "\n";
				}
				
				// Output to browser
				header("Content-type: text/x-csv");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Disposition: attachment; filename=" . time() . ".csv");
				header("Content-Length: " . strlen($report_csv));
				echo $report_csv;
				exit;
				
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
		
		$this->template->content->form = $form;
	    $this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		
		// Javascript Header
		$this->template->js = new View('admin/reports_download_js');
		$this->template->js->calendar_img = url::base() . "media/img/icon-calendar.gif";
	}
    function upload() {
		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->template->content = new View('admin/reports_upload');
			$this->template->content->title = 'Upload Reports';
			$this->template->content->form_error = false;
		}
		if($_SERVER['REQUEST_METHOD']=='POST') {
			$errors = array();
			$notices = array();
			 if(!$_FILES['csvfile']['error']) {
			if(file_exists($_FILES['csvfile']['tmp_name'])) {
			if($filehandle = fopen($_FILES['csvfile']['tmp_name'], 'r')) {
			$importer = new ReportsImporter;
			if($importer->import($filehandle)) {
			$this->template->content = new View('admin/reports_upload_success');
			$this->template->content->title = 'Upload Reports';
			$this->template->content->rowcount = $importer->totalrows;
			$this->template->content->imported = $importer->importedrows;
			$this->template->content->notices = $importer->notices;
			}
			else {
			$errors = $importer->errors;
			}
			}
			else {
			$errors[] = 'Could not open file for reading';
			}
			} // file exists?
			else {
			$errors[] = 'Could not find uploaded file';
			}
			} // upload errors?
			else {
			$errors[] = $_FILES['csvfile']['error'];
			}
			if(count($errors)) {
				$this->template->content = new View('admin/reports_upload');
				$this->template->content->title = 'Upload Reports';		
				$this->template->content->errors = $errors;
				$this->template->content->form_error = 1;
			}
		} // _POST
	}*/

	/**
	* Translate a report
    * @param bool|int $id The id no. of the report
    * @param bool|string $saved
    */
    
	function translate( $id = false, $saved = false )
	{
		$this->template->content = new View('admin/jobs_translate');
		$this->template->content->title = 'Translate Report';
		
		// Which job are we adding this translation for?
		if (isset($_GET['iid']) && !empty($_GET['iid']))
		{
			$job_id = $_GET['iid'];
			$job = ORM::factory('job', $job_id);
			if ($job->loaded == true)
			{
				$orig_locale = $job->locale;
				$this->template->content->orig_title = $job->job_title;
				$this->template->content->orig_description = $job->job_description;
			}
			else
			{
				// Redirect
				url::redirect('admin/jobs/');
			}
		}
		else
		{
			// Redirect
			url::redirect('admin/jobs/');
		}
		
		
		// setup and initialize form field names
		$form = array
	    (
	        'locale'      => '',
			'job_title'      => '',
			'job_description'    => ''
	    );
		//  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
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
		
		// Locale (Language) Array
		$this->template->content->locale_array = Kohana::config('locale.all_languages');
	
		// check, has the form been submitted, if so, setup validation
	    if ($_POST)
	    {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory($_POST);

	         //  Add some filters
	        $post->pre_filter('trim', TRUE);

	        // Add some rules, the input field, followed by a list of checks, carried out in order
	        $post->add_rules('locale','required','alpha_dash','length[5]');
			$post->add_rules('job_title','required', 'length[3,200]');
			$post->add_rules('job_description','required');
			$post->add_callbacks('locale', array($this,'translate_exists_chk'));
			
			if ($orig_locale == $_POST['locale'])
			{
				// The original report and the translation are the same language!
				$post->add_error('locale','locale');
			}
			
			// Test to see if things passed the rule checks
	        if ($post->validate())
	        {
				// SAVE job TRANSLATION
				$job_l = new Job_Lang_Model($id);
				$job_l->job_id = $job_id;
				$job_l->locale = $post->locale;
				$job_l->job_title = $post->job_title;
				$job_l->job_description = $post->job_description;
				$job_l->save();
				
				
				// SAVE AND CLOSE?
				if ($post->save == 1)		// Save but don't close
				{
					url::redirect('admin/jobs/translate/'. $job_l->id .'/saved/?iid=' . $job_id);
				}
				else 						// Save and close
				{
					url::redirect('admin/jobs/');
				}
	        }
	
            // No! We have validation errors, we need to show the form again, with the errors
	        else   
			{
	            // repopulate the form fields
	            $form = arr::overwrite($form, $post->as_array());

	            // populate the error fields, if any
	            $errors = arr::overwrite($errors, $post->errors('job'));
				$form_error = TRUE;
	        }
	    }
		else
		{
			if ( $id )
			{
				// Retrieve Current job
				$job_l = ORM::factory('job_lang', $id)->where('job_id', $job_id)->find();
				if ($job_l->loaded == true)
				{
					$form['locale'] = $job_l->locale;
					$form['job_title'] = $job_l->job_title;
					$form['job_description'] = $job_l->job_description;
				}
				else
				{
					// Redirect
					url::redirect('admin/jobs/');
				}		
				
			}
		}
	
		$this->template->content->form = $form;
	    $this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		
		// Javascript Header
		$this->template->js = new View('admin/jobs_translate_js');
	}




    /**
    * Save newly added dynamic categories
    */
	function save_category()
	{
		$this->auto_render = FALSE;
		$this->template = "";
		
		// check, has the form been submitted, if so, setup validation
	    if ($_POST)
	    {
	        // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory($_POST);
			
	         //  Add some filters
	        $post->pre_filter('trim', TRUE);

	        // Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('job_category_title','required', 'length[3,200]');
			$post->add_rules('job_category_description','required');
			$post->add_rules('job_category_color','required', 'length[6,6]');
			
			
			// Test to see if things passed the rule checks
	        if ($post->validate())
	        {
				// SAVE Category
				$job_category = new Job_Category_Model();
				$job_category->job_category_title = $post->job_category_title;
				$job_category->job_category_description = $post->job_category_description;
				$job_category->job_category_color = $post->job_category_color;
				$job_category->save();
				$form_saved = TRUE;

				echo json_encode(array("status"=>"saved", "id"=>$job_category->id));
	        }
            
	        else
	        
			{
	            echo json_encode(array("status"=>"error"));
	        }
	    }
		else
		{
			echo json_encode(array("status"=>"error"));
		}
	}

	/** 
    * Delete Photo 
    * @param int $id The unique id of the photo to be deleted
    */
	function deletePhoto ( $id )
	{
		$this->auto_render = FALSE;
		$this->template = "";
		
		if ( $id )
		{
			$photo = ORM::factory('media', $id);
			$photo_large = $photo->media_link;
			$photo_thumb = $photo->media_thumb;
			
			// Delete Files from Directory
			if (!empty($photo_large))
				unlink(Kohana::config('upload.directory', TRUE) . $photo_large);
			if (!empty($photo_thumb))
				unlink(Kohana::config('upload.directory', TRUE) . $photo_thumb);

			// Finally Remove from DB
			$photo->delete();
		}
	}
	
	/* private functions */
	
	// Return thumbnail photos
	//XXX: This needs to be fixed, it's probably ok to return an empty iterable instead of "0"
	private function _get_thumbnails( $id )
	{
		$job = ORM::factory('job', $id);
		
		if ( $id )
		{
			$job = ORM::factory('job', $id);
			
			return $job;
		
		}
		return "0";
	}
	
    private function _get_job_categories()
    {
 	    // get categories array
		//$this->template->content->bind('categories', $categories);
				
        $job_categories_total = ORM::factory('j_category')->where('job_category_visible', '1')->count_all();
        $this->template->content->job_categories_total = $job_categories_total;

		$job_categories = array();
		
		foreach (ORM::factory('j_category')->where('job_category_visible', '1')->find_all() as $job_category)
		{
			// Create a list of all categories
			$job_categories[$job_category->id] = array($job_category->job_category_title, $job_category->job_category_color);
		}
		
	    return $job_categories;
		
	}

    // Dynamic categories form fields
    private function _new_job_categories_form_arr()
    {
        return array
        (
            'job_category_name' => '',
            'job_category_description' => '',
            'job_category_color' => '',
        );
    }

    // Time functions
    private function _hour_array()
    {
        for ($i=1; $i <= 12 ; $i++) 
        { 
		    $hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i); 	// Add Leading Zero
		}
	    return $hour_array;	
	}
									
	private function _minute_array()
	{								
		for ($j=0; $j <= 59 ; $j++) 
		{ 
			$minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);	// Add Leading Zero
		}
		
		return $minute_array;
	}
	
	private function _ampm_array()
	{								
	    return $ampm_array = array('pm'=>'pm','am'=>'am');
	}
	
	// Javascript functions
	 private function _color_picker_js()
    {
     return "<script type=\"text/javascript\">
				$(document).ready(function() {
                $('#category_color').ColorPicker({
                        onSubmit: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onChange: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onBeforeShow: function () {
                            $(this).ColorPickerSetColor(this.value);
                        }
                    })
                .bind('keyup', function(){
                    $(this).ColorPickerSetColor(this.value);
                });
				});
            </script>";
    }
    
    private function _date_picker_js() 
    {
        return "<script type=\"text/javascript\">
				$(document).ready(function() {
				$(\"#job_date\").datepicker({ 
				showOn: \"both\", 
				buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\", 
				buttonImageOnly: true 
				});
				});
			</script>";	
    }
    

    private function _new_category_toggle_js()
    {
        return "<script type=\"text/javascript\">
			    $(document).ready(function() {
			    $('a#category_toggle').click(function() {
			    $('#category_add').toggle(400);
			    return false;
				});
				});
			</script>";
    }


	/**
	 * Checks if translation for this report & locale exists
     * @param Validation $post $_POST variable with validation rules 
	 * @param int $iid The unique job_id of the original report
	 */
	public function translate_exists_chk(Validation $post)
	{
		// If add->rules validation found any errors, get me out of here!
		if (array_key_exists('locale', $post->errors()))
			return;
		
		$iid = $_GET['iid'];
		if (empty($iid)) {
			$iid = 0;
		}
		$translate = ORM::factory('job_lang')->where('job_id',$iid)->where('locale',$post->locale)->find();
		if ($translate->loaded == true) {
			$post->add_error( 'locale', 'exists');		
		// Not found
		} else {
			return;
		}
	}

	/**
	 * Creates a SQL string from search keywords
	 */
	private function _get_searchstring($keyword_raw)
	{
		$or = '';
		$where_string = '';
		
		
		// Stop words that we won't search for
		// Add words as needed!!
		$stop_words = array('the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it', 
		'on', 'you', 'this', 'for', 'but', 'with', 'are', 'have', 'be', 
		'at', 'or', 'as', 'was', 'so', 'if', 'out', 'not');
		
		$keywords = explode(' ', $keyword_raw);
		if (is_array($keywords) && !empty($keywords)) {
			array_change_key_case($keywords, CASE_LOWER);
			$i = 0;
			foreach($keywords as $value) {
				if (!in_array($value,$stop_words) && !empty($value))
				{
					$chunk = mysql_real_escape_string($value);
					if ($i > 0) {
						$or = ' OR ';
					}
					$where_string = $where_string.$or."job_title LIKE '%$chunk%' OR job_description LIKE '%$chunk%'  OR location_name LIKE '%$chunk%'";
					$i++;
				}
			}
		}
		
		if ($where_string)
		{
			return $where_string;
		}
		else
		{
			return "1=1";
		}
	}
}
