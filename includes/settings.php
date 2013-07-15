<?php 
 $settings = get_option('widget_settings');
 $languages = array('English'=>'en', 'Hebrew'=>'he');
?>
<div class="wrap">
	<h2>Snoox Widgets Settings</h2>
	<form method="post" action="options.php">
		<?php settings_fields('snoox-settings'); ?>
	 
		<table class='form-table'>
			<tr valign="top">
				<td colspan="2">
					Please paste your business token in the below textbox<br>
					If you didnt already create a business via Snoox, please visit <a href="<?php echo SNOOX_URL ?>/Business/join?reff=wp" target="_blank">Snoox Business Page</a>.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="token">API Token</label></th>
				<td><input type="text" name="widget_settings[token]" id="token" style="width:300px" value="<?php echo $settings['token'] ?>" /></td>
			</tr>
			<tr>
				<td colspan="2">
					<h2 style="font-size:19px ">General Settings</h2>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="allowLogin">Allow Login</label></th>
				<td><input type="checkbox" name="widget_settings[allowLogin]" id="allowLogin" <?php if($settings['allowLogin']) echo "Checked" ?> /></td>
			</tr>		
			<tr valign="top">
				<th scope="row"><label for="allowExternalLinks">Allow External Links</label></th>
				<td><input type="checkbox" name="widget_settings[allowExternalLinks]" id="allowExternalLinks" <?php if($settings['allowExternalLinks']) echo "Checked" ?>  /></td>
			</tr>	
			<tr valign="top">
				<th scope="row"><label for="allowRecommendationComments">Allow Recommendation Comments</label></th>
				<td><input type="checkbox" name="widget_settings[allowRecommendationComments]" id="allowRecommendationComments" <?php if($settings['allowRecommendationComments']) echo "Checked" ?>  /></td>
			</tr>				
			<tr valign="top">
				<th scope="row"><label for="preloaderIcon">Preloader Icon</label></th>
				<td><input type="text" name="widget_settings[preloaderIcon]" id="preloaderIcon" style="width:300px" value="<?php echo $settings['preloaderIcon'] ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="language_id">Widget Language</label></th>
				<td>
					<select name="widget_settings[language_id]" id="language_id">		
						<?php foreach ($languages as $k=>$v) {
							$selected = $settings['language_id']==$v?'selected':'';
							echo "<option ". $selected ." value='". $v ."'>". $k ."</options>";
						}
						?>				
					</select>										
				</td>
			</tr>			
			<?php if (false){ ?>	
			<tr>
				<td colspan="2">
					<h2 style="font-size:19px ">Recommendations Settings</h2>
				</td>
			</tr>	
			<tr valign="top">
				<th scope="row"><label for="rec_isActive">Is Recommendation Widget Active?</label></th>
				<td><input type="checkbox" name="widget_settings[rec_isActive]" id="rec_isActive" <?php if($settings['rec_isActive']) echo "Checked" ?> /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="rec_category">Default Category</label></th>
				<td>
					<select name="widget_settings[rec_category]" id="rec_category">		
						<?php foreach ($categories as $k=>$v) {
							$selected = $settings['rec_category']==$v?'selected':'';
							echo "<option ". $selected ." value='". $v ."'>". $k ."</options>";
						}
						?>				
					</select>
				</td>
			</tr>	
			<?php } ?>																			
		</table>
		<?php submit_button(); ?>
	</form>
</div>

 
 