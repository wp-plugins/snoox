<?php
/*
Plugin Name: Snoox Widget
Plugin URI: http://snoox.com/widgets/plugins
Description: Snoox.com wordpress plugin
Version: 1.0
Author: Eden Zaharoni
Author URI: http://snoox.com
License: GPL
*/


class WidgetElement {
	public $properties ='';
	public $id;
	public $identifier;
	public $type;
	public function __construct($id){
		$this->id = $id;
		$this->identifier = 'SnooxWidget_'.$id;
	}
}
global $def_categories;
global $plugin;

define('MSP_SNOOX_DIR', plugin_dir_path(__FILE__));
define('MSP_SNOOX_URL', plugin_dir_url(__FILE__));
define('SNOOX_URL', 'https://www.snoox.com');

function InitDefaultCategories(){

	global $def_categories;
 
	if(!isset($_SESSION['def_categories']) || empty($_SESSION['def_categories']))
	{
 		//print_r("no no!!");

		$args = array (
			'method' => 'GET',
			'sslverify' => false
		); 
		
		//$categories = array('Movies'=>522,'Music'=>525,'Restaurants'=>499,'Tech'=>1);
		//var_dump($categories);
		
		$response = wp_remote_get(SNOOX_URL . "/shared/GetDefaultCategories",$args);
		$arr = json_decode(wp_remote_retrieve_body($response),true);
		$def_categories = array();
		foreach ($arr as $key => $v) {
			$def_categories[$v['CategoryName']]=$v['CategoryID'];
		}
		
		$_SESSION['def_categories'] = $def_categories;
	}
	else
	{
		$def_categories = $_SESSION['def_categories'];
	}
		
		
	
}
	 
//InitDefaultCategories();
 
function admin_init() {
	 // Set up the settings for this plugin 
	 init_settings(); 
}

function init_settings() 
{
	
	register_setting('snoox-settings', 'widget_settings','validate_settings');

}

function GetCategoryName(){
     global $wp_query;
     if(is_category() || is_single()){      
          $cat_id = get_query_var('cat');
     }
 		
     if (empty($cat_id)){
     	//try get first category in page
     	$category = get_the_category(); 

		if (count($category)>0){
			$cat_name = $category[0]->cat_name;	
			if ($cat_name=="Uncategorized")
			{
				$cat_name = "";
			}
		}
		else
		{
			$cat_name = "";
		}	
		
     }
	 else {
		 $cat_name = get_the_category_by_ID($cat_id);
	 }
     return $cat_name;
}

function validate_settings($settings){
	$tosave = array();
 	$tosave['token'] = $settings['token']; 
	$tosave['preloaderIcon'] = $settings['preloaderIcon']; 
	$tosave['language_id'] = $settings['language_id'];
	$tosave['allowLogin'] = isset($settings['allowLogin'])?1:0;
	$tosave['autoDetectCategories'] = isset($settings['autoDetectCategories'])?1:0;
	
	$tosave['allowExternalLinks'] = isset($settings['allowExternalLinks'])?1:0;
	$tosave['allowRecommendationComments'] = isset($settings['allowRecommendationComments'])?1:0;
	unset($options);
	return $tosave;
	
}
function add_menu(){
	add_options_page('WP Plugin Template Settings', 
	'WP Plugin Template', 
	'manage_options', 
	'wp_plugin_template', 'plugin_settings_page'); 
}

function plugin_settings_page(){
	
	include(sprintf("%s/includes/settings.php", dirname(__FILE__)));
	
}

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
	
	
	InitDefaultCategories();
}

function myEndSession() {
    session_destroy ();
}

add_action('admin_init', 'admin_init'); 
add_action('admin_menu', 'add_menu');
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');


function my_plugin_activate() {
	//set default settings
 	$def_settings = array('token'=>'','allowLogin'=>0,'allowExternalLinks'=>0,'allowRecommendationComments'=>1,'preloaderIcon'=>'','language_id'=>'en');
	add_option('widget_settings',$def_settings);

  /* activation code here */
}
register_activation_hook( __FILE__, 'my_plugin_activate' ); 








function snoox_load(){
	//register_activation_hook(__FILE__,'on_activate');
	global $plugin;
	$plugin = plugin_basename(__FILE__);
    if(is_admin()) //load admin files only in admin
    {
        require_once(MSP_SNOOX_DIR.'includes/admin.php');		
	}
        
    require_once(MSP_SNOOX_DIR.'includes/core.php');
}
snoox_load();

function insert_widget_script() {
	$settings = get_option('widget_settings');
	if (!isset($settings['token'])){
		echo "<script>alert('Snoox Widgets Plugin token is missing!')</script>";
	}
	else {
		echo '<script src="'. SNOOX_URL .'/widgets/script.extended/1.1/?token='.$settings['token'].'"></script>';
		//echo settings
		 
		 //$settings_script = "<script type='text/javascript'>";
		 ?>
		 <script type='text/javascript'>
		 	SW.settings = {
                Allowlogin: <?php echo $settings['allowLogin']==1?'true':'false' ?>,
                AllowExternalLinks: <?php echo $settings['allowExternalLinks']==1?'true':'false' ?>,
                AllowRecommendationComments: <?php echo $settings['allowRecommendationComments']==1?'true':'false' ?>,
                Language : "<?php echo $settings['language_id'] ?>"
            };
            
            <?php if (!empty($settings['preloaderIcon'])) { ?>
            	SW.settings.PreloaderIcon = "<?php echo $settings['preloaderIcon'] ?>";
            <?php } ?>
            
            SW.onready(function () {
            	SW.RegisterSideBarWidget();
            });
		 </script>		 
		 <?php		 
		 //$settings_script.="</script>";
		 
	}

}
add_action('wp_head', 'insert_widget_script');


function snoox_post_footer($content) {   


		$widgetsArray = array();
        $pattern = '/\[Snoox:(Display|Recommendations)(.*?)\]/';
		preg_match_all($pattern, $content,$matches);
		for ($i = 0; $i < count($matches[0]); $i++) { //for each widget pattern found
				$parts = split(',',$matches[2][$i]); //split the plugin key value parameters
				$type =$matches[1][$i];
				$widget = new WidgetElement($i);
				$widget->type = $type;
				//iterate parameters
				$widget->properties.= '{';
				for($j = 0;$j<count($parts);$j++)
				{
					//split current key value
					$keyValue = split('=',$parts[$j]);
					if ($j>0){
						$widget->properties.= ',';
					}
					$widget->properties.=$keyValue[0]. ':' . $keyValue[1];
				}
				$widget->properties.= '}';
				//do replace magic
				$content = str_replace($matches[0][$i],'<div class='. $widget->identifier .'>eden</div>',$content);
				
				//add widget to widgets collection
				array_push($widgetsArray,$widget);
		}
		
		//iterate widgets and call functions
		$script = '<script>';
		$script.= 'SW.onready(function () { ';				
		for($i=0;$i<count($widgetsArray);$i++){
			
			$current = $widgetsArray[$i];
			$script.= '$(".'.$current->identifier .'").'. $current->type .'(' . $current->properties . ');';
				
		}
		$script.= '});';
		$script.= '</script>';
		$content.= $script;
        return $content;
}




class MyNewWidget extends WP_Widget {

	function MyNewWidget() {
		// Instantiate the parent object
		$widget_ops = array('description' => 'Snoox Recommendations widget');
		$this->WP_Widget('MyNewWidget', 'Snoox Recommendations', $widget_ops);
		
		//parent::__construct( false, 'Snoox Recommendations');
	}

	function widget( $args, $instance ) {
		 
		if ($instance['autoDetectCategory'])
		{
			$catName = GetCategoryName();
			if (!empty($catName)){
				$autoCat = "data-autocategory='".$catName."'";
			}
		}
		//echo get_the_category_by_ID(3);		
	 	?>
	 		
	 		<div class='SnooxSideBarWidget' <?php echo $autoCat ?>  data-title="<?php echo $instance['widget_title'] ?>" data-term="<?php echo $instance['search_term'] ?>" data-categoryname="<?php echo $instance['category_name'] ?>" data-category="<?php echo $instance['default_category'] ?>">

	 		</div>
	 	
	 	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;  
  		global $def_categories;
	    //Strip tags from title and name to remove HTML  
	    $instance['default_category'] = strip_tags( $new_instance['default_category'] );  
	    $instance['search_term'] = strip_tags( $new_instance['search_term'] );  
		$instance['category_name'] = array_search($instance['default_category'], $def_categories);
	  	$instance['widget_title'] = strip_tags( $new_instance['widget_title'] );  
		$instance['autoDetectCategory'] = $new_instance['autoDetectCategory'];// empty(strip_tags( $new_instance['autoDetectCategory']))?0:1;  
		//print_r ("x=".strip_tags( $new_instance['autoDetectCategory']));
 
	    return $instance; 
	}

	function form( $instance ) {
		// Output admin widget options form
		global $def_categories;
		//$categories = array('Movies'=>522,'Music'=>525,'Restaurants'=>499,'Tech'=>1);
		
		$defaults = array('default_category'=>522);
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		?>
		  <table>
		  	<tr>
		  		<td style="width:100px">
		  	<label for="<?php echo $this->get_field_name( 'default_category' ); ?>">Category</label>
		  	</td>
		  	<td>
		  	<select id="<?php echo $this->get_field_name( 'default_category' ); ?>" name="<?php echo $this->get_field_name( 'default_category' ); ?>">
				<?php foreach ($def_categories as $k=>$v) {
					$selected = $instance['default_category']==$v?'selected':'';
					echo "<option ". $selected ." value='". $v ."'>". $k ."</options>";
				}
				?>			  		
		  	</select>
		  	</td>
		  	</tr>
		  	
		  	<tr>
		  		<td colspan="2">
		  			<table style="width:100%">
		  				<tr>
				  		<td>
				  			<label for="<?php echo $this->get_field_name( 'autoDetectCategory' ); ?>">Auto Detect Category</label>
				  		</td>
				  		<td align="right">
				  			<input id="<?php echo $this->get_field_id('autoDetectCategory'); ?>" name="<?php echo $this->get_field_name('autoDetectCategory'); ?>" type="checkbox" <?php checked(isset($instance['autoDetectCategory']) ? 1: 0); ?> />
				  		</td>
				  		</tr>		  				
		  			</table>
		  		</td>

		  	</tr>		  	
		  	
		  	<tr>
		  		<td>
		  			<label for="<?php echo $this->get_field_name( 'search_term' ); ?>">Term</label>
		  		</td>
		  		<td>
		  			<input value="<?php echo $instance['search_term']; ?>" type="text" id="<?php echo $this->get_field_name( 'search_term' ); ?>" name="<?php echo $this->get_field_name( 'search_term' ); ?>" />
		  		</td>
		  	</tr>
		  	
		  	<tr>
		  		<td>
		  			<label for="<?php echo $this->get_field_name( 'widget_title' ); ?>">Title</label>
		  		</td>
		  		<td>
		  			<input value="<?php echo $instance['widget_title']; ?>" type="text" id="<?php echo $this->get_field_name( 'widget_title' ); ?>" name="<?php echo $this->get_field_name( 'widget_title' ); ?>" />
		  		</td>
		  	</tr>		  	
		  </table>
		<?php
	}
}

function myplugin_register_widgets() {
	register_widget( 'MyNewWidget' );
}

add_action( 'widgets_init', 'myplugin_register_widgets' );


add_filter('the_content', 'snoox_post_footer', 100);

?>
