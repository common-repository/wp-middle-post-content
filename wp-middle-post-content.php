<?php
/*
Plugin Name: WP Middle Post Content
Plugin URI: http://www.niceplugins.com/
Description: Insert any HTML code on the middle on your post. You can define whether the HTML code appears on the middle of the content, 1/3 of the content, or 2/3 of the content, etc. You can use ads code as the HTML code.
Author: Xrvel
Version: 1.0.0
Author URI: http://www.xrvel.com/
*/

function xrvel_mdc_get_options() {
	$opt = get_option('xrvel_mdc_options');
	if ($opt == false || $opt == '') {
		$opt = array(
			'enabled' => 1,
			'enable_on' => 1,
			'position' => 1,
			'text' => ''
		);
	} else {
		if (!is_array($opt)) {
			$opt = unserialize($opt);
		}
	}
	return $opt;
}

function xrvel_mdc_options() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if (isset($_POST['go'])) {
		$x_enabled = '';
		$x_enable_on = 1;
		$x_text = '';
		$x_position = 1;
		if (isset($_POST['x_enabled'])) {
			$x_enabled = intval($_POST['x_enabled']);
		}
		if (isset($_POST['x_enable_on'])) {
			$x_enable_on = intval($_POST['x_enable_on']);
		}
		if (isset($_POST['x_text'])) {
			$x_text = trim($_POST['x_text']);
		}
		if (isset($_POST['x_position'])) {
			$x_position = intval(trim($_POST['x_position']));
		}
		$opt = array(
			'enabled' => $x_enabled,
			'enable_on' => $x_enable_on,
			'text' => $x_text,
			'position' => $x_position
		);
		update_option('xrvel_mdc_options', serialize($opt));
		_e('<div id="message" class="updated fade"><p>Options updated.</p></div>');
	}
	$opt = xrvel_mdc_get_options();
	$opt['text'] = stripslashes($opt['text']);
	echo '<div class="wrap">';
	?>
	<h2>WP Middle Post Content Options</h2>
	<form name="form1" method="post" action="">
	<input type="hidden" name="go" value="1" />
	<p>
	WP Middle Post Content status :
	<select name="x_enabled">
	<option value="1">Enabled</option>
	<option value="0"<?php if ($opt['enabled'] == 0) : ?> selected="selected"<?php endif; ?>>Disabled</option>
	</select>
	</p>
	<p>
	Activate on :
	<select name="x_enable_on">
	<option value="1">Posts &amp; Pages</option>
	<option value="2"<?php if ($opt['enable_on'] == 2) : ?> selected="selected"<?php endif; ?>>Posts</option>
	<option value="3"<?php if ($opt['enable_on'] == 3) : ?> selected="selected"<?php endif; ?>>Pages</option>
	</select>
	</p>
	<p>
		Text to insert :<br />
		<textarea name="x_text" cols="100" rows="5"><?php echo htmlentities($opt['text']); ?></textarea>
	</p>
	<p>
	Text position :
	<select name="x_position">
	<optgroup label="2 parts">
	<option value="1">1/2 (middle) of the content</option>
	</optgroup>
	<optgroup label="3 parts">
	<option value="2"<?php if ($opt['position'] == 2) : ?> selected="selected"<?php endif; ?>>1/3 of the content</option>
	<option value="3"<?php if ($opt['position'] == 3) : ?> selected="selected"<?php endif; ?>>2/3 of the content</option>
	</optgroup>
	<optgroup label="4 parts">
	<option value="4"<?php if ($opt['position'] == 4) : ?> selected="selected"<?php endif; ?>>1/4 of the content</option>
	<option value="5"<?php if ($opt['position'] == 5) : ?> selected="selected"<?php endif; ?>>3/4 of the content</option>
	</optgroup>
	<optgroup label="5 parts">
	<option value="6"<?php if ($opt['position'] == 6) : ?> selected="selected"<?php endif; ?>>1/5 of the content</option>
	<option value="7"<?php if ($opt['position'] == 7) : ?> selected="selected"<?php endif; ?>>2/5 of the content</option>
	<option value="8"<?php if ($opt['position'] == 8) : ?> selected="selected"<?php endif; ?>>3/5 of the content</option>
	<option value="9"<?php if ($opt['position'] == 9) : ?> selected="selected"<?php endif; ?>>4/5 of the content</option>
	</optgroup>
	</select>
	</p>
	<p>
		You also can exclude a post / page from being modified by adding <code>xrvel_mdc_skip</code> with value <code>1</code> on the custom field.
	</p>
	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
	</p>
	</form>
	<p>
		Plugin by <a href="http://www.niceplugins.com" target="_blank">NicePlugins.com</a>, by <a href="http://www.xrvel.com" target="_blank">Xrvel</a>
	</p>
	<?php
	echo '</div>';
}

function xrvel_mdc_add_pages() {
	add_options_page('WP Middle Post Content', 'WP Middle Post Content', 'manage_options', 'wp-middle-post-content', 'xrvel_mdc_options');
}

function xrvel_mdc_text_mod($text) {
	global $post;

	$opt = xrvel_mdc_get_options();

	if (is_single() == false || $opt['enabled'] == 0) {
		return $text;
	}

	if ($opt['enable_on'] == 2 && $post->post_type != 'post') {
		return $text;
	}

	if ($opt['enable_on'] == 3 && $post->post_type != 'page') {
		return $text;
	}

	if ((string)get_post_meta($post->ID, 'xrvel_mdc_skip', true) == '1') {
		return $text;
	}

	$s = $text;
	$text_length = strlen($text);
	$s2 = strip_tags($s);
	$s2 = str_replace("\n", ' ', $s2);
	$s2 = str_replace("\r", ' ', $s2);
	$s2 = preg_replace('/([ ]+){2,}/', ' ', $s2);
	$words = explode(' ', $s2);
	if ($opt['position'] == 2) {
		$factor_1 = 1;
		$factor_2 = 3;
	} else if ($opt['position'] == 3) {
		$factor_1 = 2;
		$factor_2 = 3;
	} else if ($opt['position'] == 4) {
		$factor_1 = 1;
		$factor_2 = 4;
	} else if ($opt['position'] == 5) {
		$factor_1 = 3;
		$factor_2 = 4;
	} else if ($opt['position'] == 6) {
		$factor_1 = 1;
		$factor_2 = 5;
	} else if ($opt['position'] == 7) {
		$factor_1 = 2;
		$factor_2 = 5;
	} else if ($opt['position'] == 8) {
		$factor_1 = 3;
		$factor_2 = 5;
	} else if ($opt['position'] == 9) {
		$factor_1 = 4;
		$factor_2 = 5;
	} else {
		$factor_1 = 1;
		$factor_2 = 2;
	}
	if ($words != array()) {
		$mid = intval(ceil(count($words) * $factor_1 / $factor_2) - 1);
		$word = $words[$mid];
		$pos = strpos($s, $word);
		$split1 = substr($s, 0, $pos);
		$split2 = substr($s, $pos);
		$text = $split1.stripslashes($opt['text']).' '.$split2;
	}
	return $text;
}

function xrvel_mdc_uninstall() {
	delete_option('xrvel_mdc_options');
}

add_filter('the_content', 'xrvel_mdc_text_mod');
add_action('admin_menu', 'xrvel_mdc_add_pages');

register_uninstall_hook(ABSPATH.PLUGINDIR.'/wp-middle-post-content/wp-middle-post-content.php', 'xrvel_mdc_uninstall');
?>