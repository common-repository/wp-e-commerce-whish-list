<?php if ( ! defined( 'ABSPATH' ) ) exit; 

	function sanitize_wpewl_data( $input ) {

		if(is_array($input)){
		
			$new_input = array();
	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wpewl_data($val):sanitize_text_field( $val ));
			}
			
		}else{
			$new_input = sanitize_text_field($input);
		}
		
		return $new_input;
	}
	
	function wpews_plugin_links($links) { 
	
		global $wpews_premium_link, $wpews_pro;
		
		$settings_link = '<a href="widgets.php">'.__('Widgets', 'wp-ecomm').'</a>';
		
		if($wpews_pro){
			array_unshift($links, $settings_link); 
		}else{
			 
			$wpews_premium_link = '<a href="'.esc_url($wpews_premium_link).'" title="'.__('Go Premium', 'wp-ecomm').'" target=_blank>'.__('Go Premium', 'wp-ecomm').'</a>'; 
			array_unshift($links, $settings_link, $wpews_premium_link); 
		
		}		
		
		return $links; 
	}
	
	
	function wpecwl_scripts() {			
			wp_register_style('wpecwl-style', plugins_url('css/style.css', dirname(__FILE__)));
			wp_enqueue_style( 'wpecwl-style' );
	}
	
	function initialize_wpecwl(){
		if(!is_user_logged_in())
		return false;	
		global $current_user;
		?>
		
		<input type="image" title="Add to Wishlist" src="<?php echo plugins_url( 'images/heart-icon.png', dirname(__FILE__)); ?>"class="wpecwl_add" proid="<?php echo wpsc_the_product_id();?>" value="&nbsp;" />
		<?php echo get_user_meta( $current_user->ID, 'wp_smart_wpecwl', true ) ;?>
		<?php
	}
	if(!function_exists('wp_plugin_info')){
		function wp_plugin_info($name){
				$trunk = @file_get_contents('https://plugins.svn.wordpress.org/'.$name.'/trunk/readme.txt');
		
				$pattern = '/Stable tag:(.*)/';
				preg_match($pattern, $trunk, $matches);
				$version = trim(end($matches));
				
				$ver = @file_get_contents('https://plugins.svn.wordpress.org/'.$name.'/tags/'.$version.'/readme.txt');
			
				$pattern = '/Tested up to:(.*)/';
				preg_match($pattern, $ver, $matches);
				$tested = trim(end($matches));
				$ret['version'] = (float)$version;
				$ret['tested'] = (float)$tested;
				$ret['icon'] = 'http://ps.w.org/'.$name.'/assets/icon-256x256.png';
				return $ret;
		}
	}
	
	if(!function_exists('pre')){
		function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 
		
	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 
	
	
	function ajaxUrl() {
		?>
		<script type="text/javascript" language="javascript">
		function loadWishlist(){
			var data = { 'action': 'load_wish_list'};
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				jQuery.post(ajaxurl, data, function(response) {
				jQuery('#wpecwl').html(response);
			});
		}
		
		jQuery(document).ready(function($) {
			loadWishlist();
			jQuery('.wpecwl_add').click(function(){
				<?php if ( is_user_logged_in() ) {?>
				var data = { 'action': 'add_wish_list', 'proid': $(this).attr('proid') };
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
					$.post(ajaxurl, data, function(response) {
					loadWishlist();
				});
				<?php }else{?>
					alert('<?php _e('Please login after using this option.', 'wp-ecomm'); ?>');
				<?php }?>
			});
			
			
			jQuery('.wpecwl_remove').live('click', function(){
				//console.log(jQuery(this));
				var proid = $(this).attr('id').replace('r_0', '');
			
				var data = { 'action': 'remove_wish_list', 'proid': proid };
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				$.post(ajaxurl, data, function(response) {
				loadWishlist();
				
				 });
				
			 });
			
		});
		</script>
		<?php
	}
	
	function add_wish_list_callback() {
		global $wpdb;
	
		$proid = $_POST['proid'];
		$user_ID = get_current_user_id();
	
		$wpecwl = get_user_meta( $user_ID , 'wpecwl', true);
		$array = unserialize ( $wpecwl );
		if( ! in_array($proid ,$array) ){
			$array[] .= $proid;
			$new_wish = serialize( $array );
			update_user_meta( $user_ID , 'wpecwl' , sanitize_wpewl_data($new_wish), $wpecwl );	
		}
		
		die();
	}
	
	function remove_wish_list_callback() {
		global $wpdb;
	
		$proid = $_POST['proid'];
		$user_ID = get_current_user_id();
	
		$wpecwl = get_user_meta( $user_ID , 'wpecwl', true);
		$array = unserialize ( $wpecwl );
		if( in_array($proid ,$array) ){
			$key = array_search($proid ,$array); 
			unset( $array[$key]);
			$new_wish = serialize( $array );
			update_user_meta( $user_ID , 'wpecwl' , sanitize_wpewl_data($new_wish), $wpecwl );	
		}
		
		die();
	}
	
	function load_wish_list_callback() {
		global $wpdb, $wpews_pro;
	
		$user_ID = get_current_user_id();
		
		$close_btn = plugins_url( 'images/remove.png' , dirname(__FILE__) );
		
		if($wpews_pro){
			$theme = wpews_opts('theme');
			if($theme!=''){
				$close_btn = plugins_url( 'pro/wpews_close_'.$theme.'.png' , dirname(__FILE__) );
			}
		}
	
		$wpecwl = get_user_meta( $user_ID , 'wpecwl', true);
		$array = unserialize ( $wpecwl );
		
		$html = '<ul>
							';
							$i = 1;
			if( ! empty($array) ){
				foreach( $array as $k => $v ){
					global $post;
					$post = get_post( $v );
					setup_postdata($post);
					
					if ( has_post_thumbnail() ) {
						$img = get_the_post_thumbnail( $post->ID, array(64,64) );
					}
					else {
						$img = '<img src="' . plugins_url( 'images/notfound.png' , dirname(__FILE__) ).'" alt="Image" width="64" height="64" />';
					}
		
					//Template Start
					$html .= '	<li><a href="'.get_permalink().'">'.$img.'</a><a href="'.get_permalink().'">'. get_the_title() .'</a><a class="wpecwl_remove" id="r_0'.get_the_ID().'"><img src="' . $close_btn.'" alt="Remove" /></a></li>';
					//Template End
					$i++;
				}
			}
			else{
				$html .= '<tr><td colspan="4">'._e('No Wishlist Found', 'wp-ecomm').'</td></tr>';
			}
			$html .= '	</ul>';
			echo $html;
			
		
		die();
	}
	
	if(!function_exists('wpews_opts')){
		function wpews_opts($val){
			$instance = get_option('widget_wpecwlwidget');
			$instance = (is_array($instance)?current($instance):array($instance));
			return isset($instance[$val])?$instance[$val]:'';
		}
	}
	class wpecwlWidget extends WP_Widget
	{
	  function wpecwlWidget()
	  {		  
		$theme = wpews_opts('theme');
		$widget_ops = array('classname' => 'wpecwlWidget '.$theme, 'description' => 'Displays Wishlist' );
		$this->WP_Widget('wpecwlWidget', 'eCommerce Wishlist', $widget_ops);
	  }
	 
	  function form($instance)
	  {
		global $wpews_pro;
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];
		$theme_selected = $instance['theme'];
		$themes = array('golden'=>__('Golden Flower', 'wp-ecomm'), 'purple'=>__('Purple Flower', 'wp-ecomm'), 'rock'=>__('Grey Alpine', 'wp-ecomm'));
	?>
	  <p><label for="<?php echo $this->get_field_id.('title'); ?>"><?php _e('Title', 'wp-ecomm'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
      
      <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Theme', 'wp-ecomm'); ?>:</label>

      <select name="<?php echo $this->get_field_name('theme'); ?>">
      	<option value=""><?php _e('Default', 'wp-ecomm'); ?></option>
        <?php if(!empty($themes)): foreach($themes as $theme=>$title): ?>
        <option <?php echo ($theme_selected==$theme?'selected="selected"':''); ?> value="<?php echo $theme; ?>" <?php echo ($wpews_pro?'':'disabled="disabled"'); ?>><?php echo $title; ?> (<?php echo ($wpews_pro?'':__('Premium', 'wp-ecomm')); ?>)</option>
        <?php endforeach; ?>
        <?php endif; ?>
      </select>      

      </p>
      
      <?php
		  $wpsc_compatibility = get_option('wpsc_compatibility');
		  if(is_array($wpsc_compatibility)):
?>
			<strong>Compatibility:</strong>
            <ul>
				<li><a href="https://wordpress.org/plugins/wp-e-commerce/" target="_blank"><img src="<?php echo $wpsc_compatibility['icon']; ?>" /></a><a href="https://wordpress.org/plugins/wp-e-commerce/" target="_blank"><?php _e('Download', 'wp-ecomm'); ?> WP eCommerce <?php echo $wpsc_compatibility['version']; ?></a></li>
                <li><a href="https://wordpress.org/support/plugin/wp-e-commerce-whish-list" target="_blank"><?php _e('Support', 'wp-ecomm'); ?></a></li>
                <li><a href=" https://wordpress.org/plugins/wp-e-commerce-whish-list/screenshots" target="_blank"><?php _e('Demo Shots', 'wp-ecomm'); ?></a></li>
               
			</ul>
<?php		  
		  endif;
		  
	  ?>
      
      </p>
	<?php
	  }
	 
	  function update($new_instance, $old_instance)
	  {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['theme'] = $new_instance['theme'];
		return $instance;
	  }
	 
	  function widget($args, $instance)
	  {
		if(!is_user_logged_in())
		return false;
			  
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	 
		if (!empty($title))
		  echo $before_title . $title . $after_title;;
	 
		
		echo '<div id="wpecwl" class="'.$instance['theme'].'"></div>';
	 
		echo $after_widget;
	  }
	 
	}					
	