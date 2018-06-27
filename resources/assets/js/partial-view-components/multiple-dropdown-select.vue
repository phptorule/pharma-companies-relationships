<template>
    <ul class="nav nav-tabs multiple-dropdown-select" :id="blockId">
        <li class="dropdown">
            <a class="dropdown-toggle" @click="toogleDropdown($event)" data-toggle="dropdown" href="#" :title="selectedValuesNamesString? name +': '+ selectedValuesNamesString : name">

                <span class="caret"></span>
                {{selectedValuesNamesString? selectedValuesNamesString : name}}
            </a>
            <ul class="dropdown-menu">
                <li v-for="option in options">
                    <div class="grey-checkbox">
                        <label>
                            <input type="checkbox"
                                   @click="checkboxClick(option.value)"
                                   :id="blockId + option.value"
                                   :checked="selectedValues.indexOf(option.value) !== -1"
                            >
                            <span class="borders"></span>
                            <span class="remember_text" v-html="option.label"></span>
                        </label>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
    export default {

        data: function () {
            return {
                selectedValues: [],
                blockId: ''
            }
        },

        computed: {
            selectedValuesNamesString: function () {
                let str = '';
                this.selectedValues.forEach((id, index) => {
                    let option = this.options.find(el => el.value == id);

                    str += option? option.label.replace(/<(?:.|\n)*?>/gm, '') : '';

                    if(++index !== this.selectedValues.length) {
                        str += ', ';
                    }
                });

                return str;
            }
        },

        watch: {
            options: function (newVal, oldVal) {
                if(newVal.length && !oldVal.length) {
                    this.presetSelectedValue();
                }
            },

            selected: function (newVal, oldVal) {
                if(newVal && newVal.length) {
                    this.presetSelectedValue();
                }
            }
        },

        methods: {
            toogleDropdown: function ($event) {

                let dropdownContainer = $($event.target).parent();

                if(dropdownContainer.hasClass('open')) {
                    dropdownContainer.removeClass('open');
                }
                else {
                    dropdownContainer.addClass('open');
                }
            },

            checkboxClick: function (selectedValue) {

                let index = this.selectedValues.indexOf(selectedValue);

                if (index === -1) {
                    this.selectedValues.push(selectedValue);
                }
                else {
                    this.selectedValues.splice(index, 1);
                }

                this.notifyParentComponent();
            },

            notifyParentComponent: function () {
                this.$emit('changed', JSON.parse(JSON.stringify(this.selectedValues)));
            },

            setIdForCurrentComponent: function () {
                this.blockId = this.name.replace(/[^A-Za-z0-9]/g,'').toLowerCase() + Math.round(Math.random()*100);
            },
            resetSelectedValues: function () {
                $('#'+this.blockId+' input[type="checkbox"]:checked').prop('checked', false)
                this.selectedValues = [];
            },

            presetSelectedValue: function () {
                if(!this.selected || !this.selected.length || !this.options || !this.options.length) {
                    return;
                }

                this.selectedValues = this.selected.map(el => parseInt(el));
            }
        },

        mounted: function () {
            $('.dropdown-menu').on('click', function (e) {
                e.stopPropagation();
            });

            this.setIdForCurrentComponent();
        },


        props: ['options', 'selected', 'name']

    }
</script>

<style scoped>

</style>