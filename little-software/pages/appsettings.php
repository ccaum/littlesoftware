<?php
/**
 * Little Software Stats
 *
 * An open source program that allows developers to keep track of how their software is being used
 *
 * @package		Little Software Stats
 * @author		Little Apps
 * @copyright           Copyright (c) 2011, Little Apps
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @link		http://little-apps.org
 * @since		Version 0.1
 */

if ( !defined( 'LSS_LOADED' ) ) die( 'This page cannot be loaded directly' );

require_once '../inc/main.php';

// Make sure user is logged in
verify_user( );
    
$app_id = $_GET['id'];
if ( !$db->select( "applications", array( "ApplicationId" => $app_id ), "", "0,1" ) )
    die( "Unable to query database: " . $db->last_error );

if ( $db->records == 0 )
    die( "Unable to find application information" );

$app_name = $db->arrayed_result['ApplicationName'];
$application_recieving = $db->arrayed_result['ApplicationRecieving'];

if ( isset( $_POST['type'] ) ) {
    // Verify CSRF token
    verify_csrf_token( );

    $application_recieving = $db->arrayed_result['ApplicationRecieving'];
    
    $returned = false;
    
    echo '<div id="output">';

    if ( $_POST['type'] == 'update' ) {
        $application_name = $_POST['appname'];

        if ( $application_name == '' ) {
            show_msg_box( __( "The application name cannot be empty" ), "red" );
        } else if ( !$db->update( "applications", array( "ApplicationName" => $application_name ), array( "ApplicationId" => $app_id ) ) ) {
            show_msg_box( __( "Unable to query database: " ) . $db->last_error, "red" );
        } else {
            show_msg_box( __( "This page will be refreshed in a moment. Click" ) . " <a href='javascript: refreshUrl()'>" . __( "here" ) . "</a> " . __( "if your not redirected" ), "green" );
            echo "<script type='text/javascript'>";
            echo "window.setTimeout('refreshUrl()', 3000);";
            echo "</script>";
        }
    } else if ( $_POST['type'] == 'reset' ) {
        // Remove all users + sessions + events w/ application id
        $query = "DELETE u.*, s.*, e.* ";
        $query .= "FROM `".$db->prefix."uniqueusers` AS u, `".$db->prefix."sessions` AS s, `".$db->prefix."events` AS e ";
        $query .= "WHERE u.UniqueUserId = s.UniqueUserId AND s.SessionId = e.SessionId AND s.ApplicationId = '" . $app_id . "'";
        
        if ( !$db->execute_sql( $query ) ) {
            show_msg_box( __( "Unable to query database: " ) . $db->last_error, "red" );
        } else {
            show_msg_box( __( "This page will be refreshed in a moment. Click" ) . " <a href='javascript: refreshUrl()'>" . __( "here" ) . "</a> " . __( "if your not redirected" ), "green" );
            echo "<script type='text/javascript'>";
            echo "window.setTimeout('refreshUrl()', 3000);";
            echo "</script>";
        }
    } else if ( $_POST['type'] == 'status' ) {
        // Start/Stop application

        if ( !$db->update( "applications", array( "ApplicationRecieving" => !( $application_recieving ) ), array( "ApplicationId" => $app_id ) ) ) {
            show_msg_box( __( "Unable to query database: " ) . $db->last_error, "red" );
        } else {
            show_msg_box( __( "This page will be refreshed in a moment. Click" ) . " <a href='javascript: refreshUrl()'>" . __( "here" ) . "</a> " . __( "if your not redirected" ), "green" );
            echo "<script type='text/javascript'>";
            echo "window.setTimeout('refreshUrl()', 3000);";
            echo "</script>";
        }
    }
    
    echo '</div>';
}
?>
<div class="contentcontainers">
    <div class="contentcontainer med left">
        <div class="headings alt">
            <h2 class="left"><?php _e( 'Application' ); ?></h2>
        </div>

        <!-- Application Info Start -->
        <div class="contentbox">
            <form id="form" action="#" method="post">
                <?php generate_csrf_token(); ?>
                <input name="type" type="hidden" value="update" />
                <table id="id-form" border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <th><?php _e( 'Application name:' ); ?></th>
                            <td><input type="text" class="inp-form" name="appname" value="<?php echo $app_name ?>" /></td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Application ID:' ); ?></th>
                            <td><?php echo $app_id ?></td>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <td><input class="form-submit" type="submit" name="update" value="<?php _e( 'Update' ); ?>" /></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <!-- Application Info Start -->
    </div>
    <div class="contentcontainer sml right">
        <div class="headings alt">
            <h2 class="left"><?php _e( 'Reset Analytics Data' ); ?></h2>
        </div>

        <!-- Reset Analytics Data Start -->
        <div class="contentbox">
            <form id="form" action="#" method="post">
                <?php generate_csrf_token(); ?>
                <input name="type" type="hidden" value="reset" />
                <h2><?php _e( 'Reset Analytics Data' ); ?></h2>
                <strong><?php _e( 'Warning:' ); ?></strong> <?php _e( 'This will delete all the gathered data for this application!' ); ?>
                <br /><br />
                <input name="reset" type="submit" class="form-submit" value="<?php _e( 'Reset' ); ?>" style="float: none" onclick="if (!confirm('<?php _e( 'Are you sure?' ); ?>')) return false;" />
            </form>
        </div>
        <!-- Reset Analytics Data End -->
    </div>

    <div class="contentcontainer sml right">
        <div class="headings alt">
            <h2 class="left"><?php _e( 'Application Status' ); ?></h2>
        </div>

        <!-- Application Status Start -->
        <div class="contentbox">
            <form id="form" action="#" method="post">
                <?php generate_csrf_token(); ?>
                <input name="type" type="hidden" value="status" />
                <h2><?php _e( 'Application Status' ); ?></h2>
                <font size="2"><strong><?php _e( 'Status:' ); ?></strong> <?php echo ( $application_recieving ) ? "<span class='usagetxt greentxt'>" . __( 'Started' ) . "</span>" : "<span class='usagetxt redtxt'>" . __( 'Stopped' ) . "</span>"; ?></font>
                <br /><br />
                <?php _e( 'You can stop your application if you do not want to receive data from it' ); ?>
                <br /><br />
                <input name="status" type="submit" class="form-submit" value="<?php echo ( $application_recieving ) ? __( "Stop" ) : __( "Start" ); ?>" style="float: none" />
            </form>
        </div>
        <!-- Application Status End -->
    </div>
</div>