<template>
    <div>

        <ul class="publication-list">
            <li v-for="item of items">
                <div>
                    <h3>{{item.title}}</h3>
                    <p>{{item.journal}}</p>
                    <p>
                        <a :href="item.url" target="_blank"></a>
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