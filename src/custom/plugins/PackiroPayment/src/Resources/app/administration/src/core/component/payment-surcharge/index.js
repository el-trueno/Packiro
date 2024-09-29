import template from './payment-surcharge.html.twig';

Shopware.Component.extend('payment-surcharge', 'sw-condition-base', {
    template,

    computed: {
        selectValues() {
            return [
                {
                    label: this.$tc('global.sw-condition.condition.yes'),
                    value: true
                },
                {
                    label: this.$tc('global.sw-condition.condition.no'),
                    value: false
                }
            ];
        },

        isPaymentCommissionExists: {
            get() {
                this.ensureValueExist();

                if (this.condition.value.isPaymentCommissionExists == null) {
                    this.condition.value.isPaymentCommissionExists = false;
                }

                return this.condition.value.isPaymentCommissionExists;
            },
            set(isPaymentCommissionExists) {
                this.ensureValueExist();
                this.condition.value = { ...this.condition.value, isPaymentCommissionExists };
            }
        }
    }
});