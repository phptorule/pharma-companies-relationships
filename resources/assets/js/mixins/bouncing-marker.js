const framesPerSecond = 15;
const initialOpacity = 1;
let opacity = initialOpacity;
const initialRadius = 7;
let radius = initialRadius;
const maxRadius = 18;


const bouncingMarker = {

    data: function(){
        return {
            bouncingTimeoutId: null
        }
    },

    methods: {
        animateMarker: function() {
            this.bouncingTimeoutId = setTimeout(() => {
                requestAnimationFrame(this.animateMarker);

                if (!this.map.getLayer('hovered-point')) {
                    return;
                }

                radius += (maxRadius - radius) / framesPerSecond;
                opacity -= ( .9 / framesPerSecond );

                if (opacity <= 0) {
                    radius = initialRadius;
                    opacity = initialOpacity;
                }

                this.map.setPaintProperty('hovered-point', 'circle-radius', radius);
                this.map.setPaintProperty('hovered-point', 'circle-opacity', opacity);

            }, 1000 / framesPerSecond);

        },

        addHoveredPoint: function(address) {

            let mapData = this.composeMapData(address);

            let featureCollection = {
                "type": "FeatureCollection",
                "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
                "features": mapData
            };

            this.map.addSource("earthquakes2", {
                type: "geojson",
                data: featureCollection,
                cluster: true,
                clusterMaxZoom: 14, // Max zoom to cluster points on
                clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
            });

            this.map.addLayer({
                id: "hovered-point",
                type: "circle",
                source: "earthquakes2",
                filter: ["!has", "point_count"],
                paint: {
                    'circle-color': 'red',
                    "circle-radius": initialRadius,
                    "circle-stroke-width": 1,
                    "circle-stroke-color": "#fff"
                }
            }, 'unclustered-point');

            this.map.addLayer({
                id: "hovered-point-solid",
                type: "circle",
                source: "earthquakes2",
                filter: ["!has", "point_count"],
                paint: {
                    'circle-color': ['get', 'customer_status_color'],
                    "circle-radius": initialRadius,
                    "circle-stroke-width": 1,
                    "circle-stroke-color": "#fff"
                }
            }, 'unclustered-point');
        },

        listenToHoveringOverAddressAtSidebar: function () {
            this.$eventGlobal.$on('hover-over-address-at-the-sidebar', (address) => {

                this.addHoveredPoint([address]);

                this.animateMarker(0);
            })
        },

        listenToHoverOutFromSidebar: function () {
            this.$eventGlobal.$on('hover-out-from-the-sidebar', ()=>{

                clearTimeout(this.bouncingTimeoutId);

                if (this.map.getLayer('hovered-point')) {
                    this.map.removeLayer('hovered-point');
                    this.map.removeLayer('hovered-point-solid');
                    this.map.removeSource('earthquakes2');
                }

            })
        }
    }
};


export default bouncingMarker;