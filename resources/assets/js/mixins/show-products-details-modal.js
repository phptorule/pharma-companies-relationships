const ProductsModal = {

    methods: {
        showProductsDetailsModal: function (addressId, purchaseId) {
            let data = {
                addressId: addressId,
                purchaseId: purchaseId,
                // address: addressData
            };

            this.$eventGlobal.$emit('showModalProductsDetails', data);
        }
    }

};

export default ProductsModal;