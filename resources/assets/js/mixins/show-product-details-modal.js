const ProductModal = {

    methods: {
        showProductDetailsModal: function (addressId, productId, addressData) {
            let data = {
                addressId: addressId,
                purchaseId: productId,
                 address: addressData
            };

            this.$eventGlobal.$emit('showModalProductDetails', data);
        }
    }

};

export default ProductModal;