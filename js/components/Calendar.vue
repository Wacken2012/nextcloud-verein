<template>
  <div class="calendar-container">
    <!-- Header mit Ansicht-Umschalter -->
    <div class="calendar-header">
      <div class="header-left">
        <h2>Vereinskalender</h2>
        <div class="view-switcher">
          <button 
            :class="{ active: currentView === 'month' }" 
            @click="currentView = 'month'"
          >
            Monat
          </button>
          <button 
            :class="{ active: currentView === 'list' }" 
            @click="currentView = 'list'"
          >
            Liste
          </button>
        </div>
      </div>
      <div class="header-right">
        <div class="type-filter">
          <select v-model="selectedType">
            <option value="">Alle Typen</option>
            <option value="meeting">Versammlung</option>
            <option value="rehearsal">Probe</option>
            <option value="performance">Auftritt</option>
            <option value="event">Veranstaltung</option>
            <option value="other">Sonstiges</option>
          </select>
        </div>
        <button class="btn-primary" @click="openEventModal()">
          <span class="icon icon-add"></span>
          Termin erstellen
        </button>
      </div>
    </div>

    <!-- Monatsnavigation -->
    <div v-if="currentView === 'month'" class="month-navigation">
      <button class="nav-btn" @click="previousMonth">
        <span class="icon icon-triangle-w"></span>
      </button>
      <h3>{{ currentMonthName }} {{ currentYear }}</h3>
      <button class="nav-btn" @click="nextMonth">
        <span class="icon icon-triangle-e"></span>
      </button>
      <button class="today-btn" @click="goToToday">Heute</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <span class="icon icon-loading"></span>
      Lade Termine...
    </div>

    <!-- Monatsansicht -->
    <div v-else-if="currentView === 'month'" class="calendar-grid">
      <div class="weekday-header">
        <div v-for="day in weekdays" :key="day" class="weekday">{{ day }}</div>
      </div>
      <div class="days-grid">
        <div 
          v-for="(day, index) in calendarDays" 
          :key="index"
          :class="getDayClass(day)"
          @click="day.date && openDayEvents(day)"
        >
          <span class="day-number">{{ day.dayNumber }}</span>
          <div class="day-events">
            <div 
              v-for="event in getDayEvents(day.date)" 
              :key="event.id"
              :class="['event-dot', `type-${event.type}`]"
              :title="event.title"
              @click.stop="openEventDetail(event)"
            >
              <span v-if="!day.isOtherMonth" class="event-title">{{ event.title }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Listenansicht -->
    <div v-else-if="currentView === 'list'" class="events-list">
      <div v-if="filteredEvents.length === 0" class="no-events">
        <span class="icon icon-calendar"></span>
        <p>Keine Termine gefunden</p>
      </div>
      <div v-else>
        <div 
          v-for="event in filteredEvents" 
          :key="event.id" 
          class="event-card"
          @click="openEventDetail(event)"
        >
          <div :class="['event-type-badge', `type-${event.type}`]">
            {{ getTypeLabel(event.type) }}
          </div>
          <div class="event-info">
            <h4>{{ event.title }}</h4>
            <div class="event-meta">
              <span class="event-date">
                <span class="icon icon-calendar"></span>
                {{ formatEventDate(event) }}
              </span>
              <span v-if="event.location" class="event-location">
                <span class="icon icon-address"></span>
                {{ event.location }}
              </span>
            </div>
            <p v-if="event.description" class="event-description">{{ event.description }}</p>
          </div>
          <div class="event-rsvp-summary">
            <span class="rsvp-count yes" :title="'Zusagen'">
              <span class="icon icon-checkmark"></span>
              {{ event.rsvpStats?.yes || 0 }}
            </span>
            <span class="rsvp-count no" :title="'Absagen'">
              <span class="icon icon-close"></span>
              {{ event.rsvpStats?.no || 0 }}
            </span>
            <span class="rsvp-count maybe" :title="'Vielleicht'">
              <span class="icon icon-info"></span>
              {{ event.rsvpStats?.maybe || 0 }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistiken -->
    <div class="calendar-stats" v-if="statistics">
      <div class="stat-card">
        <span class="stat-value">{{ statistics.totalEvents }}</span>
        <span class="stat-label">Termine gesamt</span>
      </div>
      <div class="stat-card">
        <span class="stat-value">{{ statistics.upcomingEvents }}</span>
        <span class="stat-label">Bevorstehend</span>
      </div>
      <div class="stat-card">
        <span class="stat-value">{{ statistics.thisMonthEvents }}</span>
        <span class="stat-label">Diesen Monat</span>
      </div>
    </div>

    <!-- Event-Modal -->
    <div v-if="showEventModal" class="modal-overlay" @click.self="closeEventModal">
      <div class="modal event-modal">
        <div class="modal-header">
          <h3>{{ editingEvent ? 'Termin bearbeiten' : 'Neuer Termin' }}</h3>
          <button class="close-btn" @click="closeEventModal">
            <span class="icon icon-close"></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Titel *</label>
            <input 
              v-model="eventForm.title" 
              type="text" 
              placeholder="z.B. Jahreshauptversammlung"
              required
            />
          </div>
          <div class="form-group">

          <div class="form-row">
            <div class="form-group checkbox-group">
              <label>
                <input type="checkbox" v-model="eventForm.rsvpEnabled" />
                RSVP aktivieren
              </label>
            </div>
            <div class="form-group" v-if="eventForm.rsvpEnabled">
              <label>Anmeldefrist (Deadline)</label>
              <input type="datetime-local" v-model="eventForm.rsvpDeadline" />
            </div>
            <div class="form-group" v-if="eventForm.rsvpEnabled">
              <label>Max. Teilnehmer</label>
              <input type="number" min="1" v-model.number="eventForm.maxParticipants" placeholder="optional" />
            </div>
          </div>
            <label>Typ *</label>
            <select v-model="eventForm.type" required>
              <option value="meeting">Versammlung</option>
              <option value="rehearsal">Probe</option>
              <option value="performance">Auftritt</option>
              <option value="event">Veranstaltung</option>
              <option value="other">Sonstiges</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Startdatum *</label>
              <input 
                v-model="eventForm.startDate" 
                type="date" 
                required
              />
            </div>
            <div class="form-group" v-if="!eventForm.allDay">
              <label>Startzeit</label>
              <input 
                v-model="eventForm.startTime" 
                type="time"
              />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Enddatum</label>
              <input 
                v-model="eventForm.endDate" 
                type="date"
              />
            </div>
            <div class="form-group" v-if="!eventForm.allDay">
              <label>Endzeit</label>
              <input 
                v-model="eventForm.endTime" 
                type="time"
              />
            </div>
          </div>
          <div class="form-group checkbox-group">
            <label>
              <input type="checkbox" v-model="eventForm.allDay" />
              Ganztägig
            </label>
          </div>
          <div class="form-group">
            <label>Ort</label>
            <input 
              v-model="eventForm.location" 
              type="text" 
              placeholder="z.B. Vereinsheim"
            />
          </div>
          <div class="form-group">
            <label>Beschreibung</label>
            <textarea 
              v-model="eventForm.description" 
              rows="3"
              placeholder="Weitere Details zum Termin..."
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeEventModal">Abbrechen</button>
          <button 
            class="btn-primary" 
            @click="saveEvent"
            :disabled="!eventForm.title || !eventForm.type || !eventForm.startDate"
          >
            {{ editingEvent ? 'Speichern' : 'Erstellen' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Event-Detail-Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetailModal">
      <div class="modal detail-modal">
        <div class="modal-header">
          <div :class="['event-type-badge', `type-${selectedEvent.type}`]">
            {{ getTypeLabel(selectedEvent.type) }}
          </div>
          <h3>{{ selectedEvent.title }}</h3>
          <button class="close-btn" @click="closeDetailModal">
            <span class="icon icon-close"></span>
          </button>
        </div>
        <div class="modal-body">
          <div class="detail-row">
            <span class="icon icon-calendar"></span>
            <span>{{ formatEventDate(selectedEvent) }}</span>
          </div>
          <div v-if="selectedEvent.location" class="detail-row">
            <span class="icon icon-address"></span>
            <span>{{ selectedEvent.location }}</span>
          </div>
          <div v-if="selectedEvent.description" class="detail-description">
            <p>{{ selectedEvent.description }}</p>
          </div>

          <!-- RSVP Section -->
          <div class="rsvp-section" v-if="selectedEvent && selectedEvent.rsvpEnabled">
            <h4>Deine Rückmeldung</h4>
            <div class="rsvp-buttons">
              <button 
                :class="['rsvp-btn', 'yes', { active: myRsvp === 'yes' }]"
                @click="setMyRsvp('yes')"
              >
                <span class="icon icon-checkmark"></span>
                Zusagen
              </button>
              <button 
                :class="['rsvp-btn', 'maybe', { active: myRsvp === 'maybe' }]"
                @click="setMyRsvp('maybe')"
              >
                <span class="icon icon-info"></span>
                Vielleicht
              </button>
              <button 
                :class="['rsvp-btn', 'no', { active: myRsvp === 'no' }]"
                @click="setMyRsvp('no')"
              >
                <span class="icon icon-close"></span>
                Absagen
              </button>
            </div>
          </div>

          <!-- Teilnehmerliste -->
          <div class="attendees-section" v-if="selectedEvent && selectedEvent.rsvpEnabled && eventRsvpList.length > 0">
            <h4>Rückmeldungen ({{ eventRsvpList.length }})</h4>
            <div class="attendees-summary">
              <span class="summary-item yes">
                <span class="icon icon-checkmark"></span>
                {{ rsvpCounts.yes }} Zusagen
              </span>
              <span class="summary-item maybe">
                <span class="icon icon-info"></span>
                {{ rsvpCounts.maybe }} Vielleicht
              </span>
              <span class="summary-item no">
                <span class="icon icon-close"></span>
                {{ rsvpCounts.no }} Absagen
              </span>
            </div>
            <div class="attendees-list">
              <div 
                v-for="rsvp in eventRsvpList" 
                :key="rsvp.memberId"
                :class="['attendee', rsvp.response]"
              >
                <span class="attendee-name">{{ rsvp.memberName }}</span>
                <span :class="['attendee-status', rsvp.response]">
                  {{ getRsvpLabel(rsvp.response) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-danger" @click="confirmDeleteEvent">
            <span class="icon icon-delete"></span>
            Löschen
          </button>
          <button class="btn-secondary" @click="editEvent(selectedEvent)">
            <span class="icon icon-rename"></span>
            Bearbeiten
          </button>
        </div>
      </div>
    </div>

    <!-- Tagesansicht Modal -->
    <div v-if="showDayModal" class="modal-overlay" @click.self="closeDayModal">
      <div class="modal day-modal">
        <div class="modal-header">
          <h3>{{ formatDayDate(selectedDay) }}</h3>
          <button class="close-btn" @click="closeDayModal">
            <span class="icon icon-close"></span>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="selectedDayEvents.length === 0" class="no-events">
            <p>Keine Termine an diesem Tag</p>
          </div>
          <div 
            v-for="event in selectedDayEvents" 
            :key="event.id"
            class="day-event-item"
            @click="openEventDetail(event); closeDayModal()"
          >
            <div :class="['event-type-indicator', `type-${event.type}`]"></div>
            <div class="event-info">
              <strong>{{ event.title }}</strong>
              <span v-if="!event.allDay" class="event-time">
                {{ formatTime(event.startDate) }}
              </span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-primary" @click="openEventModal(selectedDay); closeDayModal()">
            <span class="icon icon-add"></span>
            Termin hinzufügen
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../api.js'

export default {
  name: 'Calendar',
  
  data() {
    return {
      // View state
      currentView: 'month',
      currentDate: new Date(),
      loading: false,
      
      // Data
      events: [],
      statistics: null,
      
      // Filters
      selectedType: '',
      
      // Event Modal
      showEventModal: false,
      editingEvent: null,
      eventForm: {
        title: '',
        type: 'meeting',
        description: '',
        location: '',
        startDate: '',
        startTime: '10:00',
        endDate: '',
        endTime: '12:00',
        allDay: false,
        rsvpEnabled: false,
        rsvpDeadline: '',
        maxParticipants: null
      },
      
      // Detail Modal
      showDetailModal: false,
      selectedEvent: null,
      eventRsvpList: [],
      myRsvp: null,
      
      // Day Modal
      showDayModal: false,
      selectedDay: null,
      selectedDayEvents: [],
      
      // Constants
      weekdays: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
      typeLabels: {
        meeting: 'Versammlung',
        rehearsal: 'Probe',
        performance: 'Auftritt',
        event: 'Veranstaltung',
        other: 'Sonstiges'
      }
    }
  },
  
  computed: {
    currentYear() {
      return this.currentDate.getFullYear()
    },
    
    currentMonth() {
      return this.currentDate.getMonth()
    },
    
    currentMonthName() {
      const months = [
        'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
        'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'
      ]
      return months[this.currentMonth]
    },
    
    calendarDays() {
      const days = []
      const firstDay = new Date(this.currentYear, this.currentMonth, 1)
      const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0)
      
      // Wochentag des 1. (0=Sonntag, 1=Montag, ...)
      // Für deutschen Kalender: Montag = 0
      let startDay = firstDay.getDay() - 1
      if (startDay < 0) startDay = 6
      
      // Tage vom vorherigen Monat
      const prevMonth = new Date(this.currentYear, this.currentMonth, 0)
      for (let i = startDay - 1; i >= 0; i--) {
        days.push({
          dayNumber: prevMonth.getDate() - i,
          date: new Date(this.currentYear, this.currentMonth - 1, prevMonth.getDate() - i),
          isOtherMonth: true
        })
      }
      
      // Tage des aktuellen Monats
      for (let i = 1; i <= lastDay.getDate(); i++) {
        days.push({
          dayNumber: i,
          date: new Date(this.currentYear, this.currentMonth, i),
          isOtherMonth: false
        })
      }
      
      // Tage des nächsten Monats (bis 42 Tage = 6 Wochen)
      const remaining = 42 - days.length
      for (let i = 1; i <= remaining; i++) {
        days.push({
          dayNumber: i,
          date: new Date(this.currentYear, this.currentMonth + 1, i),
          isOtherMonth: true
        })
      }
      
      return days
    },
    
    filteredEvents() {
      let events = [...this.events]
      
      if (this.selectedType) {
        events = events.filter(e => e.type === this.selectedType)
      }
      
      // Sortiere nach Startdatum (API gibt startDate zurück)
      events.sort((a, b) => new Date(a.startDate) - new Date(b.startDate))
      
      return events
    },
    
    rsvpCounts() {
      const counts = { yes: 0, no: 0, maybe: 0, pending: 0 }
      this.eventRsvpList.forEach(r => {
        if (counts[r.response] !== undefined) {
          counts[r.response]++
        }
      })
      return counts
    }
  },
  
  async mounted() {
    await this.loadEvents()
    await this.loadStatistics()
  },
  
  methods: {
    async loadEvents() {
      this.loading = true
      try {
        const response = await api.getEvents()
        // Die API gibt direkt ein Array zurück oder {events: [...]}
        this.events = Array.isArray(response.data) ? response.data : (response.data.events || response.data || [])
        
        // Lade RSVP-Stats für Events mit RSVP aktiviert
        for (const event of this.events) {
          if (event.rsvpEnabled) {
            try {
              const rsvpResponse = await api.getEventRsvp(event.id)
              const responses = rsvpResponse.data.responses || []
              event.rsvpStats = rsvpResponse.data.statistics || {
                yes: responses.filter(r => r.response === 'yes').length,
                no: responses.filter(r => r.response === 'no').length,
                maybe: responses.filter(r => r.response === 'maybe').length
              }
            } catch (e) {
              event.rsvpStats = { yes: 0, no: 0, maybe: 0 }
            }
          }
        }
        
        // Lade Statistiken nach Events
        this.loadStatistics()
      } catch (error) {
        console.error('Fehler beim Laden der Termine:', error)
        this.events = []
      } finally {
        this.loading = false
      }
    },
    
    async loadStatistics() {
      // Berechne Statistiken aus geladenen Events
      try {
        const now = new Date()
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
        const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)
        
        this.statistics = {
          totalEvents: this.events.length,
          upcomingEvents: this.events.filter(e => new Date(e.startDate || e.startTime) > now).length,
          thisMonthEvents: this.events.filter(e => {
            const d = new Date(e.startDate || e.startTime)
            return d >= startOfMonth && d <= endOfMonth
          }).length
        }
      } catch (error) {
        console.error('Fehler beim Laden der Statistiken:', error)
      }
    },
    
    // Navigation
    previousMonth() {
      this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1)
    },
    
    nextMonth() {
      this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1)
    },
    
    goToToday() {
      this.currentDate = new Date()
    },
    
    // Helpers
    getDayClass(day) {
      const classes = ['day-cell']
      if (day.isOtherMonth) classes.push('other-month')
      
      const today = new Date()
      if (day.date && 
          day.date.getDate() === today.getDate() &&
          day.date.getMonth() === today.getMonth() &&
          day.date.getFullYear() === today.getFullYear()) {
        classes.push('today')
      }
      
      if (this.getDayEvents(day.date).length > 0) {
        classes.push('has-events')
      }
      
      return classes
    },
    
    getDayEvents(date) {
      if (!date) return []
      
      const dateStr = this.formatDateString(date)
      return this.events.filter(event => {
        const eventDate = (event.startDate || '').split('T')[0]
        return eventDate === dateStr
      })
    },
    
    formatDateString(date) {
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    },
    
    getTypeLabel(type) {
      return this.typeLabels[type] || type
    },
    
    getRsvpLabel(response) {
      const labels = {
        yes: 'Zugesagt',
        no: 'Abgesagt',
        maybe: 'Vielleicht',
        pending: 'Offen'
      }
      return labels[response] || response
    },
    
    formatEventDate(event) {
      const start = new Date(event.startDate)
      const options = { 
        weekday: 'short', 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric'
      }
      
      let result = start.toLocaleDateString('de-DE', options)
      
      if (!event.allDay) {
        result += ` um ${start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })} Uhr`
      }
      
      return result
    },
    
    formatDayDate(date) {
      if (!date) return ''
      const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
      return date.toLocaleDateString('de-DE', options)
    },
    
    formatTime(datetime) {
      if (!datetime) return ''
      const date = new Date(datetime)
      return date.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })
    },

    formatDateTimeLocal(date) {
      if (!date) return ''
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      return `${year}-${month}-${day}T${hours}:${minutes}`
    },
    
    // Event Modal
    openEventModal(presetDate = null) {
      this.editingEvent = null
      this.eventForm = {
        title: '',
        type: 'meeting',
        description: '',
        location: '',
        startDate: presetDate ? this.formatDateString(presetDate) : this.formatDateString(new Date()),
        startTime: '10:00',
        endDate: '',
        endTime: '12:00',
        allDay: false,
        rsvpEnabled: false,
        rsvpDeadline: '',
        maxParticipants: null
      }
      this.showEventModal = true
    },
    
    closeEventModal() {
      this.showEventModal = false
      this.editingEvent = null
    },
    
    editEvent(event) {
      this.editingEvent = event
      const startDate = new Date(event.startDate)
      const endDate = event.endTime ? new Date(event.endTime) : null
      const deadlineDate = event.rsvpDeadline ? new Date(event.rsvpDeadline) : null
      
      this.eventForm = {
        title: event.title,
        type: event.type,
        description: event.description || '',
        location: event.location || '',
        startDate: this.formatDateString(startDate),
        startTime: startDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }),
        endDate: endDate ? this.formatDateString(endDate) : '',
        endTime: endDate ? endDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) : '',
        allDay: event.allDay || false,
        rsvpEnabled: !!event.rsvpEnabled,
        rsvpDeadline: deadlineDate ? this.formatDateTimeLocal(deadlineDate) : '',
        maxParticipants: event.maxParticipants ?? null
      }
      
      this.showDetailModal = false
      this.showEventModal = true
    },
    
    async saveEvent() {
      try {
        const startDateStr = this.eventForm.allDay 
          ? `${this.eventForm.startDate}T00:00:00`
          : `${this.eventForm.startDate}T${this.eventForm.startTime}:00`
        
        let endDateStr = null
        if (this.eventForm.endDate) {
          endDateStr = this.eventForm.allDay
            ? `${this.eventForm.endDate}T23:59:59`
            : `${this.eventForm.endDate}T${this.eventForm.endTime}:00`
        }
        
        const deadlineValue = this.eventForm.rsvpEnabled && this.eventForm.rsvpDeadline
          ? (this.eventForm.rsvpDeadline.length === 16
            ? `${this.eventForm.rsvpDeadline}:00`
            : this.eventForm.rsvpDeadline)
          : null

        const data = {
          title: this.eventForm.title,
          eventType: this.eventForm.type,
          description: this.eventForm.description,
          location: this.eventForm.location,
          startDate: startDateStr,
          endDate: endDateStr,
          allDay: this.eventForm.allDay,
          rsvpEnabled: !!this.eventForm.rsvpEnabled,
          rsvpDeadline: deadlineValue,
          maxParticipants: this.eventForm.rsvpEnabled ? this.eventForm.maxParticipants : null
        }
        
        if (this.editingEvent) {
          await api.updateEvent(this.editingEvent.id, data)
        } else {
          await api.createEvent(data)
        }
        
        this.closeEventModal()
        await this.loadEvents()
      } catch (error) {
        console.error('Fehler beim Speichern:', error)
        alert('Fehler beim Speichern des Termins: ' + (error.response?.data?.message || error.message))
      }
    },
    
    // Detail Modal
    async openEventDetail(event) {
      this.selectedEvent = event
      this.showDetailModal = true
      this.myRsvp = null
      this.eventRsvpList = []
      
      if (!event.rsvpEnabled) {
        return
      }

      try {
        const response = await api.getEventRsvp(event.id)
        this.eventRsvpList = response.data.responses || response.data || []

        const myResp = await api.getMyRsvp(event.id)
        const my = myResp.data
        if (my && my.response) {
          this.myRsvp = my.response
        }
      } catch (error) {
        console.error('Fehler beim Laden der RSVPs:', error)
      }
    },
    
    closeDetailModal() {
      this.showDetailModal = false
      this.selectedEvent = null
    },
    
    async setMyRsvp(response) {
      if (!this.selectedEvent) return
      if (!this.selectedEvent.rsvpEnabled) {
        alert('RSVP ist für diesen Termin nicht aktiviert')
        return
      }
      
      try {
        await api.setEventRsvp(this.selectedEvent.id, response)
        this.myRsvp = response
        
        // Reload RSVP list
        const rsvpResponse = await api.getEventRsvp(this.selectedEvent.id)
        this.eventRsvpList = rsvpResponse.data.responses || rsvpResponse.data || []
        
        // Update event in list
        await this.loadEvents()
      } catch (error) {
        console.error('Fehler beim Setzen der RSVP:', error)
        const msg = error.response?.data?.error || error.response?.data?.message || 'Fehler beim Speichern der Rückmeldung'
        alert(msg)
      }
    },
    
    async confirmDeleteEvent() {
      if (!this.selectedEvent) return
      
      if (confirm(`Möchten Sie den Termin "${this.selectedEvent.title}" wirklich löschen?`)) {
        try {
          await api.deleteEvent(this.selectedEvent.id)
          this.closeDetailModal()
          await this.loadEvents()
          await this.loadStatistics()
        } catch (error) {
          console.error('Fehler beim Löschen:', error)
          alert('Fehler beim Löschen des Termins')
        }
      }
    },
    
    // Day Modal
    openDayEvents(day) {
      this.selectedDay = day.date
      this.selectedDayEvents = this.getDayEvents(day.date)
      this.showDayModal = true
    },
    
    closeDayModal() {
      this.showDayModal = false
      this.selectedDay = null
    }
  }
}
</script>

<style scoped lang="scss">
.calendar-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 20px;
    
    h2 {
      margin: 0;
      color: var(--color-main-text);
    }
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 15px;
  }
}

.view-switcher {
  display: flex;
  background: var(--color-background-dark);
  border-radius: 20px;
  padding: 3px;
  
  button {
    padding: 6px 16px;
    border: none;
    background: transparent;
    border-radius: 18px;
    cursor: pointer;
    color: var(--color-main-text);
    transition: all 0.2s;
    
    &.active {
      background: var(--color-primary);
      color: var(--color-primary-text);
    }
    
    &:hover:not(.active) {
      background: var(--color-background-hover);
    }
  }
}

.type-filter select {
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  background: var(--color-main-background);
  color: var(--color-main-text);
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: var(--color-primary);
  color: var(--color-primary-text);
  border: none;
  border-radius: 20px;
  cursor: pointer;
  
  &:hover {
    background: var(--color-primary-hover);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.btn-secondary {
  padding: 8px 16px;
  background: var(--color-background-dark);
  color: var(--color-main-text);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  cursor: pointer;
  
  &:hover {
    background: var(--color-background-hover);
  }
}

.btn-danger {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: var(--color-error);
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  
  &:hover {
    opacity: 0.9;
  }
}

.month-navigation {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
  
  h3 {
    min-width: 180px;
    text-align: center;
    margin: 0;
  }
  
  .nav-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: var(--color-background-dark);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    
    &:hover {
      background: var(--color-background-hover);
    }
  }
  
  .today-btn {
    padding: 6px 12px;
    background: transparent;
    border: 1px solid var(--color-border);
    border-radius: 15px;
    cursor: pointer;
    color: var(--color-main-text);
    
    &:hover {
      background: var(--color-background-hover);
    }
  }
}

.loading {
  text-align: center;
  padding: 40px;
  color: var(--color-text-maxcontrast);
}

// Calendar Grid
.calendar-grid {
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
}

.weekday-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: var(--color-background-dark);
  
  .weekday {
    padding: 12px;
    text-align: center;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
    border-right: 1px solid var(--color-border);
    
    &:last-child {
      border-right: none;
    }
  }
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.day-cell {
  min-height: 100px;
  padding: 8px;
  border-right: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
  cursor: pointer;
  transition: background 0.2s;
  
  &:nth-child(7n) {
    border-right: none;
  }
  
  &:hover {
    background: var(--color-background-hover);
  }
  
  &.other-month {
    background: var(--color-background-dark);
    
    .day-number {
      color: var(--color-text-maxcontrast);
    }
  }
  
  &.today {
    .day-number {
      background: var(--color-primary);
      color: var(--color-primary-text);
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
  
  &.has-events {
    background: rgba(var(--color-primary-rgb), 0.05);
  }
}

.day-number {
  font-weight: 500;
  margin-bottom: 4px;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.event-dot {
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  
  &.type-meeting { background: #4CAF50; color: white; }
  &.type-rehearsal { background: #2196F3; color: white; }
  &.type-performance { background: #FF9800; color: white; }
  &.type-event { background: #9C27B0; color: white; }
  &.type-other { background: #607D8B; color: white; }
}

// Events List
.events-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.no-events {
  text-align: center;
  padding: 60px 20px;
  color: var(--color-text-maxcontrast);
  
  .icon {
    font-size: 48px;
    display: block;
    margin-bottom: 10px;
  }
}

.event-card {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 16px;
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
}

.event-type-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  
  &.type-meeting { background: #E8F5E9; color: #2E7D32; }
  &.type-rehearsal { background: #E3F2FD; color: #1565C0; }
  &.type-performance { background: #FFF3E0; color: #EF6C00; }
  &.type-event { background: #F3E5F5; color: #7B1FA2; }
  &.type-other { background: #ECEFF1; color: #455A64; }
}

.event-info {
  flex: 1;
  min-width: 0;
  
  h4 {
    margin: 0 0 8px 0;
    color: var(--color-main-text);
  }
}

.event-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  font-size: 13px;
  color: var(--color-text-maxcontrast);
  
  span {
    display: flex;
    align-items: center;
    gap: 4px;
  }
}

.event-description {
  margin-top: 8px;
  font-size: 13px;
  color: var(--color-text-maxcontrast);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.event-rsvp-summary {
  display: flex;
  gap: 8px;
  
  .rsvp-count {
    display: flex;
    align-items: center;
    gap: 3px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    
    &.yes { background: #E8F5E9; color: #2E7D32; }
    &.no { background: #FFEBEE; color: #C62828; }
    &.maybe { background: #FFF8E1; color: #F57F17; }
  }
}

// Statistics
.calendar-stats {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.stat-card {
  flex: 1;
  min-width: 120px;
  padding: 16px;
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  text-align: center;
  
  .stat-value {
    display: block;
    font-size: 28px;
    font-weight: 600;
    color: var(--color-primary);
  }
  
  .stat-label {
    font-size: 12px;
    color: var(--color-text-maxcontrast);
  }
}

// Modals
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal {
  background: var(--color-main-background);
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--color-border);
  
  h3 {
    margin: 0;
    flex: 1;
  }
  
  .close-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: var(--color-text-maxcontrast);
    
    &:hover {
      color: var(--color-main-text);
    }
  }
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid var(--color-border);
}

.form-group {
  margin-bottom: 16px;
  
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: var(--color-main-text);
  }
  
  input, select, textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: var(--color-main-background);
    color: var(--color-main-text);
    
    &:focus {
      outline: none;
      border-color: var(--color-primary);
    }
  }
  
  textarea {
    resize: vertical;
  }
}

.form-row {
  display: flex;
  gap: 15px;
  
  .form-group {
    flex: 1;
  }
}

.checkbox-group {
  label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    
    input[type="checkbox"] {
      width: auto;
    }
  }
}

// Detail Modal
.detail-modal {
  .modal-header {
    flex-wrap: wrap;
    gap: 10px;
  }
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  color: var(--color-text-maxcontrast);
}

.detail-description {
  padding: 12px;
  background: var(--color-background-dark);
  border-radius: 6px;
  margin: 16px 0;
  
  p {
    margin: 0;
    white-space: pre-wrap;
  }
}

.rsvp-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
  
  h4 {
    margin: 0 0 12px 0;
  }
}

.rsvp-buttons {
  display: flex;
  gap: 10px;
}

.rsvp-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
  background: var(--color-main-background);
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: var(--color-primary);
  }
  
  &.yes.active {
    background: #E8F5E9;
    border-color: #4CAF50;
    color: #2E7D32;
  }
  
  &.maybe.active {
    background: #FFF8E1;
    border-color: #FFC107;
    color: #F57F17;
  }
  
  &.no.active {
    background: #FFEBEE;
    border-color: #F44336;
    color: #C62828;
  }
}

.attendees-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
  
  h4 {
    margin: 0 0 12px 0;
  }
}

.attendees-summary {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  flex-wrap: wrap;
  
  .summary-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    
    &.yes { color: #2E7D32; }
    &.maybe { color: #F57F17; }
    &.no { color: #C62828; }
  }
}

.attendees-list {
  max-height: 200px;
  overflow-y: auto;
}

.attendee {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid var(--color-border);
  
  &:last-child {
    border-bottom: none;
  }
}

.attendee-status {
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 10px;
  
  &.yes { background: #E8F5E9; color: #2E7D32; }
  &.maybe { background: #FFF8E1; color: #F57F17; }
  &.no { background: #FFEBEE; color: #C62828; }
  &.pending { background: #ECEFF1; color: #607D8B; }
}

// Day Modal
.day-modal {
  max-width: 400px;
}

.day-event-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  margin-bottom: 8px;
  background: var(--color-background-dark);
  border-radius: 6px;
  cursor: pointer;
  
  &:hover {
    background: var(--color-background-hover);
  }
  
  &:last-child {
    margin-bottom: 0;
  }
}

.event-type-indicator {
  width: 4px;
  height: 40px;
  border-radius: 2px;
  
  &.type-meeting { background: #4CAF50; }
  &.type-rehearsal { background: #2196F3; }
  &.type-performance { background: #FF9800; }
  &.type-event { background: #9C27B0; }
  &.type-other { background: #607D8B; }
}

.event-time {
  display: block;
  font-size: 12px;
  color: var(--color-text-maxcontrast);
  margin-top: 4px;
}

// Responsive
@media (max-width: 768px) {
  .calendar-header {
    flex-direction: column;
    align-items: stretch;
    
    .header-left, .header-right {
      justify-content: center;
    }
  }
  
  .day-cell {
    min-height: 60px;
    padding: 4px;
    
    .event-title {
      display: none;
    }
  }
  
  .form-row {
    flex-direction: column;
    gap: 0;
  }
  
  .rsvp-buttons {
    flex-direction: column;
  }
}
</style>
