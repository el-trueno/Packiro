import template from './packiro-checkout-split-order-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('packiro-checkout-split-order-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    props: {
        currentOrder: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            orders: null,
            isLoading: true,
            searchConfigEntity: 'order',
        };
    },

    computed: {
        orderRepository() {
            return this.repositoryFactory.create('order');
        },

        columns() {
            return [{
                property: 'orderNumber',
                label: 'sw-order.list.columnOrderNumber',
                routerLink: 'sw.order.detail',
                allowResize: true,
                primary: true,
            }, {
                property: 'amountTotal',
                label: 'sw-order.list.columnAmount',
                align: 'right',
                allowResize: true,
            }, {
                property: 'stateMachineState.name',
                label: 'sw-order.list.columnState',
                allowResize: true,
            }, {
                property: 'transactions.last().stateMachineState.name',
                dataIndex: 'transactions.stateMachineState.name',
                label: 'sw-order.list.columnTransactionState',
                allowResize: true,
            }, {
                property: 'deliveries[0].stateMachineState.name',
                dataIndex: 'deliveries.stateMachineState.name',
                label: 'sw-order.list.columnDeliveryState',
                allowResize: true,
            }];
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addFilter(
                Criteria.equals('splitOrder.checkoutId', this.currentOrder.extensions.splitOrder.checkoutId)
            );

            criteria.addSorting(Criteria.sort('orderNumber', 'ASC'));
            criteria.addAssociation('currency');
            criteria.addAssociation('transactions');
            criteria.addAssociation('deliveries');
            criteria.getAssociation('transactions').addSorting(Criteria.sort('createdAt'));

            return criteria;
        },
    },

    methods: {
        async getList() {
            this.isLoading = true;

            if (!this.entitySearchable || !this.currentOrder.extensions.splitOrder) {
                this.isLoading = false;
                this.total = 0;

                return;
            }

            try {
                const response = await this.orderRepository.search(this.defaultCriteria);

                this.total = response.total;
                this.orders = response;
                this.isLoading = false;
            } catch (error) {
                console.log(error);

                this.isLoading = false;
            }
        },
    }
});
