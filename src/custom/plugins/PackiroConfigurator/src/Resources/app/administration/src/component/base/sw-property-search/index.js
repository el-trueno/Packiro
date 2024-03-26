import template from './sw-property-search.html.twig';

const {Component} = Shopware;

Component.override('sw-product-variants-configurator-selection', {
    template
});
