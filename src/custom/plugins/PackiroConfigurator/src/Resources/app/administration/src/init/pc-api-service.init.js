const {Application} = Shopware;

import PcStoreApiService from '../core/service/api/pc-store-api.api.service';

Application.addServiceProvider('pcStoreApiService', (container) => {
    const initContainer = Application.getContainer('init');

    return new PcStoreApiService(initContainer.httpClient, container.loginService);
});
