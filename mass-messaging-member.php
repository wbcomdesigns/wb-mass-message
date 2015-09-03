<?php
if(!class_exists("WbMassMessagingMember")){
	class WbMassMessagingMember{
		public function __construct()
		{
			define('MassMessageBlogsOverrideShowOnlyPublic', 'true');
			add_action('wp_head', array($this, 'addHeaderCode'), 1);
			add_action('bp_setup_nav', array($this, 'setupNavigation'), 1);
			add_action('admin_bar_menu', array($this,'setupMsgToolbar'), 900 );
		}
		function addHeaderCode(){
			global $bp;
			?>
			<!-- Using Stormation's 'Mass Messaging' plugin from stormation.info -->
			<?php
			if($bp->current_component == 'messages' && $bp->current_action == 'mass-messages'){
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
			$MassMessageMembers = get_site_option('MassMessageMembers');
			$MassMessageAllMembers = get_site_option('MassMessageAllMembers');
			$MassMessageAllMembersOverride = get_site_option('MassMessageAllMembersOverride');
			$MassMessageSendType = get_site_option('MassMessageSendType');
			do_action('mass_messaging_for_buddypress_before_user_can_view');
			if(current_user_can($role) && $MassMessageMembers=="true"){
				bp_core_new_subnav_item( array(
   					'name' => __('Mass Messages', WBCOM_MASS_MESSAGE_TEXT_DOMIAN),
   					'slug' => 'mass-messages',
   					'parent_url' => $bp->loggedin_user->domain.$bp->messages->slug.'/',
   					'parent_slug' => $bp->messages->slug,
   					'screen_function' => array($this,'mass_messaging_page_screen'),
   					'default_subnav_slug' => 'inbox',
   					'item_css_id' => $bp->messages->id,
   					'position' => 100 ) );
   			}
   			do_action('mass_messaging_for_buddypress_after_user_can_view');
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
				$blogs = isset($_POST['blogs'])?$_POST['blogs']:"";
				$MassMessageSendType = get_site_option('MassMessageSendType');
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
    			
    			do_action('mass_messaging_for_buddypress_before_members_actions');
 				$members = isset($_POST['members'])?$_POST['members']:"";
  				if(!empty($members)){
    					$membersCount = count($members);
    					for($i=0; $i < $membersCount; $i++){
      						array_push($users, $members[$i]);
      					}
 				}
 				do_action('mass_messaging_for_buddypress_after_members_actions');
 				$usersFinal = array_unique($users, SORT_NUMERIC);
 				$sent = 0;
 				$sender = $bp->loggedin_user->id;
 				
				if($threaded == false){
 					foreach ($usersFinal as $value) {
 						if($value != $sender){
							if( messages_new_message( array('sender_id' => $sender, 'subject' => $subject, 'content' => $content, 'recipients' => $value ) ) ){
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
 				
				$MassMessageMembers = get_site_option('MassMessageMembers');
				$MassMessageAllMembers = get_site_option('MassMessageAllMembers');
				$MassMessageAllMembersOverride = get_site_option('MassMessageAllMembersOverride');
				$MassMessageMinimumType = get_site_option('MassMessageMinimumType');
				$MassMessageSendType = get_site_option('MassMessageSendType');
    			?>
    			<form action="" method="post" id="send_message_form" class="standard-form">
				
				<label for="subject" class="subject"><?php echo __("Subject", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<input type="text" name="subject" id="subject" value="" />

				<label for="content" class="content"><?php echo __("Message", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label>
				<textarea name="content" id="message_content" rows="15" cols="50"></textarea>
				
				
				<?php if($MassMessageAllMembers == 'true'){ ?>
				
				<h3 style="width:100%;"><?php echo __("All Members", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
				<label><input type=checkbox style="width:12px;" onClick="toggle(this, 'members[]')" >All Members</label><br />
				
				<?php } ?>
				<?php if($MassMessageMembers == 'true'){ ?>
			
					<h3 style="width:100%;"><?php echo __("Members", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></h3>
                    <div class="wb-mass-member-list" style="max-height:300px; overflow-y:auto;">
				
					<?php if($MassMessageAllMembersOverride == 'true'){
					
						$membersAll = ($MassMessageSendType=="all")?get_users():get_users('role='.$MassMessageSendType);
						
						foreach ( $membersAll as $memberAll ) {
							?>
							<label style="width:50%; float:left; padding:1%;margin:0px;"><input type=checkbox style="width:12px;" name="members[]" value="<?php echo $memberAll->ID; ?>"><?php echo $memberAll->display_name; ?></label>
							<?php
						}
					
					}else{ ?>
				
						<?php if(bp_has_members('per_page=99999&type=newest&user_id='.bp_loggedin_user_id())): ?>
							<?php while ( bp_members() ) : bp_the_member(); ?>
								<input type=checkbox style="width:12px;" name="members[]" value="<?php bp_member_user_id(); ?>"><?php bp_member_name(); ?><br />
							<?php endwhile; ?>
						<?php endif; ?>
					<?php } ?>
                	</div>
                    <div style="clear:both;"></div>
				<?php } ?>
				<br />
				<label><input type=checkbox style="width:12px;" name="thread" class="thread" value="1"><?php echo __("Send as 1 message?", WBCOM_MASS_MESSAGE_TEXT_DOMIAN)?></label><br />
				<input type="submit" value="Send Message &rarr;" name="send" id="send" />
				</form>
        	<?php
    		}
		public function setupMsgToolbar($wp_admin_bar){
			global $bp;
			$user_domain = $bp->loggedin_user->domain;
			$link = trailingslashit( $user_domain . $bp->messages->slug );
			$wp_admin_nav = array(
				'id'	 => 'my-account-message-mass-messages',
				'parent' => 'my-account-' . $bp->messages->id,
				'title'  => __('Mass Messages', WBCOM_MASS_MESSAGE_TEXT_DOMIAN),
				'href'   => trailingslashit($link . 'mass-messages')
			);
			$role = get_site_option('MassMessageMinimumType');
			do_action('wb_mass_message_for_buddypress_before_user_can_view');
			if(current_user_can($role)){
				$wp_admin_bar->add_node( $wp_admin_nav );
			}
			do_action('wb_mass_message_for_buddypress_after_user_can_view');
		}
	}
	new WbMassMessagingMember();
}
