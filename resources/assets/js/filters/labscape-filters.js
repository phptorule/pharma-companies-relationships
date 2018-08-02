const labscapeFilters = {

    filters: {

        currency: function (value, currency_type) {
            if (!currency_type) {
                currency_type = '';
            }
            if(value){
                value = String(value);
                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' ' + currency_type;
            }
            return '';
        },

        tendername: function (name, size) {

            if (name.length > size) {

                return name.slice(0, size) + ' ...';

            } else {

                return name;

            }
        },

        capitalize: function (value) {
            if (!value) {
                return '';
            }
            value = value.toString();
            return value.charAt(0).toUpperCase() + value.slice(1)
        }

    }
};

export default labscapeFilters;