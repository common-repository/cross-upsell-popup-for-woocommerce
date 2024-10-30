<div class="relative z-[1]">
    <div class="ywcn-fixed ywcn-inset-0 ywcn-overflow-y-auto ywcn-modal-box">
        <div class="product-modal-overlay"></div>
        <div class="ywcn-flex ywcn-min-h-full ywcn-items-center ywcn-justify-center ywcn-p-4 ywcn-text-center">
            <div class="product-modal-body  ">
                <div class=" ywcn-bg-black ywcn-text-center ywcn-py-4 ywcn-px-6 ywcn-text-white ywcn-rounded-t-md">
                    <?php if ( $crossSellTitle ): ?>
                    <h2 class="ywcn-text-3xl ywcn-font-bold ywcn-mb-2 ywcn-text-white"><?php echo esc_html( $crossSellTitle ); ?></h2>
                    <?php endif;?>
                    <?php if ( $crossSellDescription ): ?>
                    <p class=" ywcn-text-sm"><?php echo esc_html( $crossSellDescription ); ?></p>
                    <?php endif;?>
                    <span class="ywcn-h-7 ywcn-w-7 ywcn-bg-white ywcn-text-black ywcn-text-xl ywcn-rounded-full ywcn-inline-flex closemodal
                        ywcn-justify-center ywcn-items-center ywcn-cursor-pointer ywcn-absolute ywcn--right-[4px] ywcn--top-[4px] ywcn-z-[9999]">
                      X
                    </span>
                </div>
				<?php
$productQuery = new WP_Query( array(
    'post_type'      => 'product',
    'posts_per_page' => 1,
    'post__in'       => array( $crossSellItemId ),
) );
?>
				<?php while ( $productQuery->have_posts() ): ?>
					<?php $productQuery->the_post();?>
                    <div class="ywcn-grid ywcn-grid-cols-12 ywcn-gap-5  ywcn-p-6">
                        <div class="lg:ywcn-col-span-6  ywcn-col-span-12 ywcn_woo_slider">

							<?php do_action( 'woocommerce_before_single_product_summary' );?>
                        </div>
                        <div class="lg:ywcn-col-span-6  ywcn-col-span-12 woo_popup_tools">
							<?php do_action( 'woocommerce_single_product_summary' );?>

                        </div>
                    </div>
				<?php endwhile; // end of the loop. ?>
				<?php wp_reset_postdata();?>
            </div>
        </div>
    </div>
</div>
