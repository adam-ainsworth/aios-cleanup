<?php

/*
Plugin Name: AIOS Cleanup
Plugin URI: https://github.com/adam-ainsworth/aios-cleanup
Description: Removes old entries in the AIOS plugin audit log table
Version: 1.0.0
Author: Adam Ainsworth
Author URI: https://github.com/adam-ainsworth
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

declare(strict_types=1);

class AIOSCleanup {
    private static int $period = (3600 * 24 * 7);

    public static function init()
    {
        error_log('AIOS Cleanup: init');

        add_action('wp_ajax_run_aios_cleanup', [self::class, 'exec'], 10, 0);

        add_filter(sprintf('plugin_action_links_%s', plugin_basename(__FILE__)), [self::class, 'links']);

        add_action('admin_footer', [self::class, 'footer_scripts'], 10, 0);
    }

    public static function footer_scripts() {
        echo('
            <script type="text/javascript">
                jQuery("#aios-cleanup-run").on("click",
                    function(e) {
                        e.preventDefault();
                        jQuery.ajax({
                            type : "GET",
                            url : "/wp-admin/admin-ajax.php",
                            data : { action: "run_aios_cleanup"},
                            success: function(result) {
                                if (result.success === true) {
                                    alert("Clean up succeeded - " + result.rows + " rows cleared");
                                } else {
                                    alert("Clean up failed - check error log for details");
                                }
                            },
                            error: function(e) {
                                alert("Clean up failed - check error log for details");
                            }
                        })
                    }
                );
            </script>
        ');
    }

    public static function links($links) {
        return array_merge($links, [
            '<a href="#" id="aios-cleanup-run">Cleanup!</a>',
        ]);
    }

    public static function exec()
    {
        header('Content-type: application/json');

        global $wpdb;
        $table_name = sprintf('%saiowps_audit_log', $wpdb->prefix);
        error_log(sprintf('AIOS Cleanup: table name %s', $table_name));

        try {
            // Check that the table exists
            $check_query = $wpdb->prepare(
                "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '%s') AND (TABLE_NAME = '%s')",
                DB_NAME,
                $table_name
            );
            $tableCount = $wpdb->get_var($check_query);
        } catch (\Exception $ex) {
            error_log(sprintf('AIOS Cleanup: ERROR in checking table - %s', $ex->getMessage()));
            $success = false;
            $rows = 0;
        }

        // will be null on failure, 0 if no table found or any other number for utter weirdness
        if ($tableCount === '1') {
            error_log(sprintf('AIOS Cleanup: table found'));

            try {
                $ticks = intval(date('U')) - self::$period;
                $delete_query = $wpdb->prepare(
                    'DELETE FROM %i WHERE %i < %d;',
                    $table_name,
                    'created',
                    $ticks
                );
                $result = $wpdb->query($delete_query);

                if ($result === false) {
                    $success = false;
                    $rows = 0;
                } else {
                    $success = true;
                    $rows = $result;
                }
            } catch (\Exception $ex) {
                error_log(sprintf('AIOS Cleanup: ERROR in deleting entries - %s', $ex->getMessage()));
                $success = false;
                $rows = 0;
            }
        } else {
            error_log(sprintf('AIOS Cleanup: table not found'));
            $success = false;
            $rows = 0;
        }

        echo(json_encode(['success' => $success, 'rows' => $rows]));
        wp_die();
    }
}

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

if (current_user_can('manage_options')) {
    AIOSCleanup::init();
}
