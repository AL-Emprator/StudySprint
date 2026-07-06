
<template>
  <div class="meetings-page">
    <header class="page-header">
      <h1>Termine</h1>
      <p>Hier kannst du Termine anlegen und verwalten.</p>
    </header>

    <p
      v-if="errorMessage"
      class="message error-message"
    >
      {{ errorMessage }}
    </p>

    <div class="layout-grid">
      <!-- Terminformular -->
      <section class="panel form-panel">
        <div class="panel-header">
          <div>
            <h2>
              {{
                isEditMode
                  ? 'Termin bearbeiten'
                  : 'Neuer Termin'
              }}
            </h2>

            <p>
              {{
                isEditMode
                  ? 'Passe die Daten des ausgewählten Termins an.'
                  : 'Erstelle einen neuen Termin für deine Gruppe.'
              }}
            </p>
          </div>
        </div>

        <form
          class="meeting-form"
          @submit.prevent="handleSubmit"
        >
          <div class="form-group">
            <label for="group">Gruppe</label>

            <select
              id="group"
              v-model="selectedGroupId"
              :disabled="isLoadingGroups || isEditMode"
              required
            >
              <option value="" disabled>
                Gruppe auswählen
              </option>

              <option
                v-for="group in groups"
                :key="group.id"
                :value="group.id"
              >
                {{ group.name }}
              </option>
            </select>

            <p
              v-if="isLoadingGroups"
              class="field-info"
            >
              Gruppen werden geladen...
            </p>

            <p
              v-else-if="isEditMode"
              class="field-info"
            >
              Die Gruppe kann beim Bearbeiten nicht geändert werden.
            </p>
          </div>

          <div class="form-group">
            <label for="title">Titel</label>

            <input
              id="title"
              v-model.trim="form.title"
              type="text"
              placeholder="Titel des Termins"
              required
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="meetingDate">Datum</label>

              <input
                id="meetingDate"
                v-model="form.meeting_date"
                type="date"
                required
              />
            </div>

            <div class="form-group">
              <label for="meetingTime">Uhrzeit</label>

              <input
                id="meetingTime"
                v-model="form.meeting_time"
                type="time"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="locationOrLink">
              Ort oder Link
            </label>

            <input
              id="locationOrLink"
              v-model.trim="form.location_or_link"
              type="text"
              placeholder="Raum 3 oder https://..."
            />
          </div>

          <div class="form-group">
            <label for="notes">Notizen</label>

            <textarea
              id="notes"
              v-model.trim="form.notes"
              rows="5"
              placeholder="Zusätzliche Informationen zum Termin"
            ></textarea>
          </div>

          <div class="form-actions">
            <button
              type="submit"
              class="primary-button"
              :disabled="isSaving || !selectedGroupId"
            >
              {{
                isSaving
                  ? 'Bitte warten...'
                  : isEditMode
                    ? 'Termin speichern'
                    : 'Termin erstellen'
              }}
            </button>

            <button
              v-if="isEditMode"
              type="button"
              class="secondary-button"
              :disabled="isSaving"
              @click="cancelEdit"
            >
              Abbrechen
            </button>
          </div>
        </form>
      </section>

      <!-- Terminliste -->
      <section class="panel list-panel">
        <div class="panel-header list-header">
          <div>
            <h2>Terminliste</h2>

            <p v-if="selectedGroup">
              Gruppe: {{ selectedGroup.name }}
            </p>

            <p v-else>
              Wähle eine Gruppe aus, um Termine anzuzeigen.
            </p>
          </div>

          <span
            v-if="selectedGroupId"
            class="meeting-count"
          >
            {{ meetings.length }}
            {{ meetings.length === 1 ? 'Termin' : 'Termine' }}
          </span>
        </div>

        <div
          v-if="!selectedGroupId"
          class="info-box"
        >
          Bitte wähle zuerst eine Gruppe aus.
        </div>

        <div
          v-else-if="isLoading"
          class="info-box"
        >
          Termine werden geladen...
        </div>

        <div
          v-else-if="meetings.length === 0"
          class="info-box"
        >
          Für diese Gruppe sind noch keine Termine vorhanden.
        </div>

        <div
          v-else
          class="card-list"
        >
          <article
            v-for="meeting in meetings"
            :key="meeting.id"
            class="item-card"
          >
            <div class="card-top">
              <div class="meeting-content">
                <h3>{{ meeting.title }}</h3>

                <p>
                  {{
                    meeting.notes ||
                    'Keine Notizen vorhanden.'
                  }}
                </p>
              </div>

              <span class="date-badge">
                {{ formatDate(meeting.meeting_date) }}
              </span>
            </div>

            <div class="meta-list">
              <div class="meta-item">
                <span class="meta-label">Uhrzeit</span>
                <span>
                  {{ formatTime(meeting.meeting_time) }}
                </span>
              </div>

              <div class="meta-item location-item">
                <span class="meta-label">Ort/Link</span>

                <a
                  v-if="isLink(meeting.location_or_link)"
                  :href="meeting.location_or_link"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="meeting-link"
                >
                  {{ meeting.location_or_link }}
                </a>

                <span v-else>
                  {{
                    meeting.location_or_link ||
                    'Nicht angegeben'
                  }}
                </span>
              </div>
            </div>

            <div class="card-actions">
              <button
                type="button"
                class="edit-button"
                @click="startEdit(meeting)"
              >
                Bearbeiten
              </button>

              <button
                type="button"
                class="danger-button"
                @click="handleDeleteMeeting(meeting.id)"
              >
                Löschen
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import {
  computed,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'

import { useRoute } from 'vue-router'

import {
  getMeetings,
  createMeeting,
  updateMeeting,
  deleteMeeting as deleteMeetingRequest,
} from '../services/meetingService'

import {
  getGroups,
} from '../services/groupService'

const route = useRoute()

const groups = ref([])
const meetings = ref([])

const selectedGroupId = ref('')
const editingMeetingId = ref(null)

const isLoadingGroups = ref(false)
const isLoading = ref(false)
const isSaving = ref(false)

const errorMessage = ref('')

const form = reactive({
  title: '',
  meeting_date: '',
  meeting_time: '',
  location_or_link: '',
  notes: '',
})

const isEditMode = computed(() => {
  return editingMeetingId.value !== null
})

const selectedGroup = computed(() => {
  return groups.value.find(
    group =>
      Number(group.id) ===
      Number(selectedGroupId.value)
  ) ?? null
})

function isLink(value) {
  if (!value) {
    return false
  }

  return /^https?:\/\//i.test(value)
}

function formatDate(date) {
  if (!date) {
    return 'Kein Datum'
  }

  const normalizedDate = String(date).slice(0, 10)
  const [year, month, day] = normalizedDate.split('-')

  if (!year || !month || !day) {
    return date
  }

  return `${day}.${month}.${year}`
}


async function loadGroups() {
  isLoadingGroups.value = true
  errorMessage.value = ''

  try {
    const data = await getGroups()

    groups.value = Array.isArray(data)
      ? data
      : []

    const routeGroupId =
      route.params.groupId ??
      route.params.id

    if (
      routeGroupId &&
      groups.value.some(
        group =>
          Number(group.id) ===
          Number(routeGroupId)
      )
    ) {
      selectedGroupId.value =
        Number(routeGroupId)

      return
    }

    if (groups.value.length === 1) {
      selectedGroupId.value =
        groups.value[0].id
    }
  } catch (error) {
    console.error(
      'Fehler beim Laden der Gruppen:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ??
      'Die Gruppen konnten nicht geladen werden.'
  } finally {
    isLoadingGroups.value = false
  }
}

async function loadMeetings() {
  meetings.value = []
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    return
  }

  isLoading.value = true

  try {
    const data = await getMeetings(
      selectedGroupId.value
    )

    meetings.value = Array.isArray(data)
      ? data
      : []
  } catch (error) {
    console.error(
      'Fehler beim Laden der Termine:',
      error
    )

    if (error.response?.status === 401) {
      errorMessage.value =
        'Du bist nicht angemeldet.'
    } else if (error.response?.status === 403) {
      errorMessage.value =
        'Du hast keinen Zugriff auf diese Gruppe.'
    } else if (error.response?.status === 404) {
      errorMessage.value =
        'Die ausgewählte Gruppe wurde nicht gefunden.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Termine konnten nicht geladen werden.'
    }
  } finally {
    isLoading.value = false
  }
}

async function handleSubmit() {
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    errorMessage.value =
      'Bitte wähle zuerst eine Gruppe aus.'

    return
  }

  if (!form.title.trim()) {
    errorMessage.value =
      'Bitte gib einen Titel ein.'

    return
  }

  if (!form.meeting_date) {
    errorMessage.value =
      'Bitte gib ein Datum ein.'

    return
  }

  if (!form.meeting_time) {
    errorMessage.value =
      'Bitte gib eine Uhrzeit ein.'

    return
  }

  isSaving.value = true

  const meetingData = {
    title: form.title.trim(),
    meeting_date: form.meeting_date,
    meeting_time: form.meeting_time,
    location_or_link:
      form.location_or_link.trim() || null,
    notes:
      form.notes.trim() || null,
  }

  try {
    if (isEditMode.value) {
      const updatedMeeting =
        await updateMeeting(
          editingMeetingId.value,
          meetingData
        )

      const index = meetings.value.findIndex(
        meeting =>
          meeting.id === editingMeetingId.value
      )

      if (index !== -1) {
        meetings.value[index] =
          updatedMeeting
      }
    } else {
      const newMeeting =
        await createMeeting(
          selectedGroupId.value,
          meetingData
        )

      meetings.value.push(newMeeting)

      sortMeetings()
    }

    resetForm()
  } catch (error) {
    console.error(
      'Fehler beim Speichern des Termins:',
      error
    )

    if (error.response?.status === 422) {
      const errors =
        error.response?.data?.errors

      errorMessage.value =
        errors?.title?.[0] ??
        errors?.meeting_date?.[0] ??
        errors?.meeting_time?.[0] ??
        errors?.location_or_link?.[0] ??
        errors?.notes?.[0] ??
        'Bitte überprüfe deine Eingaben.'
    } else if (error.response?.status === 403) {
      errorMessage.value =
        'Du darfst in dieser Gruppe keine Termine bearbeiten.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Der Termin konnte nicht gespeichert werden.'
    }
  } finally {
    isSaving.value = false
  }
}

function startEdit(meeting) {
  editingMeetingId.value = meeting.id

  form.title = meeting.title
  form.meeting_date = normalizeDate(
    meeting.meeting_date
  )
  form.meeting_time = normalizeTime(
    meeting.meeting_time
  )
  form.location_or_link =
    meeting.location_or_link ?? ''
  form.notes =
    meeting.notes ?? ''
}

function cancelEdit() {
  resetForm()
}

async function handleDeleteMeeting(meetingId) {
  const confirmed = window.confirm(
    'Möchtest du diesen Termin wirklich löschen?'
  )

  if (!confirmed) {
    return
  }

  errorMessage.value = ''

  try {
    await deleteMeetingRequest(meetingId)

    meetings.value = meetings.value.filter(
      meeting => meeting.id !== meetingId
    )

    if (
      editingMeetingId.value === meetingId
    ) {
      resetForm()
    }
  } catch (error) {
    console.error(
      'Fehler beim Löschen des Termins:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ??
      'Der Termin konnte nicht gelöscht werden.'
  }
}

function resetForm() {
  editingMeetingId.value = null

  form.title = ''
  form.meeting_date = ''
  form.meeting_time = ''
  form.location_or_link = ''
  form.notes = ''
}

function normalizeDate(date) {
  if (!date) {
    return ''
  }

  return String(date).substring(0, 10)
}

function normalizeTime(time) {
  if (!time) {
    return ''
  }

  return String(time).substring(0, 5)
}


function formatTime(time) {
  if (!time) {
    return 'keine Uhrzeit'
  }

  return normalizeTime(time)
}

function sortMeetings() {
  meetings.value.sort((a, b) => {
    const first = `${a.meeting_date} ${a.meeting_time}`
    const second = `${b.meeting_date} ${b.meeting_time}`

    return first.localeCompare(second)
  })
}

watch(selectedGroupId, async () => {
  resetForm()
  await loadMeetings()
})

onMounted(async () => {
  await loadGroups()

  if (selectedGroupId.value) {
    await loadMeetings()
  }
})
</script>

<style scoped>
.meetings-page {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px;
  box-sizing: border-box;
}

.page-header {
  margin-bottom: 28px;
}

.page-header h1 {
  margin: 0;
  color: #111827;
  font-size: 36px;
  line-height: 1.2;
}

.page-header p {
  margin: 6px 0 0;
  color: #6b7280;
  font-size: 17px;
}

/* Hauptlayout */

.layout-grid {
  display: grid;
  grid-template-columns: minmax(360px, 430px) minmax(0, 1fr);
  align-items: start;
  gap: 24px;
}

.panel {
  min-width: 0;
  padding: 26px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.form-panel {
  position: sticky;
  top: 24px;
}

.list-panel {
  min-height: 620px;
}

/* Panel-Kopf */

.panel-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  padding-bottom: 18px;
  margin-bottom: 22px;
  border-bottom: 1px solid #e5e7eb;
}

.panel-header h2 {
  margin: 0;
  color: #111827;
  font-size: 24px;
  line-height: 1.3;
}

.panel-header p {
  margin: 5px 0 0;
  color: #6b7280;
  font-size: 14px;
  line-height: 1.5;
}

.meeting-count {
  flex-shrink: 0;
  padding: 7px 12px;
  color: #1d4ed8;
  background: #eff6ff;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
}

/* Formular */

.meeting-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
  min-width: 0;
}

.form-group label {
  color: #374151;
  font-size: 14px;
  font-weight: 700;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

select,
input,
textarea {
  width: 100%;
  min-height: 46px;
  padding: 11px 13px;
  color: #111827;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  outline: none;
  font: inherit;
  box-sizing: border-box;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

textarea {
  min-height: 120px;
  resize: vertical;
}

input::placeholder,
textarea::placeholder {
  color: #9ca3af;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

input:disabled,
textarea:disabled,
select:disabled {
  color: #6b7280;
  background: #f3f4f6;
  cursor: not-allowed;
}

.field-info {
  margin: 0;
  color: #6b7280;
  font-size: 12px;
  line-height: 1.4;
}

.form-actions {
  display: flex;
  gap: 10px;
  padding-top: 4px;
}

.form-actions button {
  flex: 1;
}

/* Buttons */

button {
  min-height: 42px;
  padding: 10px 16px;
  border: none;
  border-radius: 9px;
  cursor: pointer;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  transition:
    background-color 0.2s ease,
    border-color 0.2s ease,
    color 0.2s ease,
    transform 0.1s ease,
    opacity 0.2s ease;
}

button:active:not(:disabled) {
  transform: translateY(1px);
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.primary-button,
.edit-button {
  color: #ffffff;
  background: #2563eb;
}

.primary-button:hover:not(:disabled),
.edit-button:hover {
  background: #1d4ed8;
}

.secondary-button {
  color: #374151;
  background: #e5e7eb;
}

.secondary-button:hover:not(:disabled) {
  background: #d1d5db;
}

.danger-button {
  color: #ffffff;
  background: #dc2626;
}

.danger-button:hover {
  background: #b91c1c;
}

/* Terminkarten */

.card-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.item-card {
  padding: 18px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #2563eb;
  border-radius: 13px;
  transition:
    transform 0.2s ease,
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.item-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
}

.card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
}

.meeting-content {
  min-width: 0;
}

.meeting-content h3 {
  margin: 0 0 7px;
  color: #111827;
  font-size: 19px;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.meeting-content p {
  margin: 0;
  color: #4b5563;
  line-height: 1.55;
  overflow-wrap: anywhere;
}

.date-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 7px 11px;
  color: #1d4ed8;
  background: #dbeafe;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

/* Metadaten */

.meta-list {
  display: grid;
  grid-template-columns: minmax(130px, auto) minmax(0, 1fr);
  gap: 14px 24px;
  padding: 14px 0;
  margin-top: 14px;
  color: #475569;
  border-top: 1px solid #f1f5f9;
  font-size: 13px;
}

.meta-item {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  min-width: 0;
}

.meta-label {
  flex-shrink: 0;
  color: #64748b;
  font-weight: 700;
}

.location-item span:last-child,
.meeting-link {
  overflow-wrap: anywhere;
}

.meeting-link {
  color: #2563eb;
  text-decoration: none;
}

.meeting-link:hover {
  text-decoration: underline;
}

/* Kartenaktionen */

.card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 9px;
}

.card-actions button {
  min-height: 38px;
  padding: 8px 13px;
}

/* Meldungen */

.info-box,
.message {
  padding: 16px;
  border-radius: 11px;
  font-size: 14px;
  line-height: 1.5;
}

.info-box {
  color: #64748b;
  text-align: center;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
}

.message {
  margin-bottom: 18px;
}

.error-message {
  color: #991b1b;
  background: #fee2e2;
  border: 1px solid #fecaca;
}

/* Tablet */

@media (max-width: 1000px) {
  .layout-grid {
    grid-template-columns: 1fr;
  }

  .form-panel {
    position: static;
  }

  .list-panel {
    min-height: auto;
  }
}

/* Smartphone */

@media (max-width: 640px) {
  .meetings-page {
    padding: 20px 14px;
  }

  .page-header {
    margin-bottom: 20px;
  }

  .page-header h1 {
    font-size: 28px;
  }

  .page-header p {
    font-size: 15px;
  }

  .panel {
    padding: 20px;
    border-radius: 14px;
  }

  .panel-header {
    flex-direction: column;
    gap: 12px;
  }

  .panel-header h2 {
    font-size: 21px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }

  .card-top {
    flex-direction: column;
    gap: 12px;
  }

  .date-badge {
    align-self: flex-start;
  }

  .meta-list {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .meta-item {
    flex-direction: column;
    gap: 3px;
  }

  .card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  .card-actions button {
    width: 100%;
  }
}
</style>