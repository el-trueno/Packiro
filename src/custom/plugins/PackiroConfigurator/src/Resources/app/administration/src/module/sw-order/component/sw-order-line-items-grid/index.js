import template from './sw-order-line-items-grid.html.twig';
const {Component} = Shopware;

Component.override('sw-order-line-items-grid', {
    template,
    methods: {
        isProductItem(item) {
            if (this.$super('isProductItem', item)) {
                return true;
            }

            return this.isPouchBundle(item);
        },

        isPouchBundle(item) {
            return ['pc_pouch_bundle'].includes(item.type);
        },

        getProductItem(item) {
            if (this.$super('isProductItem', item)) {
                return item;
            }

            const productItem = item.children.find(element => this.$super('isProductItem', element));
            if (productItem !== undefined) {
                return productItem;
            }

            return item;
        },
    }
});
