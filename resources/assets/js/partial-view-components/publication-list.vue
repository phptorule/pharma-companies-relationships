<template>
    <div>
        <div class="panel panel-default" v-if="coAuthoredPublications && coAuthoredPublications.length">
            <div class="panel-heading">
                <h3 class="panel-title">Co Authored Publications</h3>
            </div>
            <div class="panel-body">

                <publication-list-item :items="coAuthoredPublications"></publication-list-item>

            </div>
        </div>

        <div class="panel panel-default" v-if="citedPublications && citedPublications.length">
            <div class="panel-heading">
                <h3 class="panel-title">Cited Publications</h3>
            </div>
            <div class="panel-body">

                <publication-list-item :items="citedPublications"></publication-list-item>

            </div>
        </div>
    </div>
</template>

<script>
    export default {


        props: ['coAuthoredPublications', 'citedPublications']

    }
</script>

<style scoped>

</style>