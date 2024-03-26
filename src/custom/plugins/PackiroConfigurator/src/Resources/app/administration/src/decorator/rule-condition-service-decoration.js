const { Application } = Shopware;

import '../component/customer-has-vat-id-rule';

Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addCondition('customerHasVatIdRule', {
        component: 'customer-has-vat-id-rule',
        label: 'acrisTax.condition.customerHasVatIdRuleLabel',
        scopes: ['global']
    });

    return ruleConditionService;
});
