<?php
/**
 * Main view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Dashboard Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General
 * Public License (LGPL)
 */
?>

				<!-- main body -->
				<div id="main" class="clearingfix">
					<div id="mainmiddle" class="floatbox withright">
				
						<!-- right column -->
						<div id="right" class="clearingfix">
					
							<!-- category filters -->
							<div class="cat-filters clearingfix">
								<strong><?php echo Kohana::lang('ui_main.category_filter');?></strong>
							</div>
						
							<ul class="category-filters">
								<li><a class="active" id="cat_0" href="#"><div class="swatch" style="background-color:#<?php echo $default_map_all;?>"></div><div class="category-title">All Categories</div></a></li>
								<?php
									foreach ($categories as $category => $category_info)
									{
										$category_title = $category_info[0];
										$category_color = $category_info[1];
										echo '<li><a href="#" id="cat_'. $category .'"><div class="swatch" style="background-color:#'.$category_color.'"></div><div class="category-title">'.$category_title.'</div></a></li>';
										// Get Children
										echo '<div class="hide" id="child_'. $category .'">';
										foreach ($category_info[2] as $child => $child_info)
										{
											$child_title = $child_info[0];
											$child_color = $child_info[1];
											echo '<li style="padding-left:20px;"><a href="#" id="cat_'. $child .'"><div class="swatch" style="background-color:#'.$child_color.'"></div><div class="category-title">'.$child_title.'</div></a></li>';
										}
										echo '</div>';
									}
								?>
							</ul>
							<!-- / category filters -->
							<br />
							<!-- additional content -->
							<div class="additional-content">
								<h5><?php echo Kohana::lang('ui_main.how_to_report'); ?></h5>
								<ol>
									<?php if (!empty($phone_array)) 
									{ ?><li class="report r-sms">By sending a message to <?php foreach ($phone_array as $phone) {
										echo "<strong>". $phone ."</strong>";
										if ($phone != end($phone_array)) {
											echo " or ";
										}
									} ?></li><?php } ?>
									<?php if (!empty($report_email)) 
									{ ?><li class="report r-email">By sending an email to <a href="mailto:<?php echo $report_email?>"><?php echo $report_email?></a></li><?php } ?>
									<?php if (!empty($twitter_hashtag_array)) 
												{ ?><li class="report r-twitter">By sending a tweet with the hashtag/s <?php foreach ($twitter_hashtag_array as $twitter_hashtag) {
									echo "<strong>". $twitter_hashtag ."</strong>";
									if ($twitter_hashtag != end($twitter_hashtag_array)) {
										echo " or ";
									}
									} ?></li><?php } ?>
									<li class="report r-online">By <a href="<?php echo url::base() . 'reports/submit/'; ?>">filling a form</a> at the website</li>
								</ol>					
		
							</div>
							<!-- / additional content -->
					
						</div>
						<!-- / right column -->
					
						<!-- content column -->
						<div id="content" class="clearingfix">
							<div class="floatbox">
							
								<!-- filters -->
								<div class="filters clearingfix">
								<div style="float:left; width: 65%">
									<strong><?php echo Kohana::lang('ui_main.filters'); ?></strong>
									<ul>
										<li><a id="media_0" class="active" href="#"><span><?php echo Kohana::lang('ui_main.reports'); ?></span></a></li>
										<li><a id="media_1" href="#"><span><?php echo Kohana::lang('ui_main.pictures'); ?></span></a></li>
										<li><a id="media_0" href="#"><span><?php echo Kohana::lang('ui_main.all'); ?></span></a></li>
									</ul>
</div>
								<div style="float:right; width: 35%">
									<strong><?php echo Kohana::lang('ui_main.views'); ?></strong>
									<ul>
										<li><a id="view_0" <?php if($map_enabled === 'streetmap') { echo 'class="active" '; } ?>href="#"><span><?php echo Kohana::lang('ui_main.clusters'); ?></span></a></li>
										<li><a id="view_1" <?php if($map_enabled === '3dmap') { echo 'class="active" '; } ?>href="#"><span><?php echo Kohana::lang('ui_main.time'); ?></span></a></li>
</div>
								</div>
								<!-- / filters -->
						
								<!-- map -->
								<?php
									// My apologies for the inline CSS. Seems a little wonky when styles added to stylesheet, not sure why.
								?>
								<div class="<?php echo $map_container; ?>" id="<?php echo $map_container; ?>" <?php if($map_container === 'map3d') { echo 'style="width:573px; height:573px;"'; } ?>></div> 
								 
							</div>
						</div>
						<!-- / content column -->
				
					</div>
				</div>
				<!-- / main body -->
			
				<!-- content -->
				<div class="content-container">
			
					<!-- content blocks -->
					<div class="content-blocks clearingfix">
				
						<!-- left content block -->
						<div class="content-block-left">
							<h5><?php echo Kohana::lang('ui_main.jobs_listed_listed'); ?></h5>
							<table class="table-list">
								<thead>
									<tr>
										<th scope="col" class="title"><?php echo Kohana::lang('ui_main.title'); ?></th>
										<th scope="col" class="location"><?php echo Kohana::lang('ui_main.location'); ?></th>
										<th scope="col" class="date"><?php echo Kohana::lang('ui_main.date'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
	 								if ($total_items == 0)
									{
									?>
									<tr><td colspan="3">No Jobs In The System</td></tr>

									<?php
									}
									foreach ($incidents as $incident)
									{
										$incident_id = $incident->id;
										$incident_title = text::limit_chars($incident->job_title, 40, '...', True);
										$incident_date = $incident->job_dateadd;
										$incident_date = date('M j Y', strtotime($incident->job_dateadd));
										$incident_location = $incident->location->location_name;
									?>
									<tr>
										<td><a href="<?php echo url::base() . 'jobs/view/' . $incident_id; ?>"> <?php echo $incident_title ?></a></td>
										<td><?php echo $incident_location ?></td>
										<td><?php echo $incident_date; ?></td>
									</tr>
									<?php
									}
									?>

								</tbody>
							</table>
							<a class="more" href="<?php echo url::base() . 'jobs/' ?>">View More...</a>
						</div>
						<!-- / left content block -->
				
						<!-- right content block -->
						<div class="content-block-right">
							<h5><?php echo Kohana::lang('ui_main.official_news'); ?></h5>
							<table class="table-list">
								<thead>
									<tr>
										<th scope="col"><?php echo Kohana::lang('ui_main.title'); ?></th>
										<th scope="col"><?php echo Kohana::lang('ui_main.source'); ?></th>
										<th scope="col"><?php echo Kohana::lang('ui_main.date'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($feeds as $feed)
									{
										$feed_id = $feed->id;
										$feed_title = text::limit_chars($feed->item_title, 40, '...', True);
										$feed_link = $feed->item_link;
										$feed_date = date('M j Y', strtotime($feed->item_date));
										$feed_source = text::limit_chars($feed->feed->feed_name, 15, "...");
									?>
									<tr>
										<td><a href="<?php echo $feed_link; ?>" target="_blank"><?php echo $feed_title ?></a></td>
										<td><?php echo $feed_source; ?></td>
										<td><?php echo $feed_date; ?></td>
									</tr>
									<?php
									}
									?>
								</tbody>
							</table>
							<a class="more" href="<?php echo url::base() . 'feeds' ?>">View More...</a>
						</div>
						<!-- / right content block -->
				
					</div>
					<!-- /content blocks -->
				
				</div>
				<!-- content -->
				<div style="display:none">
				<?php if($map_container === 'map') { ?>
					<div class="slider-holder">
						
							<form action="">
								<fieldset>
									<div class="play"><a href="#" id="playTimeline">PLAY</a></div>
										<label for="startDate">From:</label>
										<select name="startDate" id="startDate"><?php echo $startDate; ?></select>
										<label for="endDate">To:</label>
										<select name="endDate" id="endDate"><?php echo $endDate; ?></select>
								</fieldset>
							</form>
						</div>
					<?php } ?>
					<!-- / map -->
				
			</div>
		</div>
		<!-- / main body -->

	</div>
	<!-- / wrapper -->
