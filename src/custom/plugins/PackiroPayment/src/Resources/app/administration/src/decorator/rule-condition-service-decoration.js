import '../../src/core/component/payment-surcharge';

Shopware.Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addCondition('payment_surcharge', {
        component: 'payment-surcharge',
        label: 'If payment commission exists',
        scopes: ['global'],
    });

    return ruleConditionService;
});