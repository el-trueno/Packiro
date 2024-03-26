const { Application } = Shopware;

Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addCondition('parentCartPositionPrice', {
        component: 'sw-condition-cart-position-price',
        label: 'Parent Cart Total',
        scopes: ['cart'],
        group: 'cart'
    });

    return ruleConditionService;
});
