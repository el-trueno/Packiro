import template from './sw-property-option-detail.html.twig';

Shopware.Component.override('sw-property-option-detail', {
    template,

    created() {
        if (!this.currentOption.customFields) {
            this.currentOption.customFields = {
                techicalName: ""
            }
        }
    }
});
