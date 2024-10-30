<?php
/**
 * Plugin Name: Cross/Upsell Popup for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/cross-upsell-popup-for-woocommerce
 * Description: A simple plugin to boost your sales with WooCommerce Upsell and Cross-Sell offers upon purchase of particular products on any page.
 * Version: 1.0.0
 * Author: Your WC Ninja
 * Author URI: https://yourwcninja.com/
 * Text Domain: cross-upsell-popup-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Cross_Up_Sell_Popup_For_WC {
	static $PLUGIN_NAME = 'Cross/Upsell Popup for WooCommerce';
	static $SLUG = 'cross-upsell-popup-for-woocommerce';

	private static $instance;

	function __construct() {
		add_action( 'plugin_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'check_woocommerce_is_active' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'admin_settings_link' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_id' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_backend_assets' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_assets' ) );
		add_action( 'wp_footer', array( $this, 'popup_modal' ) );
		add_action( 'wp_ajax_validate_cart_to_popup', array( $this, 'validate_cart_to_popup' ) );
		add_action( 'wp_ajax_nopriv_validate_cart_to_popup', array( $this, 'validate_cart_to_popup' ) );
		add_action( 'wp_ajax_close_popup', array( $this, 'close_popup' ) );
		add_action( 'wp_ajax_nopriv_close_popup', array( $this, 'close_popup' ) );
		add_action( 'woocommerce_cart_emptied', array( $this, 'unset_cookie' ) );
		add_action( 'woocommerce_add_to_cart', array( $this, 'trigger_added_to_cart_event' ), 10, 6 );
	}

	function load_plugin_textdomain() {
		load_plugin_textdomain(
			'cross-upsell-popup-for-woocommerce',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * @param $screenIds
	 *
	 * @return mixed
	 */
	function add_screen_id( $screenIds ) {
		$screenIds[] = 'toplevel_page_' . self::$SLUG;

		return $screenIds;
	}

	/**
	 * @param $links
	 *
	 * @return mixed
	 */
	function admin_settings_link( $links ) {
		$url           = esc_url( add_query_arg(
			'page',
			self::$SLUG,
			get_admin_url() . 'admin.php'
		) );
		$settings_link = "<a href='$url'>" . __( 'Settings', 'cross-upsell-popup-for-woocommerce' ) . '</a>';

		$links[] = $settings_link;

		return $links;
	}

	/**
	 * @return void
	 */
	function load_backend_assets() {
		if ( get_current_screen()->id === 'toplevel_page_' . self::$SLUG ) {
			wp_enqueue_style( self::$SLUG . '-admin-css', self::plugin_url() . '/assets/css/admin.css', array(), '1.0.0' );
		}
	}

	/**
	 * @return void
	 */
	function register_settings_page() {
		add_menu_page( __( 'WC Cross Up Sell Popup', 'cross-upsell-popup-for-woocommerce' ), __( 'WC Cross Up Sell Popup', 'cross-upsell-popup-for-woocommerce' ), 'manage_options', self::$SLUG, array(
			$this,
			'admin_view'
		), 'dashicons-admin-generic', 70 );
	}

	/**
	 * @return void
	 */
	function admin_view() {
		$errors                  = array();
		$message                 = null;
		$crossSellItemIdKey      = self::$SLUG . '-crossSellItemId';
		$crossSellProductIdsKey  = self::$SLUG . '-crossSellProductIds';
		$crossSellTitleKey       = self::$SLUG . '-crossSellTitle';
		$crossSellDescriptionKey = self::$SLUG . '-crossSellDescription';

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			if ( isset( $_POST[ $crossSellItemIdKey ] ) && is_numeric( $_POST[ $crossSellItemIdKey ] ) ) {
			} else {
				$errors[] = __( 'Please select a valid cross sell item', 'cross-upsell-popup-for-woocommerce' );
			}
			if ( isset( $_POST[ $crossSellProductIdsKey ] ) && is_array( $_POST[ $crossSellProductIdsKey ] ) ) {
			} else {
				$errors[] = __( 'Please select valid products', 'cross-upsell-popup-for-woocommerce' );
			}

			if ( ! count( $errors ) ) {
				// Do save all the things
				update_option( $crossSellItemIdKey, absint( wp_unslash( $_POST[ $crossSellItemIdKey ] ) ) );
				update_option( $crossSellProductIdsKey, array_map( 'intval', (array) wp_unslash( $_POST[ $crossSellProductIdsKey ] ) ) );
				update_option( $crossSellTitleKey, wc_clean( wp_unslash( $_POST[ $crossSellTitleKey ] ) ) );
				update_option( $crossSellDescriptionKey, wc_clean( wp_unslash( $_POST[ $crossSellDescriptionKey ] ) ) );
				$message = __( 'Popup settings updated successfully', 'cross-upsell-popup-for-woocommerce' );
			}
		}

		$crossSellItemId      = get_option( $crossSellItemIdKey, false );
		$crossSellProductIds  = get_option( $crossSellProductIdsKey, [] );
		$crossSellTitle       = get_option( $crossSellTitleKey, '' );
		$crossSellDescription = get_option( $crossSellDescriptionKey, '' );

		include_once 'views/admin.php';
	}

	/**
	 * @return void
	 */
	function check_woocommerce_is_active() {
		if ( ! class_exists( 'woocommerce' ) ) {
			include_once 'views/wc-not-activated.php';
		}
	}

	/**
	 * @return void
	 */
	function trigger_added_to_cart_event() {
		$params = func_get_args();
		add_action( 'wp_footer', function () use ( $params ) {
			?>
            <script>
                jQuery(function ($) {
                    $(document.body).trigger({
                        type: 'added_to_cart',
                        product_id:<?php echo esc_attr( $params[1] ) ?>,
                        quantity:<?php echo esc_attr( $params[2] ) ?>
                    });
                });
            </script>
			<?php
		} );
	}

	/**
	 * @return void
	 */
	function load_frontend_assets() {
		wp_enqueue_style( self::$SLUG . '-popup-css', self::plugin_url() . '/assets/css/popup.css', array(), '1.0.0' );

		wp_enqueue_style( 'photoswipe' );
		wp_enqueue_style( 'photoswipe-default-skin' );

		wp_enqueue_script( 'zoom' );
		wp_enqueue_script( 'flexslider' );
		wp_enqueue_script( 'photoswipe' );
		wp_enqueue_script( 'photoswipe-ui-default' );
		wp_enqueue_script( 'wc-single-product' );

		wp_enqueue_script( self::$SLUG . '-popup-js', self::plugin_url() . '/assets/js/popup.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( self::$SLUG . '-popup-js', 'Cross_Up_Sell_Popup_For_WC', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	/**
	 * @return void
	 */
	function unset_cookie() {
		setcookie( self::$SLUG . '-disabled', 'no', time() + ( 3600 * 24 ) );
	}

	/**
	 * @return void
	 */
	function close_popup() {
		setcookie( self::$SLUG . '-disabled', 'yes', time() + ( 3600 * 24 ) );
		wp_send_json_success( [ 'success' => true ] );
		wp_die();
	}

	/**
	 * @return void
	 */
	function validate_cart_to_popup() {
		$crossSellItemIdKey     = self::$SLUG . '-crossSellItemId';
		$crossSellProductIdsKey = self::$SLUG . '-crossSellProductIds';
		$popupDisabled          = 'no';
		if ( isset( $_COOKIE[ self::$SLUG . '-disabled' ] ) ) {
			$popupDisabled = $_COOKIE[ self::$SLUG . '-disabled' ] === 'yes' ? 'yes' : 'no';
		}
		$cart = WC()->cart;

		if ( $cart && $popupDisabled === 'no' ) {
			$items               = $cart->get_cart_contents();
			$crossSellItemId     = get_option( $crossSellItemIdKey, false );
			$crossSellProductIds = get_option( $crossSellProductIdsKey, [] );
			$ids                 = [];
			foreach ( $items as $key => $item ) {
				if ( ! in_array( $item['product_id'], $ids ) ) {
					$ids[] = $item['product_id'];
				}
				if ( $item['variation_id'] && ! in_array( $item['variation_id'], $ids ) ) {
					$ids[] = $item['variation_id'];
				}
			}
			if ( ! in_array( $crossSellItemId, $ids ) ) {
				$requiredIdsByNotInCart = array_diff( $crossSellProductIds, $ids );
				if ( ! count( $requiredIdsByNotInCart ) ) {
					// All The Required Products Are Into The Cart
					wp_send_json_success( [ 'popup' => true ] );
					wp_die();
				}
			}
		}
		wp_send_json_success( [ 'popup' => false ] );
		wp_die();
	}

	/**
	 * @return void
	 */
	function popup_modal() {
		$crossSellItemIdKey      = self::$SLUG . '-crossSellItemId';
		$crossSellTitleKey       = self::$SLUG . '-crossSellTitle';
		$crossSellDescriptionKey = self::$SLUG . '-crossSellDescription';

		$crossSellItemId      = get_option( $crossSellItemIdKey, false );
		$crossSellTitle       = get_option( $crossSellTitleKey, '' );
		$crossSellDescription = get_option( $crossSellDescriptionKey, '' );

		$crossSellItem = wc_get_product( $crossSellItemId );
		if ( is_object( $crossSellItem ) ) {
			include_once 'views/theme.php';
		}
		wc_get_template( 'single-product/photoswipe.php' );
	}

	/**
	 * @return string
	 */
	static function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * @return Cross_Up_Sell_Popup_For_WC
	 */
	static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

add_action( 'plugin_loaded', function () {
	Cross_Up_Sell_Popup_For_WC::getInstance();
} );
