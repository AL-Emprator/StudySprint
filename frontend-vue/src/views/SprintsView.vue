
<template>
  <div class="sprints-page">
    <header class="page-header">
      <h1>Sprints</h1>
      <p>Hier kannst du Sprints anlegen und verwalten.</p>
    </header>

    <p
      v-if="errorMessage"
      class="message error-message"
    >
      {{ errorMessage }}
    </p>

    <div class="layout-grid">
      <!-- Sprint-Formular -->
      <section class="panel form-panel">
        <div class="panel-header">
          <div>
            <h2>
              {{
                isEditMode
                  ? 'Sprint bearbeiten'
                  : 'Neuer Sprint'
              }}
            </h2>

            <p>
              {{
                isEditMode
                  ? 'Passe die Daten des ausgewählten Sprints an.'
                  : 'Erstelle einen neuen Sprint für deine Gruppe.'
              }}
            </p>
          </div>
        </div>

        <form
          class="sprint-form"
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
              placeholder="Titel des Sprints"
              required
            />
          </div>

          <div class="form-group">
            <label for="description">
              Beschreibung
            </label>

            <textarea
              id="description"
              v-model.trim="form.description"
              rows="5"
              placeholder="Beschreibe das Ziel des Sprints"
            ></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="startDate">
                Startdatum
              </label>

              <input
                id="startDate"
                v-model="form.start_date"
                type="date"
                required
              />
            </div>

            <div class="form-group">
              <label for="endDate">
                Enddatum
              </label>

              <input
                id="endDate"
                v-model="form.end_date"
                type="date"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="status">Status</label>

            <select
              id="status"
              v-model="form.status"
            >
              <option value="planned">
                Geplant
              </option>

              <option value="active">
                Aktiv
              </option>

              <option value="finished">
                Beendet
              </option>
            </select>
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
                    ? 'Sprint speichern'
                    : 'Sprint erstellen'
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

      <!-- Sprintliste -->
      <section class="panel list-panel">
        <div class="panel-header list-header">
          <div>
            <h2>Sprintliste</h2>

            <p v-if="selectedGroup">
              Gruppe: {{ selectedGroup.name }}
            </p>

            <p v-else>
              Wähle eine Gruppe aus, um Sprints anzuzeigen.
            </p>
          </div>

          <span
            v-if="selectedGroupId"
            class="sprint-count"
          >
            {{ sprints.length }}
            {{ sprints.length === 1 ? 'Sprint' : 'Sprints' }}
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
          Sprints werden geladen...
        </div>

        <div
          v-else-if="sprints.length === 0"
          class="info-box"
        >
          Für diese Gruppe sind noch keine Sprints vorhanden.
        </div>

        <div
          v-else
          class="card-list"
        >
          <article
            v-for="sprint in sprints"
            :key="sprint.id"
            class="item-card"
            :class="`item-card--${sprint.status}`"
          >
            <div class="card-top">
              <div class="sprint-content">
                <h3>{{ sprint.title }}</h3>

                <p>
                  {{
                    sprint.description ||
                    'Keine Beschreibung vorhanden.'
                  }}
                </p>
              </div>

              <span
                class="status-badge"
                :class="`status-badge--${sprint.status}`"
              >
                {{ formatStatus(sprint.status) }}
              </span>
            </div>

            <div class="meta-list">
              <div class="date-item">
                <span class="meta-label">Start</span>
                <span>{{ formatDate(sprint.start_date) }}</span>
              </div>

              <div class="date-separator">
                →
              </div>

              <div class="date-item">
                <span class="meta-label">Ende</span>
                <span>{{ formatDate(sprint.end_date) }}</span>
              </div>
            </div>

            <div class="card-actions">
              <button
                type="button"
                class="edit-button"
                @click="startEdit(sprint)"
              >
                Bearbeiten
              </button>

              <button
                type="button"
                class="danger-button"
                @click="handleDeleteSprint(sprint.id)"
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
  getSprints,
  createSprint,
  updateSprint,
  deleteSprint as deleteSprintRequest,
} from '../services/sprintService'

import {
  getGroups,
} from '../services/groupService'

const route = useRoute()

const groups = ref([])
const sprints = ref([])

const selectedGroupId = ref('')
const editingSprintId = ref(null)

const isLoadingGroups = ref(false)
const isLoading = ref(false)
const isSaving = ref(false)

const errorMessage = ref('')

const form = reactive({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  status: 'planned',
})

const isEditMode = computed(() => {
  return editingSprintId.value !== null
})

const selectedGroup = computed(() => {
  return groups.value.find(
    group =>
      Number(group.id) ===
      Number(selectedGroupId.value)
  ) ?? null
})

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

async function loadSprints() {
  sprints.value = []
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    return
  }

  isLoading.value = true

  try {
    const data = await getSprints(
      selectedGroupId.value
    )

    sprints.value = Array.isArray(data)
      ? data
      : []
  } catch (error) {
    console.error(
      'Fehler beim Laden der Sprints:',
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
        'Die Sprints konnten nicht geladen werden.'
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

  if (!form.start_date) {
    errorMessage.value =
      'Bitte gib ein Startdatum ein.'

    return
  }

  if (!form.end_date) {
    errorMessage.value =
      'Bitte gib ein Enddatum ein.'

    return
  }

  if (form.end_date < form.start_date) {
    errorMessage.value =
      'Das Enddatum darf nicht vor dem Startdatum liegen.'

    return
  }

  isSaving.value = true

  const sprintData = {
    title: form.title.trim(),
    description:
      form.description.trim() || null,
    start_date: form.start_date,
    end_date: form.end_date,
    status: form.status,
  }

  try {
    if (isEditMode.value) {
      const updatedSprint =
        await updateSprint(
          editingSprintId.value,
          sprintData
        )

      const index = sprints.value.findIndex(
        sprint =>
          sprint.id === editingSprintId.value
      )

      if (index !== -1) {
        sprints.value[index] =
          updatedSprint
      }
    } else {
      const newSprint =
        await createSprint(
          selectedGroupId.value,
          sprintData
        )

      sprints.value.unshift(newSprint)
    }

    resetForm()
  } catch (error) {
    console.error(
      'Fehler beim Speichern des Sprints:',
      error
    )

    if (error.response?.status === 422) {
      const errors =
        error.response?.data?.errors

      errorMessage.value =
        errors?.title?.[0] ??
        errors?.description?.[0] ??
        errors?.start_date?.[0] ??
        errors?.end_date?.[0] ??
        errors?.status?.[0] ??
        'Bitte überprüfe deine Eingaben.'
    } else if (error.response?.status === 403) {
      errorMessage.value =
        'Du darfst in dieser Gruppe keine Sprints bearbeiten.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Der Sprint konnte nicht gespeichert werden.'
    }
  } finally {
    isSaving.value = false
  }
}

function startEdit(sprint) {
  editingSprintId.value = sprint.id

  form.title = sprint.title
  form.description =
    sprint.description ?? ''

  form.start_date = normalizeDate(
    sprint.start_date
  )

  form.end_date = normalizeDate(
    sprint.end_date
  )

  form.status =
    sprint.status ?? 'planned'
}

function cancelEdit() {
  resetForm()
}

async function handleDeleteSprint(sprintId) {
  const confirmed = window.confirm(
    'Möchtest du diesen Sprint wirklich löschen?'
  )

  if (!confirmed) {
    return
  }

  errorMessage.value = ''

  try {
    await deleteSprintRequest(sprintId)

    sprints.value = sprints.value.filter(
      sprint => sprint.id !== sprintId
    )

    if (
      editingSprintId.value === sprintId
    ) {
      resetForm()
    }
  } catch (error) {
    console.error(
      'Fehler beim Löschen des Sprints:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ??
      'Der Sprint konnte nicht gelöscht werden.'
  }
}

function resetForm() {
  editingSprintId.value = null

  form.title = ''
  form.description = ''
  form.start_date = ''
  form.end_date = ''
  form.status = 'planned'
}

function normalizeDate(date) {
  if (!date) {
    return ''
  }

  return String(date).substring(0, 10)
}

function formatDate(date) {
  if (!date) {
    return 'kein Datum'
  }

  return normalizeDate(date)
}

function formatStatus(status) {
  if (status === 'planned') {
    return 'Geplant'
  }

  if (status === 'active') {
    return 'Aktiv'
  }

  if (status === 'finished') {
    return 'Beendet'
  }

  return status
}

watch(selectedGroupId, async () => {
  resetForm()
  await loadSprints()
})

onMounted(async () => {
  await loadGroups()

  if (selectedGroupId.value) {
    await loadSprints()
  }
})
</script>

<style scoped>
.sprints-page {
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

/* Panel-Überschriften */

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

.sprint-count {
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

.sprint-form {
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

/* Sprintkarten */

.card-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.item-card {
  padding: 18px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #cbd5e1;
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

.item-card--planned {
  border-left-color: #6366f1;
}

.item-card--active {
  border-left-color: #f59e0b;
}

.item-card--finished {
  border-left-color: #22c55e;
}

.card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
}

.sprint-content {
  min-width: 0;
}

.sprint-content h3 {
  margin: 0 0 7px;
  color: #111827;
  font-size: 19px;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.sprint-content p {
  margin: 0;
  color: #4b5563;
  line-height: 1.55;
  overflow-wrap: anywhere;
}

/* Status */

.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  padding: 7px 11px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.status-badge--planned {
  color: #3730a3;
  background: #e0e7ff;
}

.status-badge--active {
  color: #92400e;
  background: #fef3c7;
}

.status-badge--finished {
  color: #166534;
  background: #dcfce7;
}

/* Datumsbereich */

.meta-list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
  padding: 14px 0;
  margin-top: 14px;
  color: #475569;
  border-top: 1px solid #f1f5f9;
  font-size: 13px;
}

.date-item {
  display: flex;
  align-items: center;
  gap: 7px;
}

.meta-label {
  color: #64748b;
  font-weight: 700;
}

.date-separator {
  color: #94a3b8;
  font-weight: 700;
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
  .sprints-page {
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

  .status-badge {
    align-self: flex-start;
  }

  .meta-list {
    align-items: flex-start;
    flex-direction: column;
    gap: 8px;
  }

  .date-separator {
    display: none;
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