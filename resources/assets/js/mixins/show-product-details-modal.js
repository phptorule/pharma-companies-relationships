const ProductModal = {

    methods: {
        showProductDetailsModal: function (addressId, purchaseId, addressData) {
            let data = {
                addressId: addressId,
                purchaseId: purchaseId,
                 address: addressData
            };

            this.$eventGlobal.$emit('showModalProductDetails', data);
        }
    }

};

export default ProductModal;