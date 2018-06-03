const TenderModal = {

    methods: {
        showTenderDetailsModal: function (addressId) {
            let data = {
                addressId: addressId
            };

            this.$eventGlobal.$emit('showModalTenderDetails', data);
        }
    }

};

export default TenderModal;