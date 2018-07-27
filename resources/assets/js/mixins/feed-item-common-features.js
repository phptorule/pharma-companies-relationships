const features = {

    methods: {

        showFeedDetailsPopUp: function (feedId) {
            this.$emit('onShowFeedDetailsPopUp', feedId);
        }

    }

};

export default features;