<template>
    <div>

        <h3 class="font-weight-500 purchase-tender-header">Purchases within the tender</h3>
        <table class="tender-data-table">
            <tr v-for="purchase in tenderData.purchases">
                <!--<td>-->
                    <!--<ul class="list-style-type-circle"><li></li></ul>-->
                <!--</td>-->
                <td>
                    <h3>{{purchase.name}}</h3>
                    <p class="units-and-quantity">{{purchase.quantity}} {{purchase.units}}</p>

                    <div>
                        <ul class="tag-list">
                            <li v-for="product in purchase.products">
                                <a href="javascript:void(0)" class="tags product-tag">
                                    {{product.company}} :
                                    {{product.name ? product.name : 'unspecified product'}}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <p class="remark">
                        Remark: {{purchase.remark}}
                    </p>
                </td>
                <td class="total-price">
                    {{purchase.total_price | currency('Rub')}}
                </td>
            </tr>
        </table>
    </div>
</template>

<script>

    import labscapeFilters from '../filters/labscape-filters';

    export default {

        mixins: [labscapeFilters],

        data: function() {
            return {
                tenderData: {
                    purchases: []
                }
            }
        },

        watch: {
            incomingTenderData:function (newData) {
                this.tenderData = newData;
            }
        },

        methods: {

        },

        props: ['incomingTenderData']
    }
</script>

<style scoped>

</style>