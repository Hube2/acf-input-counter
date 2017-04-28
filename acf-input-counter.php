<?php

	/*
		Plugin Name: ACF Input Counter
		Plugin URI: https://github.com/Hube2/acf-input-counter/
		Description: Show character count for limited text and textarea fields
		Version: 1.4.1
		Author: John A. Huebner II
		Author URI: https://github.com/Hube2/
		Text-domain: acf-counter
		Domain-path: languages
		GitHub Plugin URI: https://github.com/Hube2/acf-input-counter/
		License: GPL
	*/

	// If this file is called directly, abort.
	if (!defined('WPINC')) {die;}

	new acf_input_counter();

	class acf_input_counter {

		private $version = '1.4.1';

		public function __construct() {
			add_action('plugins_loaded', 					array($this, 'acf_counter_load_plugin_textdomain'));
			add_action('acf/render_field/type=text', 		array($this, 'render_field'), 20, 1);
			add_action('acf/render_field/type=textarea', 	array($this, 'render_field'), 20, 1);
			add_action('acf/input/admin_enqueue_scripts', 	array($this, 'scripts'));
			add_filter('jh_plugins_list', 					array($this, 'meta_box_data'));
		} // end public function __construct

		public function acf_counter_load_plugin_textdomain() {
	    	load_plugin_textdomain( 'acf-counter', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

			function meta_box_data($plugins=array()) {

				$plugins[] = array(
					'title' 	=> 'ACF Input Counter',
					'screens' 	=> array('acf-field-group', 'edit-acf-field-group'),
					'doc' 		=> 'https://github.com/Hube2/acf-input-counter'
				);
				return $plugins;

			} // end function meta_box

		private function run() {
			// cannot run on field group editor or it will
			// add code to every ACF field in the editor
			$run = true;
			global $post;
			if ($post && $post->ID && get_post_type($post->ID) == 'acf-field-group') {
				$run = false;
			}
			return $run;
		} // end private function run

		public function scripts() {
			if (!$this->run()) {
				return;
			}
			// wp_enqueue_script
			$handle    	= 'acf-input-counter';
			$src       	= plugin_dir_url(__FILE__).'acf-input-counter.js';
			$deps      	= array('acf-input');
			$ver       	= $this->version;
			$in_footer 	= false;
			wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
			wp_enqueue_style('acf-counter', plugins_url( 'acf-counter.css' , __FILE__ ));
		} // end public function scripts

		public function render_field($field) {
			//echo '<pre>'; print_r($field); echo '</pre>';
			if (!$this->run() ||
			    !$field['maxlength'] ||
			    ($field['type'] != 'text' && $field['type'] != 'textarea')) {
				// only run on text and text area fields when maxlength is set
				return;
			}
			if (function_exists('mb_strlen')) {
				$len = mb_strlen($field['value']);
			} else {
				$len = strlen($field['value']);
			}
			$max = $field['maxlength'];

			$classes 	= apply_filters('acf-input-counter/classes', array());
			$ids 		= apply_filters('acf-input-counter/ids', array());

			$insert = true;
			if (count($classes) || count($ids)) {
				$insert = false;

				$exist = array();
				if ($field['wrapper']['class']) {
					$exist = explode(' ', $field['wrapper']['class']);
				}
				$insert = $this->check($classes, $exist);

				if (!$insert && $field['wrapper']['id']) {
					$exist = array();
					if ($field['wrapper']['id']) {
						$exist = explode(' ', $field['wrapper']['id']);
					}
					$insert = $this->check($ids, $exist);
				}
			} // end if filter classes or ids

			if (!$insert) {
				return;
			}
			$display = sprintf(
				__('chars: %1$s of %2$s', 'acf-counter'),
				'%%len%%',
				'%%max%%'
			);
			$display = apply_filters('acf-input-counter/display', $display);
			$display = str_replace('%%len%%', '<span class="count">'.$len.'</span>', $display);
			$display = str_replace('%%max%%', $max, $display);
			?>
				<span class="char-count">
					<?php
						echo $display;
					?>
				</span>
			<?php
		} // end public function render_field

		private function check($allow, $exist) {
			// if there is anything in $allow
			// see if any of those values are in $exist
			$intersect = array_intersect($allow, $exist);
			if (count($intersect)) {
				return true;
			}
			return false;
		} // end private function check

	} // end class acf_input_counter

	if (!function_exists('jh_plugins_list_meta_box')) {
		function jh_plugins_list_meta_box() {
			if (apply_filters('remove_hube2_nag', false)) {
				return;
			}
			$plugins = apply_filters('jh_plugins_list', array());

			$id 		= 'plugins-by-john-huebner';
			$title 		= '<a style="text-decoration: none; font-size: 1em;" href="https://github.com/Hube2" target="_blank">Plugins by John Huebner</a>';
			$callback 	= 'show_blunt_plugins_list_meta_box';
			$screens 	= array();
			foreach ($plugins as $plugin) {
				$screens = array_merge($screens, $plugin['screens']);
			}
			$context 	= 'side';
			$priority 	= 'low';
			add_meta_box($id, $title, $callback, $screens, $context, $priority);


		} // end function jh_plugins_list_meta_box
		add_action('add_meta_boxes', 'jh_plugins_list_meta_box');

		function show_blunt_plugins_list_meta_box() {
			$plugins = apply_filters('jh_plugins_list', array());
			?>
				<p style="margin-bottom: 0;">Thank you for using my plugins</p>
				<ul style="margin-top: 0; margin-left: 1em;">
					<?php
						foreach ($plugins as $plugin) {
							?>
								<li style="list-style-type: disc; list-style-position:">
									<?php
										echo $plugin['title'];
										if ($plugin['doc']) {
											?> <a href="<?php echo $plugin['doc']; ?>" target="_blank">Documentation</a><?php
										}
									?>
								</li>
							<?php
						}
					?>
				</ul>
				<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=hube02%40earthlink%2enet&lc=US&item_name=Donation%20for%20WP%20Plugins%20I%20Use&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">Please consider making a small donation.</a></p><?php
		}
	} // end if !function_exists

?>
