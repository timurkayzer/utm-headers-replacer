<?php


class KSR_UtmSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'UTM Options',
            'UTM Options',
            'manage_options',
            'ksr-utm-settings',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        if (isset($_REQUEST['ksr-utm-list']) && is_array($_REQUEST['ksr-utm-list'])) {
            $utm_list = serialize($_REQUEST['ksr-utm-list']);
            update_option('ksr-utm-list', $utm_list);
        }
        else{

            $new_utm_list = [];

            $file_name = $_FILES['csv_file']['tmp_name'];
            $row = 1;
            if (($handle = fopen("$file_name", "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);

                    if($num != 4)continue;
//utm tag replacer default
                    $new_utm_list[] = [
                            'utm' => $data[0],
                            'tag' => $data[1],
                            'replacer' => $data[2],
                            'default' => $data[3],
                    ];
                }
                fclose($handle);
            }

            if(!empty($new_utm_list)){
                if(isset($_REQUEST['replace_old'])){
                    update_option('ksr-utm-list',serialize($new_utm_list));
                }
                else{
                    $utm_list = get_option('ksr-utm-list');
                    $utm_list = unserialize($utm_list);

                    $all_utm = array_merge($utm_list,$new_utm_list);

                    $all_utm = serialize($all_utm);

                    update_option('ksr-utm-list',$all_utm);
                }
            }

        }
    }

    /**
     * Options page callback
     */
    public
    function create_admin_page()
    {
        // Set class property
        $this->options = get_option('ksr-utm-list');

        ?>
        <div class="wrap">
            <h1>UTM Options</h1>
            <form method="post" name="save_options" class="repeater-form" action="options.php">
                <?php settings_fields('ksr_utm_option_group'); ?>
                <div data-repeater-list="ksr-utm-list">
                    <div data-repeater-item>
                        <div class="plugin-select">
                            <label for="utm">UTM Key</label>
                            <input type="text" name="utm">
                        </div>
                        <div class="plugin-select">
                            <label for="tag">Tag to replace</label>
                            <input type="text" name="tag">
                        </div>
                        <div class="plugin-select">
                            <label for="replacer">Replacer</label>
                            <textarea name="replacer"></textarea>
                        </div>
                        <div class="plugin-select">
                            <label for="default">Text by default</label>
                            <textarea name="default"></textarea>
                        </div>
                        <input data-repeater-delete type="button" value="Delete"/>
                        <input data-repeater-copy type="button" value="Copy"/>
                    </div>
                </div>
                <input data-repeater-create type="button" value="Add"/>
                <?php
                submit_button();
                ?>
            </form>
        </div>
        <script>
            <?
            if(isset($this->options) && !empty(unserialize($this->options))):
            $arr = unserialize($this->options);
            ?>
            var valuesList = [
                <?
                foreach ($arr as $item):
                    echo json_encode($item) . ',';
                endforeach;
                ?>
            ];
            <? endif; ?>
        </script>
        <form name="file_import" enctype="multipart/form-data" action="options.php" method="post">
            <?php settings_fields('ksr_utm_option_group'); ?>
            <div class="plugin-select">
                <div class="mb-20">
                    <label for="csv_file">Import from file</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                </div>
                <div class="mb-20">
                    <input type="checkbox" name="replace_old" id="replace_old">
                    <label for="replace_old">Replace old values</label>
                </div>

                <input type="submit" name="submit" id="submit" class="button button-primary" value="Import file">
            </div>
        </form>
        <?php

    }

    /**
     * Register and add settings
     */
    public
    function page_init()
    {
        register_setting(
            'ksr_utm_option_group', // Option group
            'ksr_utm_option_group', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            [$this, 'return_false'], // Callback
            'ksr_utm_option_group' // Page
        );

        add_settings_field(
            'ksr-utm-list', // ID
            'UTM Options', // Title
            [$this, 'return_false'], // Callback
            'ksr_utm_option_group', // Page
            'setting_section_id' // Section
        );
    }

    public
    function return_false()
    {
        return;
    }

    public
    function getOptions()
    {
        return $this->options;
    }

}

if (is_admin())
    return $my_settings_page = new KSR_UtmSettingsPage();