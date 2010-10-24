<?php 
/**
 * Reports edit view page.
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
				<h2><?php print $title; ?> <span></span><a href="<?php print url::base() ?>admin/jobs">View Jobs</a></h2>
				<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'jobForm', 'name' => 'jobForm')); ?>
					<input type="hidden" name="save" id="save" value="">
					<input type="hidden" name="location_id" id="location_id" value="<?php print $form['location_id']; ?>">
					<!-- report-form -->
					<div class="report-form">
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

						if ($form_saved) {
						?>
							<!-- green-box -->
							<div class="green-box">
								<h3>Your Job Has Been Saved!</h3>
							</div>
						<?php
						}
						?>
						<div class="head">
							<h3><?php echo $id ? "Edit Job" : "New Job"; ?></h3>
							<div class="btns" style="float:right;">
								<ul>
									<li><a href="#" class="btn_save">SAVE JOB</a></li>
									<li><a href="#" class="btn_save_close">SAVE & CLOSE</a></li>
									<li><a href="<?php echo url::base().'admin/jobs/';?>" class="btns_red">CANCEL</a>&nbsp;&nbsp;&nbsp;</li>
									<?php if ($id) {?>
									<li><a href="<?php echo $previous_url;?>" class="btns_gray">&laquo; PREVIOUS</a></li>
									<li><a href="<?php echo $next_url;?>" class="btns_gray">NEXT &raquo;</a></li>
									<?php } ?>
								</ul>
							</div>
						</div>
						<!-- f-col -->
						<div class="f-col">
							<?php if ($show_messages) { ?>
							<div class="row">
								<h4 style="margin:0;padding:0;"><a href="#" id="messages_toggle" class="show-messages">Show Messages</a>&nbsp;</h4>
								<!--messages table goes here-->
			                    <div id="show_messages">
									<?php
									foreach ($all_messages as $message) {
										echo "<div class=\"message\">";
										echo "<strong><u>" . $message->message_from . "</u></strong> - ";
										echo $message->message;
										echo "</div>";
									}
									?>
								</div>
							</div>
							<?php } ?>
							<div class="row">
								<h4>Title</h4>
								<?php print form::input('job_title', $form['job_title'], ' class="text title"'); ?>
							</div>
							<div class="row">
								<h4>Description <span>Please include as much detail as possible.</span></h4>
								<?php print form::textarea('job_description', $form['job_description'], ' rows="12" cols="40"') ?>
							</div>
							
							<div class="row">
								<h4><a href="#" id="category_toggle" class="new-cat">new category</a>Categories 
								<span>Select as many as needed.</span></h4>
								<?php print $new_category_toggle_js; ?>
								<!--category_add form goes here-->
			                    <div id="category_add" class="category_add">
			                        <?php
			                        print '<p>Add New Category<hr/></p>';
                                    print form::label(array("id"=>"job_category_name_label", "for"=>"category_name"), 'Name');
                                    print '<br/>';
                                    print form::input('job_category_name', $new_job_categories_form['job_category_name'], 'class=""');
                                    print '<br/>';
                                    print form::label(array("id"=>"description_label", "for"=>"description"), 'Description');
                                    print '<br/>';
                                    print form::input('job_category_description', $new_job_categories_form['job_category_description'], 'class=""');
                                    print '<br/>';
                                    print form::label(array("id"=>"color_label", "for"=>"color"), 'Color');
                                    print '<br/>';
                                    print form::input('job_category_color', $new_job_categories_form['job_category_color'], 'class=""');
                                    print $color_picker_js;
                                    print '<br/>';
                                    print '<span>';
                                    print '<a href="#" id="add_new_category">Add</a>';
                                    print '</span>';
                                    ?> 
                                </div>

			                    <div class="category">
                        	    <?php
                        		//format categories for 2 column display
                                $this_col = 1; // First column
                                $maxper_col = round($job_categories_total/2); // Maximum number of elements per column
                                
								$i = 1; // Element Count
                                foreach ($job_categories as $category => $category_extra)
                                {
                                    $category_title = $category_extra[0];
                                    $category_color = $category_extra[1];
                                    if ($this_col == 1) 
                                        print "<ul>";
                                
                                    if (!empty($form['job_category']) 
                                        && in_array($category, $form['job_category'])) {
                                        $category_checked = TRUE;
                                    }
                                    else
                                    {
                                        $category_checked = FALSE;
                                    }
                                                                                                    
                                    print "<li><label>";
                                    print form::checkbox('job_category[]', $category, $category_checked, ' class="check-box"');
                                    print "$category_title";
                                    print "</label></li>";

                                    if ($this_col == $maxper_col || $i == count($job_categories)) 
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
			                        <ul id="user_categories">
			                        </ul>
								</div>
							</div>			
						</div>
						<!-- f-col-1 -->
						<div class="f-col-1">
							<div class="incident-location">
								<h4>Job Location</h4>
								<div class="location-info">
									<span>Latitude:</span>
									<?php print form::input('latitude', $form['latitude'], ' class="text"'); ?>
									<span>Longitude:</span>
									<?php print form::input('longitude', $form['longitude'], ' class="text"'); ?>
								</div>
								<div id="divMap" class="map_holder_reports"></div>
							</div>
							<div class="incident-find-location">
								<?php print form::input('location_find', '', ' title="City, State and/or Country" class="findtext"'); ?>
								<div class="btns" style="float:left;">
									<ul>
										<li><a href="#" class="btn_find">FIND LOCATION</a></li>
									</ul>
								</div>
								<div id="find_loading" class="incident-find-loading"></div>
								<div style="clear:both;">* If you can't find your location, please click on the map to pinpoint the correct location.</div>
							</div>
							<div class="row">
								<div class="town">
									<h4>Refine Your Location Name <br /><span>Examples: Dansoman, Accra,8th Street Sahara</span></h4>
									<?php print form::input('location_name', $form['location_name'], ' class="text long"'); ?>
								</div>
							</div>
				
				
						</div>
						<!-- f-col-bottom -->
						<div class="f-col-bottom-container">
							<div class="f-col-bottom">
								<div class="row">
									<h4>Personal Information <span>For contact purposes only.</span></h4>
									<label>
										<span>First Name</span>
										<?php print form::input('person_first', $form['person_first'], ' class="text"'); ?>
									</label>
									<label>
										<span>Last Name</span>
										<?php print form::input('person_last', $form['person_last'], ' class="text"'); ?>
									</label>
								</div>
								<div class="row">
									<label>
										<span>Email Address</span>
										<?php print form::input('person_email', $form['person_email'], ' class="text"'); ?>
									</label>
								</div>
							</div>
							<!-- f-col-bottom-1 -->
							<div class="f-col-bottom-1">
								<h4>Information Evaluation</h4>
								<div class="row">
									<div class="f-col-bottom-1-col">Approve this report?</div>
									<input type="radio" name="job_active" value="1"
									<?php if ($form['job_active'] == 1)
									{
										echo " checked=\"checked\" ";
									}?>> Yes
									<input type="radio" name="job_active" value="0"
									<?php if ($form['job_active'] == 0)
									{
										echo " checked=\"checked\" ";
									}?>> No
								</div>
								<div class="row">
									<div class="f-col-bottom-1-col">Verify this report?</div>
									<input type="radio" name="job_verified" value="1"
									<?php if ($form['job_verified'] == 1)
									{
										echo " checked=\"checked\" ";
									}?>> Yes
									<input type="radio" name="job_verified" value="0"
									<?php if ($form['job_verified'] == 0)
									{
										echo " checked=\"checked\" ";
									}?>> No									
								</div>
								<div class="row">
									<div class="f-col-bottom-1-col">Source Reliability:</div>
									<?php print form::dropdown('job_source', 
									array(""=>"--- Select One ---", 
									"A"=>"Yes, the source has direct access to information (witness or actor)", 
									"B"=>"Yes, the source has access to information, but can be wrong", 
									"C"=>"Yes, the source has no direct access to information, but is often right", 
									"D"=>"Not always, but is often right", 
									"E"=>"No, the source has (had) no access to information.", 
									"F"=>"I don’t know"
									)
									, $form['job_source']) ?>									
								</div>
								<div class="row">
									<div class="f-col-bottom-1-col">Information Probability:</div>
									<?php print form::dropdown('job_information', 
									array(""=>"--- Select One ---", 
									"1"=>"Yes, the information is confirmed by several independent sources", 
									"2"=>"Yes, the information is not confirmed (yet), but is likely", 
									"3"=>"Yes, the information makes sense", 
									"4"=>"No, the information is surprising", 
									"5"=>"No, the information is unlikely and may be disinformation", 
									"6"=>"I don’t know"
									)
									, $form['job_information']) ?>									
								</div>								
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="btns">
							<ul>
								<li><a href="#" class="btn_save">SAVE JOB</a></li>
								<li><a href="#" class="btn_save_close">SAVE & CLOSE</a></li>
								<?php 
								if($id)
								{
									echo "<li><a href=\"#\" class=\"btn_delete btns_red\">DELETE THIS REPORT</a></li>";
								}
								?>
								<li><a href="<?php echo url::base().'admin/jobs/';?>" class="btns_red">CANCEL</a></li>
							</ul>
						</div>						
					</div>
				<?php print form::close(); ?>
				<?php
				if($id)
				{
					// Hidden Form to Perform the Delete function
					print form::open(url::base().'admin/jobs/', array('id' => 'jobMain', 'name' => 'jobMain'));
					$array=array('action'=>'d','job_id[]'=>$id);
					print form::hidden($array);
					print form::close();
				}
				?>
			</div>
