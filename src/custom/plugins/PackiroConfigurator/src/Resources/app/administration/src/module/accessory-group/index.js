import './page/list';

import './component/option-list';
import './page/detail';
import './page/create';

Shopware.Module.register('pc-accessory-group', {
    type: 'plugin',
    name: 'pc-accessory-group',
    title: 'pc-accessory-group.general.title',
    color: '#aeaeae',
    icon: 'default-avatar-single',
    entity: 'pc_accessory_group',

    routes: {
        list: {
            component: 'pc-accessory-group-list',
            path: 'list',
            meta: {
                privilege: 'pc_accessory_group:read'
            }
        },
        detail: {
            component: 'pc-accessory-group-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'pc.accessory.group.list',
                privilege: 'pc_accessory_group:read'
            }
        },
        create: {
            component: 'pc-accessory-group-create',
            path: 'create',
            meta: {
                parentPath: 'pc.accessory.group.list',
                privilege: 'pc_accessory_group:read'
            }
        }
    },

    navigation: [{
        label: 'pc-accessory-group.general.title',
        color: '#aeaeae',
        icon: 'default-avatar-single',
        path: 'pc.accessory.group.list',
        position: 202,
        parent: 'sw-catalogue'
    }],

    defaultSearchConfiguration: {
        _searchable: true,
        name: {
            _searchable: true,
            _score: 500,
        },
        technicalName: {
            _searchable: true,
            _score: 500,
        }
    }
});

const SearchTypeService = Shopware.Service('searchTypeService');

SearchTypeService.upsertType('pc_accessory_group', {
    entityName: 'pc_accessory_group',
    placeholderSnippet: 'pc-accessory-group.general.placeholderSearchBar',
    listingRoute: 'pc.accessory.group.list'
});
