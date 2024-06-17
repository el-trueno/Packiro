import template from './sw-settings-payment-detail.html.twig';

Shopware.Component.override('sw-settings-payment-detail', {
    template,
    inject: ['repositoryFactory'],
    computed: {
        commission: {
            get() {
                return this.paymentMethod.extensions?.paymentMethodExtension?.commission ?? null;
            },
            set(value) {
                if (!this.paymentMethod.extensions.paymentMethodExtension) {
                    this.$set(this.paymentMethod?.extensions, 'paymentMethodExtension', this.repositoryFactory.create('pc_payment_method').create());
                }
                this.$set(this.paymentMethod.extensions.paymentMethodExtension, 'commission', value*1);
            },
        },
    }
});

