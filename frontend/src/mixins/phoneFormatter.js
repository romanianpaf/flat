export default {
  methods: {
    formatPhone(value) {
      // Dacă e un event, extragem valoarea
      const phoneValue = typeof value === 'string' ? value : value?.target?.value || '';
      
      // Elimină tot ce nu e cifră
      let phone = phoneValue.replace(/\D/g, '');
      
      // Dacă începe cu 40, păstrează-l
      // Altfel, dacă începe cu 0, înlocuiește cu 40
      if (phone.startsWith('0')) {
        phone = '40' + phone.substring(1);
      }
      
      // Formatează: +40 XXX XXX XXX
      let formatted = '';
      if (phone.length > 0) {
        formatted = '+';
        if (phone.length <= 2) {
          formatted += phone;
        } else if (phone.length <= 5) {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2);
        } else if (phone.length <= 8) {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2, 5) + ' ' + phone.substring(5);
        } else {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8, 11);
        }
      }
      
      return formatted;
    },
    
    handlePhoneInput(event, fieldName = 'phone') {
      // Pentru v-model binding
      this.form[fieldName] = this.formatPhone(event);
    }
  }
};

