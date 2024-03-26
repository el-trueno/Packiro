import './extension';
import './page/detail';

Shopware.Module.register('pc-pro-lib-item', {
    type: 'plugin',
    name: 'pc-pro-lib-item',
    title: 'pc-pro-lib-item.general.title',
    color: '#aeaeae',
    icon: 'default-avatar-single',
    entity: 'pc_pro_lib_item',

    routes: {
        detail: {
            component: 'pc-pro-lib-item-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'pc.pro.lib.group.detail',
                privilege: 'pc_pro_lib_item:read'
            }
        }
    }
});
