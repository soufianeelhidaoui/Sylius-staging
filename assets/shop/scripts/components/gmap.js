import Vue from 'vue';
import GoogleMapsApiLoader from 'google-maps-api-loader';
import proxyRepository from "../repositories/proxyRepository";
import eventBus from "../services/event-bus";
import { MarkerClusterer } from "@googlemaps/markerclusterer";

Vue.component('gmap', {
    props:['apiKey', 'brand', 'pin', 'color', 'skra', 'skdi'],
    data(){
        return{
            google: false,
            search:'',
            showResults: false,
            config:{
                style:[
                    {
                        "featureType": "poi",
                        "stylers": [
                            {
                                "visibility": "off"
                            }
                        ]
                    }
                ],
                map: {
                    center: { lat: 46.603354, lng: 1.888334 },
                    zoom: 6,
                    mapTypeControl: false,
                    zoomControl: true,
                    scaleControl: false,
                    streetViewControl: false,
                    fullscreenControl: false
                },
                autocomplete:{
                    componentRestrictions: { country: "fr" },
                    strictBounds: false
                }
            },
            autocomplete: null,
            map: null,
            markers: [],
            closestDealers: [],
            selectedDealer: null,
            skoda:{
                cardStyle: {
                    backgroundColor: '#0E3A2F',
                    color: 'white',
                }
            },
            seat:{
                cardStyle: {
                    backgroundColor: '#EA5D1B',
                    color: 'white'
                }
            },
            cupra:{
                cardStyle: {
                    backgroundColor: '#003C4A',
                    color: 'white'
                }
            },
            vw:{
                cardStyle: {
                    backgroundColor: '#011E50',
                    color: 'white'
                }
            },
            vwu:{
                cardStyle: {
                    backgroundColor: '#011E50',
                    color: 'white'
                }
            }
        }
    },
    computed:{
        filtered(){
            if( this.search.length >= 1 ){

                return this.markers.filter(marker=>{
                    return marker.title.toLowerCase().indexOf(this.search.toLowerCase()) !== -1 || marker.postal_code.toString().indexOf(this.search) !== -1 || marker.city.toLowerCase().indexOf(this.search.toLowerCase()) !== -1
                })
            }
            else{
                return [];
            }
        }
    },
    methods:{
        createMarker(dealer){

            // Set the coordinates of the new point
            let latLng = new google.maps.LatLng(dealer.position.lat,dealer.position.lng);

            //Check Markers array for duplicate position and offset a little
            if(this.markers.length) {
                for (let i=0; i < this.markers.length; i++) {
                    let existingMarker = this.markers[i];
                    let pos = existingMarker.getPosition();
                    if (latLng.equals(pos)) {
                        let a = 360.0 / this.markers.length;
                        let newLat = pos.lat() + -.00004 * Math.cos((+a*i) / 180 * Math.PI);  //x
                        let newLng = pos.lng() + -.00004 * Math.sin((+a*i) / 180 * Math.PI);  //Y
                        latLng = new google.maps.LatLng(newLat,newLng);
                    }
                }
            }

            let params = {
                position: latLng,
                marker: dealer,
                map: this.map,
                icon: this.pin
            };

            const marker = new this.google.maps.Marker(params)

            marker.addListener("click", async () => {
                this.selectedDealer = dealer?.address;
                this.closestDealers = [];
                await this.markers.forEach(marker => {
                    marker.setIcon(this.pin);
                });
                marker.setIcon(`/assets/shop/img/pin-${this.brand}.svg`);
                
                this.closestDealers = [marker];
                eventBus.$emit('dealer', dealer);
                this.map.panTo(dealer.position);
                if (this.map.getZoom() < 8) {
                    this.map.setZoom(8);
                }

                let event = new CustomEvent('dealerSelected', { detail: marker });
				document.dispatchEvent(event);
            });

            return marker;
        },
        async initializeMap(){

            const mapContainer = this.$refs.googleMap

            this.map = new this.google.maps.Map(mapContainer, this.config.map)

            const styledMapType = new google.maps.StyledMapType(this.config.style)
            this.map.mapTypes.set("styled_map", styledMapType);
            this.map.setMapTypeId("styled_map");

            const autocompleteContainer = this.$refs.autocomplete
            this.autocomplete = new google.maps.places.Autocomplete(autocompleteContainer, this.config.autocomplete);

            await proxyRepository.getDealers(this.brand).then(dealers=>{

                dealers.forEach(dealer=>{
                    this.markers.push(this.createMarker(dealer))
                })

                let color = this.color

                new MarkerClusterer({map:this.map, markers: this.markers, renderer:{
                        render({ count, position }, stats) {
                            const svg = window.btoa(`<svg fill="${color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240"><circle cx="120" cy="120" r="110" /></svg>`);
                            return new google.maps.Marker({
                                position,
                                icon: {
                                    url: `data:image/svg+xml;base64,${svg}`,
                                    scaledSize: new google.maps.Size(45, 45),
                                },
                                label: {
                                    text: String(count),
                                    color: "#fff",
                                    fontSize: "18px",
                                    fontWeight: "bold",
                                },
                                zIndex: Number(google.maps.Marker.MAX_ZINDEX) + count,
                            });
                        }
                    }});
            })

            this.autocomplete.addListener("place_changed", () => {
              this.closestDealers = [];

              const place = this.autocomplete.getPlace();

              this.markers.forEach((marker) => {
                const markerPosition = marker.getPosition();
                const distance =
                  this.google.maps.geometry.spherical.computeDistanceBetween(
                    place.geometry.location,
                    markerPosition
                  );
                const distanceInKilometers = distance / 1000; // Convert meters to kilometers
                marker.distance = distanceInKilometers.toFixed(1);
              });

              this.markers.sort((a, b) => a.distance - b.distance);

              this.closestDealers = this.markers.slice(0, 10);

              if (!place.geometry || !place.geometry.location) {
                //window.alert("No details available for input: '" + place.name + "'");
                return;
              }

              if (place.geometry.viewport) {
                this.map.fitBounds(place.geometry.viewport);
              } else {
                this.map.setCenter(place.geometry.location);
                this.map.setZoom(17);
              }
            });
            // Get user's current location
            this.getUserLocation();
        },
        select(dealer){

            this.showResults = true;

            if (dealer.icon) {
                this.selectedDealer = dealer.marker.address;
                eventBus.$emit('dealer', dealer.marker);
            }else{
                eventBus.$emit('dealer', dealer);
            }
            
            this.map.panTo(dealer.position);
            this.map.setZoom(17);

        },
        isSelected(dealer) {
            const address = dealer?.marker?.address || dealer?.address;
                if (this.selectedDealer === address) {
                    dealer.setIcon(`/assets/shop/img/pin-${this.brand}.svg`);
                }else{
                    dealer.icon = this.pin;
                }
            
            return this.selectedDealer === address;
        },
        getCardStyle() {
            // Return the appropriate card style based on the 'brand' prop
            const brand = this.brand;
            if (brand && this.hasOwnProperty(brand)) {
                return this[brand].cardStyle;
            } else {
                return {};
            }
        },
        getPhoneStyle(dealer) {
            if (this.isSelected(dealer)) {
                return { filter: 'brightness(0) invert(1) !important' };
            } else {
                return {};
            }
        },
        getDealerCardStyle(dealer) {
            if (this.isSelected(dealer) && this.closestDealers.length == 1) {
              return { height: 'fit-content', ...this.getCardStyle() };
            } else {
              return this.isSelected(dealer) ? this.getCardStyle() : (this.closestDealers.length == 1 ? { height: 'fit-content', ...this.getCardStyle() } : {});
            }
        },
        getUserLocation() {
            // Check if geolocation is supported
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };

                        this.map.setCenter(userLocation);

                        // Reverse geocode to obtain address
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ location: userLocation }, (results, status) => {
                            if (status === "OK") {
                                if (results[0]) {
                                    const formattedAddress = results[0].formatted_address;
                                    const autocompleteContainer = this.$refs.autocomplete;

                                    // Update the input field with the obtained address
                                    autocompleteContainer.value = formattedAddress;

                                    // Calculate distances to dealers
                                    this.markers.forEach((marker) => {
                                        const markerPosition = marker.getPosition();
                                        const distance = google.maps.geometry.spherical.computeDistanceBetween(
                                            userLocation,
                                            markerPosition
                                        );
                                        const distanceInKilometers = distance / 1000;
                                        marker.distance = distanceInKilometers.toFixed(1);
                                    });

                                    this.markers.sort((a, b) => a.distance - b.distance);

                                    this.closestDealers = this.markers.slice(0, 10);
                                } else {
                                    throw new Error("No address found");
                                }
                            } else {
                                throw new Error("Geocoder failed due to: " + status);
                            }
                        });
                    },
                    (error) => {
                        throw new Error("Error getting user location:", error);
                    }
                );
            } else {
                throw new Error("Geolocation is not supported in this browser");
            }
        }
    },
    mounted(){

        GoogleMapsApiLoader({apiKey: this.apiKey, libraries:['places','geometry',]}).then(googleMapApi=>{
            this.google = googleMapApi
            this.initializeMap()
        })
    }
});