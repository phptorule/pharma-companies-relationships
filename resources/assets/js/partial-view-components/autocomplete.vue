<template>
    <div class="autocomplete-block">
        <input 
            class="search-input form-control"
            v-model="searchQuery" 
            type="search" 
            @input="onChange(searchQuery)"
            :placeholder="placeholder ? placeholder : 'Search'"
        >
        <ul class="person-list">
            <li v-if="isItems && searchQuery.length >= 3 && itemsType=='People'" 
                v-for="(item, i) in items" 
                @click.prevent="onItemClick(item)"
                class="person-item"
            >
                <div class="image">
                    <a href="javascript:void(0)">
                        <span class="person-initials">{{getPersonInitials(item.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="person-info">
                    <div>Name: {{ item.name }}</div>
                    <div>City: {{ item.town }}</div>
                    <div>Role: {{ item.description }}</div>
                    <div v-if="item.addresses.length">
                        Addresses: {{ getAddressesString(item.addresses) }}
                    </div>
                </div>
            </li>
            <li v-if=" ! isItems && searchQuery.length >= 3" class="no-matches">
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
    import getPersonInitials from '../mixins/get-person-initials';
    export default {
        name: "autocomplete",
        props: ['items', 'onChange', 'onClick', 'itemsTotal', 'itemsType', 'placeholder'],
        mixins: [http, getPersonInitials],
        data: function () {
            return {
                searchQuery: '',
                selected: null
            }
        },
        watch: {
            selected: function () {
                if (this.selected) {
                    this.searchQuery = '';
                }
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
            },
            onItemClick: function (item) {
                this.selected = item;
                this.onClick(item);
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
    
</style>
