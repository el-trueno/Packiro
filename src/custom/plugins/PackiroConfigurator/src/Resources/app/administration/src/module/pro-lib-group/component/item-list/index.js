const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('pc-pro-lib-group-item-list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory'
    ],

    mixins: [
        Mixin.getByName('listing')
    ],

    props: {
        proLibGroupId: {
            type: String,
            required: true,
        }
    },

    data() {
        return {
            items: null,
            selectedItems: null,
            sortBy: 'version',
            sortDirection: 'DESC',
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
            const defaultCriteria = new Criteria(this.page, this.limit);

            defaultCriteria.addFilter(Criteria.equals('proLibGroupId', this.proLibGroupId));

            this.naturalSorting = this.sortBy === 'priority';

            defaultCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                defaultCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            this.filterCriteria.forEach(filter => {
                defaultCriteria.addFilter(filter);
            });

            defaultCriteria.addAssociation('product');

            return defaultCriteria;
        },

        searchContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true
            };
        },

        columns() {
            return [
                {
                    property: 'version',
                    dataIndex: 'version',
                    label: this.$tc('pc-pro-lib-group.properties.version'),
                    align: 'center',
                    routerLink: 'pc.accessory.option.detail'
                },
                {
                    property: 'product.productNumber',
                    dataIndex: 'product.productNumber',
                    label: this.$tc('pc-pro-lib-group.properties.product'),
                    align: 'left'
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

            return this.repository.search(criteria, this.searchContext)
                .then(searchResult => {
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        changeLanguage() {
            this.getList();
        },

        onDuplicate(reference) {
            this.repository.clone(reference.id, Shopware.Context.api, {
                cloneChildren: false,
                overwrites: {
                    locked: false,
                    version: null
                }
            }).then((duplicate) => {
                this.$router.push({name: 'pc.pro.lib.item.detail', params: {id: duplicate.id}});
            });
        },
    }
});
