const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapState, mapGetters} = Shopware.Component.getComponentHelper();

import template from './index.html.twig';

Component.register('sw-product-detail-pc', {
    template,

    inject: ['repositoryFactory', 'context'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data: {
        accessoryOptionCollection: null
    },

    computed: {
        ...mapState('swProductDetail', [
            'parentProduct',
            'loading',
        ]),

        ...mapGetters('swProductDetail', [
            'productRepository',
            'isLoading',
            'isChild',
            'defaultCurrency'
        ]),

        accessoryOptionRepository() {
            return this.repositoryFactory.create('pc_accessory_option');
        },

        pcProductRepository() {
            return this.repositoryFactory.create('pc_product');
        },

        product() {
            const product = Shopware.State.get('swProductDetail').product;

            if (typeof product.extensions === 'undefined') {
                return product;
            }

            if (!product.extensions.pcProduct) {
                product.extensions.pcProduct = this.pcProductRepository.create(Shopware.Context.api);
            }

            if (!Array.isArray(product.extensions.pcProduct.notCombinable)) {
                product.extensions.pcProduct.notCombinable = [];
            }

            return product;
        },

        pcProductTypes() {
            return [
                {
                    label: 'pc_pouch_bundle',
                    value: 'pc_pouch_bundle',
                },
                {
                    label: 'pc_other_bundle',
                    value: 'pc_other_bundle',
                }
            ];
        },

        accessoryCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('accessoryGroup');
            return criteria;
        }
    },

    created() {
    },

    methods: {
        addNotCombinable() {
            this.product.extensions.pcProduct.notCombinable.push(null);
        },

        removeNotCombinable() {
            this.product.extensions.pcProduct.notCombinable.pop();
        },

        saveProduct() {
            if (this.product) {
                this.productRepository.save(this.product, Shopware.Context.api);
            }
        }
    }
});
