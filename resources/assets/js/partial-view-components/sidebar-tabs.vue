<template>
    <div class="nav-tabs-custom sidebar-tabs">
        <ul class="nav nav-tabs">
            <li :class="{active: activeTab === 'dashboard' }">

                <span v-show="countAddresses !== null" class="global-search-result-counter">
                    {{countAddresses}}
                </span>

                <router-link :to="iterationsString ? '/dashboard' + iterationsString : '/dashboard'"><i class="fa fa-building"></i> Organisations</router-link>
            </li>
            <li :class="{active: activeTab === 'people-dashboard' }">

                <span v-show="countPeople !== null" class="global-search-result-counter">
                    {{countPeople}}
                </span>

                <router-link :to="iterationsString ? '/people-dashboard' + iterationsString : '/people-dashboard'"><i class="fa fa-group"></i> People <sup>&beta;</sup></router-link>
            </li>
        </ul>
    </div>
</template>

<script>

    import GlobalSearch from '../services/global-search';

    export default {

        data: function() {
            return {
                countAddresses: null,
                countPeople: null,
                globalSearch: GlobalSearch,
                addressIdsToString: '',
                peopleIdsToString: '',
                iterationsString: ''
            }
        },

        computed: {
            activeTab: function () {
                return this.$route.path.replace('/', '')
            },
        },

        methods: {
            fillCounterProps: function (counterData) {
                this.countAddresses = counterData.count_addresses;
                this.countPeople = counterData.count_people;
                this.addressIdsToString = counterData.address_ids.toString();
                this.peopleIdsToString = counterData.people_ids.toString();

                this.iterationsString = GlobalSearch.iterationsString.replace('&', '?');
            }
        },

        mounted: function () {

            this.$eventGlobal.$on('notifyGlobalSearchCountResults', data =>{
                this.fillCounterProps(data);
            });

            this.fillCounterProps(GlobalSearch.resultCounter);

        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('notifyGlobalSearchCountResults');
        }
    }
</script>

<style scoped>

</style>