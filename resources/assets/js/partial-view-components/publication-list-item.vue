<template>
    <div>

        <ul class="publication-list">
            <li v-for="item of items">
                <div>
                    <h4>
                        <a :href="item.url" target="_blank">{{item.title}} <i class="fa fa-external-link"></i></a></h4>
                    <p>{{item.journal}}</p>
                </div>
            </li>
        </ul>

    </div>
</template>

<script>
    export default {


        props: ['items']

    }
</script>

<style scoped>

</style>