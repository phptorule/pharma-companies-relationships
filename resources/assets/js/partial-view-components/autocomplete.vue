<template>
    <div>
        <input type="search">
    </div>
</template>

<script>
    import http from '../mixins/http';
    export default {
        name: "autocomplete",
        props: [],
        mixins: [http],
        data: function () {
            return {

            }
        }
    }
</script>

<style scoped>

</style>
