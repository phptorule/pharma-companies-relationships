<template>
    <ul class="nav nav-tabs single-dropdown-select" :id="blockId">
        <li class="dropdown">
            <a class="dropdown-toggle"
               @click="toogleDropdown($event)"
               data-toggle="dropdown"
               href="#"
               :title="selectedValuesNamesStringForTitle? name +': '+ selectedValuesNamesStringForTitle : name"
            >

                <span class="caret"></span>

                <i v-if="showCircle && selectedValue !== null"
                   class="oval"
                   :class="{
                        both: !selectedValue || selectedValue === '',
                        'potential-customers': selectedValue == 1,
                        'my-customers': selectedValue == 2,
                   }"
                ></i>

                <span v-html="selectedValuesNamesString ? selectedValuesNamesString : name"></span>

            </a>
            <ul class="dropdown-menu">
                <li @click="selectValue('', name)" :class="{'hidden': isHiddenEmptyOption, selected: !selectedValue}">
                    <div class="grey-checkbox">
                        <label>
                            <span class="remember_text">All</span>
                        </label>
                    </div>
                </li>
                <li v-for="option in options"
                    @click="selectValue(option.value, option.label)"
                    :class="{selected: option && selectedValue == option.value}"
                >
                    <div class="grey-checkbox">
                        <label>
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
                blockId: '',
                selectedValue: null,
                selectedValuesNamesString: ''
            }
        },

        watch: {
            options: function (newVal, oldVal) {
                if(newVal.length && !oldVal.length) {
                    this.presetSelectedValue();
                }
            }
        },

        computed: {
            selectedValuesNamesStringForTitle: function () {
                if(this.selectedValuesNamesString.indexOf('&uarr;') !== -1) {
                    return this.selectedValuesNamesString.replace('&uarr;', 'asc')
                }
                else if(this.selectedValuesNamesString.indexOf('&darr;') !== -1) {
                    return this.selectedValuesNamesString.replace('&darr;', 'desc')
                }
                else {
                    return this.selectedValuesNamesString;
                }
            }
        },

        methods: {

            selectValue: function (value, displayedLabel) {
                this.selectedValuesNamesString = displayedLabel.replace(/<(?:.|\n)*?>/gm, '');
                this.selectedValue = value;
                $('#'+this.blockId+' li.open').removeClass('open');
                this.notifyParentComponent();
            },

            toogleDropdown: function ($event) {

                let dropdownContainer = $($event.target).parent().hasClass('dropdown') ? $($event.target).parent() : $($event.target).parent().parent();

                if(dropdownContainer.hasClass('open')) {
                    dropdownContainer.removeClass('open');
                }
                else {
                    dropdownContainer.addClass('open');
                }
            },

            notifyParentComponent: function () {
                this.$emit('changed', this.selectedValue);
            },

            setIdForCurrentComponent: function () {
                this.blockId = this.name.replace(/[^A-Za-z0-9]/g,'').toLowerCase() + Math.round(Math.random()*100);
            },
            resetSelectedValues: function () {
                this.selectedValue = '';
                this.selectedValuesNamesString = this.name;
            },

            presetSelectedValue: function () {

                if(!this.selected || !this.options.length) {
                    return;
                }

                this.selectedValue = this.selected;

                let element = this.options.find(el => el.value == this.selected);

                if (element) {
                    this.selectedValuesNamesString = element.label;
                }
            }
        },

        mounted: function () {
            $('.dropdown-menu').on('click', function (e) {
                e.stopPropagation();
            });

            this.setIdForCurrentComponent();

            this.presetSelectedValue();
        },

        props: ['options', 'selected', 'name', 'isHiddenEmptyOption', 'showCircle']

    }
</script>

<style scoped>

</style>