<?php
/*
	Plugin Name: Ajax Domain Checker
	Plugin URI: http://asdqwe.net/
	Description: Check domain name availability for all Top Level Domains using shortcode or widget with Ajax search.
	Author: Asdqwe Dev
	Version: 1.1.1
	Author URI: http://asdqwe.net/plugins/wp-domain-checker/
	Text Domain: wdc
 */


function wdc_load_styles() {
	wp_enqueue_style( 'wdc-styles', plugins_url( 'main.css', __FILE__ ) );
	wp_enqueue_script( 'wdc-script', plugins_url( 'script.js', __FILE__ ), array('jquery'));
 	wp_localize_script( 'wdc-script', 'wdc_ajax', array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        'wdc_nonce'     => wp_create_nonce( 'wdc_nonce' ))
    );
  
}
add_action( 'wp_enqueue_scripts', 'wdc_load_styles' );
add_action( 'admin_enqueue_scripts', 'wdc_load_styles' );


function wdc_display_func(){
	check_ajax_referer( 'wdc_nonce', 'security' );

if(isset($_POST['domain']))
{
	$domain = str_replace(array('www.', 'http://'), NULL, $_POST['domain']);
	$split = explode('.', $domain);

		if(count($split) == 1) {
			$domain = $domain.".com";
		}
	$domain = preg_replace("/[^-a-zA-Z0-9.]+/", "", $domain);
	if(strlen($domain) > 0)
	{

		include ('DomainAvailability.php');  
		$Domains = new DomainAvailability();
		$available = $Domains->is_available($domain);
		$custom_found_result_text = __('Congratulations! <b>'.$domain.'</b> is available!', 'wdc');
    	$custom_not_found_result_text = __('Sorry! <b>'.$domain.'</b> is already taken!', 'wdc');
		
		if ($available == '1') {
				$result = array('status'=>1,'domain'=>$domain, 'text'=> '<p class="available">'.$custom_found_result_text.'</p>');
		    	echo json_encode($result);
		} elseif ($available == '0') {
				$result = array('status'=>0,'domain'=>$domain, 'text'=> '<p class="not-available">'.$custom_not_found_result_text.'</p>');
		    	echo json_encode($result);
		}elseif ($available == '2'){
				$result = array('status'=>0,'domain'=>$domain, 'text'=> '<p class="not-available">WHOIS server not found for that TLD</p>');
		    	echo json_encode($result);
		}
		
	}
	else
	{
		echo 'Please enter the domain name';
	}
}
die();
}

add_action('wp_ajax_wdc_display','wdc_display_func');
add_action('wp_ajax_nopriv_wdc_display','wdc_display_func');

function wdc_display_dashboard(){
	do_shortcode('[wpdomainchecker width="350"]');
}

function wdc_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'wdc_dashboard_widget',         
                 'WP Domain Checker',        
                 'wdc_display_dashboard'
                 
        );	
}
add_action( 'wp_dashboard_setup', 'wdc_add_dashboard_widgets' );


function wdc_display_shortcode($atts){

		$image = plugins_url( '/load.gif', __FILE__ );
	
		$atts = shortcode_atts(
		array(
			'width' => '600',
			'button' => 'Check'
		), $atts );

$content = '<div id="domain-form">
	<form method="post" action="./" id="form" class="pure-form"> 
		<input type="text" autocomplete="off" id="Search" name="domain" class="" style="width:100%;max-width:'.$atts['width'].'px;"> 
		<input type="submit" id="Submit" value="'.$atts['button'].'" class="pure-button button-blue">
		<p><div id="loading"><img src="'.$image.'"></img></div></p>
	</form>
		<p><div id="results" class="result"></div></p>
</div>';

return $content;

}


add_shortcode( 'wpdomainchecker', 'wdc_display_shortcode' );


class wdc_widget extends WP_Widget {
	function __construct() {
		parent::__construct(false, $name = __('WP Domain Checker Widget'));
	}
	function form($instance) {
			if (isset($instance['title'])) {
				$title = $instance['title'];
				$width = $instance['width'];
				$button = $instance['button'];
			}else{
			$title = "Domain Availability Check";
			}
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','wdc'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:','wdc'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
		</label>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('button'); ?>"><?php _e('Button Name:','wdc'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('button'); ?>" name="<?php echo $this->get_field_name('button'); ?>" type="text" value="<?php echo $button; ?>" />
		</label>
		</p>
	<?php
	}
	function update($new_instance, $old_instance) {
	    $instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? strip_tags( $new_instance['width'] ) : '';
		$instance['button'] = ( ! empty( $new_instance['button'] ) ) ? strip_tags( $new_instance['button'] ) : '';

		return $instance;
	}

	function widget($args, $instance) {
		$title = $instance['title']; if ($title == '') $title = 'Domain Availability Check';
		$width = $instance['width']; if ($width == '') $width = '150';
		$button = $instance['button']; if ($button == '') $button = 'Check';
		echo $args['before_widget'];
	   
	 	if ( $title ) {
	      echo $args['before_title'] . $title. $args['after_title'];
	   	}
			
		echo do_shortcode("[wpdomainchecker width='$width' button='$button']");

	  	echo $args['after_widget'];
		}
}

function register_wdc_widget()
{
    register_widget( 'wdc_widget' );
}
add_action( 'widgets_init', 'register_wdc_widget');
