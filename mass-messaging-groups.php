<?php
if(!class_exists("WbMassMessagingGroups")){
	class WbMassMessagingGroups{
		public function __construct()
		{
			define('MassMessageGroupsOverrideShowOnlyPublic', 'false');
			define('MassMessageBlogsOverrideShowOnlyPublic', 'true');
			add_action('wp_head', array($this, 'addHeaderCode'), 1);
			add_action('bp_setup_nav', array($this, 'setupNavigation'), 1);
			add_action('admin_bar_menu', array($this,'setupGrpToolbar'), 999 );
		}
		
		function addHeaderCode(){
			global $bp;
			?>
			<!-- Using Stormation's 'Mass Messaging' plugin from stormation.info -->
			<?php
			if($bp->current_component == 'groups' && $bp->current_action == 'mass-messages'){
				?>
				<script language="JavaScript">
					function toggle(source, action) {
						checkboxes = document.getElementsByName(action);
						for(var i in checkboxes)
    						checkboxes[i].checked = source.checked;
					}
				</script>
			<?php
			}
		}
		public function setupNavigation(){
			global $bp, $current_user, $wp_admin_bar;
			get_currentuserinfo();

			$role = get_site_option('MassMessageMinimumType');
			$MassMessageGroups = get_site_option('MassMessageGroups');
			$MassMessageAllGroups = get_site_option('MassMessageAllGroups');
			$MassMessageAllGroupsOverride = get_site_option('MassMessageAllGroupsOverride');
			do_action('mass_messaging_grp_buddypress_before_user_can_view');
			if(current_user_can($role) && $MassMessageGroups=="true"){
				bp_core_new_subnav_item( array(
   					'name' => __('Mass Messages', WBCOM_MASS_MESSAGE_TEXT_DOMIAN),
   					'slug' => 'mass-messages',
   					'parent_url' => $bp->loggedin_user->domain.$bp->groups->slug.'/',
   					'parent_slug' => $bp->groups->slug,
   					'screen_function' => array($this,'mass_messaging_page_screen'),
   					'item_css_id' => $bp->groups->id,
   					'position' => 100 ) );
   			}
			
   			do_action('mass_messaging_grp_buddypress_after_user_can_view');
		}
		
		public function mass_messaging_page_screen(){
			add_action('bp_template_title', array($this,'mass_messaging_page_screen_title'));
			add_action('bp_template_content', array($this,'mass_messaging_page_screen_content'));
			bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
		}
		
		public function mass_messaging_page_screen_title(){
        		echo __('Mass Messaging', WBCOM_MASS_MESSAGE_TEXT_DOMIAN);
    		}
    	public function mass_messaging_page_screen_content(){
    			global $wpdb, $bp, $blogs_template;
    			
    			do_action('mass_messaging_for_buddypress_before_post_settings');    			
    			$users = array();
    			
    			$subject = $_POST['subject'];
    			$content = $_POST['content'];
    			
				$thread = $_POST['thread'];
				$threaded = false;
				if(!empty($thread)){
					if($thread == 1){
						$threaded = true;
					}
				}
				do_action('mass_messaging_for_buddypress_after_post_settings');
				
				do_action('mass_messaging_for_buddypress_before_blogs_actions');
				$blogs = $_POST['blogs'];
    			if(!empty($blogs)){
    				$blogsCount = count($blogs);
    				for($i=0; $i < $blogsCount; $i++){
    					$blogMembers = get_users('blog_id='.$blogs[$i]);
						foreach($blogMembers as $blogMember){
							array_push($users, $blogMember->ID);
						}
    				}
    			}
    			do_action('mass_messaging_for_buddypress_after_blogs_actions');
    			
    			do_action('mass_messaging_for_buddypress_before_groups_actions');
    			$groups = $_POST['groups'];
  				if(!empty($groups)){
    				$groupCount = count($groups);
    				for($i=0; $i < $groupCount; $i++){
						if(is_numeric($groups[$i])){
    						$groupMembers = $wpdb->get_col("SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = ".$groups[$i]." AND is_confirmed = 1 AND is_banned = 0");
						}
  						$groupMembersCount = count($groupMembers);
    					for($i=0; $i < $groupMembersCount; $i++){
      						array_push($users, $groupMembers[$i]);
      					}
      				}
 				}
 				do_action('mass_messaging_for_buddypress_after_groups_actions');
 				
    			
 				$usersFinal = array_unique($users, SORT_NUMERIC);
 				$sent = 0;
 				$sender = $bp->loggedin_user->id;
 				
				if($threaded == false){
 					foreach ($usersFinal as $value) {
 						if($value != $sender){
							if( messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $content, 	'recipients' => $value ) ) ){
								$sent++;
							}
						}
						if ( $sent % 50 == 0 )
							sleep(5);
					}
					if($sent > 0){
						echo "<div id=\"message\" class=\"updated\"><p>";
						if($sent != 1){
							echo __("Messages sent to ", WBCOM_MASS_MESSAGE_TEXT_DOMIAN).$sent.__(" people", WBCOM_MASS_MESSAGE_TEXT_DOMIAN);
						}else{
							echo __("Message sent to 1 person", WBCOM_MASS_MESSAGE_TEXT_DOMIAN);
						}
						echo "</p></div>";
					}
				}else{
					foreach ($usersFinal as $key => $value) {
 						if($value == $sender){
							unset($usersFinal[$key]);
						}
					}
					messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $content, 	'recipients' => $usersFinal ) );
					$sent = count($usersFinal);
					if($sent > 0){
						echo "<div id=\"message\" class=\"updated\"><p>";
						if($sent != 1){
							echo __("Messages sent to ", WBCOM_MASS_MESSAGE_TEXT_DOMIAN).$sent.__(" people", WBCOM_MASS_MESSAGE_TEXT_DOMIAN);
						}else{
							echo __("Message sent to 1 person", WBCOM_MASS_MESSAGE_TEXT_DOMIAN);
						}
						echo "</p></div>";
					}
				}
 				if($sent>=1)
				{
					$table_name = $wpdb->prefix . 'mass_message_log';
					$wpdb->insert( 
						$table_name, 
						array( 
							'user_id'			=> $sender, 
							'message_receiver'	=> serialize($usersFinal),
							'msg_time'			=> date( "Y-m-d H:i:s" )
						), 
						array( 
							'%d',
							'%s', 
							'%s'
						) 
					);
				}
				$MassMessageGroups = get_site_option('MassMessagingGroups');
				$MassMessageAllGroups = get_site_option('MassMessagingAllGroups');
				$MassMessageAllGroupsOverride = get_site_option('MassMessagingAllGroupsOverride');
				$MassMessageBlogs = get_site_option('MassMessageBlogs');
				$MassMessageAllBlogs = get_site_option('MassMessageAllBlogs');
				$MassMessageAllBlogsOverride = get_site_option('MassMessageAllBlogsOverride');
				$MassMessageMinimumType = get_site_option('MassMessageMinimumType');
    			?>
    			<form action="" method="post" id="send_message_form" class="standard-form">
				
				<label for="subject" class="subject"><?php echo __("Subject", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<input type="text" name="subject" id="subject" value="" />

				<label for="content" class="content"><?php echo __("Message", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<textarea name="content" id="message_content" rows="15" cols="50"></textarea>
				
				<?php if($MassMessageAllBlogs == 'true'){ ?>
				
				<h3 style="width:100%;"><?php echo __("All Blogs", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
				<input type=checkbox style="width:12px;" onClick="toggle(this, 'blogs[]')" >All Blogs<br />
				
				<?php } ?>
				<?php if($MassMessageBlogs == 'true'){ ?>
				
					<h3 style="width:100%;"><?php echo __("Blogs", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
				
					<?php if($MassMessageAllBlogsOverride == 'true'){ 
			
						if(MassMessageBlogsOverrideShowOnlyPublic == 'true'){
						
							$blogsAll = $wpdb->get_results("SELECT * FROM {$wpdb->blogs} AND spam = '0' AND deleted = '0' AND archived = '0' AND public='1'");
							
						}else{
							$blogsAll = $wpdb->get_results("SELECT * FROM {$wpdb->blogs} AND spam = '0' AND deleted = '0' AND archived = '0'");
						}
						foreach ( $blogsAll as $blogAll) {
							?>
							<input type=checkbox style="width:12px;" name="blogs[]" value="<?php echo $blogAll->id; ?>"><?php echo $blogAll->name; ?>
							<br />
							<?php
						}
				
					}else{
					
						if ( bp_has_blogs( 'user_id='.$user_id ) ) : ?>
							<?php while ( bp_blogs() ) : bp_the_blog(); ?>
								<input type=checkbox style="width:12px;" name="blogs[]" value="<?php echo $blogs_template->blog->blog_id; ?>"><?php bp_blog_name(); ?>
								<br />
							<?php endwhile; ?>
						<?php endif; ?>
					
					<?php } ?>
				
				<?php } ?>
				
				<?php if($MassMessageAllGroups == 'true'){ ?>
				
				<h3 style="width:100%;"><?php echo __("All Groups", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
				<input type=checkbox style="width:12px;" onClick="toggle(this, 'groups[]')" >All Groups<br />
				
				<?php } ?>
				<?php if($MassMessageGroups == 'true'){ ?>
				
					<h3 style="width:100%;"><?php echo __("Groups", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3><br>
				
					<?php if($MassMessageAllGroupsOverride == 'true'){ 
					
						if(MassMessageGroupsOverrideShowOnlyPublic == 'true'){
				
							$groupsAll = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name} WHERE status = 'public'");
							
						}else{
						
							$groupsAll = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name}");
							
						}
						
						foreach ( $groupsAll as $groupAll) {
							?>
							<input type=checkbox style="width:12px;" name="groups[]" value="<?php echo $groupAll->id; ?>"><?php echo $groupAll->name; ?>
							<br />
							<?php
						}
				
					}else{ ?>

					<?php if ( bp_has_groups('type=newest&per_page=99999&user_id='.$bp->loggedin_user->id) ) : ?>
						
						<?php while ( bp_groups() ) : bp_the_group(); ?>
							<input type=checkbox style="width:12px;" name="groups[]" value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?>
							<br />
						<?php endwhile; ?>
					<?php endif; ?>
				
					<?php } ?>
				<?php } ?>
				<br />
				<input type=checkbox style="width:12px;" name="thread" class="thread" value="1"><?php echo __("Send as 1 message?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?><br />
				<input type="submit" value="Send Message &rarr;" name="send" id="send" />
				</form>
        	<?php
    		}
		
		public function setupGrpToolbar($wp_admin_bar){
			global $bp;
			$user_domain = $bp->loggedin_user->domain;
			
			$glink = trailingslashit( $user_domain . $bp->groups->slug );
			$wp_admin_nav_gp = array(
				'parent' => 'my-account-' . $bp->groups->id,
				'title'  => __('Mass Messages', WBCOM_MASS_MESSAGE_TEXT_DOMIAN),
				'href'   => trailingslashit($glink . 'mass-messages')
			);
			$role = get_site_option('MassMessageMinimumType');
			do_action('wb_mass_message_grp_buddypress_before_user_can_view');
			if(current_user_can($role)){
				$wp_admin_bar->add_node( $wp_admin_nav_gp );
			}
			do_action('wb_mass_message_grp_buddypress_after_user_can_view');
		}
	}
	new WbMassMessagingGroups();
}