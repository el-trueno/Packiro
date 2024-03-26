const {Component} = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.override('sw-settings-country-detail', {
    methods: {
        loadEntityData() {
            /* There is no computed criteria method */
            const criteria = new Criteria();
            criteria.addAssociation('taxFreeRules');

            this.isLoading = true;
            return this.countryRepository.get(this.countryId, Shopware.Context.api, criteria).then(country => {
                this.country = country;

                this.isLoading = false;

                this.countryStateRepository = this.repositoryFactory.create(
                    this.country.states.entity,
                    this.country.states.source,
                );
            });
        }
    }
});
