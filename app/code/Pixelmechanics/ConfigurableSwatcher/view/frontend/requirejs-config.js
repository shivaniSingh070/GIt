var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Pixelmechanics_ConfigurableSwatcher/js/model/skuswitch': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Pixelmechanics_ConfigurableSwatcher/js/model/swatch-skuswitch': true
            }
        }
	}
};