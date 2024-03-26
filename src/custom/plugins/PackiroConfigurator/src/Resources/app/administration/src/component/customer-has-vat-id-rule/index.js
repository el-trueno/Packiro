import template from './customer-has-vat-id-rule.html.twig';

const { Component } = Shopware;
const { mapPropertyErrors } = Component.getComponentHelper();

/**
 * @public
 * @description Condition for the ACRIS additional rules. This component must a be child of sw-condition-tree.
 * @status prototype
 * @example-type code-only
 * @component-example
 * <customer-has-vat-id-rule :condition="condition" :level="0"></customer-has-vat-id-rule>
 */
Component.extend('customer-has-vat-id-rule', 'sw-condition-base', {
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

        customerHasVatId: {
            get() {
                this.ensureValueExist();
                return this.condition.value.customerHasVatId;
            },
            set(customerHasVatId) {
                this.ensureValueExist();
                this.condition.value = { ...this.condition.value, customerHasVatId };
            }
        },

        ...mapPropertyErrors('condition', ['value.customerHasVatId']),

        currentError() {
            return this.conditionValueCustomerHasVatIdError;
        }
    }
});
