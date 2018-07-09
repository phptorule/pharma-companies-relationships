<template>

        <div class="nav-search">
            <ul class="nav navbar-nav">
                <li>
                    <img src="/images/ic-search.png" alt="">
                    <input
                            v-model="globalSearchInput"
                            @keyup="makeGlobalSearch()"
                            placeholder="Search by laboratory, people or location"
                    >

                    <i v-if="globalSearchInput"
                       @click="resetGlobalSearch()"
                       class="fa fa-remove clear-global-search-input"></i>
                </li>
            </ul>

            <select id="global-search-input" style="width: 300px"></select>
        </div>

</template>

<script>

    import http from '../mixins/http';

    const OPTIONS = [
        {
            "id": 'Person',
            "text": "Person",
            "html": "<div>Person</div>"
        },
        {
            "id": 'Product',
            "text": "Product",
            "html": "<div>Product</div>",
        },
        {
            "id": 'Organisation',
            "text": "Organisation",
            "html": "<div>Organisation</div>",
        },
        {
            "id": 'City',
            "text": "City",
            "html": "<div>City</div>"
        },
    ];

    export default {

        mixins: [http],

        data: function() {
            return {
                select2Element: null,
                selectedCategory: '',
                globalSearchInput: '',
                options: JSON.parse(JSON.stringify(OPTIONS))
            }
        },

        watch: {
            selectedCategory: function (newCategory) {
                console.log('newCategory',newCategory);
            },

            options: function (options) {

                $(this.select2Element).empty();

                this.initSelect2();
            }
        },

        methods: {
            makeGlobalSearch: function () {

                if (this.globalSearchIdCount) {
                    clearTimeout(this.globalSearchIdCount)
                }


                this.globalSearchIdCount = setTimeout(()=>{

                    if(this.globalSearchInput === '') {
                        return this.options = JSON.parse(JSON.stringify(OPTIONS));
                    }

                    this.makeGlobalSearchServerRequest();

                }, 500);
            },

            makeGlobalSearchServerRequest: function() {
                return this.httpGet('/api/count-global-search-results')
                    .then(data => {

                        this.options = this.options.map(opt => {
                            let newOption = opt;
                            newOption.text = `${opt.id}: ${this.globalSearchInput}`;
                            newOption.html = `<div>${opt.id}: ${this.globalSearchInput}</div>`;
                            return newOption;
                        });

                        setTimeout(()=>{
                            this.select2Element.select2('open');
                        }, 0)
                    });
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
                // .val(this.value)
                // .trigger('change')
                    .on('select2:select', (e) => {
                        console.log('e',e.params.data.id);

                        this.selectedCategory = e.params.data.id;
                    })
            },
        },

        mounted: function () {

            this.select2Element = $('#global-search-input');

            this.initSelect2();

        }
    }
</script>

<style scoped>

</style>