<template>
    <div>
        <p style="text-align: center" v-if="!personData.relationships.length">This person doesn't have relationships yet.</p>

        <ul class="staff-list staff-with-publication-list" v-if="relationshipsCollapsedData && relationshipsCollapsedData.length && relationshipsCollapsed">

            <li v-if="i < 3" v-for="(relation, i) in relationshipsCollapsedData">
                <div class="image">
                    <a href="javascript:void(0)" @click="loadAnotherUser(relation)">
                        <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name"><a href="javascript:void(0)" @click="loadAnotherUser(relation)">{{relation.name}}</a></p>
                    <p class="occupation" style="text-align: left">{{relation.description}}</p>
                    <p class="connection-type" style="text-align: left">
                        <a href="javascript:void(0)" @click="loadRelationship(relation)">
                            {{connectionNameForPagination(relation)}}
                        </a>
                    </p>
                </div>

                <div style="clear: both"></div>

                <publication-list
                        class="publication-list relation-row-class"
                        style="display: none;"
                        :id="'relation-row-'+relation.id"
                        :coAuthoredPublications="publications.co_authored_paper"
                        :citedPublications="publications.cited_paper"
                ></publication-list>
            </li>
        </ul>

        <ul class="staff-list staff-with-publication-list" v-if="person.relationships && person.relationships.length && !relationshipsCollapsed">

            <li v-for="(relation, i) in person.relationships">
                <div class="image">
                    <a href="javascript:void(0)" @click="loadAnotherUser(relation)">
                        <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name"><a href="javascript:void(0)" @click="loadAnotherUser(relation)">{{relation.name}}</a></p>
                    <p class="occupation" style="text-align: left">{{relation.description}}</p>
                    <p class="connection-type" style="text-align: left">
                        <a href="javascript:void(0)" @click="loadRelationship(relation)">
                            {{connectionNameForPagination(relation)}}
                        </a>
                    </p>
                </div>

                <div style="clear: both"></div>

                <publication-list
                        class="publication-list relation-row-class"
                        style="display: none;"
                        :id="'relation-row-'+relation.id"
                        :coAuthoredPublications="publications.co_authored_paper"
                        :citedPublications="publications.cited_paper"
                ></publication-list>
            </li>
        </ul>

        <div class="pagination-box" style="margin-top: 20px" v-if="!relationshipsCollapsed">
            <pagination :records="relationshipsTotal"
                        :class="'pagination pagination-sm no-margin pull-right'"
                        :per-page="10"
                        @paginate="relationshipsPageChanged"

            ></pagination>
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
               @click="showLessRelationships()"
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
    import helpers from '../mixins/helpers';

    export default {

        mixins: [getPersonInitials, http, helpers],

        data: function () {
            return {
                person: {
                    relationships: []
                },
                relationshipsTotal: 0,
                relationshipsCollapsed: true,
                publications: {},
                relationshipPage: 1
            }
        },


        methods: {

            showLessRelationships: function () {
                this.slideUpAllPublications();
                this.relationshipsCollapsed = true;
            },

            connectionNameForPagination: function(relation){

                let text = '';

                if(relation.co_authored_paper) {
                    let numberOfCoAuthPapers = relation.co_authored_paper.split(',').length;

                    text += 'Co-Authored ' + numberOfCoAuthPapers + ' paper' + (numberOfCoAuthPapers > 1? 's' : '');
                }

                if(relation.cited_paper) {

                    let ids = relation.cited_paper.replace(/ cites /g, ',');

                    let numberOfCitedPapers = this.getUniqueArrayElements(ids.split(',')).length;

                    if(text.length) {
                        text += ', ';
                    }

                    text += 'Cited ' + numberOfCitedPapers + ' paper' + (numberOfCitedPapers > 1? 's ' : '');
                }

                if(relation.signatory_at_company) {
                    if(text.length) {
                        text += ', ';
                    }

                    text += 'Signatory at the same company';
                }

                return text

            },

            loadPersonRelationshipsPaginated: function (page) {

                this.slideUpAllPublications();

                let p = page || 1;

                let url = '/api/people/'+this.personId+'/relationships?page='+p;

                this.relationshipPage = p;

                return this.httpGet(url)
                    .then(data => {
                        this.relationshipsCollapsed = false;

                        this.person.relationships = data.data;

                        this.relationshipsTotal = data.total;

                        return data;
                    });
            },

            relationshipsPageChanged: function(page) {
                this.loadPersonRelationshipsPaginated(page);
            },


            composeRelationshipDetailsUrl: function(relation) {
                let urlQuery = '';

                if (relation.co_authored_paper) {
                    urlQuery += 'co_authored_paper=' + relation.co_authored_paper;
                }

                if (relation.cited_paper) {

                    if(urlQuery.length) {
                        urlQuery += '&';
                    }

                    let ids = relation.cited_paper.replace(/ cites /g, ',');

                    ids = this.getUniqueArrayElements(ids.split(','));

                    urlQuery += 'cited_paper=' + ids.join(',');
                }

                return urlQuery;
            },

            slideUpAllPublications: function() {
                $('.relation-row-class').slideUp(0);
            },

            checkIfShouldBeSlidedUp: function(relationId) {

                let element = $('#relation-row-'+relationId);

                if(element.attr('style').replace(/\s/g, '').indexOf('display:none') === -1) {
                    element.slideUp('slow');
                    return true;
                }
                return false;
            },

            loadRelationship: function (relation) {

                if (this.checkIfShouldBeSlidedUp(relation.id)) {
                    return;
                }

                this.slideUpAllPublications();

                let url = '/api/people/'+this.personId+'/relationship-details?' + this.composeRelationshipDetailsUrl(relation);

                return this.httpGet(url)
                    .then(data => {

                        this.publications = data;

                        setTimeout(()=>{
                            $('#relation-row-'+relation.id).slideDown('slow');
                        }, 50);

                        this.updateHashStr(relation.id);

                    })
            },

            loadAnotherUser: function (relation) {
                location.hash = '#person-' + relation.id;
                this.$emit('resetTab', {});
            },

            updateHashStr: function(relationId) {
                let hashStr = this.$route.hash.split('&')[0];

                hashStr += '&relation-person=' + relationId;
                hashStr += '&relation-page=' + this.relationshipPage;

                location.hash = hashStr;
            },

            openRelationIfHashDetected: function () {
                if(this.$route.hash.indexOf('#person-') !== -1
                    && this.$route.hash.indexOf('&relation-person=') !== -1
                    && this.$route.hash.indexOf('&relation-page=') !== -1)
                {
                    let relationshipParams = this.getRelationshipParams();

                    this.loadPersonRelationshipsPaginated(relationshipParams.relationPage)
                        .then(data => {

                            let relation = this.person.relationships.find(el => el.id == relationshipParams.relationPerson);

                            $('#personal-modal .page-item.active').removeClass('active');
                            $('#personal-modal .page-link:contains("'+relationshipParams.relationPage+'")').parent().addClass('active');

                            this.loadRelationship(relation)
                                .then(() => {
                                    this.scrollToElement($('#relation-row-' + relationshipParams.relationPerson).parent(), 100, '#personal-modal');
                                })
                        })
                }
            },

            getRelationshipParams: function () {
                let hashStr = this.$route.hash;
                let person = hashStr.split('&').find(el => el.indexOf('#person-') !== -1).replace('#person-', '');
                let relationPerson = hashStr.split('&').find(el => el.indexOf('relation-person=') !== -1).replace('relation-person=', '');
                let relationPage = hashStr.split('&').find(el => el.indexOf('relation-page=') !== -1).replace('relation-page=', '');

                return {
                    person: person,
                    relationPerson: relationPerson,
                    relationPage: relationPage
                }
            }
        },


        watch: {

            personData: function (newPersonData) {
                this.relationshipsCollapsed = true;
                this.person = newPersonData;
            },

        },


        props: ['personData', 'personId', 'relationshipsCollapsedData', 'connectionTypes'],

        mounted: function () {
            this.openRelationIfHashDetected()
        }

    }
</script>

<style scoped>

</style>