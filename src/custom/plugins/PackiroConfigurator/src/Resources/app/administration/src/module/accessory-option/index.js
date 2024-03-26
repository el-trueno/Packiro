import './extension';
import './page/detail';

Shopware.Module.register('pc-accessory-option', {
    type: 'plugin',
    name: 'pc-accessory-option',
    title: 'pc-accessory-option.general.title',
    color: '#aeaeae',
    icon: 'default-avatar-single',
    entity: 'pc_accessory_option',

    routes: {
        detail: {
            component: 'pc-accessory-option-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'pc.accessory.group.detail',
                privilege: 'pc_accessory_option:read'
            }
        }
    }
});
