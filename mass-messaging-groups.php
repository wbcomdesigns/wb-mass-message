<?php
if(!class_exists("WbMassMessagingGroups")){
	class WbMassMessagingGroups{
		public function __construct()
		{
			define('MassMessageGroupsOverrideShowOnlyPublic', 'false');
			add_action('wp_head', array($this, 'addHeaderCode'), 1);
			add_action('bp_setup_nav', array($this, 'setupNavigation'), 1);
			add_action('admin_bar_menu', array($this,'setupGrpToolbar'), 900 );
		}
		
		function addHeaderCode(){
			global $bp;
			?>
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
			$MassMessageSendType = get_site_option('MassMessageSendType');
			$MassMessageGroupRole = get_site_option('MassMessageGroupRole');
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
    			
    			$subject = htmlspecialchars(isset($_POST['subject'])?sanitize_text_field($_POST['subject']):"");
    			$content = htmlspecialchars(isset($_POST['content'])?sanitize_text_field($_POST['content']):"");
				$thread = isset($_POST['thread'])?sanitize_text_field($_POST['thread']):"";
				$threaded = false;
				if(!empty($thread)){
					if($thread == 1){
						$threaded = true;
					}
				}
				do_action('mass_messaging_for_buddypress_after_post_settings');
				
				do_action('mass_messaging_for_buddypress_before_blogs_actions');
				$blogs = isset($_POST['thread'])?$_POST['blogs']:"";
				$MassMessageSendType = get_site_option('MassMessageSendType');
				$MassMessageGroupRole = get_site_option('MassMessageGroupRole');
    			if(!empty($blogs)){
    				$blogsCount = count($blogs);
    				for($i=0; $i < $blogsCount; $i++){
    					$blogMembers = ($MassMessageSendType=="all")?get_users('blog_id='.$blogs[$i]):get_users('blog_id='.$blogs[$i].'&role='.$MassMessageSendType);
						foreach($blogMembers as $blogMember){
							array_push($users, $blogMember->ID);
						}
    				}
    			}
    			do_action('mass_messaging_for_buddypress_after_blogs_actions');
    			
    			do_action('mass_messaging_for_buddypress_before_groups_actions');
    			$groups = isset($_POST['thread'])?$_POST['groups']:"";
  				if(!empty($groups)){
    				$groupCount = count($groups);
    				for($i=0; $i < $groupCount; $i++){
						if(is_numeric($groups[$i])){
							if($MassMessageGroupRole == 'true')
							{
								$groupMembers = $wpdb->get_col("SELECT grp.user_id FROM {$bp->groups->table_name_members} AS grp LEFT JOIN ".$wpdb->usermeta." AS usermeta ON grp.user_id=usermeta.user_id WHERE grp.group_id = ".$groups[$i]." AND grp.is_confirmed = 1 AND grp.is_banned = 0 AND usermeta.meta_key = '".$wpdb->prefix . "capabilities' AND usermeta.meta_value LIKE '%".$MassMessageSendType."%'");
							}
							else
							{
    						$groupMembers = $wpdb->get_col("SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = ".$groups[$i]." AND is_confirmed = 1 AND is_banned = 0");
							}
							
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
				$MassMessageGroups = get_site_option('MassMessageGroups');
				$MassMessageAllGroups = get_site_option('MassMessageAllGroups');
				$MassMessageAllGroupsOverride = get_site_option('MassMessageAllGroupsOverride');
				$MassMessageBlogs = get_site_option('MassMessageBlogs');
				$MassMessageAllBlogs = get_site_option('MassMessageAllBlogs');
				$MassMessageAllBlogsOverride = get_site_option('MassMessageAllBlogsOverride');
				$MassMessageMinimumType = get_site_option('MassMessageMinimumType');
				$MassMessageSendType = get_site_option('MassMessageSendType');
    			?>
    			<form action="" method="post" id="send_message_form" class="standard-form">
				
				<label for="subject" class="subject"><?php echo __("Subject", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<input type="text" name="subject" id="subject" value="" />

				<label for="content" class="content"><?php echo __("Message", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<textarea name="content" id="message_content" rows="15" cols="50"></textarea>
				
				<?php if($MassMessageAllGroups == 'true'){ ?>
				
				<h3 style="width:100%;"><?php echo __("All Groups", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
				<label><input type=checkbox style="width:12px;" onClick="toggle(this, 'groups[]')" >All Groups</label><br />
				
				<?php } ?>
				<?php if($MassMessageGroups == 'true'){ ?>
				
					<h3 style="width:100%;"><?php echo __("Groups", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3><br>
					 <div class="wb-mass-member-list" style="max-height:300px; overflow-y:auto;">
					<?php if($MassMessageAllGroupsOverride == 'true'){ 
					
						if(MassMessageGroupsOverrideShowOnlyPublic == 'true'){
				
							$groupsAll = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name} WHERE status = 'public'");
							
						}else{
						
							$groupsAll = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name}");
							
						}
						
						foreach ( $groupsAll as $groupAll) {
							?>
							<label style="width:50%; float:left; padding:1%;margin:0px;"><input type=checkbox style="width:12px;" name="groups[]" value="<?php echo $groupAll->id; ?>"><?php echo $groupAll->name; ?></label>
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
                    </div>
                    <div style="clear:both;"></div>
				<?php } ?>
				<br />
				<label><input type=checkbox style="width:12px;" name="thread" class="thread" value="1"><?php echo __("Send as 1 message?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?><br /></label>
				<input type="submit" value="Send Message &rarr;" name="send" id="send" />
				</form>
        	<?php
    		}
		
		public function setupGrpToolbar($wp_admin_bar){
			global $bp;
			$user_domain = $bp->loggedin_user->domain;
			
			$glink = trailingslashit( $user_domain . $bp->groups->slug );
			$wp_admin_nav_gp = array(
				'id'	 => 'my-account-group-mass-messages',
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
