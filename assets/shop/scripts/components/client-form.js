import Vue from 'vue';

Vue.component('client-form', {
  data() {
    return {
      firstName: '',
      lastName: '',
      street: '',
      postcode: '',
      city: '',
      phoneNumber: '',
      email: '',
      isEditing: true,
      cguCheckbox: false,
      errors: {
        phoneNumber: '',
        cguCheckbox: '',
        general: ''
      }
    };
  },
  methods: {
    startEditing() {
      this.isEditing = true;
    },
    validatePhoneNumber(phoneNumber) {
      const phoneRegex = /[0-9+]{10,13}/;
      return phoneRegex.test(phoneNumber);
    },
    validateForm() {
      let isValid = true;
      this.errors = { phoneNumber: '', cguCheckbox: '', general: '' };

      if (!this.firstName || !this.lastName || !this.street || !this.postcode || !this.city || !this.phoneNumber || !this.email) {
        this.errors.general = 'Tous les champs sont obligatoires.';
        isValid = false;
      }

      if (!this.validatePhoneNumber(this.phoneNumber)) {
        this.errors.phoneNumber = 'Numéro de téléphone invalide. Veuillez entrer un numéro de téléphone valide.';
        isValid = false;
      }

      if (!this.cguCheckbox) {
        this.errors.cguCheckbox = 'Veuillez sélectionner cette case.';
        isValid = false;
      }

      return isValid;
    },
    closeBlock() {
      if (this.validateForm()) {
        this.isEditing = false;
      } else {
        this.isEditing = true;
      }
    }
  }
});
