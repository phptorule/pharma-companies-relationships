<template>
    <ul v-if="tags && tags.length" class="tag-list">
        <li v-for="tag of tags">
            <a href="#">{{tag}}</a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: ['tags']
    }
</script>

<style scoped>

</style>