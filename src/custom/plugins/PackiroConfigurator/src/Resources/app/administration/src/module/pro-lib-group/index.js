import './component/item-list';

import './page/list';
import './page/detail';
import './page/create';

Shopware.Module.register('pc-pro-lib-group', {
    type: 'plugin',
    name: 'pc-pro-lib-group',
    title: 'pc-pro-lib-group.general.title',
    color: '#aeaeae',
    icon: 'default-object-image',
    entity: 'pc_pro_lib_group',

    routes: {
        list: {
            component: 'pc-pro-lib-group-list',
            path: 'list',
            meta: {
                privilege: 'pc_pro_lib_group:read'
            }
        },
        detail: {
            component: 'pc-pro-lib-group-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'pc.pro.lib.group.list',
                privilege: 'pc_pro_lib_group:read'
            }
        },
        create: {
            component: 'pc-pro-lib-group-create',
            path: 'create',
            meta: {
                parentPath: 'pc.pro.lib.group.list',
                privilege: 'pc_pro_lib_group:read'
            }
        }
    },

    navigation: [{
        label: 'pc-pro-lib-group.general.title',
        color: '#aeaeae',
        icon: 'default-object-image',
        path: 'pc.pro.lib.group.list',
        position: 202,
        parent: 'sw-customer'
    }],

    defaultSearchConfiguration: {
        _searchable: true,
        name: {
            _searchable: true,
            _score: 500,
        }
    }
});

const SearchTypeService = Shopware.Service('searchTypeService');

SearchTypeService.upsertType('pc_pro_lib_group', {
    entityName: 'pc_pro_lib_group',
    placeholderSnippet: 'pc-pro-lib-group.general.placeholderSearchBar',
    listingRoute: 'pc.pro.lib.group.list'
});
