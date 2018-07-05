<template>
    <div>
        <div class="box box-solid" v-if="coAuthoredPublications && coAuthoredPublications.length" style="margin-top: 20px;">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users" aria-hidden="true" style="color: #989898"></i> Co Authored Publications</h3>
            </div>
            <div class="box-body">

                <publication-list-item :items="coAuthoredPublications"></publication-list-item>

            </div>
        </div>

        <div class="box box-solid" v-if="citedPublications && citedPublications.length">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-quote-left" aria-hidden="true" style="color: #989898"></i> Cited Publications</h3>
            </div>
            <div class="box-body">

                <publication-list-item :items="citedPublications"></publication-list-item>

            </div>
        </div>

        <div class="box box-solid" v-if="signatoryAtSameCompany">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-globe" aria-hidden="true" style="color: #989898"></i>
                    <small>Both are/were signatories at: <strong>{{signatoryAtSameCompany.name}}</strong>.
                        {{signatoryAtSameCompany.address}}
                    </small>
                </h3>
            </div>
        </div>
    </div>
</template>

<script>
    export default {


        props: ['coAuthoredPublications', 'citedPublications', 'signatoryAtSameCompany']

    }
</script>

<style scoped>

</style>