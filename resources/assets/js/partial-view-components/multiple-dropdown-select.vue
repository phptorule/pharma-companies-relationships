<template>
    <ul class="nav nav-tabs" :id="blockId">
        <li class="dropdown">
            <a class="dropdown-toggle" 
                @click="toogleDropdown($event)" 
                data-toggle="dropdown" 
                href="#" 
                :title="selectedValuesNamesString ? name + ': ' + selectedValuesNamesString : name"
            >
                <span class="caret"></span>
                {{ selectedValuesNamesString ? selectedValuesNamesString : name }}
            </a>
            <ul class="dropdown-menu" v-if="this.name != 'Used Products'">
                <li v-for="option in options">
                    <div class="grey-checkbox">
                        <label>
                            <input type="checkbox"
                                @click="checkboxClick(option.value)"
                                :id="blockId + option.value"
                                :checked="selectedValues.indexOf(option.value) !== -1"
                            >
                            <span class="borders"></span>
                            <span class="remember_text">{{option.label}}</span>
                        </label>
                    </div>
                </li>
            </ul>

            <ul class="dropdown-menu" v-if="this.name == 'Used Products'">
                <li :class="['dropdown-child-products', 'drp-' + i]" v-for="(parentProduct, i) in relationalProducts">
                    <div class="parent-product-block blue-checkbox">
                        <input type="checkbox"
                            :id="blockId + parentProduct.id"
                            :checked="childProductSelected(parentProduct.id)"
                        >
                        <span @click="parentCheckboxClick($event, parentProduct.id, i)" 
                            :class="['product-check', {'half-transparent': !checkIfAllChildsSelected(i)}]"
                            data-parent-checkbox
                        ></span>
                        <a class="dropdown-child-products-link" @click.prevent="toogleChildDropdown($event, i)" href="#">
                            <img class="parent-product-image" :src="parentProduct.image" alt="" v-if="parentProduct.image">
                            <img class="parent-product-image" :src="'/images/mask-'+i+'.png'" alt="" v-else>
                            <div class="parent-product-info">
                                <span>{{ parentProduct.company }}</span>
                                <span class="fa fa-angle-down" data-caret></span>
                            </div>
                        </a>
                    </div>
                    <ul class="child-products-list">
                        <li @click="checkboxClick(childProduct.id, i)" v-for="childProduct in parentProduct.childProducts">
                            <input type="checkbox"
                                :id="blockId + childProduct.id"
                                :checked="selectedValues.indexOf(childProduct.id) !== -1"
                            >
                            <span  class="product-check"></span>
                            <span>
                                {{ childProduct.name ? childProduct.name : 'Unspecified ' + childProduct.company + ' product' }}
                            </span>
                        </li>
                    </ul>
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
                    if (this.name == 'Used Products') {
                        let option = this.options.find(el => el.id == id);
                        str += option.company + (option.name ? ': ' + option.name : '');
                    } else {
                        let option = this.options.find(el => el.value == id);
                        str += option.label
                    }

                    if(++index !== this.selectedValues.length) {
                        str += ', ';
                    }
                });

                return str;
            },
            selectedIds: function () {
                return this.selectedValues;
            }
        },

        watch: {
            options: function (newVal, oldVal) {
                if(newVal.length && !oldVal.length) {
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
            toogleChildDropdown: function ($event, i) {
                let dropClass = '.drp-' + i;
                let down = 'fa-angle-down';
                let up = 'fa-angle-up';
                let dropdownContainer = $(dropClass);

                if(dropdownContainer.hasClass('show-child-dropdown')) {
                    dropdownContainer.removeClass('show-child-dropdown');
                    dropdownContainer.find('[data-caret]').removeClass(up).addClass(down);
                }
                else {
                    dropdownContainer.addClass('show-child-dropdown');
                    dropdownContainer.find('[data-caret]').removeClass(down).addClass(up);
                }
            },
            checkboxClick: function (selectedValue, i) {
                let index = this.selectedValues.indexOf(selectedValue);

                if (index === -1) {
                    this.selectedValues.push(selectedValue);
                }
                else {
                    this.selectedValues.splice(index, 1);
                }

                this.notifyParentComponent();
            },
            parentCheckboxClick: function ($event, selectedValue, index) {
                let parent = this.relationalProducts.find(el => el.id === selectedValue);
                let selected = [];
                let old = this.selectedIds;
                let isChildSelected = false;

                parent.childProducts.forEach(function (item) {
                    if (old.indexOf(item.id) > -1) {
                        isChildSelected = true;
                    }
                    selected.push(item.id);
                });

                if (isChildSelected) {
                    let delIndexes = [];
                    
                    old.forEach((element, i) => {
                        if (selected.indexOf(element) > -1) {
                            delIndexes.push(i);
                        }
                    });

                    while(delIndexes.length) {
                        old.splice(delIndexes.pop(), 1);
                    }

                    this.selectedValues = old;
                } else {
                    if ( ! old.length) {
                        old = selected;
                    } else {
                        selected.forEach(function (item) {
                            old.push(item);
                        })
                    }
                    this.selectedValues = old;
                }

                this.notifyParentComponent();
            },
            childProductSelected: function (id) {
                let parent = this.relationalProducts.find(el => el.id === id);
                let isChildSelected = false;
                let old = this.selectedIds;
                parent.childProducts.forEach(function (item) {
                    if (old.indexOf(item.id) > -1) {
                        isChildSelected = true;
                    }
                });

                return isChildSelected;
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
                if(!this.selected.length || !this.options.length) {
                    return;
                }

                this.selectedValues = this.selected.map(el => parseInt(el));
            },
            checkIfAllChildsSelected: function (parentIndex) {
                let parent = this.relationalProducts[parentIndex];
                let selected = this.selectedIds;
                let isAll = [];

                for (let k = 0; k < parent.childProducts.length; k++) {
                    if (selected.indexOf(parent.childProducts[k].id) > -1) {
                        isAll.push(1);
                    } else {
                        isAll.push(0);
                    }
                }

                return isAll.indexOf(0) > -1 ? false : true;
            }
        },

        mounted: function () {
            $('.dropdown-menu').on('click', function (e) {
                e.stopPropagation();
            });

            this.setIdForCurrentComponent();
        },


        props: ['options', 'selected', 'name', 'relationalProducts']

    }
</script>

<style scoped>
    li.dropdown-child-products {
        display: flex;
        flex-direction: column;
    }

    li.dropdown-child-products ul {
        display: none;
    }

    li.dropdown-child-products.show-child-dropdown ul{
        display: block;
    }

    .parent-product-block {
        display: flex;
        align-items: center;
    }

    .dropdown-child-products-link {
        background-color: transparent;
        display: flex;
        align-items: center;
    }

    .dropdown-child-products-link:hover {
        background-color: transparent;
    }
    
    .dropdown-child-products-link:focus {
        background-color: transparent;
    }

    .parent-product-image {
        height: 70px;
        width: 70px;
        border-radius: 50%;
        display: inline-block;
    }

    .parent-product-info {
        display: flex;
        justify-content: space-between;
        width: 100%;
        min-width: 200px;
        margin-left: 20px;
    }
    
    .parent-product-info span:first-child {
        color: #333333;
        white-space: normal;
    }

    .child-products-list {
        list-style-type: none;
    }

    .child-products-list input {
        display: none;
    }

    .child-products-list li {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        margin-top: 10px;
    }

    .child-products-list li span:last-child {
        white-space: normal;
    }

    .blue-checkbox input {
        display: none;
    }

    .product-check {
        float: none;
        margin: 0;
        background: #ffffff;
        padding: 8px;
        border-radius: 5px;
        border: 2px solid #e2e3e5;
        display: inline-block;
        position: relative;
        cursor: pointer;
        margin-right: 15px;
    }

    [type=checkbox]:checked + .product-check:before {
        content: '\2714';
        position: absolute;
        font-size: 14px;
        top: 0;
        left: 3px;
        color: #2488c5;
    }

    [type=checkbox]:checked + .product-check {
        background: #4081df;
        border-color: #4081df;
    }
    
    [type=checkbox]:checked + .product-check.half-transparent {
        opacity: 0.5;
    }

    [type=checkbox]:checked + .product-check:before {
        color: #ffffff;
        top: -2px;
        left: 2px;
    }
</style>