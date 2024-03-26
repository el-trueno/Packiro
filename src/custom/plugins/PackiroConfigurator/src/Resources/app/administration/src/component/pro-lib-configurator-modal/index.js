import template from './index.html.twig';
import './index.scss';

const {Component, Context, State} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('pc-pro-lib-configurator-modal', {
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

        cart: {
            type: Object,
            required: true,
        },

        currency: {
            type: Object,
            required: true,
        },

        isCustomerActive: {
            type: Boolean,
            default: false,
        },

        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    data() {
        return {
            selectedVariantId: null,
            selectedProLibItemId: null,
            salesChannelProduct: null,
            salesChannelConfigurator: null,
            prodLibItem: null,
            payload: null,
            lineItems: [],
        };
    },

    computed: {
        customer() {
            return State.get('swOrder').customer;
        },

        proLibItemRepository() {
            return this.repositoryFactory.create('pc_pro_lib_item');
        },

        searchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('product');
            criteria.addAssociation('accessoryOptions');
            criteria.addFilter(Criteria.equals('proLibGroup.customerId', this.customer.id));
            return criteria;
        },

        searchContext() {
            return {
                ...Context.api,
                inheritance: true
            };
        },

        isCustomSize() {
            let isCustomSize = false;

            this.salesChannelProduct.optionIds.forEach(optionId => {
                this.salesChannelConfigurator.forEach(property => {
                    property.options.forEach(option => {
                        if (option.name !== 'CUSTOM' && option.name !== 'CUS') {
                            return;
                        }
                        if (optionId === option.id) {
                            isCustomSize = true;
                        }
                    });
                });
            });

            return isCustomSize;
        }
    },

    methods: {
        onChange() {
            if (this.selectedProLibItemId) {
                this.proLibItemRepository.get(this.selectedProLibItemId, Shopware.Context.api, this.searchCriteria).then((prodLibItem) => {
                    this.prodLibItem = prodLibItem;
                    this.selectedVariantId = prodLibItem.productId;

                    this.pcStoreApiService.getSalesChannelProduct(
                        this.salesChannelId,
                        this.selectedVariantId
                    ).then(response => {
                        this.salesChannelProduct = response.data.product;
                        this.salesChannelConfigurator = response.data.configurator;
                        this.initPayload();
                    });
                });
            } else {
                this.prodLibItem = null;
                this.selectedVariantId = null;
                this.salesChannelProduct = null;
            }
        },

        initPayload() {
            this.payload = {
                salesChannelId: this.salesChannelId,
                id: this.selectedVariantId,
                referencedId: this.selectedVariantId,
                proLibItemId: this.selectedProLibItemId,
                stackable: true,
                removable: true,
                hasCustomPrice: false,
                customUnitPrice: 1.5,
                type: 'pc_pouch_bundle',
                quantity: 500,
                accessoryGroups: {},
                accessoryOptions: {}
            };

            this.salesChannelProduct.extensions.accessoryGroups.forEach(accessoryGroup => {
                accessoryGroup.accessoryOptions.forEach(accessoryOption => {
                    let selected = accessoryOption.preSelected;
                    if (accessoryOption.activeProductLib) {
                        selected = this.prodLibItem.accessoryOptions.has(accessoryOption.id);
                    }

                    this.payload.accessoryOptions[accessoryOption.id] = {
                        id: accessoryOption.id,
                        selected: selected,
                        quantity: 1,
                        type: accessoryGroup.type
                    };
                });
            });
        },

        /**
         * Uncheck all checkboxes from same group (radio alternative)
         * @param accessoryGroupId
         * @param accessoryOptionId
         */
        updateCheckboxGroup(accessoryGroupId, accessoryOptionId) {
            this.salesChannelProduct.extensions.accessoryGroups.forEach(accessoryGroup => {
                if (accessoryGroupId === accessoryGroup.id) {
                    accessoryGroup.accessoryOptions.forEach(accessoryOption => {
                        this.payload.accessoryOptions[accessoryOption.id].selected = (accessoryOptionId === accessoryOption.id);
                    });
                }
            });

            this.$forceUpdate();
            this.updatePayload();
        },

        updatePayload() {
            const item = JSON.parse(JSON.stringify(this.payload));

            Object.keys(item.accessoryOptions).forEach(function(x){
                if (!item.accessoryOptions[x].selected) {
                    delete item.accessoryOptions[x];
                }
            });

            return {
                items: [
                    item
                ]
            };
        },

        onClickAddToCart() {
            this.pcStoreApiService.saveLineItemByPayload(
                this.salesChannelId,
                this.cart.token,
                this.updatePayload()
            ).then(response => {
                State.commit('swOrder/setCartLineItems', response.data.lineItems);
            });

            this.$emit('on-close');
        },

        onClose() {
            this.$emit('on-close');
        }
    }
});
