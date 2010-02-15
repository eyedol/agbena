<?php defined('SYSPATH') or die('No direct script access.');

/**
* Job Comments Table Model
*/

class Job_Comment_Model extends ORM
{
	protected $has_many = array('rating');
	protected $belongs_to = array('job');
	
	// Database table name
	protected $table_name = 'job_comment';
}