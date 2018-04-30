const ProductsModal = {

    methods: {
        showProductsDetailsModal: function (addressId, productId, addressData) {
            let data = {
                addressId: addressId,
                productId: productId,
                address: addressData
            };

            this.$eventGlobal.$emit('showModalProductsDetails', data);
        }
    }

};

export default ProductsModal;