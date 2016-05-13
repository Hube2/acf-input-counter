<?php 
	
	/* 
		Plugin Name: ACF Input Counter
		Plugin URI: https://github.com/Hube2/acf-input-counter/
		Description: Show character count for limited text and textarea fields
		Version: 0.0.2
		Author: John A. Huebner II
		Author URI: https://github.com/Hube2/
		License: GPL
	*/
	
	// If this file is called directly, abort.
	if (!defined('WPINC')) {die;}
	
	new acf_input_counter();
	
	class acf_input_counter {
		
		private $version = '0.0.2';
		
		public function __construct() {
			add_action('acf/render_field/type=text', array($this, 'render_field'), 20, 1);
			add_action('acf/render_field/type=textarea', array($this, 'render_field'), 20, 1);
			add_action('admin_head', array($this, 'admin_head'));
			add_action('admin_enqueue_scripts', array($this, 'scripts'));
		} // end public function __construct
		
		private function run() {
			// cannot run on field group editor or it will
			// add code to every ACF field in the editor
			if (isset($_GET['post'])) {
				if (get_post_type(intval($_GET['post'])) == 'acf-field-group') {
					return false;
				}
			}
			return true;
		} // end private function run
		
		public function scripts() {
			if (!$this->run()) {
				return;
			}
			// wp_enqueue_script
			$handle = 'acf-input-counter';
			$src = plugin_dir_url(__FILE__).'acf-input-counter.js';
			$deps = array('acf-field-group');
			$ver = $this->version;
			$in_footer = false;
			wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
		} // end public function scripts
		
		public function render_field($field) {
			if (!$this->run() || 
					!$field['maxlength'] || 
					($field['type'] != 'text' && $field['type'] != 'textarea')) {
				// only run on text and text area fields when maxlength is set
				return;
			}
			$len = strlen($field['value']);
			$max = $field['maxlength'];
			?>
				<span class="char-count">
					chars: <span class="count"><?php echo $len; ?></span>
					of <?php echo $max; ?>
				</span>
			<?php 
		} // end public function render_field
		
		public function admin_head() {
			if (!$this->run()) {
				return;
			}
			?>
				<style type="text/css">
					.acf-field .char-count {
						display: inline-block;
						font-weight: bold;
						font-style: italic;
						margin-top: .5em;
					}
				</style>
			<?php 
		} // end public function admin_head
		
	} // end class acf_input_counter
	
?>