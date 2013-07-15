<?php
global $test;
$test = $plugin;

function my_plugin_admin_action_links($links, $file) {
	global $test;
 
     if ($file == $test) {    	
		$settings_link = '<a href="options-general.php?page=wp_plugin_template">Settings</a>';
        
        array_unshift($links, $settings_link);
    }
    return $links;
}
//echo $plugin;
add_filter('plugin_action_links', 'my_plugin_admin_action_links', 10, 2);
?>