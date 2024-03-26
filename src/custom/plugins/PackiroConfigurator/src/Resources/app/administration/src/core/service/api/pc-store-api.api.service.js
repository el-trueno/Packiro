import { deepCopyObject } from 'src/core/service/utils/object.utils';
import utils from 'src/core/service/util.service';
const ApiService = Shopware.Classes.ApiService;

class PcStoreService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = '') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'cartStoreService';
    }

    getSalesChannelProduct(salesChannelId, productId, additionalParams = {}, additionalHeaders = {}) {
        const route = `_proxy/store-api/${salesChannelId}/product/${productId}`;
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient.post(route, {}, { additionalParams, headers });
    }

    saveLineItemByPayload(
        salesChannelId,
        contextToken,
        payload,
        additionalParams = {},
        additionalHeaders = {},
    ) {
        const route = `_proxy/store-api/${salesChannelId}/checkout/cart/line-item`;
        const headers = {
            ...this.getBasicHeaders(additionalHeaders),
            'sw-context-token': contextToken,
        };
        return this.httpClient.post(route, payload, { additionalParams, headers });
    }
}

export default PcStoreService;
