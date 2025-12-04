<template>
  <div class="sepa-export">
    <button class="back-button" @click="goBack" title="Zurück zu Finanzen">
      ← Zurück
    </button>
    <h2>SEPA-Export</h2>
    
    <div class="form-container">
      <h3>SEPA-Lastschrift Daten</h3>
      <form @submit.prevent="generateSepa">
        <div class="form-group">
          <label for="creditorName">Gläubiger Name (Verein):</label>
          <input 
            id="creditorName"
            v-model="form.creditorName" 
            placeholder="Vereinsname" 
            required 
          />
        </div>
        
        <div class="form-group">
          <label for="creditorIban">Gläubiger IBAN:</label>
          <input 
            id="creditorIban"
            v-model="form.creditorIban" 
            placeholder="DE89370400440532013000" 
            required 
          />
        </div>
        
        <div class="form-group">
          <label for="creditorBic">Gläubiger BIC:</label>
          <input 
            id="creditorBic"
            v-model="form.creditorBic" 
            placeholder="COBADEFFXXX" 
            required 
          />
        </div>
        
        <div class="form-group">
          <label for="creditorId">Gläubiger-ID (SEPA):</label>
          <input 
            id="creditorId"
            v-model="form.creditorId" 
            placeholder="DE98ZZZ09999999999" 
            required 
          />
        </div>
        
        <div class="form-buttons">
          <button type="button" @click="preview" class="button">Vorschau</button>
          <button type="submit" class="button primary">SEPA-XML herunterladen</button>
        </div>
      </form>
    </div>

    <!-- Preview Section -->
    <div v-if="previewData" class="preview-container">
      <h3>Vorschau SEPA-Export</h3>
      <div class="preview-summary">
        <p><strong>Gläubiger:</strong> {{ previewData.creditorName }}</p>
        <p><strong>IBAN:</strong> {{ previewData.creditorIban }}</p>
        <p><strong>Anzahl Transaktionen:</strong> {{ previewData.transactionCount }}</p>
        <p><strong>Gesamtbetrag:</strong> {{ previewData.totalAmount.toFixed(2) }} €</p>
      </div>
      
      <h4>Transaktionen:</h4>
      <table>
        <thead>
          <tr>
            <th>Mitglied</th>
            <th>IBAN</th>
            <th>Betrag</th>
            <th>Fälligkeitsdatum</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(txn, idx) in previewData.transactions" :key="idx">
            <td>{{ txn.memberName }}</td>
            <td>{{ txn.iban }}</td>
            <td>{{ txn.amount.toFixed(2) }} €</td>
            <td>{{ txn.dueDate }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'SepaExport',
  data() {
    return {
      form: {
        creditorName: '',
        creditorIban: '',
        creditorBic: '',
        creditorId: ''
      },
      previewData: null
    }
  },
  methods: {
    goBack() {
      this.$emit('navigate', 'Finanzen')
    },
    async preview() {
      try {
        const response = await axios.post(
          generateUrl('/apps/verein/api/sepa/preview'),
          this.form
        )
        this.previewData = response.data
      } catch (error) {
        console.error('Error loading preview:', error)
        alert('Fehler beim Laden der Vorschau')
      }
    },
    async generateSepa() {
      try {
        const response = await axios.post(
          generateUrl('/apps/verein/api/sepa/export'),
          this.form,
          { responseType: 'blob' }
        )
        
        // Download file
        const blob = new Blob([response.data], { type: 'application/xml' })
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `sepa_export_${new Date().toISOString().split('T')[0]}.xml`
        a.click()
        window.URL.revokeObjectURL(url)
      } catch (error) {
        console.error('Error generating SEPA:', error)
        alert('Fehler beim Generieren der SEPA-Datei')
      }
    }
  }
}
</script>

<style scoped>
.sepa-export {
  padding: 20px;
  position: relative;
}

.back-button {
  position: absolute;
  top: 20px;
  left: 20px;
  padding: 8px 16px;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  color: #333;
  transition: all 0.2s ease;
}

.back-button:hover {
  background-color: #e0e0e0;
  border-color: #999;
}

.sepa-export h2 {
  margin-top: 40px;
}

.form-container,
.preview-container {
  background: #f5f5f5;
  padding: 20px;
  margin: 20px 0;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

.form-group input {
  display: block;
  width: 100%;
  padding: 8px;
}

.form-buttons {
  margin-top: 15px;
}

button {
  margin-right: 10px;
  padding: 8px 16px;
  cursor: pointer;
}

.button.primary {
  background-color: #0082c9;
  color: white;
  border: none;
}

.preview-summary {
  background: white;
  padding: 15px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.preview-summary p {
  margin: 5px 0;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  background: white;
}

th,
td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f5f5f5;
  font-weight: bold;
}
</style>
