define([
    'jquery'
], function ($) {
    return function (viewUrl, blockId, blockSelector, mostviewedProducts, clickUrl) {
        $.ajax({
            url: viewUrl,
            data: {block_id: blockId},
            type: 'GET'
        });

        $(blockSelector + ' .product-item').on('click', function (event) {
            var productId = $(event.currentTarget).find('[data-role="priceBox"]').data('product-id');
            if (!mostviewedProducts || mostviewedProducts.indexOf(productId) !== -1) {
                $.ajax({
                    url: clickUrl,
                    data: {
                        product_id: productId,
                        block_id: blockId
                    },
                    type: 'post'
                });
            }
        });
    }
});
