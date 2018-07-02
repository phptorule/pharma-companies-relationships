const mapHoveringNotified = {

    data: function() {
        return {
            mouseOverTimeoutId: null,
            hoveredAddressesStr: ''
        }
    },

    methods: {
        setAddressMouseOverListener: function(addresses) {

            if (addresses === null) {
                return;
            }

            if(this.mouseOverTimeoutId) {
                clearTimeout(this.mouseOverTimeoutId);
            }

            this.mouseOverTimeoutId = setTimeout(()=>{
                if(this.hoveredAddressesStr === JSON.stringify(addresses)) {
                    return;
                }

                this.$eventGlobal.$emit('hover-out-from-the-sidebar', {});

                this.hoveredAddressesStr = JSON.stringify(addresses);

                this.$eventGlobal.$emit('hover-over-address-at-the-sidebar', addresses);
            }, 100);

        },

        setAddressMouseLeaveListener: function () {
            this.hoveredAddressesStr = '';

            if(this.mouseOverTimeoutId) {
                clearTimeout(this.mouseOverTimeoutId);
            }

            this.$eventGlobal.$emit('hover-out-from-the-sidebar', {});
        },
    }
};

export default mapHoveringNotified;