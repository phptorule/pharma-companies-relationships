const ProductsModal = {

    methods: {
        showProductsDetailsModal: function () {


            this.$eventGlobal.$emit('showModalProductsDetails');
        }
    }

};

export default ProductsModal;