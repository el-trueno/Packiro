import template from './sw-property-detail-base.html.twig';

const {Component} = Shopware;

Component.override('sw-property-detail-base', {
    template,

    created() {
        if (!this.propertyGroup.customFields) {
            this.propertyGroup.customFields = {
                techicalName: ""
            }
        }
    },
});
