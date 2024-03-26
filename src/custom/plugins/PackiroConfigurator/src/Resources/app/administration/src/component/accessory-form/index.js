import template from './index.html.twig';

const {Component} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('pc-accessory-form', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        item: {
            type: Object,
            required: true,
        },
        enumTypes: {
            type: Array,
            required: false,
            default: ['product','pseudo-product','texture','artwork','delivery']
        }
    },

    data() {
        return {
            mediaModalIsOpen: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', [
            'name'
        ]),
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
        typeOptions() {
            const storeOptions = [];
            this.enumTypes.forEach(function (value) {
                storeOptions.push({
                    value: `${value}`,
                    label: `${value}`
                });
            });
            return storeOptions;
        }
    },

    methods: {
        setMediaItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.mediaId = targetId;
                this.item.media = updatedMedia;
            });
        },
        onDropMedia(dragData) {
            this.setMediaItem({targetId: dragData.id});
        },
        setMediaFromSidebar(mediaEntity) {
            this.item.mediaId = mediaEntity.id;
        },
        onUnlinkMedia() {
            this.item.mediaId = null;
        },
        onCloseModal() {
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            this.item.mediaId = mediaEntity[0].id;
            this.item.media = mediaEntity[0];
        },
        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        }
    }
});
