<?php
/*
 * Plugin Name: UTM Key Replacer
 * Description: Replaces text depending on utm key
 * Version: 1.0.0
 * Author: Timur Kayzer
 * Text Domain: kaiser-utm-headers-replacer
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

mb_internal_encoding("UTF-8");

require(__DIR__ . "/settings.php");

function ksr_utm_scripts_enqueue()
{
    if (is_admin()) {
        wp_enqueue_script('jquery-repeater', plugin_dir_url(__FILE__) . '/jquery.form-repeater.js', ['jquery'], null, true);
        wp_enqueue_script('ksr-utm-script', plugin_dir_url(__FILE__) . '/script.js', ['jquery-repeater'], null, true);
        wp_enqueue_style('ksr-utm-style', plugin_dir_url(__FILE__) . '/style.css');
    }
}


function ksr_mb_ucfirst($text)
{
    return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

add_action('admin_enqueue_scripts', 'ksr_utm_scripts_enqueue');

function ksr_utm_content_filer($content)
{
    $utm_headers = get_option('ksr-utm-list');

    if ($utm_headers) {
        $utm_headers = unserialize($utm_headers);

        if (!empty($utm_headers)) {
            if (isset($_REQUEST['utm_term'])) {
                $utm_campaign = $_REQUEST['utm_term'];
            }

            foreach ($utm_headers as $key => $utm) {
                if ($utm['utm'] && $utm['tag'] && $utm['default']) {

                    if (isset($utm_campaign)) {

                        if ($utm_campaign == $utm['utm']) {
                            if ($utm['replacer']) {
                                $content = str_replace($utm['tag'], $utm['replacer'], $content);
                            } else {
                                $utm_string = str_replace('_', ' ', $utm_campaign);
                                $utm_string = mb_ucfirst($utm_string);
                                $content = str_replace($utm['tag'], $utm_string, $content);
                            }
                            unset($utm_headers[$key]);
                        } else {
                            continue;
                        }

                    } else {
                        $content = str_replace($utm['tag'], $utm['default'], $content);
                    }

                }
            }

            if(isset($utm_campaign))
            foreach ($utm_headers as $utm) {
                $content = str_replace($utm['tag'], $utm['default'], $content);
            }
        }
    }

    return $content;
}

add_filter('the_content', 'ksr_utm_content_filer');

