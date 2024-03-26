import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('pc-accessory-option-detail', {
    template,

    inject: [
        'repositoryFactory',
        'cmsService',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            currencies: [],
            defaultCurrency: null
        };
    },

    computed: {
        ...mapPropertyErrors('item', ['name']),

        repository() {
            return this.repositoryFactory.create('pc_accessory_option');
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        defaultCriteria() {
            return (new Criteria())
                .addAssociation('accessoryGroup')
                .addAssociation('mainProducts.options.group')
                .addAssociation('tags')
                .addAssociation('tax');
        },

        currencyCriteria() {
            return (new Criteria())
                .addSorting(Criteria.sort('name', 'ASC'));
        },

        searchCriteria() {
            return (new Criteria(1, 30))
                /*.addFilter(Criteria.equals('parentId', null))*/
                .addAssociation('options.group');
        },

        accessoryCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('accessoryGroup');
            return criteria;
        },

        searchContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true
            };
        },

        identifier() {
            return this.placeholder(this.item, 'name');
        }
    },

    created() {
        this.currencyRepository.search(this.currencyCriteria).then((response) => {
            this.currencies = response;

            this.defaultCurrency = this.currencies.find(currency => currency.isSystemDefault);
        });

        this.getItem();
    },

    methods: {
        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onChangeLanguage() {
            this.getItem();
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    if (exception.response.data && exception.response.data.errors) {
                        exception.response.data.errors.forEach((error) => {
                            this.createNotificationWarning({
                                title: this.$tc('moorl-foundation.notification.errorTitle'),
                                message: error.detail
                            });
                        });
                    }
                });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
