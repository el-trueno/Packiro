const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('pc-pro-lib-group-list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory',
        'feature',
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            items: null,
            selectedItems: null,
            sortBy: 'name',
            sortDirection: 'DESC',
            filterCriteria: [],
            naturalSorting: false,
            isLoading: true,
            storeKey: 'grid.filter.pc_pro_lib_group',
            activeFilterNumber: 0,
            searchConfigEntity: 'pc_pro_lib_group',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('pc_pro_lib_group');
        },

        defaultCriteria() {
            const defaultCriteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'name';

            defaultCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                defaultCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            defaultCriteria.addAssociation('customer');

            this.filterCriteria.forEach(filter => {
                defaultCriteria.addFilter(filter);
            });

            return defaultCriteria;
        },

        columns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: 'pc-pro-lib-group.properties.name',
                    routerLink: 'pc.pro.lib.group.detail',
                },
                {
                    property: 'versionCount',
                    dataIndex: 'versionCount',
                    label: 'pc-pro-lib-group.properties.versionCount',
                    routerLink: 'pc.pro.lib.group.detail',
                },
                {
                    property: 'customer.customerNumber',
                    dataIndex: 'customer.customerNumber',
                    label: 'pc-pro-lib-group.properties.customerNumber'
                },
                {
                    property: 'customer.email',
                    dataIndex: 'customer.email',
                    label: 'pc-pro-lib-group.properties.email'
                }
            ]
        }
    },

    methods: {
        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.defaultCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.repository.search(criteria)
                .then(searchResult => {
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        changeLanguage() {
            this.getList();
        }
    }
});
