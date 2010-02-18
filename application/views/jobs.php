<?php 
/**
 *  Jobs view page.
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
?>

<div id="content">
	<div class="content-bg">
		<!-- start reports block -->
		<div class="big-block">
			<h1>Jobs <?php echo ($job_category_title) ? " in $job_category_title" : ""?>
			<?php echo $pagination_stats; ?></h1>
								
			<div class="report_rowtitle">
				<div class="report_col2">
					<strong>TITLE</strong>
				</div>
				<div class="report_col3">
					<strong>DATE</strong>
				</div>
				<div class="report_col4">
					<strong>LOCATION</strong>
				</div>
				<div class="report_col5">
					<strong>VERIFIED?</strong>
				</div>
			</div>
			<?php
			foreach ($jobs as $job)
			{
				$job_id = $job->id;
				$job_title = $job->job_title;
				$job_description = $job->job_description;
		
				// Trim to 150 characters without cutting words
				//XXX: Perhaps delcare 150 as constant
				$job_description = text::limit_chars($job_description, 150, "...", true);
				$job_date = date('Y-m-d', strtotime($job->job_dateadd));
				$job_location = $job->location->location_name;
				$job_verified = $job->job_verified;
				if ($job_verified)
				{
					$job_verified = "<span class=\"report_yes\">YES</span>";
				}
				else
				{
					$job_verified = "<span class=\"report_no\">NO</span>";
				}
												
				echo "		<div class=\"report_details report_col2\">";
				echo "			<h3><a href=\"" . url::base() . "jobs/view/" . $job_id . "\">" . $job_title . "</a></h3>";
				echo $job_description;
				echo "		</div>";
				echo "		<div class=\"report_date report_col3\">";
				echo $job_date;
				echo "		</div>";
				echo "		<div class=\"report_location report_col4\">";
				echo $job_location;
				echo "		</div>";
				echo "		<div class=\"report_status report_col5\">";
				echo $job_verified;
				echo "		</div>";
				echo "</div>";
			}
			?>
							<?php echo $pagination; ?>
						</div>
						<!-- end reports block -->
					</div>
				</div>
			</div>
		</div>
	</div>
