<?php 
/**
 * help view page.
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
						<div class="big-block">
							<?php print form::open(NULL, array('id' => 'sendMessage', 'name' => 'sendMessage')); ?>
							<div class="incident-name">
								<h1><?php echo $organization_name; ?></h1>
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
								<div id="help_view">						
								<ul>
									<li>
										<strong>ABOUT</strong>
										<p><?php echo $organization_description; ?></p>
									</li>
									<?php if( !empty($organization_website ) ) { ?>
									<li>
										<strong>WEBSITE</strong>
										<p><?php echo $organization_website; ?></p>
									</li>
									<?php } ?>
									<?php if( !empty($organization_phone1 ) ) { ?>
									<li>
										<strong>Phone</strong>
										<p><?php echo $organization_phone1; ?></p>
									</li>
									<?php } ?>
									<?php if( !empty($organization_phone2 ) ) { ?>
									<li>
										<strong>Phone</strong>
										<p><?php echo $organization_phone2; ?></p>
									</li>
									<?php } ?>
									<?php if (!empty($organization_email)) { ?>
									<li>
									</div>
										<div id="contact" class="org_contact">
											<h3>Contact Us:</h3>
											<div class="org_contact_row">
					              <h4>Name:</h4>
												<?php print form::input('name', $form['name'], ' class="text long"'); ?>
					            </div>
											<div class="org_contact_row">
					              <h4>Email:</h4>
												<?php print form::input('email', $form['email'], ' class="text long"'); ?>
					            </div>
											<div class="org_contact_row">
					              <h4>Phone:</h4>
												<?php print form::input('phone', $form['phone'], ' class="text long"'); ?>
					            </div>
											<div class="org_contact_row">
					              <h4>Message:</h4>
												<?php print form::textarea('message', $form['message'], ' rows="4" cols"20" class="textarea long" '); ?>
					            </div>
											<div class="org_contact_row">
												<h4>Security Code:</h4>
												<?php print $captcha->render(); ?><br />
												<?php print form::input('captcha', $form['captcha'], ' class="text"'); ?>
					            </div>
											<div class="org_contact_row">
					              <input class="btn_blue" type="submit" value="Send Message" />
					            </div>
										</div>
									</li>
									<?php } ?>
								</ul>
								<br />
								<br />
								<input class="btn_gray" type="button" value="Back" onclick="location.href='<?php echo url::base() . 'help/'; ?>';" />
							</div>
							<?php print form::close(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
