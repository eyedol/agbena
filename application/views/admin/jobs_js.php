/**
 * Main reports js file.
 * 
 * Handles javascript stuff related to jobs function.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

<?php require SYSPATH.'../application/views/admin/form_utils_js.php' ?>

		// Ajax Submission
		function jobAction ( action, confirmAction, job_id )
		{
			var statusMessage;
			if( !isChecked( "job" ) && job_id=='' )
			{ 
				alert('Please select at least one report.');
			} else {
				var answer = confirm('Are You Sure You Want To ' + confirmAction + ' items?')
				if (answer){
					
					// Set Submit Type
					$("#action").attr("value", action);
					
					if (job_id != '') 
					{
						// Submit Form For Single Item
						$("#job_single").attr("value", job_id);
						$("#jobMain").submit();
					}
					else
					{
						// Set Hidden form item to 000 so that it doesn't return server side error for blank value
						$("#job_single").attr("value", "000");
						
						// Submit Form For Multiple Items
						$("#jobMain").submit();
					}
				
				} else {
					return false;
				}
			}
		}
		

