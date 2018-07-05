<template>
    <div class="modal-employee-details-tab-relationship">
        <p style="text-align: center" v-if="!personData.relationships.length">This person doesn't have relationships yet.</p>

        <div class="search-block" v-if="personData.relationships.length">
            <div class="search-name">
                <i class="fa fa-search icon" aria-hidden="true"></i>
                <input 
                    v-model="query"
                    type="search" 
                    class="form-control" 
                    placeholder="Person name" 
                    @input="handleSearch">
            </div>
            <div class="search-type">
                <multiple-dropdown-select
                    class="form-control select-filter select-types"
                    :name="'Types'"
                    :options="typesOptionsForDropDown"
                    :selected="[]"
                    @changed="onChangeType"
                    ref="tagMultipleDropdownSelect"
                ></multiple-dropdown-select>
            </div>
            <div class="search-sort">
                <single-dropdown-select
                    class="form-control select-filter type-filter"
                    :options="sortByOptionsForFilter"
                    :selected="[]"
                    :isHiddenEmptyOption="true"
                    @changed="onChangeSort"
                    :name="'Sort By'"
                    ref="sortBySingleDropdownSelect"
                 ></single-dropdown-select>
            </div>
        </div>

        <div v-if="canSearch || canSearchByType">
            <ul class="staff-list staff-with-publication-list">

                <li v-for="(relation, i) in filtered" v-if="filtered && filtered.length > 0">
                    <div class="image">
                        <a href="javascript:void(0)" @click.prevent="loadAnotherUser(relation)">
                            <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">
                        <p class="name">
                            <a href="javascript:void(0)" @click.prevent="loadAnotherUser(relation)">
                                {{relation.name}}
                            </a>
                            <button v-if="isModalEditing"
                                class="delete-relation"
                                @click.prevent="deletePersonRelation(relation.from_person_id, relation.to_person_id)">
                                <i class="fa fa-minus"></i>
                            </button>
                        </p>
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

                <li v-if="filtered && ! filtered.length">
                    No matches
                </li>
            </ul>
        </div>

        <div v-if="!canSearch && !canSearchByType">
            <ul class="staff-list staff-with-publication-list" v-if="relationshipsCollapsedData && relationshipsCollapsedData.length && relationshipsCollapsed">

                <li v-if="i < 3" v-for="(relation, i) in relationshipsCollapsedData">
                    <div class="image">
                        <a href="javascript:void(0)" @click="loadAnotherUser(relation)">
                            <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">
                        <p class="pull-right last-cooperation-year" v-if="relation.lastCooperationYear">
                            <small>
                                Last Interaction <br>
                                {{relation.lastCooperationYear}}
                            </small>
                        </p>
                        <p class="name">
                            <a href="javascript:void(0)" @click="loadAnotherUser(relation)">
                                {{relation.name}}
                            </a>
                            <button v-if="isModalEditing"
                                class="delete-relation"
                                @click.prevent="deletePersonRelation(relation.from_person_id, relation.to_person_id)">
                                <i class="fa fa-minus"></i>
                            </button>
                        </p>
                        <p class="occupation" style="text-align: left">
                            {{relation.description}} at

                            <company-and-other-with-tooltip
                                    :entity="relation"
                                    :moreTextWithAND="true"
                            ></company-and-other-with-tooltip>
                        </p>
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
                            :signatoryAtSameCompany="publications.signatory_at_company"
                    ></publication-list>
                </li>
            </ul>

            <ul class="staff-list staff-with-publication-list" v-if="person.relationships && person.relationships.length && !relationshipsCollapsed">

                <li v-for="(relation, i) in person.relationships">
                    <div class="image">
                        <a href="javascript:void(0)" @click.prevent="loadAnotherUser(relation)">
                            <span class="person-initials">{{getPersonInitials(relation.name)}}</span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">

                        <p class="pull-right last-cooperation-year" v-if="relation.lastCooperationYear">
                            <small>
                                Last Interaction <br>
                                {{relation.lastCooperationYear}}
                            </small>
                        </p>

                        <p class="name">
                            <a href="javascript:void(0)" @click.prevent="loadAnotherUser(relation)">
                                {{relation.name}}
                            </a>
                            <button v-if="isModalEditing"
                                class="delete-relation"
                                @click.prevent="deletePersonRelation(relation.from_person_id, relation.to_person_id)">
                                <i class="fa fa-minus"></i>
                            </button>
                        </p>

                        <p class="occupation" style="text-align: left">{{relation.description}} at

                            <company-and-other-with-tooltip
                                    :entity="relation"
                                    :moreTextWithAND="true"
                            ></company-and-other-with-tooltip>

                        </p>

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
                            :signatoryAtSameCompany="publications.signatory_at_company"
                    ></publication-list>
                </li>
            </ul>
        </div>

        <div class="pagination-box" style="margin-top: 20px" v-if="!relationshipsCollapsed">
            <pagination :records="relationshipsTotal"
                        :class="'pagination pagination-sm no-margin pull-right'"
                        :per-page="10"
                        @paginate="relationshipsPageChanged"

            ></pagination>
        </div>

        <div style="clear: both"></div>

        <div class="text-center" style="margin-top: 20px" v-if="relationshipsCollapsedData && relationshipsCollapsedData.length > 3 && !canSearch && !canSearchByType">
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
                relationshipPage: 1,
                query: '',
                filtered: [],
                selectedTypes: null,
                selectedSort: null
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

                let url = '/api/people/'+this.personData.id+'/relationships?page='+p;

                this.relationshipPage = p;

                return this.httpGet(url)
                    .then(data => {
                        this.relationshipsCollapsed = false;

                        this.person.relationships = data.data;

                        this.relationshipsTotal = data.total;

                        this.sortBy();

                        return data;
                    });
            },

            relationshipsPageChanged: function(page) {
                this.loadPersonRelationshipsPaginated(page);
                this.$root.logData('person_relationship', 'page changed', JSON.stringify(page));
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
                this.$root.logData('person_relationship', 'slideup publications', JSON.stringify(''));
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

                this.publications = {};

                if (this.checkIfShouldBeSlidedUp(relation.id)) {
                    return;
                }

                this.slideUpAllPublications();

                let url = '/api/people/'+this.personData.id+'/relationship-details?' + this.composeRelationshipDetailsUrl(relation);

                return this.httpGet(url)
                    .then(data => {

                        this.publications = data;

                        if (this.connectionNameForPagination(relation).indexOf('Signatory at the same company') !== -1) {
                            this.publications['signatory_at_company'] = {name: this.addressData.name, address: this.addressData.address};
                        }

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
            },
            onChangeType: function (data) {
                this.selectedTypes = data;
            },
            onChangeSort: function (data) {
                this.selectedSort = data;
            },
            handleSearch: function () {
                let allRelations = this.getAllRelationships;

                this.filtered = allRelations.filter((item) => {
                    if (this.canSearch && this.canSearchByType) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1 &&
                            this.selectedTypes.indexOf(parseInt(item.edge_type)) + 1
                    } else if (this.canSearch && ! this.canSearchByType) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1
                    } else if ( ! this.canSearch && this.canSearchByType) {
                        return this.selectedTypes.indexOf(parseInt(item.edge_type)) + 1
                    }
                });

                this.sortBy();
            },
            specificSort: function (items) {
                let _this = this;
                if (items  && items.length) {
                    if (_this.selectedSort == 'name-asc') {
                        items.sort(function (a, b) {
                            if (a.name > b.name) {
                                return 1;
                            }
                            if (a.name < b.name) {
                                return -1;
                            }
                        });
                    } else if (_this.selectedSort == 'name-desc') {
                        items.sort(function (a, b) {
                            if (a.name < b.name) {
                                return 1;
                            }
                            if (a.name > b.name) {
                                return -1;
                            }
                        });
                    } else if (_this.selectedSort == 'count-asc') {
                        items.sort(function (a, b) {
                            let aPapers = 0, aCited = 0, aTotal = 0, bPapers = 0, bCited = 0, bTotal = 0;
                            
                            if(a.co_authored_paper) {
                                aPapers = a.co_authored_paper.split(',').length;
                            }

                            if(a.cited_paper) {
                                let ids = a.cited_paper.replace(/ cites /g, ',');

                                aCited = _this.getUniqueArrayElements(ids.split(',')).length;
                            }

                            aTotal = parseInt(aPapers) + parseInt(aCited);

                            if(b.co_authored_paper) {
                                bPapers = b.co_authored_paper.split(',').length;
                            }

                            if(b.cited_paper) {
                                let ids = b.cited_paper.replace(/ cites /g, ',');

                                bCited = _this.getUniqueArrayElements(ids.split(',')).length;
                            }

                            bTotal = parseInt(bPapers) + parseInt(bCited);
                            
                            return aTotal - bTotal;
                        });
                    } else if (_this.selectedSort == 'count-desc') {
                        items.sort(function (a, b) {
                            let aPapers = 0, aCited = 0, aTotal = 0, bPapers = 0, bCited = 0, bTotal = 0;
                            
                            if(a.co_authored_paper) {
                                aPapers = a.co_authored_paper.split(',').length;
                            }

                            if(a.cited_paper) {
                                let ids = a.cited_paper.replace(/ cites /g, ',');

                                aCited = _this.getUniqueArrayElements(ids.split(',')).length;
                            }

                            aTotal = parseInt(aPapers) + parseInt(aCited);

                            if(b.co_authored_paper) {
                                bPapers = b.co_authored_paper.split(',').length;
                            }

                            if(b.cited_paper) {
                                let ids = b.cited_paper.replace(/ cites /g, ',');

                                bCited = _this.getUniqueArrayElements(ids.split(',')).length;
                            }

                            bTotal = parseInt(bPapers) + parseInt(bCited);
                            
                            return bTotal - aTotal;
                        });
                    } else if (_this.selectedSort == 'date-asc') {
                        items.sort(function (a, b) {
                            return a.lastCooperationYear[0].year - b.lastCooperationYear[0].year;
                        });
                    } else if (_this.selectedSort == 'date-desc') {
                        items.sort(function (a, b) {
                            return b.lastCooperationYear[0].year - a.lastCooperationYear[0].year;
                        });
                    }
                }
            },
            sortBy: function () {
                let _this = this;
                if (_this.canSort) {
                    _this.specificSort(_this.filtered);
                    _this.specificSort(_this.person.relationships);
                    _this.specificSort(_this.relationshipsCollapsedData);
                }
            }
        },

        computed: {
            typesOptionsForDropDown: function () {
                return this.connectionTypes.map(type => {
                    return {
                        label: type.name,
                        value: type.id
                    }
                })
            },
            sortByOptionsForFilter: function () {
                return [
                    {value: 'name-asc', label: 'Name &uarr;'},
                    {value: 'name-desc', label: 'Name &darr;'},
                    {value: 'count-asc', label: 'Number of interactions &uarr;'},
                    {value: 'count-desc', label: 'Number of interactions &darr;'},
                    {value: 'date-asc', label: 'Date of last cooperation &uarr;'},
                    {value: 'date-desc', label: 'Date of last cooperation &darr;'},
                ]
            },
            getAllRelationships: function () {
                if (this.personData && this.personData.relationships && this.personData.relationships.length) {
                    return this.personData.relationships;
                }
                else {
                    return []
                }
            },
            canSearch: function () {
                return this.query.trim() === '' ? false : true
            },
            canSearchByType: function () {
                return this.selectedTypes && this.selectedTypes.length > 0 ? true : false
            },
            canSort: function () {
                return this.selectedSort && this.selectedSort.length > 0 ? true : false
            },
            deleteRelation: function (id) {

            }
        },


        watch: {

            personData: function (newPersonData) {
                this.relationshipsCollapsed = true;
                this.person = newPersonData;
                this.sortBy();
                this.handleSearch();
            },

            query: function () {
                if ( ! this.canSearch && ! this.canSearchByType) {
                    this.filtered = [];
                }
                this.sortBy();
            },

            selectedTypes: function () {
                this.handleSearch();
            },

            selectedSort: function () {
                this.sortBy();
            }

        },


        props: [
            'personData',
            'personId',
            'relationshipsCollapsedData',
            'connectionTypes',
            'addressData',
            'isModalEditing',
            'deletePersonRelation'
        ],

        mounted: function () {
            this.openRelationIfHashDetected()
        }

    }
</script>

<style scoped>

</style>