import template from './sw-order-line-items-grid-sales-channel.html.twig';
const {Component} = Shopware;

Component.override('sw-order-line-items-grid-sales-channel', {
    template,
    data() {
        return {
            showProductConfiguratorModal: false,
            showProLibConfiguratorModal: false,
            showProLibOrderItemConfiguratorModal: false,
            currentCartLineItem: null,
        };
    },
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

        proLibItemOrders(item) {
            return item?.extensions?.proLibItemOrders;
        },

        customSize(item) {
            if (item?.payload?.sizeHeight) {
                return `H${item.payload.sizeHeight}W${item.payload.sizeWidth}D${item.payload.sizeDepth}`;
            } else {
                return false;
            }
        },

        openProLibOrderItemConfiguratorModal(item) {
            this.$set(this, 'currentCartLineItem', item);
            //this.currentCartLineItem = item;
            this.showProLibOrderItemConfiguratorModal = true;
        },

        closeProLibOrderItemConfiguratorModal() {
            this.showProLibOrderItemConfiguratorModal = false;
            this.$set(this, 'currentCartLineItem', null);
            //this.currentCartLineItem = null;
        }
    }
});
