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

        <div class="box box-solid" v-if="matchedAddresses.length">
            <div class="box-header">
                <h3 class="box-title">
                    <i class="fa fa-globe" aria-hidden="true" style="color: #989898"></i>
                    <small>Both are/were signatories at: </small>

                    <ul class="list-signatories-at">
                        <li v-for="add of matchedAddresses">
                            <small><strong>{{add.name}}</strong>. {{add.address}}</small>
                        </li>
                    </ul>
                </h3>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        data: function() {
            return {
                matchedAddresses: []
            }
        },

        methods: {
            updateMatchedAddresses: function () {

                this.matchedAddresses = [];

                if(this.currentPersonAddresses && this.currentPersonAddresses.length && this.relationAddresses && this.relationAddresses.length) {

                    this.currentPersonAddresses.forEach(address => {
                        let matchedAddress = this.relationAddresses.find(a => a.id == address.id);

                        if(matchedAddress) {
                            this.matchedAddresses.push(matchedAddress);
                        }
                    });

                }
            }
        },

        watch: {

            personId: function (newVal) {
                this.updateMatchedAddresses();

            }
        },

        props: [
            'coAuthoredPublications',
            'citedPublications',
            'signatoryAtSameCompany',
            'currentPersonAddresses',
            'relationAddresses',
            'personId'
        ],

        mounted: function () {
            this.updateMatchedAddresses();
        }

    }
</script>

<style scoped>

</style>