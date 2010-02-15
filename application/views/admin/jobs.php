<?php 
/**
 * Reports view page.
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
<div class="bg">
	<h2><?php echo $title; ?> <span>(<?php echo $total_items; ?>)</span><a href="<?php print url::base() ?>admin/jobs/edit">Create New Job</a></h2>
	<!-- tabs -->
	<div class="tabs">
		<!-- tabset -->
		<ul class="tabset">
			<li><a href="?status=0" <?php if ($status != 'a' && $status !='v') echo "class=\"active\""; ?>>Show All</a></li>
			<li><a href="?status=a" <?php if ($status == 'a') echo "class=\"active\""; ?>>Awaiting Approval</a></li>
			<li><a href="?status=v" <?php if ($status == 'v') echo "class=\"active\""; ?>>Awaiting Verification</a></li>
		</ul>
	
		<!-- tab -->
		<div class="tab">
			<ul>
				<li><a href="#" onclick="jobAction('a','APPROVE', '');">APPROVE</a></li>
				<li><a href="#" onclick="jobAction('u','UNAPPROVE', '');">UNAPPROVED</a></li>
				<li><a href="#" onclick="jobAction('v','VERIFY', '');">VERIFY</a></li>
				<li><a href="#" onclick="jobAction('d','DELETE', '');">DELETE</a></li>
			</ul>
		</div>
	</div>
	<?php
if ($form_error) {
	?>
	<!-- red-box -->
	<div class="red-box">
		<h3>Error!</h3>
		<ul>Please verify that you have checked an item</ul>
	</div>
<?php
}

if ($form_saved) {
?>
	<!-- green-box -->
	<div class="green-box" id="submitStatus">
		<h3>Jobs <?php echo $form_action; ?> <a href="#" id="hideMessage" class="hide">hide this message</a></h3>
	</div>
<?php
}
?>
	<!-- report-table -->
	<?php print form::open(NULL, array('id' => 'jobMain', 'name' => 'jobMain')); ?>
	<input type="hidden" name="action" id="action" value="">
	<input type="hidden" name="job_id[]" id="job_single" value="">
	<div class="table-holder">
		<table class="table">
			<thead>
				<tr>
									<th class="col-1"><input id="checkalljobs" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'job_id[]' )" /></th>
									<th class="col-2">Report Details</th>
									<th class="col-3">Date</th>
									<th class="col-4">Actions</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="foot">
					<td colspan="4">
						<?php echo $pagination; ?>
					</td>
				</tr>
			</tfoot>
		<tbody>
		<?php
		if ($total_items == 0)
		{
		?>
			<tr>
				<td colspan="4" class="col">
					<h3>No Results To Display!</h3>
				</td>
			</tr>
		<?php	
		}
		
		foreach ($jobs as $job)
		{
			$job_id = $job->id;
			$job_title = $job->job_title;
			$job_description = text::limit_chars($job->job_description, 150, "...", true);
			$job_date = $job->job_dateadd;
			$job_date = date('Y-m-d', strtotime($job->job_dateadd));
			$job_mode = $job->job_mode;	// Mode of submission... WEB/SMS/EMAIL?
									
			//XXX job_Mode will be discontinued in favour of $service_id
			if ($job_mode == 1)	// Submitted via WEB
			{
				$submit_mode = "WEB";
				// Who submitted the report?
				if ($job->job_person->id)
				{
											// Report was submitted by a visitor
											$submit_by = $job->job_person->person_first . " " . $job->job_person->person_last;
				}
				else
				{
					if ($job->user_id)					// Report Was Submitted By Administrator
					{
						$submit_by = $job->user->name;
					}
					else
					{
						$submit_by = 'Unknown';
					}
				}
			}
			elseif ($job_mode == 2) 	// Submitted via SMS
			{
				$submit_mode = "SMS";
				$submit_by = $job->message->message_from;
			}
			elseif ($job_mode == 3) 	// Submitted via Email
			{
				$submit_mode = "EMAIL";
				$submit_by = $job->message->message_from;
			}
			elseif ($job_mode == 4) 	// Submitted via Twitter
			{
				$submit_mode = "TWITTER";
				$submit_by = $job->message->message_from;
			}
			elseif ($job_mode == 5) 	// Submitted via Laconica
			{
				$submit_mode = "LACONICA";
				$submit_by = $job->message->message_from;
			}
									
			$job_location = $job->location->location_name;

			// Retrieve job Categories
			$job_category = "";
			foreach($job->job_category as $category) 
			{ 
				
				$job_category .= "<a href=\"#\">" . $category->j_category->job_category_title . "</a>&nbsp;&nbsp;";
			}
									
			// job Status
			$job_approved = $job->job_active;
			$job_verified = $job->job_verified;
									
			// Get Any Translations
			$i = 1;
			$job_translation  = "<div class=\"post-trans-new\">";
			$job_translation .= "<a href=\"" . url::base() . 'admin/jobs/translate/?iid=' . $job_id . "\">+ADD TRANSLATION:</a></div>";
			foreach ($job->job_lang as $translation) {
				$job_translation .= "<div class=\"post-trans\">";
				$job_translation .= "Translation " . $i . ": ";
				$job_translation .= "<a href=\"" . url::base() . 'admin/jobs/translate/'. $translation->id .'/?iid=' . $job_id . "\">"
					. text::limit_chars($translation->job_title, 150, "...", true) 
				. "</a>";
				$job_translation .= "</div>";
			}
			?>
			<tr>
				<td class="col-1"><input name="job_id[]" id="job" value="<?php echo $job_id; ?>" type="checkbox" class="check-box"/></td>
				<td class="col-2">
					<div class="post">
						<h4><a href="<?php echo url::base() . 'admin/jobs/edit/' . $job_id; ?>" class="more"><?php echo $job_title; ?></a></h4>
						<p><?php echo $job_description; ?>... <a href="<?php echo url::base() . 'admin/reports/edit/' . $job_id; ?>" class="more">more</a></p>
					</div>
					<ul class="info">
						<li class="none-separator">Location: <strong><?php echo $job_location; ?></strong>, <strong><?php echo $countries[Kohana::config('settings.default_country')]; ?></strong></li>
						<li>Submitted by <strong><?php echo $submit_by; ?></strong> via <strong><?php echo $submit_mode; ?></strong></li>
					</ul>
					<ul class="links">
						<li class="none-separator">Categories:<?php echo $job_category; ?></li>
					</ul>
				<?php
					//XXX DISABLED Until Completed
					// echo $job_translation;
				?>
				</td>
				<td class="col-3"><?php echo $job_date; ?></td>
				<td class="col-4">
					<ul>
						<li class="none-separator"><a href="#"<?php if ($job_approved) echo " class=\"status_yes\"" ?> onclick="jobAction('a','APPROVE', '<?php echo $job_id; ?>');">Approve</a></li>
						<li><a href="#"<?php if ($job_verified) echo " class=\"status_yes\"" ?> onclick="jobAction('v','VERIFY', '<?php echo $job_id; ?>');">Verify</a></li>
						<li><a href="#" class="del" onclick="jobAction('d','DELETE', '<?php echo $job_id; ?>');">Delete</a></li>
					</ul>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div>
<?php print form::close(); ?>
</div>
