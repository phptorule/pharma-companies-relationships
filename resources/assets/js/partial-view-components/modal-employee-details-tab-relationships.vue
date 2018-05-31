<template>
    <div>
        <p style="text-align: center" v-if="!personData.relationships.length">This person doesn't have relationships yet.</p>

        <ul class="staff-list" v-if="relationshipsCollapsedData && relationshipsCollapsedData.length && relationshipsCollapsed">

            <li v-if="i < 3" v-for="(relation, i) in relationshipsCollapsedData">
                <div class="image">
                    <a href="javascript:void(0)">
                        <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name"><a href="javascript:void(0)">{{relation.name}}</a></p>
                    <p class="occupation" style="text-align: left">{{relation.description}}</p>
                    <p class="connection-type" style="text-align: left">{{connectionName(relation.pivot.edge_type, relation.pivot.edge_comment)}}</p>
                </div>
            </li>
        </ul>

        <ul class="staff-list" v-if="person.relationships && person.relationships.length && !relationshipsCollapsed">

            <li v-for="(relation, i) in person.relationships">
                <div class="image">
                    <a href="javascript:void(0)">
                        <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name"><a href="javascript:void(0)">{{relation.name}}</a></p>
                    <p class="occupation" style="text-align: left">{{relation.description}}</p>
                    <p class="connection-type" style="text-align: left">{{connectionName(relation.edge_type, relation.edge_comment)}}</p>
                </div>
            </li>
        </ul>

        <div class="pagination-box" style="margin-top: 20px" v-if="!relationshipsCollapsed">
            <pagination :records="relationshipsTotal"  :class="'pagination pagination-sm no-margin pull-right'" :per-page="10" @paginate="relationshipsPageChanged"></pagination>
        </div>

        <div style="clear: both"></div>

        <div class="text-center" style="margin-top: 20px" v-if="relationshipsCollapsedData && relationshipsCollapsedData.length > 3">
            <a href="javascript:void(0)"
               v-if="relationshipsCollapsed"
               @click="loadPersonRelationshipsPaginated()"
               class="address-box-show-more-link show-all-employees-link"
            >
                Show all Relationships
            </a>

            <a href="javascript:void(0)"
               v-if="!relationshipsCollapsed"
               @click="relationshipsCollapsed = true"
               class="address-box-show-more-link show-all-employees-link"
            >
                Show Less
            </a>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import getPersonInitials from '../mixins/get-person-initials';

    export default {

        mixins: [getPersonInitials, http],

        data: function () {
            return {
                person: {
                    relationships: []
                },
                relationshipsTotal: 0,
                relationshipsCollapsed: true
            }
        },


        methods: {

            connectionName: function (id, edgeComment) {

                let num = 0;
                let displayConnectionName = '';

                if(edgeComment) {
                    num = edgeComment.split(',').length;
                }

                switch (id) {
                    case '1':
                        displayConnectionName = 'Co-Authored ' + num + ' paper' + (num > 1? 's' : '');
                        break;
                    case '2':
                        displayConnectionName = 'Cited '  + num + ' paper' + (num > 1? 's' : '');
                        break;
                    case '3':
                        displayConnectionName = 'Signatory at the same company';
                        break;
                }

                return displayConnectionName;
            },

            loadPersonRelationshipsPaginated: function (page) {

                let p = page || 1;

                let url = '/api/people/'+this.personId+'/relationships?page='+p;

                this.httpGet(url)
                    .then(data => {
                        this.relationshipsCollapsed = false;

                        this.person.relationships = data.data;

                        this.relationshipsTotal = data.total;
                    });
            },

            relationshipsPageChanged: function(page) {
                this.loadPersonRelationshipsPaginated(page);
            },
        },


        watch: {

            personData: function (newPersonData) {
                this.relationshipsCollapsed = true;
                this.person = newPersonData;
            },

        },


        props: ['personData', 'personId', 'relationshipsCollapsedData', 'connectionTypes'],

        mounted: function () {

        }

    }
</script>

<style scoped>

</style>