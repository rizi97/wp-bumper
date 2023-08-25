<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define('CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0');

/**
 * Enqueue styles
 */
function child_enqueue_styles()
{

	wp_enqueue_style('astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), '', 'all');

}

add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);



add_filter( 'auto_update_theme', '__return_false' );


function bump_change_readmore_text( $translated_text, $text, $domain ) {
	if ( ! is_admin() && $domain === 'woocommerce' && $translated_text === 'Read more') {
		$translated_text = 'Add to cart';
	}
	return $translated_text;
}
add_filter( 'gettext', 'bump_change_readmore_text', 20, 3 );


function bump_custom_override_checkout_fields( $address_fields ) {
	unset($address_fields['billing']['billing_state']);
	unset($address_fields['billing']['billing_postcode']);
	$address_fields['billing']['billing_city']['custom_attributes'] = array('readonly'=>'readonly');
	$address_fields['billing']['billing_city']['default'] = 'Lahore';
	return $address_fields;
}
add_filter( 'woocommerce_checkout_fields', 'bump_custom_override_checkout_fields');


function bump_check_user_logged_in_before_checkout() {
	$pageid = get_option( 'woocommerce_checkout_page_id' );

	if( !is_user_logged_in() && is_page($pageid) ) {
		$url = add_query_arg(
			'redirect_to',
			get_permalink($pagid),
			site_url('/my-account/') 
		);
		wp_redirect($url);
		exit;
	}
	if( is_user_logged_in() ) {

		$product_id = 535;
		$found = false;

		if( is_page('checkout') && !is_wc_endpoint_url('order-received') && ! WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product_id ) ) ) {
			
			foreach ( WC()->cart->get_cart() as $cart_item ) {		
				if ( $cart_item['product_id'] == $product_id ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				WC()->cart->add_to_cart( $product_id ); 

				echo '<style>.elementor-menu-cart__main{visibility:hidden}</style>';
				echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>';
				echo '<script>setTimeout(()=>{ jQuery(".elementor-menu-cart__close-button").trigger("click"); }, 500)</script>';
				echo '<script>setTimeout(()=>{ jQuery(".elementor-menu-cart__main").css("visibility", "visible"); }, 1000)</script>';
			}
		}

		$redirect = isset( $_GET['redirect_to'] );
		if ( !empty( $redirect ) ) {
			echo '<script>window.location.href = "'.$redirect.'";</script>';
		}
	}
}
add_action('template_redirect','bump_check_user_logged_in_before_checkout');




function bump_review_product_tabs( $tabs ) {
	if( ! is_user_logged_in() && is_array ( $tabs['reviews'] ) ) { 
		$tabs['reviews'] = array(
			'title'	=> 'Reviews',
			'callback'	=> function() {
				printf('For dropping a review you will have to <a href="%s">login</a>.', get_site_url(null, '/my-account'));
			}
		);
	}
    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'bump_review_product_tabs', 98 );




function bumper_reg_custom_form_fields() {
    ?>
   	 	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="account_type"><?php _e( 'Account Type', 'woocommerce' ); ?></label>
			<select name="account_type" id="account_type">
				<option value="personal">Personal</option>
				<option value="business">Business</option>
			</select>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="business_name" style="display: none">
			<label for="business_name"><?php _e( 'Business Name', 'woocommerce' ); ?><span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
				name="business_name" />
		</p>
    <?php
}
add_action('woocommerce_register_form_start', 'bumper_reg_custom_form_fields');


function bumper_save_reg_custom_form_fields( $customer_id ) {
    if ( isset( $_POST['account_type'] ) ) {
		update_user_meta( $customer_id, 'account_type', sanitize_text_field( $_POST['account_type'] ) );
    }

	if ( isset( $_POST['business_name'] ) ) {
		update_user_meta( $customer_id, 'business_name', sanitize_text_field( $_POST['business_name'] ) );
    }
}
add_action('woocommerce_created_customer', 'bumper_save_reg_custom_form_fields');




function bumper_add_account_type_to_edit_account_form() {
    $user = wp_get_current_user();

	/*
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="account_type"><?php _e( 'Account Type', 'woocommerce' ); ?></label>
			<select name="account_type" id="account_type">
				<option value="personal" <?php echo ( $user->account_type == 'personal' ) ? 'selected' : 'disabled'; ?>>Personal</option>
				<option value="business" <?php echo ( $user->account_type == 'business' ) ? 'selected' : 'disabled'; ?>>Business</option>
			</select>
		</p>
*/ ?>

<?php if( $user->account_type == 'business' ) : ?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="">
			<label for="business_name"><?php _e( 'Business Name', 'woocommerce' ); ?><span class="required">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
				name="business_name" 
				readonly
				value="<?php echo esc_attr( $user->business_name ); ?>" />
		</p>
<?php
	endif;
}
add_action( 'woocommerce_edit_account_form_start', 'bumper_add_account_type_to_edit_account_form' );


// Save the custom field 'account_type' 
function bumper_save_business_name_account_details( $user_id ) {
    if( isset( $_POST['business_name'] ) )
        update_user_meta( $user_id, 'business_name', sanitize_text_field( $_POST['business_name'] ) );
}
// add_action( 'woocommerce_save_account_details', 'bumper_save_business_name_account_details', 12, 1 );


function bumbper_show_extra_account_details( $user ) {
	$account_type = get_user_meta( $user->ID, 'account_type', true );
	$business_name = get_user_meta( $user->ID, 'business_name', true );

	if ( empty( $account_type ) ) {
		return;
	}
?>
	<h3><?php esc_html_e( 'Extra Account Details', 'bumper' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Account Type', 'bumper' ); ?></th>
			<td>
				<p><?php echo esc_html( ucwords( $account_type ) ); ?></p>
			</td>
		</tr>
		<?php if( $account_type == 'business' ) : ?>
			<tr>
				<th><?php esc_html_e( 'Business Name', 'bumper' ); ?></th>
				<td>
					<p><?php echo esc_html( ucwords( $business_name ) ); ?></p>
				</td>
			</tr>
		<?php endif; ?>
	</table>
<?php
}
add_action( 'show_user_profile', 'bumbper_show_extra_account_details', 15 );
add_action( 'edit_user_profile', 'bumbper_show_extra_account_details', 15 );




add_shortcode('single_add_to_cart_btn', function(){
	$productID = get_the_ID();

	return '<input type="number" value="1" id="fake_input_quantity" style="border-color: #e2e8f0; width: 15%; margin-right: 20px; height: 48px;" /><a href="javascript:void(0)" class="button" id="fake_add_to_cart_btn">Add to cart</a><a style="display:none" href="?add-to-cart=' . $productID . '" data-quantity="4" class="button product_type_simple add_to_cart_button ajax_add_to_cart original" data-product_id="' . $productID . '" data-product_sku="" aria-label="" aria-describedby="" rel="nofollow">Add to cart</a>';
});





// Outputting the hidden field in checkout page
add_action( 'woocommerce_after_order_notes', 'add_custom_checkout_hidden_field' );
function add_custom_checkout_hidden_field( $checkout ) {

    // Output the hidden field
    echo '<div id="user_location_wrapper">
            <input type="hidden" class="input-hidden" name="user_location_lat" id="user_location_lat" value="">
            <input type="hidden" class="input-hidden" name="user_location_long" id="user_location_long" value="">
    </div>';
}

// Saving the hidden field value in the order metadata
add_action( 'woocommerce_checkout_update_order_meta', 'save_custom_checkout_hidden_field' );
function save_custom_checkout_hidden_field( $order_id ) {
    if ( ! empty( $_POST['user_location_lat'] ) ) {
        update_post_meta( $order_id, '_user_location_lat', sanitize_text_field( $_POST['user_location_lat'] ) );
    }
	if ( ! empty( $_POST['user_location_lat'] ) ) {
        update_post_meta( $order_id, '_user_location_long', sanitize_text_field( $_POST['user_location_long'] ) );
    }
}


 // Display "Verification ID" on Admin order edit page
add_action( 'woocommerce_admin_order_data_after_billing_address', 'display_verification_id_in_admin_order_meta', 10, 1 );
function display_verification_id_in_admin_order_meta( $order ) {
    // compatibility with WC +3
    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    echo '<p><strong>'.__('Map Pin Latitude', 'woocommerce').':</strong> ' . get_post_meta( $order_id, '_user_location_lat', true ) . '</p>';
    echo '<p><strong>'.__('Map Pin Longitude', 'woocommerce').':</strong> ' . get_post_meta( $order_id, '_user_location_long', true ) . '</p>';
}
