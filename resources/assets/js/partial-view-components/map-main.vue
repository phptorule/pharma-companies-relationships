<template>
    <div>
        <div id="map-element"></div>
    </div>
</template>

<script>
    var mapboxgl = require('mapbox-gl');
    var geojsonExtent = require('@mapbox/geojson-extent');

    import http from '../mixins/http';
    import bouncingMarker from '../mixins/bouncing-marker';
    import employeeModal from '../mixins/show-employee-details-modal';

    export default {

        mixins: [http, bouncingMarker, employeeModal],

        data: function () {
            return {
                map: null,
                FeatureCollection: {
                    features: []
                },
                isFirstLoad: true,
                isGlobalSearchInitator: false,
                cluster: {},
                popup: new mapboxgl.Popup({
                    closeButton: false,
                    closeOnClick: false
                }),
                mapZoom: null,
                mapCenterLng: null,
                mapCenterLat: null,
                moveendId: null,
                isMapMovedBecauseOfSearch: true,
                isMapMovedBecauseOfFitMapContent: true,
                oldNotificationBlockHeight: 0
            }
        },

        watch: {
            $route: function (to, from) {

                let zoom = this.$route.query['zoom'];
                let centerLng = this.$route.query['center-lng'];
                let centerLat = this.$route.query['center-lat'];

                if (!zoom && !centerLng && !centerLat && this.$route.path == '/dashboard' && this.FeatureCollection.features.length) {
                    this.fitMapBounds();
                    return;
                }
                else if (!zoom || !centerLng || !centerLat) {
                    return;
                }

                if (zoom != this.mapZoom || centerLng != this.mapCenterLng || centerLat != this.mapCenterLat) {
                    this.map.flyTo({center: [centerLng, centerLat], zoom: zoom});
                }
            }
        },

        methods: {

            loadAddresses: function (queryString, isGlobalSearchInitiator) {
                return this.makeHttpCall('addresses', queryString, isGlobalSearchInitiator);
            },

            loadPeople: function (queryString, isGlobalSearchInitiator) {
                return this.makeHttpCall('people-for-map', queryString, isGlobalSearchInitiator);
            },

            makeHttpCall: function(baseUrl, queryString, isGlobalSearchInitiator) {
                if (isGlobalSearchInitiator) {

                    let url = '/api/'+baseUrl+'?global-search=' + encodeURIComponent(this.$route.query['global-search']);

                    return this.httpGet(url);
                }

                return this.httpGet('/api/'+ baseUrl + (queryString || ''));
            },

            composeMapData: function (data) {

                var mapData = [];

                for (var i = 0; i < data.length; i++) {

                    var adr = data[i];

                    mapData[i] = {
                        "type": "Feature",
                        "properties": {
                            "id": adr.id,
                            "mag": 2.3,
                            "time": 1507425650893,
                            "felt": null,
                            "tsunami": 0,
                            "name": adr.name,
                            "people_count": adr.people_count,
                            "customer_status_color": adr.customer_status == 2 ? '#34cc8c' : '#ff894f',
                        },
                        "geometry": {
                            "type": "Point",
                            "coordinates": [adr.lon, adr.lat, 0.0]
                        }
                    };
                }

                return mapData;
            },

            provideDataToMap: function (data) {

                let mapData = this.composeMapData(data);

                this.FeatureCollection = {
                    "type": "FeatureCollection",
                    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
                    "features": mapData
                };

                this.map.addSource("earthquakes", {
                    type: "geojson",
                    data: this.FeatureCollection,
                    cluster: true,
                    clusterMaxZoom: 14, // Max zoom to cluster points on
                    clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
                });
            },

            addClustersLayer: function () {
                this.map.addLayer({
                    id: "clusters",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["has", "point_count"],
                    paint: {
                        // Use step expressions (https://www.mapbox.com/mapbox-gl-js/style-spec/#expressions-step)
                        // with three steps to implement three types of circles:
                        //   * Blue, 20px circles when point count is less than 100
                        //   * Yellow, 30px circles when point count is between 100 and 750
                        //   * Pink, 40px circles when point count is greater than or equal to 750
                        "circle-color": '#51bbd6',
                        "circle-radius": [
                            "step",
                            ["get", "point_count"],
                            20,
                            100,
                            30,
                            750,
                            40
                        ],
                        "circle-stroke-width": 2,
                        "circle-stroke-color": "#fff"
                    }
                });
            },

            addClustersCountLayer: function () {
                this.map.addLayer({
                    id: "cluster-count",
                    type: "symbol",
                    source: "earthquakes",
                    filter: ["has", "point_count"],
                    layout: {
                        "text-field": "{point_count_abbreviated}",
                        "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
                        "text-size": 14,
                    },
                    paint: {
                        "text-color": "#ffffff",
                    }
                });
            },

            addUnclusteredPointLayer: function () {
                this.map.addLayer({
                    id: "unclustered-point",
                    type: "circle",
                    source: "earthquakes",
                    filter: ["!has", "point_count"],
                    paint: {
                        'circle-color': ['get', 'customer_status_color'],
                        "circle-radius": 7,
                        "circle-stroke-width": 1,
                        "circle-stroke-color": "#fff"
                    }
                });
            },

            fitMapBounds: function () {
                this.isMapMovedBecauseOfFitMapContent = true;
                this.map.fitBounds(geojsonExtent(this.FeatureCollection), {maxZoom: 12, padding: 50});
            },

            initMap: function () {
                mapboxgl.accessToken = document.head.querySelector('meta[name="mapbox-key"]').content;

                this.map = new mapboxgl.Map({
                    container: 'map-element',
                    style: 'mapbox://styles/mapbox/light-v9',
                    center: this.COUNTRY_FEATURES['default-map-coords'],
                    zoom: this.COUNTRY_FEATURES['default-map-zoom']
                });
            },

            countDisplayedMarkers: function () {

                var timeoutId;

                this.map.on('move', (e) => {

                    if (timeoutId) {
                        clearTimeout(timeoutId)
                    }

                    timeoutId = setTimeout(()=>{

                        let totalPointsDisplayed = 0;

                        let uniqueSingleFeatureIds = [];
                        let uniqueClusterIds = [];

                        let unclusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                            layers: ['unclustered-point']
                        });

                        for (let i = 0; i < unclusteredFeatures.length; i++) {
                            if (uniqueSingleFeatureIds.indexOf(unclusteredFeatures[i].properties.id) === -1) {
                                totalPointsDisplayed++;
                                uniqueSingleFeatureIds.push(unclusteredFeatures[i].properties.id);
                            }
                        }

                        let clusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                            layers: ['clusters']
                        });

                        for (let i = 0; i < clusteredFeatures.length; i++) {
                            if (uniqueClusterIds.indexOf(clusteredFeatures[i].properties.cluster_id) === -1) {
                                totalPointsDisplayed += clusteredFeatures[i].properties.point_count;
                                uniqueClusterIds.push(clusteredFeatures[i].properties.cluster_id);
                            }
                        }

                        this.notifyTotalPointsDisplayedOnMapChanged(totalPointsDisplayed)

                    }, 500)

                });
            },

            listenToMouseMoves: function () {
                this.map.on('mousemove', (e) => {
                    let unclusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                        layers: ['unclustered-point']
                    });

                    let clusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                        layers: ['clusters']
                    });

                    if (unclusteredFeatures.length || clusteredFeatures.length) {
                        this.map.getCanvas().style.cursor = 'pointer';
                    }
                    else {
                        this.map.getCanvas().style.cursor = '';
                    }

                    this.displayTooltip(unclusteredFeatures)
                });
            },

            displayTooltip: function (unclusteredFeatures) {
                if (unclusteredFeatures.length) {
                    this.popup.setLngLat(unclusteredFeatures[0].geometry.coordinates)
                        .setHTML('<h3 class="address-name-in-map-tooltip">' + unclusteredFeatures[0].properties.name + '</h3>')
                        .addTo(this.map)
                }
                else {
                    this.popup.remove();
                }
            },

            handleClickOnUnclusteredFeature: function(feature) {
                let id = feature.properties.id;

                if (this.$route.path === '/people-dashboard') {

                    if (+feature.properties.people_count === 1) {

                        this.httpGet('/api/address-details/' + id)
                            .then(addressData => {
                                let personId = addressData.people[0].id;

                                this.showEmployeeDetailsModal(personId, addressData.id, addressData);
                            })

                    }
                    else if (+feature.properties.people_count > 1) {
                        this.$router.push('/address-details/'+id + '?all-employees=1');
                    }
                }
                else {
                    this.$router.push('/address-details/'+id);
                }
            },

            listenToMarkerClicks: function () {
                this.map.on('click', (e) => {

                    let unclusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                        layers: ['unclustered-point']
                    });

                    let clusteredFeatures = this.map.queryRenderedFeatures(e.point, {
                        layers: ['clusters']
                    });

                    if (unclusteredFeatures.length) {

                        this.handleClickOnUnclusteredFeature(unclusteredFeatures[0]);

                    }
                    else if (clusteredFeatures.length) {

                        let clusterId = clusteredFeatures[0].properties.cluster_id;

                        var allFeatures = this.cluster.getLeaves(clusterId, Math.floor(this.map.getZoom()), Infinity);

                        let ids = [];

                        for (var i = 0; i < allFeatures.length; ++i) {
                            ids.push(allFeatures[i].properties.id);
                        }

                        this.$root.logData('overview', 'map click', JSON.stringify(ids));

                        if(ids.length){
                            this.$router.push('/dashboard?address-ids=' + ids.toString());
                        }
                    }
                });
            },

            notifyTotalPointsDisplayedOnMapChanged: function (totalPoints) {
                this.$eventGlobal.$emit('totalPointsDisplayedOnMapChanged', totalPoints);
            },

            initDataSource: function (addressList) {

                this.provideDataToMap(addressList);

                this.addClustersLayer();

                this.addClustersCountLayer();

                this.addUnclusteredPointLayer();

                if (addressList.length) {
                    this.fitMapBounds();

                    this.countDisplayedMarkers();

                }
                else {
                    alertify.notify('No addresses have been found', 'warning', 3);
                    this.notifyTotalPointsDisplayedOnMapChanged(0)
                }
            },

            updateMapLayers: function (data) {
                ['clusters', 'cluster-count', 'unclustered-point'].forEach(el => {
                    if (this.map.getLayer(el)) this.map.removeLayer(el);
                });

                this.map.removeSource('earthquakes');

                this.initDataSource(data);

                this.cluster.load(this.FeatureCollection.features);
            },

            initSuperCluster: function () {
                var clusterRadius = 50;
                var clusterMaxZoom = 14;

                this.cluster = supercluster({
                    radius: clusterRadius,
                    maxZoom: clusterMaxZoom
                });
            },

            updateAddressBarWithMapCoords: function () {
                this.mapZoom = this.map.getZoom();
                this.mapCenterLng = this.map.getCenter().lng;
                this.mapCenterLat = this.map.getCenter().lat;

                let fullUrl = this.$route.fullPath;
                let hash = this.$route.hash;

                if(hash) {
                    fullUrl = (fullUrl.split('#'))[0];
                }

                if (fullUrl.indexOf('?') === -1) {
                    fullUrl += '?';
                }
                else {
                    fullUrl += '&';
                }

                if (fullUrl.indexOf('zoom') === -1) {
                    fullUrl += 'zoom=' + this.mapZoom + '&center-lng=' + this.mapCenterLng + '&center-lat=' + this.mapCenterLat;
                }
                else {
                    // debugger;

                    let arr;

                    if (fullUrl.indexOf('?') === -1) {
                        arr = fullUrl.split('&');
                    }
                    else {
                        arr = fullUrl.split('?');
                        arr = arr[1].split('&');
                    }

                    let zoom, centerLng, centerLat;

                    arr = arr.filter(el => {
                        if (el.indexOf('zoom') !== -1) {
                            zoom = 'zoom=' + this.mapZoom;
                            return false;
                        }
                        if (el.indexOf('center-lng') !== -1) {
                            centerLng = '&center-lng=' + this.mapCenterLng;
                            return false;
                        }
                        if (el.indexOf('center-lat') !== -1) {
                            centerLat = '&center-lat=' + this.mapCenterLat;
                            return false;
                        }
                        return true;
                    });

                    if (arr.length) {
                        fullUrl = this.$route.path + '?' + arr.join('&') + '&' + zoom + centerLng + centerLat;
                    }
                    else {
                        fullUrl = this.$route.path + '?' + zoom + centerLng + centerLat;
                    }
                }

                if (hash) {
                    fullUrl += hash;
                }

                this.$router.push(fullUrl);
            },

            detectMapMoveEnds: function () {
                this.map.on('moveend', (e) => {

                    if (this.moveendId) {
                        clearTimeout(this.moveendId);
                    }

                    this.moveendId = setTimeout(() => {

                        if (this.isMapMovedBecauseOfSearch) {

                            this.isMapMovedBecauseOfSearch = false;
                            this.isMapMovedBecauseOfFitMapContent = false;
                            return;
                        }

                        if (this.isMapMovedBecauseOfFitMapContent) {

                            this.isMapMovedBecauseOfFitMapContent = false;
                            this.isMapMovedBecauseOfSearch = false;
                            return;
                        }

                        this.updateAddressBarWithMapCoords();

                    }, 2000);

                })
            },

            setInitialMapViewFromQueryStr: function () {

                if(!this.isFirstLoad) {
                    return;
                }

                let zoom = this.$route.query['zoom'];
                let centerLng = this.$route.query['center-lng'];
                let centerLat = this.$route.query['center-lat'];

                if (zoom != this.mapZoom || centerLng != this.mapCenterLng || centerLat != this.mapCenterLat) {
                    this.map.flyTo({center: [centerLng, centerLat], zoom: zoom});
                }
            },

            proceedMapPreparationsForCurrentPage: function (data) {
                this.initDataSource(data);

                this.cluster.load(this.FeatureCollection.features);

                this.listenToMouseMoves();

                this.listenToMarkerClicks();

                this.detectMapMoveEnds();

                this.listenToHoveringOverAddressAtSidebar();

                this.listenToHoverOutFromSidebar();

                this.setInitialMapViewFromQueryStr();

                this.isFirstLoad = false;
            },

            notifyMapLoadedAndReady: function () {
                this.$eventGlobal.$emit('onMapLoadedAndReady', {});
            },

            fullMapInit: function() {
                $('#map-element').height(window.innerHeight - 70 - 51 - this.oldNotificationBlockHeight);
                this.initMap();

                this.initSuperCluster();

                this.map.on('load', () => {

                    let queryUrl = '';

                    let path = this.$route.path;

                    if(path === '/dashboard' || path.indexOf('/address-details') !== -1) {
                        queryUrl = path === '/dashboard' ? path.replace('/dashboard', '') : '';

                        this.loadAddresses(queryUrl)
                            .then(data => {
                                this.proceedMapPreparationsForCurrentPage(data);
                                this.notifyMapLoadedAndReady();
                            });
                    }
                    else if(this.$route.path === '/people-dashboard') {
                        queryUrl = this.$route.fullPath.replace('/people-dashboard', '');

                        this.loadPeople(queryUrl)
                            .then(data => {
                                this.proceedMapPreparationsForCurrentPage(data);
                                this.notifyMapLoadedAndReady();
                            });
                    }

                });
            }
        },

        mounted: function () {

            this.$eventGlobal.$on('first-time-notifications-were-shown', () => {
                setTimeout(()=>{
                    this.oldNotificationBlockHeight = $('.callout-notification-container').height();
                },0);
            });

            setTimeout(() => {
                this.fullMapInit();

                this.$eventGlobal.$on('filtersHaveBeenApplied', (queryStr) => {

                    this.isMapMovedBecauseOfSearch = true;

                    if(this.$route.path === '/dashboard') {
                        this.loadAddresses(queryStr)
                            .then((data) => {
                                this.updateMapLayers(data);
                            })
                    }
                    else if(this.$route.path === '/people-dashboard') {
                        this.loadPeople(queryStr)
                            .then(data => {
                                this.updateMapLayers(data);
                            });
                    }
                });

                this.$eventGlobal.$on('showSpecificItem', (data) => {

                    this.isMapMovedBecauseOfSearch = true;

                    this.updateMapLayers(data);
                });


                this.$eventGlobal.$on('notifications-were-shown', () => {

                    let height = $('.callout-notification-container').height();

                    if(this.oldNotificationBlockHeight !== height) {

                        setTimeout(() => {

                            $('#map-element').html('');

                            this.fullMapInit();
                        }, 0);

                        this.oldNotificationBlockHeight = height;
                    }
                });

            }, 1000);
        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('notifications-were-shown');
            this.$eventGlobal.$off('first-time-notifications-were-shown');
            this.$eventGlobal.$off('filtersHaveBeenApplied');
            this.$eventGlobal.$off('showSpecificItem');
        }

    }
</script>

<style scoped>

</style>