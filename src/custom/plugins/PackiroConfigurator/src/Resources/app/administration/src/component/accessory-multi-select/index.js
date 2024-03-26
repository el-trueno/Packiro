import template from './index.html.twig';
import './index.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('pc-accessory-multi-select', {
    template,

    props: ['value','label'],

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
    },

    computed: {
        searchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('accessoryGroup');
            return criteria;
        },

        searchContext() {
            return {
                ...Context.api,
                inheritance: true
            };
        }
    },
});
