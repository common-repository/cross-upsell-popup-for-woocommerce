<div class="wrap">
	<?php if ( count( $errors ) ): ?>
        <div class="notice notice-error is-dismissible">
			<?php foreach ( $errors as $error ): ?>
                <p><?php echo esc_html( $error ); ?></p>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>
	<?php if ( $message ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
	<?php endif; ?>
    <form method="post">
        <div class="woocommerce_options_panel">
            <div class="options_group">
                <h2><?php _e( 'General Settings', 'cross-upsell-popup-for-woocommerce' ); ?></h2>
                <p class="form-field">
                    <label for="<?php echo esc_attr( $crossSellItemIdKey ); ?>"><?php esc_html_e( 'Choose cross sell item', 'cross-upsell-popup-for-woocommerce' ); ?></label>
                    <select class="wc-product-search" id="<?php echo esc_attr( $crossSellItemIdKey ); ?>"
                            name="<?php echo esc_attr( $crossSellItemIdKey ); ?>"
                            style="width: 50%;"
                            data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                            data-action="woocommerce_json_search_products_and_variations">
						<?php
						$crossSellItem = wc_get_product( $crossSellItemId );
						if ( is_object( $crossSellItem ) ) {
							echo '<option value="' . esc_attr( $crossSellItemId ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $crossSellItem->get_formatted_name() ) ) . '</option>';
						}
						?>
                    </select> <?php echo wc_help_tip( __( 'Through this option you will choose the product which will be offering as cross sell.', 'cross-upsell-popup-for-woocommerce' ) ); // WPCS: XSS ok. ?>
                </p>
                <p class="form-field">
                    <label for="<?php echo esc_attr( $crossSellProductIdsKey ); ?>"><?php esc_html_e( 'Choose products', 'cross-upsell-popup-for-woocommerce' ); ?></label>
                    <select class="wc-product-search" multiple="multiple"
                            id="<?php echo esc_attr( $crossSellProductIdsKey ); ?>"
                            name="<?php echo esc_attr( $crossSellProductIdsKey ); ?>[]"
                            style="width: 50%;"
                            data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                            data-action="woocommerce_json_search_products_and_variations">
						<?php
						foreach ( $crossSellProductIds as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
							}
						}
						?>
                    </select> <?php echo wc_help_tip( __( 'Through this option you will be able to choose multiple products and while all of the selected product will be on cart the popup will be prompt on screen', 'cross-upsell-popup-for-woocommerce' ) ); // WPCS: XSS ok. ?>
                </p>
                <p class="form-field">
                    <label for="<?php echo esc_attr( $crossSellTitleKey ); ?>">
						<?php esc_html_e( 'Popup Title', 'cross-upsell-popup-for-woocommerce' ); ?>
                    </label>
                    <input type="text" class="short" name="<?php echo esc_attr( $crossSellTitleKey ); ?>"
                           id="<?php echo esc_attr( $crossSellTitleKey ); ?>"
                           value="<?php echo esc_attr( $crossSellTitle ); ?>"
                           placeholder="<?php esc_attr_e( 'You can choose this product&hellip;', 'cross-upsell-popup-for-woocommerce' ); ?>">
                </p>
                <p class="form-field">
                    <label for="<?php echo esc_attr( $crossSellDescriptionKey ); ?>">
						<?php esc_html_e( 'Popup Description', 'cross-upsell-popup-for-woocommerce' ); ?>
                    </label>
                    <textarea class="short" name="<?php echo esc_attr( $crossSellDescriptionKey ); ?>"
                              id="<?php echo esc_attr( $crossSellDescriptionKey ); ?>"><?php echo esc_textarea( $crossSellDescription ); ?></textarea>
                </p>
            </div>
			<?php submit_button(); ?>
        </div>
    </form>
</div>
