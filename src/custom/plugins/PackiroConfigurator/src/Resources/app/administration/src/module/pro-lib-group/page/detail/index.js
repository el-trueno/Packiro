import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('pc-pro-lib-group-detail', {
    template,

    inject: [
        'repositoryFactory'
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
            processSuccess: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', ['name']),

        repository() {
            return this.repositoryFactory.create('pc_pro_lib_group');
        },

        proLibItemRepository() {
            return this.repositoryFactory.create('pc_pro_lib_item');
        },

        defaultCriteria() {
            const criteria = new Criteria();
            return criteria;
        },

        lastItemOrderCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('proLibItem');
            criteria.addAssociation('order');
            criteria.addFilter(Criteria.not('OR', [
               Criteria.equals('proLibItem.id', null)
            ]));
            return criteria;
        },

        identifier() {
            return this.placeholder(this.item, 'name');
        }
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

        onClickNewDraft() {
            this.isLoading = true;

            const proLibItem = this.proLibItemRepository.create(Shopware.Context.api);
            proLibItem.proLibGroupId = this.item.id;
            proLibItem.customerId = this.item.customerId;

            this.proLibItemRepository
                .save(proLibItem, Shopware.Context.api)
                .then((response) => {
                    this.isLoading = false;
                    this.$router.push({name: 'pc.pro.lib.item.detail', params: {id: proLibItem.id}});
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
        }
    }
});
