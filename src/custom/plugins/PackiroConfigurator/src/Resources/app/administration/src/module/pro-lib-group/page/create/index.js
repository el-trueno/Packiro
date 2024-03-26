import template from '../detail/index.html.twig';

Shopware.Component.extend('pc-pro-lib-group-create', 'pc-pro-lib-group-detail', {
    template,

    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
        },

        async onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'pc.pro.lib.group.detail', params: {id: this.item.id}});
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
