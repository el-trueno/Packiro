import template from './index.html.twig';
import './index.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const format = Shopware.Utils.format;

Component.register('pc-pro-lib-item-order', {
    template,

    inject: [
        'repositoryFactory',
        'systemConfigApiService'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    props: {
        cartLineItemId: {
            type: String,
            required: false,
            default: '',
        },
        orderId: {
            type: String,
            required: false,
            default: '',
        },
        orderLineItemId: {
            type: String,
            required: false,
            default: '',
        },
        proLibItemId: {
            type: String,
            required: false,
            default: '',
        },
        productId: {
            type: String,
            required: false,
            default: '',
        },
        customerId: {
            type: String,
            required: false,
            default: '',
        },
        updatedDeliveryDate: {
            type: String,
            required: false,
            default: ''
        }
    },

    data() {
        return {
            items: null,
            selectedItems: null,
            sortBy: 'orderLineItemId',
            sortDirection: 'ASC',
            filterCriteria: [],
            naturalSorting: false,
            isLoading: true,
            storeKey: 'grid.filter.pc_pro_lib_item_order',
            activeFilterNumber: 0,
            searchConfigEntity: 'pc_pro_lib_item_order',
            uDashUrl: null
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('pc_pro_lib_item_order');
        },

        columns() {
            const columns = [
                {
                    property: 'quantity',
                    dataIndex: 'quantity',
                    label: this.$tc('pc-pro-lib-item.properties.quantity'),
                    inlineEdit: 'number',
                    align: 'center'
                },
                {
                    property: 'proLibItem.proLibGroup.name',
                    dataIndex: 'proLibItem.proLibGroup.name',
                    label: this.$tc('pc-pro-lib-item.properties.name'),
                    inlineEdit: 'string',
                },
                {
                    property: 'product.productNumber',
                    dataIndex: 'product.productNumber',
                    label: this.$tc('pc-pro-lib-item.properties.product')
                },
            ];

            if (this.orderId) {
                columns.push({
                    property: 'order.orderNumber',
                    dataIndex: 'order.orderNumber',
                    label: this.$tc('pc-pro-lib-item.properties.orderNumber'),
                });
            }

            columns.push({
                property: 'updatedDeliveryDate',
                dataIndex: 'updatedDeliveryDate',
                label: this.$tc('pc-pro-lib-item-order.properties.updatedDeliveryDate')
            });

            return columns;
        },

        defaultCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('order');
            criteria.addAssociation('product');
            criteria.addAssociation('orderLineItem');
            criteria.addAssociation('proLibItem.proLibGroup');
            if (this.cartLineItemId) {
                criteria.addFilter(Criteria.equals('cartLineItemId', this.cartLineItemId));
            }
            if (this.orderId) {
                criteria.addFilter(Criteria.equals('orderId', this.orderId));
            }
            if (this.orderLineItemId) {
                criteria.addFilter(Criteria.equals('orderLineItemId', this.orderLineItemId));
            }
            if (this.proLibItemId) {
                criteria.addFilter(Criteria.equals('proLibItemId', this.proLibItemId));
            }
            if (this.productId) {
                criteria.addFilter(Criteria.equals('productId', this.productId));
            }
            return criteria;
        },

        proLibItemSearchCriteria() {
            const criteria = new Criteria();
            if (this.customerId) {
                criteria.addFilter(Criteria.equals('proLibGroup.customerId', this.customerId));
            }
            if (this.productId) {
                criteria.addFilter(Criteria.equals('productId', this.productId));
            }
            return criteria;
        }
    },

    methods: {
        getUDashUrl(item) {
            if (!this.uDashUrl) {
                return '';
            }

            return this.uDashUrl.replace('{artworkId}', item.proLibItem.artworkId);
        },

        getProLibItemSearchCriteria(productId) {
            const criteria = new Criteria();
            if (this.customerId) {
                criteria.addFilter(Criteria.equals('proLibGroup.customerId', this.customerId));
            }
            if (productId) {
                criteria.addFilter(Criteria.equals('productId', productId));
            }
            return criteria;
        },

        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.defaultCriteria);

            const uDashConfigKey = 'PackiroConfigurator.config.uDashBaseUrl';
            const configValues = await this.systemConfigApiService.getValues('PackiroConfigurator');

            if (configValues[uDashConfigKey] !== undefined) {
                this.uDashUrl = configValues[uDashConfigKey];
            }

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.repository.search(criteria)
                .then(searchResult => {
                    searchResult.map(function (value, key) {
                        if (value.updatedDeliveryDate !== undefined) {
                            searchResult[key].updatedDeliveryDate = format.date(value.updatedDeliveryDate)
                        }
                    })
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },
    }
});
