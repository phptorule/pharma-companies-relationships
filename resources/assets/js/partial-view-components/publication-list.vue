<template>
    <div>
        <div class="box box-solid" v-if="coAuthoredPublications && coAuthoredPublications.length">
            <div class="box-header with-border">
                <h3 class="box-title">Co Authored Publications</h3>
            </div>
            <div class="box-body">

                <publication-list-item :items="coAuthoredPublications"></publication-list-item>

            </div>
        </div>

        <div class="box box-solid" v-if="citedPublications && citedPublications.length">
            <div class="box-header with-border">
                <h3 class="box-title">Cited Publications</h3>
            </div>
            <div class="box-body">

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