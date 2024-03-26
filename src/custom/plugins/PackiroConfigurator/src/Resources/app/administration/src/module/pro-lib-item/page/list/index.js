const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('pc-pro-lib-item-list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    data() {
        return {
            items: null,
            selectedItems: null,
            sortBy: 'name',
            sortDirection: 'ASC',
            filterCriteria: [],
            naturalSorting: false,
            isLoading: true,
            storeKey: 'grid.filter.pc_pro_lib_item',
            activeFilterNumber: 0,
            searchConfigEntity: 'pc_pro_lib_item',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('pc_pro_lib_item');
        },

        defaultCriteria() {
            const defaultCriteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'priority';

            defaultCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                defaultCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            defaultCriteria.addAssociation('media');

            this.filterCriteria.forEach(filter => {
                defaultCriteria.addFilter(filter);
            });

            return defaultCriteria;
        },

        columns() {
            return [
                {
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$tc('pc-pro-lib-item.properties.active'),
                    inlineEdit: 'boolean',
                    align: 'center'
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('pc-pro-lib-item.properties.name'),
                    routerLink: 'pc.pro.lib.item.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                },
                {
                    property: 'technicalName',
                    dataIndex: 'technicalName',
                    label: this.$tc('pc-pro-lib-item.properties.technicalName'),
                    routerLink: 'pc.pro.lib.item.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$tc('pc-pro-lib-item.properties.type'),
                    routerLink: 'pc.pro.lib.item.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                },
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