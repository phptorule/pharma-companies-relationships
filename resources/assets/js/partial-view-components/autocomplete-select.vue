<template>
    <div class="relative autocomplete-select">
        <div class="wrap">
            <div class="form-group">
                <i class="fa fa-search icon" aria-hidden="true"></i>
                <input @input="handleChange" id="input" type="text" class="input" v-model="query" placeholder="Chain name" />
            </div>
            <div class="selects form-group">
                <label class="radio-container" v-for="(item, i) in filtered">{{item.name}}
                    <input type="radio" :id="item.id" :value="item.id" v-model="cluster.id" name="radio" />
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="confirm-block">
                <div class="left-block">
                    <a href="#" class="add-link" @click.prevent="createNewLabchain">
                        <span class="plus">+</span> Create new labchain
                    </a>
                </div>
                <div class="right-block">
                    <button type="button" @click="closeSelf" class="btn cancel-address-btn">Cancel</button>
                    <a href="javascript:void(0)" class="button" @click="addItem">Add</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import http from '../mixins/http';
    export default {
        name: "autocompleteSelect",
        props: ['type', 'choose', 'selected', 'close', 'createNewChain'],
        mixins: [http],
        data: function () {
            return {
                list: [],
                filtered: [],
                query: '',
                cluster: {},
                oldValue: 0
            }
        },
        methods: {
            loadList: function () {
                this.httpGet('/api/' + this.type)
                    .then(data => {
                        this.list = data;
                        this.filtered = this.list;
                    })
            },
            handleChange: function (e) {
                this.filtered = this.list.filter((item) => {
                    return item.name.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1
                })
            },
            addItem: function () {
                this.choose(this.cluster.id)
            },
            closeSelf: function () {
                this.cluster.id = this.oldValue
                this.close()
            },
            createNewLabchain: function () {
                this.createNewChain();
            }
        },
        mounted: function () {
            if (this.selected) {
                this.cluster = this.selected;
                this.oldValue = this.selected.id;
            }

            document.getElementById('input').focus()
            
            this.loadList()
            this.$eventGlobal.$on('clusterNameUpdated', () => {
                this.loadList()
            })
        }
    }
</script>

<style scoped>
    
</style>