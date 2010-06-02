<?php 
/**
 * Jobs view page.
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
<div id="main" class="clearingfix">
	<div id="mainmiddle" class="floatbox withright">
		<!-- start incident block -->
		<div class="reports">
			<div class="report-details">
				<div class="verified <?php if ($job_verified == 1){echo " verified_yes";}?>">
					Verified<br/>
					<?php
						echo ($job_verified == 1) ?
							"<span>YES</span>" : "<span>NO</span>";
					?>
				</div>
				<h1><?php echo $job_title;
								// If Admin is Logged In - Allow For Edit Link
					if ($logged_in)
					{
						echo " [&nbsp;<a href=\"".url::base()."admin/jobs/edit/".$job_id."\">Edit</a>&nbsp;]";
					}
					?>
				</h1>
				
				<ul class="details">
					<li>
						<small>Location</small>
						<?php echo $job_location; ?>
					</li>
					<li>
						<small>Date</small>
						<?php echo $job_date; ?>
					</li>
					<li>
						<small>Category</small>
						<?php
							foreach($job_category as $category) 
							{ 
								echo "<a href=\"".url::base()."reports/?c=".$category->j_category->id."\">" .
								$category->j_category->job_category_title . "</a>&nbsp;&nbsp;&nbsp;";
							}
						?>
					</li>
				</ul>
					
			</div><!-- end report-details-->
				
		</div><!-- end reports -->
		
	</div><!-- end mainmiddle -->
	
</div> <!-- end main -->
<div class="report-description"> <!-- start report-description -->
	<h3>Job Description</h3>
	<div class="content">
		<?php echo $job_description; ?>
	</div><!-- end content -->
	<div class="orig-report">
		<div class="discussion">
			<h5>ANY COMMENT? (<a href="#comments">Add</a>)&nbsp;&nbsp; OR &nbsp;&nbsp; INTRESTED? (<a href="<?php echo url::base()."jobs/apply/$job_id";?>">Apply now &raquo;</a>)</h5>
			<?php
			foreach($job_comments as $comment)
			{
					echo "<div class=\"discussion-box\">";
					echo "<p><strong>" . $comment->comment_author . "</strong>&nbsp;(" . date('M j Y', strtotime($comment->comment_date)) . ")</p>";
					echo "<p>" . $comment->comment_description . "</p>";
					echo "<div class=\"report_rating\">";
					echo "	<div>";
					echo "	Credibility:&nbsp;";
					echo "	<a href=\"javascript:rating('" . $comment->id . "','add','comment','cloader_" . $comment->id . "')\"><img id=\"cup_" . $comment->id . "\" src=\"" . url::base() . 'media/img/' . "up.png\" alt=\"UP\" title=\"UP\" border=\"0\" /></a>&nbsp;";
					echo "	<a href=\"javascript:rating('" . $comment->id . "','subtract','comment','cloader_" . $comment->id . "')\"><img id=\"cdown_" . $comment->id . "\" src=\"" . url::base() . 'media/img/' . "down.png\" alt=\"DOWN\" title=\"DOWN\" border=\"0\" /></a>&nbsp;";
					echo "	</div>";
					echo "	<div class=\"rating_value\" id=\"crating_" . $comment->id . "\">" . $comment->comment_rating . "</div>";
					echo "	<div id=\"cloader_" . $comment->id . "\" class=\"rating_loading\" ></div>";
					echo "</div>";
					echo "</div>";
			}
			?>
						
		</div><!-- end discussion -->
	</div><!-- end orig-report -->		
</div><!-- end report-description -->

<br />
<!-- end incident block <> start other report -->
<a name="comments"></a>
<div class="big-block">
	<div id="comments" class="report_comment">
		<?php
		if ($form_error) {
		?>
			<!-- red-box -->
			<div class="red-box">
				<h3>Error!</h3>
				<ul>
					<?php
					foreach ($errors as $error_item => $error_description)
					{
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
					?>
				</ul>
			</div>
			<?php
		}
		?>
		<?php print form::open(NULL, array('id' => 'commentForm', 'name' => 'commentForm')); ?>
		<div class="report_row">
			<strong>Name:</strong><br />
			<?php print form::input('comment_author', $form['comment_author'], ' class="text"'); ?>
		</div>
		<div class="report_row">
			<strong>E-Mail:</strong><br />
			<?php print form::input('comment_email', $form['comment_email'], ' class="text"'); ?>
		</div>
		<div class="report_row">
			<strong>Comments:</strong><br />
				<?php print form::textarea('comment_description', $form['comment_description'], ' rows="4" cols="40" class="textarea long" ') ?>
			</div>
			<div class="report_row">
				<strong>Security Code:</strong><br />
				<?php print $captcha->render(); ?><br />
				<?php print form::input('captcha', $form['captcha'], ' class="text"'); ?>
			</div>
			<div class="report_row">
				<input name="submit" type="submit" value="Submit Comment" class="btn_submit" />
			</div>
			<?php print form::close(); ?>
		</div>
	</div>
</div>
</div>
</div>
