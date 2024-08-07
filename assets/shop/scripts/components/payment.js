import Vue from 'vue';
import eventBus from "../services/event-bus";
import proxyRepository from "../repositories/proxyRepository";
import cartRepository from "../repositories/cartRepository";

Vue.component('payment', {
	data(){
		return{
      payment_error: false,
      technical_error: false,
		}
	},
  mounted() {
    // erreur de paiement
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    if(urlParams.get('payment_error')=='1'){
      this.payment_error = true;
    }
    else if(urlParams.get('payment_error')=='2'){
      this.technical_error = true;
    }


    // redirection auto / bypass
    const body = document.querySelector('body');
    if (body.classList.contains('template-checkout-select-payment')) {
      var buttonNextStep = document.getElementById("next-step");
      buttonNextStep.click();
    }
    else if (body.classList.contains('template-order-show')) {
      var buttonNextStep = document.getElementById("sylius-pay-link");
      //buttonNextStep.click();
      window.location.href = "/order/thank-you";
    } else if (body.classList.contains('template-checkout-complete')) {
      // Automatically submit the form if on the checkout complete page
      const form = document.querySelector('form[name="sylius_checkout_complete"]');
      const loader = document.getElementById('loader');
      const formContainer = document.getElementById('form-container');
      // Show the loader and hide the form
      loader.style.display = 'block';
      formContainer.style.display = 'none';

      if (form) {
        form.submit();
      }
    }

  }
});
