import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

const EXPIRY_TIME_LOCALSTORAGE = window?.appConfig?.dataExpiryTimeMs || 5260032000;

let store = new Vuex.Store({
  state(){
    return {
      taxMode: 'ttc',
      vehicle: localStorage.getItem('vehicle'),
      version: localStorage.getItem('version'),
      partner: JSON.parse(localStorage.getItem('partner')),
      partner_data: JSON.parse(localStorage.getItem('partner_data')),
      vehicleId: localStorage.getItem('vehicleId'),
      cart: {
        items: []
      }
    }
  },
  getters: {
    taxMode: state => () =>{
      return state.taxMode;
    },
    cart: state => () =>{
      return state.cart;
    },
    partner: state => () =>{
      return state.partner;
    },
    partner_data: state => () => {
      const partnerData = state.partner_data;
      if (partnerData && partnerData.timestamp > Date.now()) {
        return partnerData;
      } else {
        localStorage.removeItem('partner_data');
        localStorage.removeItem('partner');
        return null;
      }
    },
    vehicle: state => () =>{
      return state.vehicle !== 'false' && state.vehicle !== 'undefined' ? state.vehicle : false;
    },
    version: state => () =>{
      return state.version !== 'false' && state.version !== 'undefined' ? state.version : false;
    },
    vehicleId: state => () =>{
      return state.vehicleId !== 'false' && state.vehicleId !== 'undefined' ? state.vehicleId : false;
    }
  },
  mutations:{
    cart(state,cart){
      state.cart = cart;
    },
    taxMode(state,taxMode){
      state.taxMode = taxMode;
    },
    vehicle(state,vehicle){
      localStorage.setItem('vehicle', vehicle);
      state.vehicle = vehicle;
    },
    partner(state,partner){
      localStorage.setItem('partner', JSON.stringify(partner));
      state.partner = partner;
    },
    partner_data(state,partner_data){
      const timestamp = Date.now() + EXPIRY_TIME_LOCALSTORAGE;
      state.partner_data = { ...partner_data, timestamp };
      localStorage.setItem('partner_data', JSON.stringify(state.partner_data));
    },
    version(state,version){
      localStorage.setItem('version', version);
      state.version = version;
    },
    vehicleId(state,vehicleId){
      localStorage.setItem('vehicleId', vehicleId);
      state.vehicleId = vehicleId;
    }
  }
});

export default store;
