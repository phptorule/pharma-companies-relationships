const helpers = {

    methods: {
        getUniqueArrayElements: function (arr) {
            return arr.filter((value, index, self) => {
                return self.indexOf(value) === index;
            });
        },

        scrollToElement: function(element, timeout = 2000, container = 'html, body') {
            setTimeout(()=>{
                $(container).animate({
                    scrollTop: $(element).offset().top
                }, 1000);
            },timeout);
        },

        showLocalLoader: function () {
            $('.loader-spinner').removeClass('hidden');
        },

        hideLocalLoader: function () {
            $('.loader-spinner').addClass('hidden');
        },
    }

};

export default helpers;