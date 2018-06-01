<template>
    <div>

        <ul class="publication-list">
            <li v-for="item of items">
                <div>
                    <h4>{{item.title}}</h4>
                    <p>{{item.journal}}</p>
                    <p>
                        <a :href="item.url" target="_blank">{{item.url}}</a>
                    </p>
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