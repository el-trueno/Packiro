import template from './index.html.twig';
import './index.scss';

const {Component, Context, State} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('pc-pro-lib-item-order-configurator-modal', {
    template,

    inject: [
        'pcStoreApiService',
        'cartStoreService',
        'repositoryFactory'
    ],

    props: {
        salesChannelId: {
            type: String,
            required: true,
            default: '',
        },

        cartLineItem: {
            type: Object,
            required: true,
        },

        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        customer() {
            return State.get('swOrder').customer;
        },

        subtitle() {
            if (this.cartLineItem) {
                return `${this.cartLineItem.payload?.quantity}x ${this.cartLineItem.label}`;
            }
        }
    },

    methods: {
        onClose() {
            this.$emit('on-close');
        }
    }
});
