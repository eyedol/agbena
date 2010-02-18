<?php 
/**
 * Reports submit view page.
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
		<!-- start report form block -->
		<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reportForm', 'name' => 'reportForm', 'class' => 'gen_forms')); ?>
		<input type="hidden" name="latitude" id="latitude" value="<?php echo $form['latitude']; ?>">
		<input type="hidden" name="longitude" id="longitude" value="<?php echo $form['longitude']; ?>">
		<div class="big-block">
			<h1><?php echo Kohana::lang('ui_main.jobs_submit_new'); ?></h1>
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
						// print "<li>" . $error_description . "</li>";
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
				?>
				</ul>
			</div>
			<?php
			}
			?>
			<div class="report_left">
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_title'); ?></h4>
					<?php print form::input('job_title', $form['job_title'], ' class="text long"'); ?>
				</div>
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_description'); ?></h4>
					<?php print form::textarea('job_description', $form['job_description'], ' rows="10" class="textarea long" ') ?>
				</div>
				
			<div class="report_row">
				<h4><?php echo Kohana::lang('ui_main.reports_categories'); ?></h4>
				<div class="report_category" id="categories">
					<?php
						//format categories for 2 column display
						$this_col = 1; // First column
						$maxper_col = round($categories_total/2); // Maximum number of elements per column
						$i = 1; // Element Count
						foreach ($categories as $category => $category_extra)
						{
							$category_title = $category_extra[0];
							$category_color = $category_extra[1];
							if ($this_col == 1) 
								echo "<ul>";
								if (!empty($selected_categories) && in_array($category, $selected_categories))
								{
									$category_checked = TRUE;
								}
								else
								{
									$category_checked = FALSE;
								}
											echo "\n<li><label>";
											echo form::checkbox('job_category[]', $category, $category_checked, ' class="check-box"');
											echo "$category_title";
											echo "</label></li>";
											if ($this_col == $maxper_col || $i == count($categories)) 
												print "</ul>\n";
											if ($this_col < $maxper_col)
											{
												$this_col++;
											} 
											else 
											{
												$this_col = 1;
											}
											$i++;
										}
										?>
									</div>
								</div>
								
								<div class="report_optional">
									<h3><?php echo Kohana::lang('ui_main.reports_optional'); ?></h3>
									<div class="report_row">
											 <h4><?php echo Kohana::lang('ui_main.reports_first'); ?></h4>
											 <?php print form::input('person_first', $form['person_first'], ' class="text long"'); ?>
									</div>
									<div class="report_row">
										<h4><?php echo Kohana::lang('ui_main.reports_last'); ?></h4>
										<?php print form::input('person_last', $form['person_last'], ' class="text long"'); ?>
									</div>
									<div class="report_row">
										<h4><?php echo Kohana::lang('ui_main.reports_email'); ?></h4>
										<?php print form::input('person_email', $form['person_email'], ' class="text long"'); ?>
									</div>
								</div>
							</div>
							<div class="report_right">
								<?php if (!$multi_country)
									{
								?>
								<div class="report_row">
									<h4><?php echo Kohana::lang('ui_main.reports_find_location'); ?></h4>
									<?php print form::dropdown('select_city',$cities,'', ' class="select" '); ?>
								</div>
								<?php
									 }
								?>
								<div class="report_row">
									<div id="divMap" class="report_map"></div>
									<div class="report-find-location">
										<?php print form::input('location_find', '', ' title="City, State and/or Country" class="findtext"'); ?>
										<div style="float:left;margin:9px 0 0 5px;"><input type="button" name="button" id="button" value="Find Location" class="btn_find" /></div>
										<div id="find_loading" class="report-find-loading"></div>
										<div style="clear:both;" id="find_text">* If you can't find your location, please click on the map to pinpoint the correct location.</div>
									</div>
								</div>
								
								<div class="report_row">
									<h4><?php echo Kohana::lang('ui_main.reports_location_name'); ?><br /><span class="example">Examples: Johannesburg, Corner City Market, 5th Street & 4th Avenue</span></h4>
									<?php print form::input('location_name', $form['location_name'], ' class="text long"'); ?>
								</div>
								
								<div class="report_row">
									<input name="submit" type="submit" value="<?php echo Kohana::lang('ui_main.reports_btn_submit'); ?>" class="btn_submit" /> 
								</div>
							</div>
						</div>
						<?php print form::close(); ?>
						<!-- end report form block -->
					</div>
				</div>
			</div>
		</div>
	</div>
