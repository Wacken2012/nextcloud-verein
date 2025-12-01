<template>
  <div class="export-buttons" :class="{ inline: inline }">
    <button
      :disabled="busyCsv"
      @click="handleCsv"
      class="button"
      :aria-busy="busyCsv ? 'true' : 'false'"
      :aria-label="`${labelBase} als CSV exportieren`"
      :title="`${labelBase} als CSV herunterladen`"
    >
      <span v-if="!busyCsv">📊 CSV Export</span>
      <span v-else class="spinner" role="status" aria-live="polite" aria-label="Export läuft"> 
        <span class="visually-hidden">Export läuft…</span>
      </span>
    </button>
    <button
      :disabled="busyPdf"
      @click="handlePdf"
      class="button"
      :aria-busy="busyPdf ? 'true' : 'false'"
      :aria-label="`${labelBase} als PDF exportieren`"
      :title="`${labelBase} als PDF herunterladen`"
    >
      <span v-if="!busyPdf">📄 PDF Export</span>
      <span v-else class="spinner" role="status" aria-live="polite" aria-label="Export läuft">
        <span class="visually-hidden">Export läuft…</span>
      </span>
    </button>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import * as notify from '../notify'

export default {
  name: 'ExportButtons',
  props: {
    resource: { type: String, required: true }, // 'members' | 'fees'
    inline: { type: Boolean, default: false }
  },
  data() {
    return {
      busyCsv: false,
      busyPdf: false
    }
  },
  computed: {
    labelBase() {
      return this.resource === 'members' ? 'Mitglieder' : 'Beiträge'
    }
  },
  methods: {
    toastSuccess(msg) { notify.success(msg); this.$emit('success', msg) },
    toastError(msg) { notify.error(msg); this.$emit('error', msg) },
    async handleCsv() {
      if (this.busyCsv) return
      this.busyCsv = true
      try {
        const endpoint = generateUrl(`/apps/verein/export/${this.resource}/csv`)
        const response = await axios.get(endpoint, { responseType: 'blob' })
        const ct = (response.headers && response.headers['content-type']) || response.data?.type || ''
        if (!ct.includes('text/csv') && !ct.includes('application/csv')) {
          const text = await new Response(response.data).text()
          throw new Error(text || 'CSV-Export fehlgeschlagen')
        }
        this.downloadFile(response.data, `${this.resource}.csv`, 'text/csv')
        this.toastSuccess(`${this.labelBase} als CSV exportiert`)
      } catch (e) {
        console.error('CSV export failed', e)
        this.toastError('Fehler beim CSV-Export')
      } finally {
        this.busyCsv = false
      }
    },
    async handlePdf() {
      if (this.busyPdf) return
      this.busyPdf = true
      try {
        const endpoint = generateUrl(`/apps/verein/export/${this.resource}/pdf`)
        const response = await axios.get(endpoint, { responseType: 'blob' })
        const ct = (response.headers && response.headers['content-type']) || response.data?.type || ''
        if (!ct.includes('application/pdf')) {
          const text = await new Response(response.data).text()
          throw new Error(text || 'PDF-Export fehlgeschlagen')
        }
        this.downloadFile(response.data, `${this.resource}.pdf`, 'application/pdf')
        this.toastSuccess(`${this.labelBase} als PDF exportiert`)
      } catch (e) {
        console.error('PDF export failed', e)
        this.toastError('Fehler beim PDF-Export')
      } finally {
        this.busyPdf = false
      }
    },
    downloadFile(blob, filename, mimeType) {
      const url = window.URL.createObjectURL(new Blob([blob], { type: mimeType }))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    }
  }
}
</script>

<style scoped>
.export-buttons {
  display: flex;
  gap: 8px;
}
.export-buttons.inline {
  display: inline-flex;
}
.button {
  background-color: #f0f0f0;
  color: #333;
  padding: 8px 14px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background-color .2s;
}
.button:hover:not(:disabled) {
  background-color: #e0e0e0;
}
.button:disabled {
  opacity: .6;
  cursor: default;
}
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.2);
  border-top-color: rgba(0,0,0,0.6);
  border-radius: 50%;
  animation: spin .8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.visually-hidden {
  position: absolute !important;
  height: 1px; width: 1px;
  overflow: hidden;
  clip: rect(1px, 1px, 1px, 1px);
  white-space: nowrap;
}
</style>