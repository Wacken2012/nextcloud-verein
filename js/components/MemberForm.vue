<template>
  <div class="member-form">
    <h3>{{ isEdit ? 'Mitglied bearbeiten' : 'Neues Mitglied' }}</h3>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="name">Name *</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          placeholder="Max Mustermann"
          required
          :class="{ 'error': errors.name }"
        />
        <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
      </div>

      <div class="form-group">
        <label for="address">Adresse</label>
        <textarea
          id="address"
          v-model="form.address"
          placeholder="Musterstraße 123&#10;12345 Musterstadt"
          rows="3"
        ></textarea>
      </div>

      <div class="form-group">
        <label for="email">E-Mail *</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="max@example.com"
          required
          :class="{ 'error': errors.email }"
        />
        <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="iban">IBAN *</label>
          <input
            id="iban"
            v-model="form.iban"
            type="text"
            placeholder="DE89370400440532013000"
            required
            maxlength="34"
            :class="{ 'error': errors.iban }"
            @blur="validateIban"
          />
          <span v-if="errors.iban" class="error-message">{{ errors.iban }}</span>
        </div>

        <div class="form-group">
          <label for="bic">BIC *</label>
          <input
            id="bic"
            v-model="form.bic"
            type="text"
            placeholder="COBADEFFXXX"
            required
            maxlength="11"
            :class="{ 'error': errors.bic }"
            @blur="validateBic"
          />
          <span v-if="errors.bic" class="error-message">{{ errors.bic }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="role">Rolle *</label>
        <select id="role" v-model="form.role" required>
          <option value="member">Mitglied</option>
          <option value="treasurer">Kassierer</option>
          <option value="board">Vorstand</option>
        </select>
      </div>

      <div class="form-buttons">
        <button type="submit" class="button primary" :disabled="!isFormValid">
          {{ isEdit ? 'Speichern' : 'Anlegen' }}
        </button>
        <button type="button" class="button" @click="handleCancel">
          Abbrechen
        </button>
      </div>
    </form>
  </div>
</template>

<script>
/**
 * Generate Vue component with form validation for member data
 * Including IBAN and BIC validation
 */
export default {
  name: 'MemberForm',
  props: {
    member: {
      type: Object,
      default: null
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      form: {
        name: '',
        address: '',
        email: '',
        iban: '',
        bic: '',
        role: 'member'
      },
      errors: {
        name: '',
        email: '',
        iban: '',
        bic: ''
      }
    }
  },
  computed: {
    isFormValid() {
      return (
        this.form.name &&
        this.form.email &&
        this.form.iban &&
        this.form.bic &&
        !this.errors.name &&
        !this.errors.email &&
        !this.errors.iban &&
        !this.errors.bic
      )
    }
  },
  watch: {
    member: {
      immediate: true,
      handler(newMember) {
        if (newMember) {
          this.form = { ...newMember }
        } else {
          this.resetForm()
        }
      }
    }
  },
  methods: {
    validateIban() {
      const iban = this.form.iban.replace(/\s/g, '').toUpperCase()
      this.form.iban = iban

      // Basic IBAN validation
      if (!iban) {
        this.errors.iban = 'IBAN ist erforderlich'
        return false
      }

      if (iban.length < 15 || iban.length > 34) {
        this.errors.iban = 'IBAN hat eine ungültige Länge'
        return false
      }

      if (!/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/.test(iban)) {
        this.errors.iban = 'IBAN hat ein ungültiges Format'
        return false
      }

      // Country-specific length validation
      const countryLengths = {
        DE: 22, AT: 20, CH: 21, FR: 27, IT: 27, NL: 18, BE: 16, ES: 24
      }
      const country = iban.substring(0, 2)
      const expectedLength = countryLengths[country]

      if (expectedLength && iban.length !== expectedLength) {
        this.errors.iban = `IBAN für ${country} muss ${expectedLength} Zeichen haben`
        return false
      }

      this.errors.iban = ''
      return true
    },

    validateBic() {
      const bic = this.form.bic.replace(/\s/g, '').toUpperCase()
      this.form.bic = bic

      if (!bic) {
        this.errors.bic = 'BIC ist erforderlich'
        return false
      }

      // BIC validation: 8 or 11 characters
      if (!/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/.test(bic)) {
        this.errors.bic = 'BIC hat ein ungültiges Format (z.B. COBADEFFXXX)'
        return false
      }

      this.errors.bic = ''
      return true
    },

    validateEmail() {
      const email = this.form.email
      if (!email) {
        this.errors.email = 'E-Mail ist erforderlich'
        return false
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        this.errors.email = 'E-Mail hat ein ungültiges Format'
        return false
      }

      this.errors.email = ''
      return true
    },

    validateName() {
      if (!this.form.name || this.form.name.trim().length < 2) {
        this.errors.name = 'Name muss mindestens 2 Zeichen lang sein'
        return false
      }

      this.errors.name = ''
      return true
    },

    validateForm() {
      const isNameValid = this.validateName()
      const isEmailValid = this.validateEmail()
      const isIbanValid = this.validateIban()
      const isBicValid = this.validateBic()

      return isNameValid && isEmailValid && isIbanValid && isBicValid
    },

    handleSubmit() {
      if (this.validateForm()) {
        this.$emit('submit', { ...this.form })
      }
    },

    handleCancel() {
      this.$emit('cancel')
      this.resetForm()
    },

    resetForm() {
      this.form = {
        name: '',
        address: '',
        email: '',
        iban: '',
        bic: '',
        role: 'member'
      }
      this.errors = {
        name: '',
        email: '',
        iban: '',
        bic: ''
      }
    }
  }
}
</script>

<style scoped>
.member-form {
  background: #f5f5f5;
  padding: 20px;
  margin: 20px 0;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 15px;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

input,
textarea,
select {
  display: block;
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.2s;
}

input:focus,
textarea:focus,
select:focus {
  outline: none;
  border-color: #0082c9;
}

input.error,
textarea.error {
  border-color: #e9322d;
}

.error-message {
  display: block;
  color: #e9322d;
  font-size: 12px;
  margin-top: 4px;
}

.form-buttons {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}

button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.button.primary {
  background-color: #0082c9;
  color: white;
}

.button.primary:hover:not(:disabled) {
  background-color: #006ba3;
}

.button.primary:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.button {
  background-color: #e0e0e0;
  color: #333;
}

.button:hover {
  background-color: #d0d0d0;
}
</style>
