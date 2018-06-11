const addressHelpers = {
    methods: {
        unifyAddressesWithDuplicatedNames: function (addressList) {
            let addressIdsWithSameNames = [];

            let duplications = {};

            addressList.forEach(address => {
                addressList.forEach(add => {
                    if(address.name === add.name) {
                        if(!duplications.hasOwnProperty(address.id)){
                            duplications[address.id] = 0;
                        }

                        ++duplications[address.id];
                    }
                });
            });

            for(let id in duplications) {
                if(duplications[id] > 1 && addressIdsWithSameNames.indexOf(id) === -1) {
                    addressIdsWithSameNames.push(id);
                }
            }

            if(!addressIdsWithSameNames.length) {
                return;
            }

            addressList.forEach(address => {
                if(addressIdsWithSameNames.indexOf(''+address.id) !== -1) {
                    let city = address.address.split(',')[1].replace(/[0-9]/g, '').replace(/\s/g, '');
                    address.name += ' ' + city;
                }
            });
        }
    }
};

export default addressHelpers;