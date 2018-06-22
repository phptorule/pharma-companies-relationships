<template>
    <div>
        <input v-model="searchQuery" type="search" @input="onChange(searchQuery)">
        <ul>
            <li v-if="isItems && searchQuery" v-for="item in items">
                <span>
                    {{ item.name }}
                </span>
            </li>
            <li v-if=" ! isItems && searchQuery.length >= 3">
                <span>
                    No matches
                </span>
            </li>
        </ul>
        <div class="pagination-box">
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
        props: ['items', 'onChange', 'itemsTotal'],
        mixins: [http],
        data: function () {
            return {
                searchQuery: ''
            }
        },
        methods: {
            peoplePageChanged: function (pageNumber) {
                this.onChange(this.searchQuery, pageNumber);
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
