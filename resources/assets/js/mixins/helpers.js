const helpers = {

    methods: {
        getUniqueArrayElements: function (arr) {
            return arr.filter((value, index, self) => {
                return self.indexOf(value) === index;
            });
        }
    }

};

export default helpers;