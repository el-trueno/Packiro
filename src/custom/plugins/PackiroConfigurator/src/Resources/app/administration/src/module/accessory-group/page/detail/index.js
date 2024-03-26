import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('pc-accessory-group-detail', {
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
            processSuccess: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', ['name']),

        repository() {
            return this.repositoryFactory.create('pc_accessory_group');
        },

        accessoryOptionRepository() {
            return this.repositoryFactory.create('pc_accessory_option');
        },

        defaultCriteria() {
            return (new Criteria())
                .addAssociation('tags');
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

        onClickNewOption() {
            this.isLoading = true;

            const accessoryOption = this.accessoryOptionRepository.create(Shopware.Context.api);
            accessoryOption.accessoryGroupId = this.item.id;
            accessoryOption.type = this.item.type;

            this.accessoryOptionRepository
                .save(accessoryOption, Shopware.Context.api)
                .then((response) => {
                    this.isLoading = false;
                    this.$router.push({name: 'pc.accessory.option.detail', params: {id: accessoryOption.id}});
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
