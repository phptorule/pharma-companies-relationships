const employeeModal = {

    methods: {
        showEmployeeDetailsModal: function (personId, addressId, address) {

            let data = {
                personId: personId,
                addressId: addressId,
                address: address
            };

            this.$eventGlobal.$emit('showModalEmployeeDetails', data);
        },

        showModalIfPersonHashDetected: function (addressId, addressData) {
            if(this.$route.hash.indexOf('#person-') !== -1) {
                let personId = this.$route.hash.replace('#person-', '');
                this.showEmployeeDetailsModal(personId, addressId, addressData);
            }
        },
    }

};

export default employeeModal;