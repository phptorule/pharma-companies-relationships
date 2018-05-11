var getProductName = {

    methods: {
        getProductName: function (productName) {

            if(!productName) {
                return '';
            }

            var name = '';

            let arr = productName.split(' ');

            if(arr.length) {
                name += arr[0].charAt(0).toUpperCase();

                if(arr[1]){
                    name += ' ' + arr[1].charAt(0).toUpperCase();
                }
            }

            return name;

        }
    }

};


export default getProductName;