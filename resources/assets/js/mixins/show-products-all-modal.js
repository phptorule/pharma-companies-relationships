const productsModal = {

    methods: {
        showProductsDetailsModal: function (addressId, purchaseId, addressData) {
            let data = {
                addressId: addressId,
                purchaseId: purchaseId,
                address: addressData
            };

            this.$eventGlobal.$emit('showModalProductsDetails', data);
        }
    }

};

export default productsModal;