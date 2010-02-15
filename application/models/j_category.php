<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Categories of reported Incidents
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Category Model  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class J_Category_Model extends ORM_Tree
{	
	protected $has_many = array('job' => 'job_category', 'j_category_lang');
	
	// Database table name
	protected $table_name = 'j_category';
	protected $children = "j_category";
	
	static function categories($id=NULL,$locale='en_US')
	{
		if($id == NULL){
			$categories = ORM::factory('j_category')->where('locale',$locale)->find_all();
		}else{
			$categories = ORM::factory('j_category')->where('id',$id)->find_all(); // Don't need locale if we specify an id
		}
		
		$cats = array();
		foreach($categories as $category) {
			$cats[$category->id]['job_category_id'] = $category->id;
			$cats[$category->id]['job_category_title'] = $category->job_category_title;
			$cats[$category->id]['job_category_color'] = $category->job_category_color;
		}
		
		return $cats;
	}
}
