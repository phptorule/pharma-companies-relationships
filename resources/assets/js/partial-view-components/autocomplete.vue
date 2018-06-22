<template>
    <div>
        <input v-model="searchQuery" type="search" @input="onChange(searchQuery)">
        <ul>
            <li v-for="item in items">
                <span v-if="items.length">
                    {{ item.name }}
                </span>
                <span v-else>
                    No matches
                </span>
            </li>
        </ul>
    </div>
</template>

<script>
    import http from '../mixins/http';
    export default {
        name: "autocomplete",
        props: ['items', 'onChange'],
        mixins: [http],
        data: function () {
            return {
                searchQuery: ''
            }
        },
        methods: {

        },
        mounted: function () {

        }
    }
</script>

<style scoped>

</style>
