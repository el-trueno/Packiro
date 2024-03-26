Shopware.Component.override('sw-order-create', {
    watch: {
        // Prevents opening of remind payment modal window, and directly redirects to order detail page
        showRemindPaymentModal(newValue, oldValue) {
            if (newValue) {
                this.showRemindPaymentModal = false;
                this.isSaveSuccessful = true;
            }
        }
    }
});
