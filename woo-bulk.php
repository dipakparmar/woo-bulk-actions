<?php
/*
Plugin Name: Woocommerce Bulk Actions
Plugin URI: https://github.com/dipakparmar/woo-bulk-actions/archive/main.zip
Description: Plugin to Add Cancel Bulk order option for Orders
Version: 1.0.0
Author: Dipak Parmar
Author URI: https://dipakparmar.tech
License: GPLv2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
*/

add_filter( 'bulk_actions-edit-shop_order', 'my_register_bulk_action' );
// edit-shop_order is the screen ID of the orders page

function my_register_bulk_action( $bulk_actions ) {

    $bulk_actions['mark_change_status_to_cancelled'] = 'Cancel Order';
    // <option value = 'mark_awaiting_shipment'>Order Cancel</option>
    return $bulk_actions;

}

/*
* Bulk action handler
* Make sure that 'action name' in the hook is the same like the option value from the above function
*/
add_action( 'admin_action_mark_change_status_to_cancelled', 'my_bulk_process_custom_status' );
// admin_action - action name

function my_bulk_process_custom_status() {

    // if an array with order IDs is not presented, exit the function
    if ( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
    return;

    foreach ( $_REQUEST['post'] as $order_id ) {

        $order = new WC_Order( $order_id );
        $order_note = 'That\'s what happened by bulk edit:';
    $order->update_status('Cancelled', $order_note, true ); // "my-shipment" is the order status name 
}

// of course using add_query_arg() is not required, you can build your URL inline
$location = add_query_arg( array(
        'post_type' => 'shop_order',
    'mark_change_status_to_cancelled' => 1, // mark_change_status_to_cancelled=1 is just the $_GET variable for notices
    'changed' => count( $_REQUEST['post'] ), // number of changed orders
    'ids' => join( $_REQUEST['post'], ', ' ),
    'post_status' => 'all'
), 'edit.php' );

wp_redirect( admin_url( $location ) );
exit;


}

/*
 * Notices
 */
add_action('admin_notices', 'my_custom_order_status_notices');


function my_custom_order_status_notices() {

    global $pagenow, $typenow;


if( $typenow == 'shop_order' 
 && $pagenow == 'edit.php'
 && isset( $_REQUEST['mark_change_status_to_cancelled'] )
 && $_REQUEST['mark_change_status_to_cancelled'] == 1
 && isset( $_REQUEST['changed'] ) ) {

    $message = sprintf( _n( 'Order status changed.', '%s order statuses changed.', $_REQUEST['changed'] ), number_format_i18n( $_REQUEST['changed'] ) );
    echo '<div class = \'updated\"><p>{$message}</p></div>";

    }

}

