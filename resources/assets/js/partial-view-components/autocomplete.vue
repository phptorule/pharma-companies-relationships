<template>
    <div>
        <input 
            class="search-input form-control"
            v-model="searchQuery" 
            type="search" 
            @input="onChange(searchQuery)"
            :placeholder="placeholder ? placeholder : 'Search'"
        >
        <ul>
            <li v-if="isItems && searchQuery && itemsType=='People'" v-for="item in items">
                <div>
                    Name: {{ item.name }}
                </div>
                <div>
                    City: {{ item.town }}
                </div>
                <div>
                    Role: {{ item.description }}
                </div>
                <div v-if="item.addresses.length">
                    Addresses: {{ getAddressesString(item.addresses) }}
                </div>
            </li>
            <li v-if=" ! isItems && searchQuery.length >= 3">
                <span>
                    No matches
                </span>
            </li>
        </ul>
        <div class="pagination-box" v-if="isItems && searchQuery.length >= 3">
            <pagination 
                :records="itemsTotal" 
                :class="'pagination pagination-sm no-margin pull-right'" 
                :per-page="5" 
                :options="{'chunk': 5}"
                @paginate="peoplePageChanged">
            </pagination>
        </div>
    </div>
</template>

<script>
    import http from '../mixins/http';
    export default {
        name: "autocomplete",
        props: ['items', 'onChange', 'itemsTotal', 'itemsType', 'placeholder'],
        mixins: [http],
        data: function () {
            return {
                searchQuery: ''
            }
        },
        methods: {
            peoplePageChanged: function (pageNumber) {
                this.onChange(this.searchQuery, pageNumber);
            },
            getAddressesString: function (addresses) {
                let str = '';
                let names = [];
                if (addresses.length) {
                    names = addresses.map(element => {
                        return element.name;
                    });
                    str = names.join(', ');
                }

                return str;
            }
        },
        mounted: function () {

        },
        computed: {
            isItems: function () {
                return this.items.length > 0 ? true : false;
            },
            itemsLength: function () {
                return this.items.length;
            }
        }
    }
</script>

<style scoped>
    .search-input {
        width: 100%;
        border: none;
        border-bottom: 2px solid #d2d6de;
    }
</style>
