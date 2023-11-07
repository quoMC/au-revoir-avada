<?php

/*
 * Plugin Name: Au Revoir Avada
 * Plugin URI:  https://github.com/quoMC/au-revoir-avada
 * Description: A simple plugin to remove all traces of Avada Shortcodes from your content. Based on https://victorfont.com/remove-divi-shortcodes-changing-themes/ and "Bye Bye Divi!" by Sean Barton https://www.sean-barton.co.uk/2017/12/bye-bye-divi/
 * Author:      Matt Cruse - Full Circle Marketing
 * Version:     1.0
 * Author URI:  https://www.full-circle-marketing.co.uk
 *
 *
 * Changelog:
 *
 * V1.0
 * - Initial version
 *
 */

add_action('plugins_loaded', 'mc_aa_init');

function mc_aa_init()
{
    add_action('admin_menu', 'mc_aa_submenu');
}

function mc_aa_submenu()
{
    add_submenu_page(
        'options-general.php',
        'Au Revoir Avada',
        'Au Revoir Avada',
        'manage_options',
        'mc_aa',
        'mc_aa_submenu_cb');
}

function mc_aa_box_start($title)
{
    return '<div class="postbox">
                    <h2 class="hndle">' . $title . '</h2>
                    <div class="inside">';
}

function mc_aa_box_end()
{
    return '    </div>
                </div>';
}

function mc_remove_avada_shortcodes( $content ) {
    $content = trim(preg_replace('/\[\/?fusion.*?\]/gm', '', $content));
    return $content;
}

function mc_aa_submenu_cb()
{
    global $wpdb;

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>Au revoir Avada!</h2>';

    echo '<div id="poststuff">';

    echo '<div id="post-body" class="metabox-holder columns-2">';

    if (isset($_GET['process']) && $_GET['process'] == 'test') {
        echo mc_aa_box_start('Test Output');

        $sql = 'SELECT ID, post_content
                FROM ' . $wpdb->posts . '
                WHERE
                    post_type = "page"
                    OR post_type = "post"
                    AND post_status = "publish"
                    AND post_content LIKE "%[fusion_%"
                    AND post_content != ""
                ORDER BY ID DESC
                LIMIT 5';

        $counter = 0;

        if ($results = $wpdb->get_results($sql)) {
            foreach ($results as $result) {
                echo '<h2>Before</h2><div style="max-height: 150px; overflow: scroll; padding: 10px; border: 1px solid #CCC;">' . $result->post_content . '</div>';

                $result->post_content = mc_remove_avada_shortcodes($result->post_content);

                echo '<h2>After</h2><div style="max-height: 150px; overflow: scroll; padding: 10px; border: 1px solid #CCC;">' . ($result->post_content ? $result->post_content:'<em>Empty Content</em>') . '</div>';

                $counter++;
            }
        }

        echo '<p><strong>Test complete. Completed ' . $counter . ' results! No actual changes were made to content this time around. If you\'re happy with the result then click the "Au Revoir Avada!" button below.</strong></p>';
        echo mc_aa_box_end();

    } else if (isset($_GET['process']) && $_GET['process'] == 'go') {
        echo mc_aa_box_start('Process Output');

        $sql = 'SELECT ID, post_content, post_title
                FROM ' . $wpdb->posts . '
                WHERE
                    post_status = "publish"
                    AND post_content LIKE "%[fusion_%"
                ORDER BY ID DESC';

        //echo $sql . '<br />';
        $counter = 0;

        if ($results = $wpdb->get_results($sql)) {
            foreach ($results as $result) {
                echo '<h2>Before - <a href="' . get_permalink($result->ID) . '" target="_blank">' . $result->post_title . '</a></h2><div style="max-height: 150px; overflow: scroll; padding: 10px; border: 1px solid #CCC;">' . $result->post_content . '</div>';

                $result->post_content = mc_remove_avada_shortcodes($result->post_content);

                echo '<h2>After - <a href="' . get_permalink($result->ID) . '" target="_blank">' . $result->post_title . '</a></h2><div style="max-height: 150px; overflow: scroll; padding: 10px; border: 1px solid #CCC;">' . ($result->post_content ? $result->post_content:'<em>Empty Content</em>') . '</div>';


                $sql = 'UPDATE ' . $wpdb->posts . '
                        SET post_content = "' . esc_sql($result->post_content) . '"
                        WHERE ID = ' . $result->ID;
                $wpdb->query($sql);
                $counter++;
            }
        }

        echo '<p><strong>Process complete. Completed ' . $counter . ' results!</strong></p>';
        echo mc_aa_box_end();
    }

    echo mc_aa_box_start('Au Revoir Avada');

    echo '<p>Simply press the button below and all Avada Fusion Builder related shortcodes will be gone.</p><p>NOTE: This is a one hit function and will literally remove data from your database. There is NO undo button. Make sure to backup your database BEFORE you continue. This will only affect Published content so Drafts and Revisions won\'t be affected. If you want to revert back you could always use the revision system to do so but there is no automated to process to do this.</p><p>Note also that this plugin will simply remove the shortcodes and not process their contents. Text modules will do well however things like image modules etc will be removed entirely.</p>';

    echo '<p>
            <a class="button-primary" href="' . admin_url('options-general.php?page=mc_aa&process=go') . '" onclick="return confirm(\'Are you sure? there is NO undo button!\')">Au Revoir Avada!</a>
            <a class="button-secondary" href="' . admin_url('options-general.php?page=mc_aa&process=test') . '" onclick="return confirm(\'This will show you on screen what it will do to the content of the 5 most recent pages in the system\')">Run a test!</a>
          </p>';

    echo mc_aa_box_end();

    echo '</div>';
    echo '</div>';

    echo '</div>';
}

?>