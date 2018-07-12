<template>

        <div class="nav-search">

            <ul class="nav navbar-nav">
                <li>
                    <img src="/images/ic-search.png" alt="">


                    <ul class="search-iteration">
                        <li v-for="(searchItem, i) of searchIterations"
                            :title="searchItem.type + ': ' + searchItem.value"
                        >

                            <i class="fa fa-users" v-if="searchItem.type === 'Person'"></i>
                            <i class="fa fa-globe" v-if="searchItem.type === 'Address'"></i>
                            <i class="fa fa-shopping-bag" v-if="searchItem.type === 'Product'"></i>
                            <i class="fa fa-building" v-if="searchItem.type === 'Organisation'"></i>
                            <i class="fa fa-question-circle" v-if="searchItem.type === 'Any'"></i>

                            : {{searchItem.value}}
                            <sup @click="searchIterations.splice(i, 1)">
                                <i class="fa fa-remove"></i>
                            </sup>
                        </li>
                    </ul>


                    <input
                            v-model="globalSearchInput"
                            @keyup="makePreliminaryGlobalSearch"
                            placeholder="Search by laboratory, people or location"
                            class="global-search-input"
                    >

                    <i v-if="globalSearchInput"
                       @click="resetGlobalSearch()"
                       class="fa fa-remove clear-global-search-input"></i>
                </li>
            </ul>

            <select id="global-search-select2" style="width: 300px"></select>
        </div>

</template>

<script>

    import http from '../mixins/http';
    import helpers from '../mixins/helpers';
    import GlobalSearch from '../services/global-search';

    const OPTIONS = [
        {
            "id": 'Person',
            "text": "Person",
            "html": "<div><i class='fa fa-users'></i> Person</div>"
        },
        {
            "id": 'Product',
            "text": "Product",
            "html": "<div><i class='fa fa-shopping-bag'></i> Product</div>",
        },
        {
            "id": 'Organisation',
            "text": "Organisation",
            "html": "<div><i class='fa fa-building'></i> Organisation</div>",
        },
        {
            "id": 'Address',
            "text": "Address",
            "html": "<div><i class='fa fa-globe'></i> Address</div>"
        },
        {
            "id": 'Any',
            "text": "Any",
            "html": "<div><i class='fa fa-question-circle'></i> Any</div>"
        },
    ];

    export default {

        mixins: [http, helpers],

        data: function() {
            return {
                select2Element: null,
                searchIterations: [],
                globalSearchInput: '',
                options: JSON.parse(JSON.stringify(OPTIONS)),
                firstBackspaceClicked: false
            }
        },

        watch: {

            options: function (options) {

                $(this.select2Element).empty();

                this.initSelect2();
            },

            searchIterations: function (newValue, oldValue) {

                if(newValue.length) {
                    this.performGlobalSearch();
                }
                else{
                    this.notifyGlobalSearchPerformed({
                        count_addresses: null,
                        count_people: null,
                        address_ids: [],
                        people_ids: []
                    });

                    this.$router.push(this.$route.path + this.$route.hash);
                }
            }
        },

        methods: {

            performGlobalSearch: function() {
                return this.httpGet('/api/global-search'+this.addSearchIterationToUrl().replace('&','?'))
                    .then(data => {

                        if(+data.count_addresses === 0){
                            data.address_ids = [-1];
                        }

                        if(+data.count_people === 0){
                            data.people_ids = [-1];
                        }

                        this.notifyGlobalSearchPerformed(data);

                        this.navigateToAppropriatePage(data);

                        return data;
                    })
            },

            navigateToAppropriatePage: function(data) {
                let hash = this.$route.hash;

                let isAnyDashboardActive = this.$route.path === '/dashboard' || this.$route.path === '/people-dashboard';

                if(+data.count_addresses > 0 && +data.count_people > 0 && isAnyDashboardActive) {
                    this.$router.push(this.$route.path + this.addSearchIterationToUrl().replace('&','?') + hash)
                }

                else if(+data.count_addresses > 0 && +data.count_people > 0) {
                    this.$router.push('/dashboard' + this.addSearchIterationToUrl().replace('&','?') + hash)
                }

                else if(+data.count_addresses > 0 && +data.count_people === 0) {
                    this.$router.push('/dashboard' + this.addSearchIterationToUrl().replace('&','?') + hash)
                }

                else if (+data.count_people > 0 && +data.count_addresses === 0) {
                    this.$router.push('/people-dashboard' + this.addSearchIterationToUrl().replace('&','?') + hash)
                }

                else {
                    this.$router.push('/dashboard?address-ids=' + '-1' + hash)
                }
            },

            notifyGlobalSearchPerformed: function(data) {

                GlobalSearch.resultCounter = data;
                GlobalSearch.iterationsString = this.addSearchIterationToUrl();

                this.$eventGlobal.$emit('notifyGlobalSearchCountResults', data);
            },

            makePreliminaryGlobalSearch: function (e) {

                if(e.keyCode === 13) {
                    return this.addSearchIteration();
                }

                if(this.isServiceKeyPressed(e)) {
                    this.checkIfTheSelect2ShouldBeOpened(e);
                    return;
                }

                if(e.keyCode === 8 && this.globalSearchInput === '') {

                    if(this.firstBackspaceClicked) {
                        this.searchIterations.splice(this.searchIterations.length-1, 1);
                        this.firstBackspaceClicked = false;
                    }

                    this.firstBackspaceClicked = true;
                    return this.options = JSON.parse(JSON.stringify(OPTIONS));
                }


                if (this.globalSearchIdCount) {
                    clearTimeout(this.globalSearchIdCount)
                }


                this.globalSearchIdCount = setTimeout(()=>{

                    this.firstBackspaceClicked = false;

                    if (this.globalSearchInput.replace(/\s/g, '') === '') {
                        return;
                    }

                    this.makePreliminaryGlobalSearchServerRequest();

                }, 500);
            },

            makePreliminaryGlobalSearchServerRequest: function() {

                let url = '/api/count-global-search-results?search='+encodeURIComponent(this.globalSearchInput);

                url += this.addSearchIterationToUrl();

                return this.httpGet(url)
                    .then(data => {

                        let newOptions = OPTIONS.filter(opt => data[opt.id]);

                        this.options = newOptions.map(opt => {
                            let newOption = opt;
                            newOption.text = `${opt.id}: ${this.globalSearchInput}`;
                            newOption.html = `<div>`;

                            newOption.html += opt.id === 'Person' ? `<i class="fa fa-users"></i> ` : '';
                            newOption.html += opt.id === 'Address' ? `<i class="fa fa-globe"></i> ` : '';
                            newOption.html += opt.id === 'Product' ? `<i class="fa fa-shopping-bag"></i> ` : '';
                            newOption.html += opt.id === 'Organisation' ? `<i class="fa fa-building"></i> ` : '';
                            newOption.html += opt.id === 'Any' ? `<i class="fa fa-question-circle"></i> ` : '';

                            newOption.html += `${opt.id}: ${this.globalSearchInput} <span class="circle">${data[opt.id]}</span>`;
                            newOption.html += `</div>`;

                            return newOption;
                        });

                        setTimeout(()=>{
                            this.select2Element.select2('open');
                            $('.select2-results__option--highlighted').removeClass('select2-results__option--highlighted');
                        }, 0)
                    });
            },

            addSearchIterationToUrl: function() {
                let str = '';

                this.searchIterations.forEach(iteration => {
                    str += '&iteration[]=' + iteration.type + '/--/' +iteration.value;
                });

                return str;
            },

            resetGlobalSearch: function () {
                this.globalSearchInput = '';

                this.options = JSON.parse(JSON.stringify(OPTIONS));
            },

            initSelect2: function () {

                this.select2Element.select2({
                    data: this.options,
                    minimumResultsForSearch: -1,
                    escapeMarkup: function(markup) {
                        return markup;
                    },
                    templateResult: function(data) {
                        return data.html;
                    },
                    templateSelection: function(data) {
                        return data.text;
                    },
                })
                    .val(null)
                    .trigger('change')
            },

            checkIfTheSelect2ShouldBeOpened: function (e) {
                if(e.keyCode === 40) {
                    this.select2Element.select2('close');
                    this.select2Element.focus();
                    this.select2Element.select2('open');
                }
            },

            addSearchIteration: function (e) {

                if(e) {
                    this.searchIterations.push({type: e.params.data.id, value: this.globalSearchInput});
                }
                else {
                    this.searchIterations.push({type: 'Any', value: this.globalSearchInput});
                }

                this.globalSearchInput = '';

                $('.global-search-input').focus();
            }
        },

        mounted: function () {

            this.select2Element = $('#global-search-select2');

            this.initSelect2();

            this.select2Element
                .on('select2:select', (e) => {

                    this.addSearchIteration(e);

                });

            this.$eventGlobal.$on('resetedAllFilters', ()=>{
                this.searchIterations = [];
            });

        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('resetedAllFilters');
        }
    }
</script>

<style scoped>

</style>