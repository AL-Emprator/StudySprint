<template>
  <div class="tasks-page">
    <header class="page-header">
      <div>
        <h1>Aufgaben</h1>
        <p>Hier kannst du Aufgaben anlegen und verwalten.</p>
      </div>
    </header>

    <p v-if="errorMessage" class="message error-message">
      {{ errorMessage }}
    </p>

    <div class="layout-grid">
      <!-- Formular -->
      <section class="panel form-panel">
        <div class="panel-header">
          <div>
            <h2>
              {{ isEditMode ? 'Aufgabe bearbeiten' : 'Neue Aufgabe' }}
            </h2>

            <p>
              {{
                isEditMode
                  ? 'Bearbeite die ausgewählte Aufgabe.'
                  : 'Erstelle eine neue Aufgabe für deine Gruppe.'
              }}
            </p>
          </div>
        </div>

        <form class="task-form" @submit.prevent="handleSubmitTask">
          <!-- Gruppe -->
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

            <p v-if="isLoadingGroups" class="field-info">
              Gruppen werden geladen...
            </p>

            <p v-else-if="isEditMode" class="field-info">
              Die Gruppe kann beim Bearbeiten nicht geändert werden.
            </p>
          </div>

          <!-- Titel -->
          <div class="form-group">
            <label for="task-title">Titel</label>

            <input
              id="task-title"
              v-model.trim="form.title"
              type="text"
              placeholder="Titel der Aufgabe"
              required
            />
          </div>

          <!-- Beschreibung -->
          <div class="form-group">
            <label for="task-description">Beschreibung</label>

            <textarea
              id="task-description"
              v-model.trim="form.description"
              rows="4"
              placeholder="Beschreibe die Aufgabe"
            ></textarea>
          </div>

          <!-- Priorität und Status -->
          <div class="form-row">
            <div class="form-group">
              <label for="task-priority">Priorität</label>

              <select
                id="task-priority"
                v-model="form.priority"
              >
                <option value="low">Niedrig</option>
                <option value="medium">Mittel</option>
                <option value="high">Hoch</option>
              </select>
            </div>

            <div class="form-group">
              <label for="task-status">Status</label>

              <select
                id="task-status"
                v-model="form.status"
              >
                <option value="open">Offen</option>
                <option value="in_progress">In Arbeit</option>
                <option value="done">Erledigt</option>
              </select>
            </div>
          </div>

          <!-- Fälligkeitsdatum -->
          <div class="form-group">
            <label for="task-date">Fälligkeitsdatum</label>

            <input
              id="task-date"
              v-model="form.due_date"
              type="date"
            />
          </div>

          <!-- Formularaktionen -->
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
                    ? 'Aufgabe speichern'
                    : 'Aufgabe erstellen'
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

      <!-- Aufgabenliste -->
      <section class="panel list-panel">
        <div class="panel-header list-header">
          <div>
            <h2>Aufgabenliste</h2>

            <p v-if="selectedGroup">
              Gruppe: {{ selectedGroup.name }}
            </p>

            <p v-else>
              Wähle eine Gruppe aus, um Aufgaben anzuzeigen.
            </p>
          </div>

          <span v-if="selectedGroupId" class="task-count">
            {{ tasks.length }}
            {{ tasks.length === 1 ? 'Aufgabe' : 'Aufgaben' }}
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
          Aufgaben werden geladen...
        </div>

        <div
          v-else-if="tasks.length === 0"
          class="info-box"
        >
          Für diese Gruppe sind noch keine Aufgaben vorhanden.
        </div>

        <div v-else class="task-list">
          <article
            v-for="task in tasks"
            :key="task.id"
            class="task-card"
            :class="`task-card--${task.status}`"
          >
            <div class="card-top">
              <div class="task-content">
                <h3>{{ task.title }}</h3>

                <p>
                  {{
                    task.description ||
                    'Keine Beschreibung vorhanden.'
                  }}
                </p>
              </div>

              <span
                class="status-badge"
                :class="`status-badge--${task.status}`"
              >
                {{ formatStatus(task.status) }}
              </span>
            </div>

            <div class="task-meta">
              <span
                class="priority-badge"
                :class="`priority-badge--${task.priority}`"
              >
                {{ formatPriority(task.priority) }}
              </span>

              <span class="due-date">
                <strong>Fällig:</strong>
                {{ formatDate(task.due_date) }}
              </span>
            </div>

            <div class="card-actions">
              <button
                type="button"
                class="action-button edit-button"
                @click="startEdit(task)"
              >
                Bearbeiten
              </button>

              <button
                type="button"
                class="action-button"
                :class="{ active: task.status === 'open' }"
                @click="changeStatus(task.id, 'open')"
              >
                Offen
              </button>

              <button
                type="button"
                class="action-button"
                :class="{ active: task.status === 'in_progress' }"
                @click="changeStatus(task.id, 'in_progress')"
              >
                In Arbeit
              </button>

              <button
                type="button"
                class="action-button"
                :class="{ active: task.status === 'done' }"
                @click="changeStatus(task.id, 'done')"
              >
                Erledigt
              </button>

              <button
                type="button"
                class="danger-button"
                @click="handleDeleteTask(task.id)"
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
  getTasks,
  createTask,
  updateTask,
  updateTaskStatus,
  deleteTask as deleteTaskRequest,
} from '../services/TaskService'

import {
  getGroups,
} from '../services/groupService'

const route = useRoute()

const groups = ref([])
const tasks = ref([])

const selectedGroupId = ref('')
const editingTaskId = ref(null)

const isLoading = ref(false)
const isLoadingGroups = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')

const form = reactive({
  title: '',
  description: '',
  priority: 'medium',
  due_date: '',
  status: 'open',
  sprint_id: null,
  assigned_user_id: null,
})

const isEditMode = computed(
  () => editingTaskId.value !== null
)

const selectedGroup = computed(() => {
  return groups.value.find(
    group =>
      Number(group.id) === Number(selectedGroupId.value)
  ) ?? null
})

function formatDate(date) {
  if (!date) {
    return 'Kein Datum'
  }

  const parsedDate = new Date(date)

  if (Number.isNaN(parsedDate.getTime())) {
    return date
  }

  return new Intl.DateTimeFormat('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(parsedDate)
}

async function loadGroups() {
  isLoadingGroups.value = true
  errorMessage.value = ''

  try {
    const data = await getGroups()

    groups.value = Array.isArray(data) ? data : []

    /*
     * Falls die Seite über /groups/:id/tasks geöffnet wird,
     * wird diese Gruppe automatisch vorausgewählt.
     */
    const routeGroupId =
      route.params.groupId ?? route.params.id

    if (
      routeGroupId &&
      groups.value.some(
        group => Number(group.id) === Number(routeGroupId)
      )
    ) {
      selectedGroupId.value = Number(routeGroupId)
      return
    }

    /*
     * Optional: Bei genau einer Gruppe automatisch auswählen.
     */
    if (groups.value.length === 1) {
      selectedGroupId.value = groups.value[0].id
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

async function loadTasks() {
  tasks.value = []
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    return
  }

  isLoading.value = true

  try {
    const data = await getTasks(
      selectedGroupId.value
    )

    tasks.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error(
      'Fehler beim Laden der Aufgaben:',
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
        'Die Aufgaben konnten nicht geladen werden.'
    }
  } finally {
    isLoading.value = false
  }
}

async function handleSubmitTask() {
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    errorMessage.value =
      'Bitte wähle zuerst eine Gruppe aus.'
    return
  }

  if (!form.title.trim()) {
    errorMessage.value =
      'Bitte gib einen Titel für die Aufgabe ein.'
    return
  }

  isSaving.value = true

  const taskData = {
    title: form.title.trim(),
    description:
      form.description.trim() || null,
    priority: form.priority,
    due_date: form.due_date || null,
    status: form.status,
    sprint_id: form.sprint_id || null,
    assigned_user_id:
      form.assigned_user_id || null,
  }

  try {
    if (isEditMode.value) {
      /*
       * Beim Bearbeiten wird nur die Task-ID gebraucht:
       * PUT /tasks/{task}
       */
      const updatedTask = await updateTask(
        editingTaskId.value,
        taskData
      )

      const index = tasks.value.findIndex(
        task => task.id === editingTaskId.value
      )

      if (index !== -1) {
        tasks.value[index] = updatedTask
      }
    } else {
      /*
       * Beim Erstellen wird die gewählte Gruppen-ID verwendet:
       * POST /groups/{groupId}/tasks
       */
      const newTask = await createTask(
        selectedGroupId.value,
        taskData
      )

      tasks.value.unshift(newTask)
    }

    resetForm()
  } catch (error) {
    console.error(
      'Fehler beim Speichern der Aufgabe:',
      error
    )

    if (error.response?.status === 422) {
      const errors =
        error.response?.data?.errors

      errorMessage.value =
        errors?.title?.[0] ??
        errors?.priority?.[0] ??
        errors?.due_date?.[0] ??
        errors?.status?.[0] ??
        errors?.sprint_id?.[0] ??
        errors?.assigned_user_id?.[0] ??
        'Bitte überprüfe deine Eingaben.'
    } else if (error.response?.status === 403) {
      errorMessage.value =
        'Du darfst in dieser Gruppe keine Aufgaben bearbeiten.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Aufgabe konnte nicht gespeichert werden.'
    }
  } finally {
    isSaving.value = false
  }
}

function startEdit(task) {
  editingTaskId.value = task.id

  form.title = task.title
  form.description = task.description ?? ''
  form.priority = task.priority ?? 'medium'
  form.due_date = task.due_date
    ? task.due_date.substring(0, 10)
    : ''
  form.status = task.status ?? 'open'
  form.sprint_id = task.sprint_id ?? null
  form.assigned_user_id =
    task.assigned_user_id ?? null
}

function cancelEdit() {
  resetForm()
}

async function changeStatus(taskId, status) {
  errorMessage.value = ''

  try {
    const updatedTask =
      await updateTaskStatus(taskId, status)

    const index = tasks.value.findIndex(
      task => task.id === taskId
    )

    if (index !== -1) {
      tasks.value[index] = {
        ...tasks.value[index],
        ...updatedTask,
      }
    }
  } catch (error) {
    console.error(
      'Fehler beim Ändern des Status:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ??
      'Der Status konnte nicht geändert werden.'
  }
}

async function handleDeleteTask(taskId) {
  const confirmed = window.confirm(
    'Möchtest du diese Aufgabe wirklich löschen?'
  )

  if (!confirmed) {
    return
  }

  errorMessage.value = ''

  try {
    await deleteTaskRequest(taskId)

    tasks.value = tasks.value.filter(
      task => task.id !== taskId
    )

    if (editingTaskId.value === taskId) {
      resetForm()
    }
  } catch (error) {
    console.error(
      'Fehler beim Löschen der Aufgabe:',
      error
    )

    errorMessage.value =
      error.response?.data?.message ??
      'Die Aufgabe konnte nicht gelöscht werden.'
  }
}

function resetForm() {
  editingTaskId.value = null
  form.title = ''
  form.description = ''
  form.priority = 'medium'
  form.due_date = ''
  form.status = 'open'
  form.sprint_id = null
  form.assigned_user_id = null
}

function formatStatus(status) {
  if (status === 'open') {
    return 'Offen'
  }

  if (status === 'in_progress') {
    return 'In Arbeit'
  }

  if (status === 'done') {
    return 'Erledigt'
  }

  return status
}

function formatPriority(priority) {
  if (priority === 'low') {
    return 'Niedrig'
  }

  if (priority === 'medium') {
    return 'Mittel'
  }

  if (priority === 'high') {
    return 'Hoch'
  }

  return priority
}

/*
 * Bei einem Gruppenwechsel werden automatisch
 * die Aufgaben der ausgewählten Gruppe geladen.
 */
watch(selectedGroupId, async () => {
  resetForm()
  await loadTasks()
})

onMounted(async () => {
  await loadGroups()

  if (selectedGroupId.value) {
    await loadTasks()
  }
})
</script>

<style scoped>
.tasks-page {
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
  grid-template-columns: minmax(340px, 420px) minmax(0, 1fr);
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

/* Überschriften */

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

.task-count {
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

.task-form {
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

input,
textarea,
select {
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
  min-height: 110px;
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

select:disabled,
input:disabled,
textarea:disabled {
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

.primary-button {
  color: #ffffff;
  background: #2563eb;
}

.primary-button:hover:not(:disabled) {
  background: #1d4ed8;
}

.secondary-button {
  color: #374151;
  background: #e5e7eb;
}

.secondary-button:hover:not(:disabled) {
  background: #d1d5db;
}

/* Aufgabenliste */

.task-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.task-card {
  padding: 18px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-left: 4px solid #cbd5e1;
  border-radius: 13px;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.task-card:hover {
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
  transform: translateY(-1px);
}

.task-card--open {
  border-left-color: #6366f1;
}

.task-card--in_progress {
  border-left-color: #f59e0b;
}

.task-card--done {
  border-left-color: #22c55e;
}

.card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
}

.task-content {
  min-width: 0;
}

.task-content h3 {
  margin: 0 0 7px;
  color: #111827;
  font-size: 18px;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.task-content p {
  margin: 0;
  color: #4b5563;
  line-height: 1.55;
  overflow-wrap: anywhere;
}

/* Metadaten */

.task-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 14px 0;
  margin-top: 14px;
  border-top: 1px solid #f1f5f9;
  color: #64748b;
  font-size: 13px;
}

.due-date {
  overflow-wrap: anywhere;
}

.priority-badge,
.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.status-badge {
  flex-shrink: 0;
  padding: 7px 11px;
}

.status-badge--open {
  color: #3730a3;
  background: #e0e7ff;
}

.status-badge--in_progress {
  color: #92400e;
  background: #fef3c7;
}

.status-badge--done {
  color: #166534;
  background: #dcfce7;
}

.priority-badge {
  padding: 5px 9px;
}

.priority-badge--low {
  color: #166534;
  background: #dcfce7;
}

.priority-badge--medium {
  color: #92400e;
  background: #fef3c7;
}

.priority-badge--high {
  color: #991b1b;
  background: #fee2e2;
}

/* Kartenaktionen */

.card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.action-button {
  min-height: 38px;
  padding: 8px 12px;
  color: #374151;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}

.action-button:hover {
  color: #1d4ed8;
  background: #eff6ff;
  border-color: #bfdbfe;
}

.action-button.active {
  color: #ffffff;
  background: #2563eb;
  border-color: #2563eb;
}

.edit-button {
  color: #1d4ed8;
  background: #eff6ff;
  border-color: #bfdbfe;
}

.danger-button {
  min-height: 38px;
  padding: 8px 12px;
  color: #ffffff;
  background: #dc2626;
}

.danger-button:hover {
  background: #b91c1c;
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
  .tasks-page {
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

  .card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  .card-actions button {
    width: 100%;
  }

  .danger-button {
    grid-column: 1 / -1;
  }
}
</style>