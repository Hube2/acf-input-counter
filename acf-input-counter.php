<?php
/*
  Plugin Name: ACF Input Counter
  Plugin URI: https://github.com/Hube2/acf-input-counter/
  Description: Show character count for limited text and textarea fields
  Version: 1.4.0
  Author: John A. Huebner II
  Author URI: https://github.com/Hube2/
  Text-domain: acf-counter
  Domain-path: languages
  GitHub Plugin URI: https://github.com/Hube2/acf-input-counter/
  License: GPL
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

new acf_input_counter();

class acf_input_counter {

    private $version = '1.4.0';
    public $limited_char_types = ['text', 'textarea', 'wysiwyg', 'qtranslate_text', 'qtranslate_textarea', 'qtranslate_wysiwyg'];

    public function __construct() {
        $this->defaults = array(
            'show_prefix' => 0,
        );
        add_action('plugins_loaded', array($this, 'acf_counter_load_plugin_textdomain'));
        foreach ($this->limited_char_types as $type) {
            add_action('acf/render_field/type=' . $type, array($this, 'render_field'), 20, 1);
        }
        add_action('wp_ajax_get_maxlength', array($this, 'get_maxlength'));
        add_filter('acf/prepare_field', array($this, 'counter_prepare_field'), 9, 1);
        add_action('acf/render_field_settings', array($this, 'additional_render_field_settings'), 10, 1);
        add_action('acf/input/admin_enqueue_scripts', array($this, 'scripts'));
        add_filter('jh_plugins_list', array($this, 'meta_box_data'));
    }

// end public function __construct

    public function acf_counter_load_plugin_textdomain() {
        load_plugin_textdomain('acf-counter', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }

    function meta_box_data($plugins = array()) {

        $plugins[] = array(
            'title' => 'ACF Input Counter',
            'screens' => array('acf-field-group', 'edit-acf-field-group'),
            'doc' => 'https://github.com/Hube2/acf-input-counter'
        );
        return $plugins;
    }

// end function meta_box

    private function run() {
        // cannot run on field group editor or it will
        // add code to every ACF field in the editor
        $run = true;
        global $post;
        if ($post && $post->ID && get_post_type($post->ID) == 'acf-field-group') {
            $run = false;
        }
        return $run;
    }

// end private function run

    public function scripts() {
        if (!$this->run()) {
            return;
        }
        // wp_enqueue_script
        $handle = 'acf-input-counter';
        $src = plugin_dir_url(__FILE__) . 'acf-input-counter.js';
        $deps = array('acf-input');
        $ver = $this->version;
        $in_footer = false;
        wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
        wp_enqueue_style('acf-counter', plugins_url('acf-counter.css', __FILE__));
    }

// end public function scripts

    /**
     * counter_prepare_field
     * 
     * prevent rendering of default "maxlength" field setting in its own line
     * 
     * @param array $field
     * @return boolean
     * 
     */
    function counter_prepare_field($field) {
        preg_match('/.*\[([^]]+)\]/', $field['name'], $matches);

        $replace = [
            'maxlength',
        ];

        if (isset($matches[1])) {
            if (in_array($matches[1], $replace)) {
                if (!isset($field['wrapper']['data-name'])) {
                    return;
                }
            }
        }

        // return
        return $field;
    }

    /**
     * additional_render_field_settings
     * 
     * add "maxlength" and "show_prefix" settings inside same row
     * 
     * @param type $field
     */
    function additional_render_field_settings($field) {
        if (in_array($field['type'], $this->limited_char_types)) {
            isset($field['show_prefix']) ? $value = $field['show_prefix'] : $value = '';

            // wrapper                                
            acf_render_field_wrap(array(
                'label' => __('Character Limit', 'acf'),
                'instructions' => __('Show \'chars:\' in front of the counter', 'acf-counter'),
                'type' => 'true_false',
                'name' => 'show_prefix',
                'ui_on_text' => __('Yes', 'acf'),
                'ui_off_text' => __('No', 'acf'),
                'ui' => 1,
                'prefix' => $field['prefix'],
                'value' => $value,
                'message' => __('Show prefix', 'acf-counter'),
                'wrapper' => array(
                    'data-name' => 'show_prefix'
                )
                    ), 'tr', 'field');

            isset($field['maxlength']) ? $maxlength = $field['maxlength'] : $maxlength = '';

            acf_render_field_wrap(array(
                'label' => __('Character Limit', 'acf'),
                'instructions' => __('Leave blank for no limit', 'acf'),
                'type' => 'number',
                'name' => 'maxlength',
                'prefix' => $field['prefix'],
                'value' => $maxlength,
                'prepend' => __('Character Limit', 'acf'),
                'append' => '',
                'wrapper' => array(
                    'data-name' => 'maxlength',
                    'data-append' => 'show_prefix'
                )
                    ), 'tr', 'field');
        }
    }

    public function render_field($field) {
        //echo '<pre>'; print_r($field); echo '</pre>';
        if (!$this->run() ||
                !isset($field['maxlength']) ||
                ($field['type'] != 'text' && !in_array($field['type'], $this->limited_char_types))) {
            // only run on text and text area fields when maxlength is set
            return;
        }
        $len = strlen($field['value']);
        $max = $field['maxlength'];

        $classes = apply_filters('acf-input-counter/classes', array());
        $ids = apply_filters('acf-input-counter/ids', array());

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

        if (!isset($field['show_prefix'])) {
            $field['show_prefix'] = 0;
        }

        if ($field['show_prefix'] == 1) {
            $display = sprintf(
                    __('chars: %1$s of %2$s', 'acf-counter'), '%%len%%', '%%max%%'
            );
        } else {
            $display = sprintf(
                    __('%1$s of %2$s', 'acf-counter'), '%%len%%', '%%max%%'
            );
        }

        $display = apply_filters('acf-input-counter/display', $display);
        $display = str_replace('%%len%%', '<span class="count">' . $len . '</span>', $display);
        $display = str_replace('%%max%%', $max, $display);
        if (isset($field['maxlength']) && $field['maxlength'] > 0) {
            ?>
            <span class="char-count">
                <?php
                echo $display;
                ?>
            </span>
            <?php
        }
    }

// end public function render_field

    private function check($allow, $exist) {
        // if there is anything in $allow
        // see if any of those values are in $exist
        $intersect = array_intersect($allow, $exist);
        if (count($intersect)) {
            return true;
        }
        return false;
    }

// end private function check

    /**
     * Ajax helper neede to retrive maxlength for wysiwyg field type
     */
    function get_maxlength() {
        $field = get_field_object($_POST['field_key']);
        isset($field['maxlength']) ? wp_send_json_success($field) : wp_send_json_error($field);
    }

}

// end class acf_input_counter

if (!function_exists('jh_plugins_list_meta_box')) {

    function jh_plugins_list_meta_box() {
        if (apply_filters('remove_hube2_nag', false)) {
            return;
        }
        $plugins = apply_filters('jh_plugins_list', array());

        $id = 'plugins-by-john-huebner';
        $title = '<a style="text-decoration: none; font-size: 1em;" href="https://github.com/Hube2" target="_blank">Plugins by John Huebner</a>';
        $callback = 'show_blunt_plugins_list_meta_box';
        $screens = array();
        foreach ($plugins as $plugin) {
            $screens = array_merge($screens, $plugin['screens']);
        }
        $context = 'side';
        $priority = 'low';
        add_meta_box($id, $title, $callback, $screens, $context, $priority);
    }

// end function jh_plugins_list_meta_box
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
