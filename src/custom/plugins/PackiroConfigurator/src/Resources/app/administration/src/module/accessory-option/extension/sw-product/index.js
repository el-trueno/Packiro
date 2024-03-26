const {Module} = Shopware;

import './page/sw-product-detail';
import './view/sw-product-detail-pc';

Module.register('sw-product-detail-pc-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'sw.product.detail.pc',
                path: '/sw/product/detail/:id/pc',
                component: 'sw-product-detail-pc',
                meta: {
                    parentPath: "sw.product.index"
                }
            });
        }
        next(currentRoute);
    }
});
