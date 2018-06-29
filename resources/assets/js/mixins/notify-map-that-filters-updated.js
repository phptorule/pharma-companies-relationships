const mapNotified = {
    methods: {
        notifyFiltersHaveBeenApplied: function (queryUrl) {

            console.log('filtersHaveBeenApplied 1', queryUrl);

            this.$eventGlobal.$emit('filtersHaveBeenApplied', queryUrl);
        },
    }
};

export default mapNotified;