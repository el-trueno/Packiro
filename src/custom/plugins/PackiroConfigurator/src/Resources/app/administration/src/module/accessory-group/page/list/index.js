const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('pc-accessory-group-list', {
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
            sortBy: 'priority',
            sortDirection: 'DESC',
            filterCriteria: [],
            naturalSorting: false,
            isLoading: true,
            storeKey: 'grid.filter.pc_accessory_group',
            activeFilterNumber: 0,
            searchConfigEntity: 'pc_accessory_group',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('pc_accessory_group');
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
                    label: this.$tc('pc-accessory-group.properties.active'),
                    inlineEdit: 'boolean',
                    align: 'center'
                },
                {
                    property: 'priority',
                    dataIndex: 'priority',
                    label: this.$tc('pc-accessory-group.properties.priority'),
                    inlineEdit: 'number',
                    align: 'center'
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('pc-accessory-group.properties.name'),
                    routerLink: 'pc.accessory.group.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                },
                {
                    property: 'technicalName',
                    dataIndex: 'technicalName',
                    label: this.$tc('pc-accessory-group.properties.technicalName'),
                    routerLink: 'pc.accessory.group.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$tc('pc-accessory-group.properties.type'),
                    routerLink: 'pc.accessory.group.detail',
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
