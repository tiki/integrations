<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Tiki_Woo_Coupons
 * @subpackage Tiki_Woo_Coupons/public
 * @author     Ricardo Gonçalves <ricardo@mytiki.com>
 * @license    GPL2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * @link       https://mytiki.com
 * @since      1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tiki_Woo_Coupons
 * @subpackage Tiki_Woo_Coupons/public
 * @author     Ricardo Gonçalves <ricardo@mytiki.com>
 * @license    GPL2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * @link       https://mytiki.com
 * @since      1.0.0
 */
class Tiki_Woo_Coupons_Public_Rest_Api {

	/**
	 * Register the WP REST API routes for the plugin.
	 */
	public function register_rest_routes() {
		register_rest_route(
			'tiki/v1/woocommerce',
			'coupon/create',
			array(
				'methods'  => 'post',
				'callback' => array(
					$this,
					'create_coupon',
				),
			)
		);
		register_rest_route(
			'tiki/v1/woocommerce',
			'coupon/delete',
			array(
				'methods'  => 'post',
				'callback' => array(
					$this,
					'remove_coupon',
				),
			)
		);
	}

	/**
	 * Create a Coupon Programmatically
	 *
	 * @param WP_REST_Request $request The WP REST API request.
	 */
	public function create_coupon( WP_REST_Request $request ) {
		require_once 'class-tiki-woo-coupons-discounts.php';
		$current_user = wp_get_current_user();
		if ( 0 === $current_user->ID ) {
			return new WP_REST_Response( array( 'error' => 'User not logged in.' ), 400 );
		}
		$tiki_user_id = get_user_meta( $current_user->ID, '_tiki_user_id', true );
		$code         = substr( $tiki_user_id, 0, 10 );
		$coupon       = new WC_Coupon( $code );
		$coupon->set_email_restrictions( array( $current_user->user_email ) );
		$coupon->set_description( 'TIKI WooCommerce autogenerated coupon' );
		$coupon->set_discount_type( Tiki_Woo_Coupons_Discounts::PERCENT );
		$coupon->set_amount( 10 );
		$coupon->save();
		return new WP_REST_Response( array( 'coupon' => $code ), 200 );
	}

	/**
	 * Remove a coupon
	 */
	public function remove_coupon() {
		$current_user = wp_get_current_user();
		if ( ! $current_user ) {
			return WP_REST_Response( array( 'error' => 'User not logged in.' ), 400 );
		}
		$tiki_user_id = get_user_meta( $current_user->ID, '_tiki_user_id', true );
		$coupon       = new WC_Coupon( substr( $tiki_user_id, 0, 10 ) );
		if ( ! empty( $coupon->id ) ) {
			wp_delete_post( $coupon->id );
		}
		return new WP_REST_Response( array( 'message' => 'coupon removed' ), 200 );
	}
}
