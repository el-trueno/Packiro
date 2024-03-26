import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('pc-pro-lib-item-detail', {
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
            defaultCurrency: null,
            makeVersion: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', ['name']),

        repository() {
            return this.repositoryFactory.create('pc_pro_lib_item');
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        customerRepository() {
            return this.repositoryFactory.create('customer');
        },

        defaultCriteria() {
            return (new Criteria())
                .addAssociation('proLibGroup')
                .addAssociation('accessoryOptions.accessoryGroup');
        },

        currencyCriteria() {
            return (new Criteria())
                .addSorting(Criteria.sort('name', 'ASC'));
        },

        searchCriteria() {
            return (new Criteria(1, 30))
                .addAssociation('options.group')
                .addFilter(Criteria.equalsAny('parent.pcProduct.type', ['pc_pouch_bundle']));
        },

        searchContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true
            };
        },

        accessoryCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('accessoryGroup');
            criteria.addFilter(Criteria.equals('accessoryGroup.activeProductLib', 1))
            return criteria;
        },

        identifier() {
            return this.placeholder(this.item, 'name');
        },

        nameAndVersion() {
            return `${this.item.proLibGroup.name} | ${this.item.version ? 'v' + this.item.version : 'Draft'}`;
        },
        artworkStatusTypes(y) {
            return [
                {
                    label: 'expert_approved',
                    value: 'expert_approved',
                },
                {
                    label: 'expert_check_requested',
                    value: 'expert_check_requested',
                },
                {
                    label: 'rejected',
                    value: 'rejected',
                },
                {
                    label: 'basic_approved',
                    value: 'basic_approved',
                },
                {
                    label: 'basic_requested',
                    value: 'basic_requested',
                }
            ];
        },
    },



    created() {
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

            this.item.name = this.nameAndVersion;

            if (this.makeVersion) {
                this.item.version = 0;
            }

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
        },
        onChangeStatus(item) {
            if (item.artworkStatus == 'expert_approved' || item.artworkStatus == 'basic_approved') {
                var current = new Date();
                var dateString = current.getUTCFullYear() +"-"+ (current.getUTCMonth()+1) +"-"+ current.getUTCDate() + " " + current.getUTCHours() + ":" + current.getUTCMinutes() + ":" + current.getUTCSeconds();
                item.expertCheckApproved = dateString;
            } else {
                item.expertCheckApproved = '';
            }
        },
    }
});
