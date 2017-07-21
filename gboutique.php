<?php
/*
Plugin Name: gboutique
Donate link: http://wpcb.fr/donate/
Plugin URI: http://wpcb.fr/gboutique
Description: Plugin eCommerce using Google Spreadsheet and paypal
Version: 1.3
Author: 6WWW
Author URI: http://6www.net
*/

function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );
	if ( version_compare($wp_version, "3.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
	 // if the ZF plugin is successfully loaded this constant is set to true
  if (defined('WP_ZEND_FRAMEWORK') && constant('WP_ZEND_FRAMEWORK')) {
    return true;
  }
  // you can also check if ZF is available on the system
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (file_exists("$path/Zend/Loader.php")) {
      define('WP_ZEND_FRAMEWORK', true);
      return true;
    }
  }
  // nothing found, you may advice the user to install the ZF plugin
  define('WP_ZEND_FRAMEWORK', false);
}
add_action( 'admin_init', 'requires_wordpress_version' );
register_activation_hook(__FILE__, 'gboutique_add_defaults');register_uninstall_hook(__FILE__, 'gboutique_delete_plugin_options');add_action('admin_init', 'gboutique_init' );add_action('admin_menu', 'gboutique_add_options_page');add_filter( 'plugin_action_links', 'gboutique_plugin_action_links', 10, 2 );
function gboutique_delete_plugin_options() {
	delete_option('gboutique_options');
}
function gboutique_add_defaults() {
	$tmp = get_option('gboutique_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('gboutique_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"email" => "youremail@gmail.com",
						"pass" => "yourpass",
						"spreadsheetKey" => "0AkLWPxefL-fydHBxX1phZnM1ZFI5aV83RzVjbnFIMG4",
						"apiKey" => "Your Key",
						"emailapiKey" => "Your Paypal Email used to buy the API Key",
						"txt_one" => "Enter whatever you like here..",
						"drp_select_box" => "four",
						"chk_default_options_db" => "",
						"rdo_group_one" => "one",
						"rdo_group_two" => "two"
		);
		update_option('gboutique_options', $arr);
	}
}
function gboutique_init(){
	register_setting( 'gboutique_plugin_options', 'gboutique_options', 'gboutique_validate_options' );
}
function gboutique_add_options_page() {
	add_options_page('GBoutique Options Page', 'GBoutique', 'manage_options', __FILE__, 'gboutique_render_form');
}
function gboutique_render_form() {
	$options = get_option('gboutique_options');
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>gboutique Options</h2>
		<ol>
		<li>
		<?php if (WP_ZEND_FRAMEWORK){
			echo 'Zend is installed -> Ok !';
		}
		else{
		echo 'Install Zend first : http://h6e.net/wiki/wordpress/plugins/zend-framework';	
		}?>
		</li>
		<li>Copy the different files <a href="https://docs.google.com/open?id=0B0LWPxefL-fyVXl6a0lyNzEzSlk">from here</a> to your google doc account</li>
		
		<?php
		$GoogleConnection=true;
		try {$client = Zend_Gdata_ClientLogin::getHttpClient($options['email'],$options['pass']);}
		catch (Zend_Gdata_App_AuthException $ae){echo $ae->exception();$GoogleConnection=false;}
		if ($GoogleConnection){
			echo '<li>Your google connection is living-> Ok!</li>';
		}
		else {
			echo '<li>Your google connection is not ok, check email and pass below</li>';
		}
		// Todo : catch error if spreadsheetKey is wrong
		?>
		
		<li>(Optional) For the billing options, copy paste the script <a href="<?php echo plugins_url();?>/gboutique/library/GoogleScript.txt">GoogleScript.txt</a> in your Google Spreadsheet Script Editor and buy an API key here : <a href="http://wpcb.fr/api-key/">http://wpcb.fr/api-key</a></li>
		<li>Place <strong>[gboutique]</strong> shortcode in one of your page to display the Boutique</li>
		<?php $plugin_dir_path = dirname(__FILE__);?>
		<li>(Optional) Edit <?php echo $plugin_dir_path;?>/templates/yourtemplate.php to edit the look of your gboutique product page</li>
		<li>Fill in the form below</li>
		</ol>
		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('gboutique_plugin_options'); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Email (Google Doc Account)</th>
					<td><input type="text" size="57" name="gboutique_options[email]" value="<?php echo $options['email']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">Pass (Google Doc Account)</th>
					<td><input type="password" size="57" name="gboutique_options[pass]" value="<?php echo $options['pass']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">Google Spreadsheet Key<br/>(look at the url of your spreadsheet)</th>
					<td><input type="text" size="57" name="gboutique_options[spreadsheetKey]" value="<?php echo $options['spreadsheetKey']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">WPCB Api Key<br/>(Optional : For Billing options)</th>
					<td><input type="text" size="57" name="gboutique_options[apiKey]" value="<?php echo $options['apiKey']; ?>" /></td>
				</tr>
				<tr>
					<th scope="row">Paypal email that you use to buy your api key<br/>(Optional : For Billing options)</th>
					<td><input type="text" size="57" name="gboutique_options[emailapiKey]" value="<?php echo $options['emailapiKey']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<p style="margin-top:15px;">
			<p><a href="http://www.seoh.fr" target="_blank" style="color:#72a1c6;">Référencement avec SEOh</a></p>
			<p>Additional help : <a href="http://wpcb.fr/gboutique" target="_blank">http://wpcb.fr/gboutique</a> (will open in a new tab)</p>
		</p>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function gboutique_validate_options($input) {
	 // strip html from textboxes
	$input['email'] =  wp_filter_nohtml_kses($input['email']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['pass'] =  wp_filter_nohtml_kses($input['pass']); // Sanitize textbox input (strip html tags, and escape characters)
	$input['spreadsheetKey'] =  wp_filter_nohtml_kses($input['spreadsheetKey']);
	$input['apiKey'] =  wp_filter_nohtml_kses($input['apiKey']);
	$input['emailapiKey'] =  wp_filter_nohtml_kses($input['emailapiKey']);
	return $input;
}

// Display a Settings link on the main Plugins page
function gboutique_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$gboutique_links = '<a href="'.get_admin_url().'options-general.php?page=gboutique/gboutique.php">'.__('Settings').'</a>';
		array_unshift( $links, $gboutique_links );
	}
	return $links;
}


function shortcode_gboutique_handler( $atts, $content=null, $code="" ) {
	include('inc.php');
	include('templates/'.$Settings['template'].'.php');
	return $html;
}
add_shortcode( 'gboutique', 'shortcode_gboutique_handler' );

?>