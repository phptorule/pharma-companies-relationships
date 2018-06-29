const mapNotified = {
    methods: {
        notifyFiltersHaveBeenApplied: function (queryUrl) {
            this.$eventGlobal.$emit('filtersHaveBeenApplied', queryUrl);
        },
    }
};

export default mapNotified;