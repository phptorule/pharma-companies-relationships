<template>
    <div class="slided-box-content">

        <a href="javascript:void(0)" class="close-icon-a" @click="closeSlidedBox()">
            <img src="/images/x.png" alt="">
        </a>

        <h3 class="">
            All products
            <a data-v-cd5686be="" href="#">
                <i data-v-cd5686be="" class="fa fa-pencil"></i>
            </a>
        </h3>

        <ul class="staff-list">
            <li v-for="(product, i) in products">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductDetailsModal(addressId, product.id, addressData)">
                        <span class="person-initials">{{getProductName(product.name? product.name : product.company)}}</span>
                        <img :src="product.image? product.image : '/images/mask-0.png'" alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name">
                        <a href="javascript:void(0)"
                           @click="showProductDetailsModal(addressId, product.id, addressData)">
                            {{product.name? product.name : "unspecified "+product.company+"-product"}}
                        </a>
                    </p>
                    <div class="amount">
                        <div class="volume">
                            <span class="volume-head" :title="product.unit">
                                {{product.volume  | volume(product.unit)}}
                                <span class="volume-title">Est. Test Volume</span>
                            </span>
                        </div>
                        <div class="spending">
                            <span class="spending-head">
                                {{product.total_spent | currency}}
                                <span class="spending-title">2y Spending (K Rub)</span>
                            </span>
                        </div>
                        <div class="last-tender">
                                <span class="last-tender-head">
                                    {{product.last_tender_date ? product.last_tender_date : ''}}
                                <span class="last-tender-title">Last Tender</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="prod-graf" :id="'graph-container-modal-'+i" style="width: 75px; height: 50px">
                    <div class="load-spinner-charts-product"></div>
                </div>
            </li>
        </ul>

        <div class="pagination-box">
            <pagination :records="productTotal"  :class="'pagination pagination-sm no-margin pull-right'" :per-page="perPage" @paginate="pageChanged"></pagination>
        </div>

    </div>
</template>

<script>

    import http from '../mixins/http';
    import ProductModal from '../mixins/show-product-details-modal';
    import getProductName from '../mixins/get-product-name';

    export default {
        mixins: [http, ProductModal, getProductName],
        data: function () {
            return {
                products: [],
                productTotal: 0,
                currentPage: 1,
                perPage: 10
            }
        },
        filters: {
            currency: function (value, currency_type) {
                if (!currency_type) {
                    currency_type = '';
                }
                value = Math.ceil(Number(value) / 1000);
                value = String(value);
                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' ' + currency_type;
            },

            volume: function (value, volume) {
                if(!volume) {
                    volume = '';
                }

                var volumeName = '';

                let arr = volume.split(' ');
                if(arr.length) {
                    volumeName += arr[0].charAt(0).toUpperCase();
                    volumeName += arr[0].charAt(1);
                    volumeName += '.';
                    if(arr[1]){
                        volumeName += ' ' + arr[1].charAt(0).toUpperCase();
                        volumeName += ' ' + arr[1].charAt(1);
                        volumeName += '.';
                    }
                    if(arr[2]){
                        volumeName += ' ' + arr[2].charAt(0).toUpperCase();
                        volumeName += ' ' + arr[2].charAt(1);
                        volumeName += '.';
                    }
                }

                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' ' + volumeName;
            },
        },
        methods: {
            computedICounter(i){
                return 'graph-container-modal-' + i;
            },
            loadProductsPaginated: function (id, page) {
                let p = page || 1;

                // init current page for correct pagination and graph indexes
                this.currentPage = p;

                try {
                    let o = JSON.parse(localStorage.getItem('productsPaginatedFirstPage'));

                    if (o && typeof o === "object" && p == 1) {
                        if(o.data && o.total)
                        {
                            this.products = o.data;
                            this.productTotal = o.total;

                            for (let key in this.products) {
                                this.dataCreateToChart(this.products[key].prod_id, key)
                            }
                        }
                    } else {
                        return this.httpGet('/api/product-by-address/'+id+'?page='+p)
                            .then(data => {
                                if(data.data && data.total) {
                                    this.products = data.data;
                                    this.productTotal = data.total;

                                    for (let key in this.products) {
                                        this.dataCreateToChart(this.products[key].prod_id, key)
                                    }
                                }
                            })
                    }
                }
                catch (e) { }
            },
            dataCreateToChart: function (productId, indexOrder) {
                setTimeout(() => {
                    var url = '/api/tenders-by-product-chart/' + productId + '/' + this.addressData.id;

                    this.httpGet(url)
                        .then(data => {

                            var title = ['Month', 'Total', {type: 'string', role: 'tooltip', 'p': {'html': true}}];

                            var DATA = data.chartsData;

                            DATA.unshift(title);

                            this.viewTendersChart(DATA, 'graph-container-modal-' + indexOrder);
                        });
                }, 1000)
            },
            pageChanged: function (pageNumber) {
                this.loadProductsPaginated(this.addressId, pageNumber);
            },
            viewTendersChart: function (data, element_id) {
                    $('#' + element_id).html('');

                    var data = google.visualization.arrayToDataTable(data);

                    var options = {
                        title: '',
                        vAxis: {title: '', gridlines: {color: '#fff', count: 0}},
                        hAxis: {baselineColor: 'none', ticks: []},
                        seriesType: 'bars',
                        legend: 'none',
                        enableInteractivity: false,
                        tooltip: {trigger: 'none'},
                        series: {0: {type: 'line'}}
                    };

                    var chart = new google.visualization.ComboChart(document.getElementById(element_id));

                    chart.draw(data, options);
            },

            closeSlidedBox: function () {
                this.$emit('closeSlidedBox')
            }
        },

        props: ['addressId', 'purchaseId', 'addressData'],

        mounted: function () {
            if (this.addressId){
                this.loadProductsPaginated(this.addressId);
            }
        }
    }
</script>

<style scoped>

</style>