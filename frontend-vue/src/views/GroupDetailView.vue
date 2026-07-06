<template>
  <div class="group-details-page">
    <header class="page-header">
      <h1>Gruppendetails</h1>
      <p>Hier siehst und verwaltest du eine einzelne Gruppe.</p>
    </header>

    <div v-if="isLoading" class="status-box">
      Gruppe wird geladen...
    </div>

    <div v-else-if="errorMessage" class="message error-message">
      {{ errorMessage }}
    </div>

    <div v-else-if="group" class="layout-grid">
      <!-- Gruppeninformationen -->
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Gruppeninformationen</h2>
            <p>Allgemeine Informationen zu deiner Gruppe.</p>
          </div>
        </div>

        <div v-if="!isEditMode" class="group-information">
          <div class="information-row">
            <span class="information-label">Name</span>
            <span class="information-value">
              {{ group.name }}
            </span>
          </div>

          <div class="information-row">
            <span class="information-label">Beschreibung</span>
            <span class="information-value">
              {{ group.description || 'Keine Beschreibung vorhanden.' }}
            </span>
          </div>

          <div v-if="isCurrentUserOwner" class="panel-actions">
            <button
              type="button"
              class="primary-button"
              @click="startEdit"
            >
              Gruppe bearbeiten
            </button>
          </div>
        </div>

        <form
          v-else
          class="form"
          @submit.prevent="handleUpdate"
        >
          <div class="form-group">
            <label for="name">Name</label>

            <input
              id="name"
              v-model.trim="groupForm.name"
              type="text"
              placeholder="Name der Gruppe"
              required
            />
          </div>

          <div class="form-group">
            <label for="description">Beschreibung</label>

            <textarea
              id="description"
              v-model.trim="groupForm.description"
              rows="5"
              placeholder="Beschreibe kurz den Zweck der Gruppe"
            ></textarea>
          </div>

          <div class="form-actions">
            <button
              type="submit"
              class="primary-button"
              :disabled="isSaving"
            >
              {{ isSaving ? 'Wird gespeichert...' : 'Änderungen speichern' }}
            </button>

            <button
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

      <!-- Mitglieder -->
      <section class="panel">
        <div class="panel-header">
          <div>
            <h2>Mitglieder</h2>
            <p>Verwalte den Besitzer und die Mitglieder der Gruppe.</p>
          </div>

          <span class="member-count">
            {{ membersWithoutOwner.length + (group.owner ? 1 : 0) }}
            Mitglieder
          </span>
        </div>

        <!-- Gruppenbesitzer -->
        <div v-if="group.owner" class="member-section">
          <h3>Gruppenbesitzer</h3>

          <article class="member-card owner-card">
            <div class="member-avatar">
              {{ group.owner.name?.charAt(0)?.toUpperCase() || '?' }}
            </div>

            <div class="member-content">
              <strong>{{ group.owner.name }}</strong>
              <p>{{ group.owner.email }}</p>
            </div>

            <span class="role-badge owner-badge">
              Besitzer
            </span>
          </article>
        </div>

        <!-- Mitglied hinzufügen -->
        <div v-if="isCurrentUserOwner" class="add-member-section">
          <h3>Mitglied hinzufügen</h3>

          <form
            class="add-member-form"
            @submit.prevent="handleAddMember"
          >
            <div class="form-group">
              <label for="memberEmail">E-Mail-Adresse</label>

              <div class="add-member-row">
                <input
                  id="memberEmail"
                  v-model.trim="memberForm.email"
                  type="email"
                  placeholder="mitglied@example.com"
                  required
                />

                <button
                  type="submit"
                  class="primary-button add-button"
                  :disabled="isAddingMember"
                >
                  {{
                    isAddingMember
                      ? 'Wird hinzugefügt...'
                      : 'Hinzufügen'
                  }}
                </button>
              </div>
            </div>
          </form>

          <p v-if="memberMessage" class="message success-message">
            {{ memberMessage }}
          </p>

          <p v-if="memberError" class="message error-message">
            {{ memberError }}
          </p>
        </div>

        <!-- Weitere Mitglieder -->
        <div class="member-section">
          <h3>Weitere Mitglieder</h3>

          <div
            v-if="membersWithoutOwner.length > 0"
            class="member-list"
          >
            <article
              v-for="member in membersWithoutOwner"
              :key="member.id"
              class="member-card"
            >
              <div class="member-avatar">
                {{ member.name?.charAt(0)?.toUpperCase() || '?' }}
              </div>

              <div class="member-content">
                <strong>{{ member.name }}</strong>
                <p>{{ member.email }}</p>
              </div>

              <span class="role-badge">
                {{ formatRole(member.pivot?.role) }}
              </span>
            </article>
          </div>

          <div v-else class="empty-state">
            Diese Gruppe hat noch keine weiteren Mitglieder.
          </div>
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
} from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../services/auth'
import {
  addMember,
  getGroup,
  updateGroup,
} from '../services/groupService'

const route = useRoute()
const authStore = useAuthStore()

const group = ref(null)


const memberMessage = ref('')
const memberError = ref('')
const memberForm = reactive({
  email: '',
})

const isLoading = ref(false)
const isSaving = ref(false)
const isEditMode = ref(false)
const errorMessage = ref('')

const groupForm = reactive({
  name: '',
  description: '',
})

const groupId = computed(() => route.params.id)

const membersWithoutOwner = computed(() => {
  if (!group.value?.users) {
    return []
  }

  return group.value.users.filter(
    user => user.id !== group.value.owner_id
  )
})

const isCurrentUserOwner = computed(() => {
  if (!group.value || !authStore.user) {
    return false
  }

  return Number(group.value.owner_id) === Number(authStore.user.id)
})

async function loadGroup() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    group.value = await getGroup(groupId.value)
  } catch (error) {
    console.error('Fehler beim Laden der Gruppe:', error)

    if (error.response?.status === 403) {
      errorMessage.value =
        'Du hast keinen Zugriff auf diese Gruppe.'
    } else if (error.response?.status === 404) {
      errorMessage.value =
        'Die Gruppe wurde nicht gefunden.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Gruppendaten konnten nicht geladen werden.'
    }
  } finally {
    isLoading.value = false
  }
}

function startEdit() {
  if (!group.value) {
    return
  }

  groupForm.name = group.value.name
  groupForm.description = group.value.description ?? ''
  isEditMode.value = true
}

function cancelEdit() {
  isEditMode.value = false
  groupForm.name = ''
  groupForm.description = ''
}

async function handleUpdate() {
  if (!groupForm.name.trim()) {
    errorMessage.value = 'Bitte gib einen Gruppennamen ein.'
    return
  }

  isSaving.value = true
  errorMessage.value = ''

  try {
    const updatedGroup = await updateGroup(groupId.value, {
      name: groupForm.name.trim(),
      description: groupForm.description.trim() || null,
    })

    group.value = {
      ...group.value,
      ...updatedGroup,
    }

    cancelEdit()
  } catch (error) {
    console.error('Fehler beim Aktualisieren:', error)

    if (error.response?.status === 403) {
      errorMessage.value =
        'Nur der Gruppenbesitzer darf die Gruppe bearbeiten.'
    } else if (error.response?.status === 422) {
      errorMessage.value =
        error.response?.data?.errors?.name?.[0] ??
        error.response?.data?.errors?.description?.[0] ??
        'Bitte überprüfe deine Eingaben.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Gruppe konnte nicht aktualisiert werden.'
    }
  } finally {
    isSaving.value = false
  }
}

function formatRole(role) {
  if (role === 'owner') {
    return 'Besitzer'
  }

  if (role === 'member') {
    return 'Mitglied'
  }

  return role || 'Mitglied'
}

const isAddingMember = ref(false)

async function handleAddMember() {
  memberMessage.value = ''
  memberError.value = ''

  if (!memberForm.email.trim()) {
    memberError.value = 'Bitte gib eine E-Mail-Adresse ein.'
    return
  }

  isAddingMember.value = true

  try {
    await addMember(
      groupId.value,
      memberForm.email.trim()
    )

    memberMessage.value =
      'Mitglied wurde erfolgreich hinzugefügt.'

    memberForm.email = ''

    await loadGroup()
  } catch (error) {
    if (error.response?.status === 403) {
      memberError.value =
        'Nur der Gruppenbesitzer darf Mitglieder hinzufügen.'
    } else if (error.response?.status === 422) {
      memberError.value =
        error.response?.data?.errors?.email?.[0] ??
        error.response?.data?.message ??
        'Das Mitglied konnte nicht hinzugefügt werden.'
    } else if (error.response?.status === 404) {
      memberError.value =
        'Der Benutzer wurde nicht gefunden.'
    } else {
      memberError.value =
        error.response?.data?.message ??
        'Das Mitglied konnte nicht hinzugefügt werden.'
    }
  } finally {
    isAddingMember.value = false
  }
}

onMounted(loadGroup)
</script>


<style scoped>
.group-details-page {
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

.layout-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  align-items: start;
  gap: 24px;
}

.panel {
  min-width: 0;
  padding: 28px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.panel-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding-bottom: 20px;
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

.member-count {
  flex-shrink: 0;
  padding: 7px 11px;
  color: #1d4ed8;
  background: #eff6ff;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
}

/* Gruppeninformationen */

.group-information {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.information-row {
  display: grid;
  grid-template-columns: 140px minmax(0, 1fr);
  gap: 16px;
  padding-bottom: 18px;
  border-bottom: 1px solid #f1f5f9;
}

.information-label {
  color: #475569;
  font-weight: 700;
}

.information-value {
  color: #111827;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.panel-actions {
  display: flex;
  justify-content: flex-start;
  margin-top: 6px;
}

/* Formulare */

.form,
.add-member-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.form-group label {
  color: #374151;
  font-size: 14px;
  font-weight: 700;
}

input,
textarea {
  width: 100%;
  padding: 12px 14px;
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

input::placeholder,
textarea::placeholder {
  color: #9ca3af;
}

input:focus,
textarea:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

textarea {
  min-height: 120px;
  resize: vertical;
}

.form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

button {
  min-height: 44px;
  padding: 10px 18px;
  border: none;
  border-radius: 9px;
  cursor: pointer;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  transition:
    background-color 0.2s ease,
    transform 0.1s ease,
    opacity 0.2s ease;
}

button:active:not(:disabled) {
  transform: translateY(1px);
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
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

/* Mitglieder */

.member-section + .member-section,
.add-member-section + .member-section {
  padding-top: 24px;
  margin-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.member-section h3,
.add-member-section h3 {
  margin: 0 0 14px;
  color: #334155;
  font-size: 15px;
}

.member-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.member-card {
  display: grid;
  grid-template-columns: 46px minmax(0, 1fr) auto;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.member-card:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}

.owner-card {
  background: #eff6ff;
  border-color: #93c5fd;
}

.owner-card:hover {
  background: #eff6ff;
  border-color: #60a5fa;
}

.member-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  color: #1d4ed8;
  background: #dbeafe;
  border-radius: 50%;
  font-size: 18px;
  font-weight: 800;
}

.member-content {
  min-width: 0;
}

.member-content strong {
  display: block;
  color: #111827;
  font-size: 15px;
  overflow-wrap: anywhere;
}

.member-content p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 14px;
  overflow-wrap: anywhere;
}

.role-badge {
  padding: 6px 10px;
  color: #475569;
  background: #f1f5f9;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.owner-badge {
  color: #1d4ed8;
  background: #dbeafe;
}

/* Mitglied hinzufügen */

.add-member-section {
  margin-top: 24px;
}

.add-member-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 10px;
}

.add-button {
  white-space: nowrap;
}

/* Meldungen */

.message,
.status-box,
.empty-state {
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 14px;
  line-height: 1.5;
}

.message {
  margin: 14px 0 0;
}

.status-box {
  color: #475569;
  background: #ffffff;
  border: 1px solid #e2e8f0;
}

.success-message {
  color: #166534;
  background: #dcfce7;
  border: 1px solid #bbf7d0;
}

.error-message {
  color: #991b1b;
  background: #fee2e2;
  border: 1px solid #fecaca;
}

.empty-state {
  color: #64748b;
  text-align: center;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
}

/* Responsive Darstellung */

@media (max-width: 1000px) {
  .layout-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .group-details-page {
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

  .information-row {
    grid-template-columns: 1fr;
    gap: 5px;
  }

  .add-member-row {
    grid-template-columns: 1fr;
  }

  .add-button,
  .form-actions button {
    width: 100%;
  }

  .member-card {
    grid-template-columns: 42px minmax(0, 1fr);
  }

  .member-avatar {
    width: 42px;
    height: 42px;
  }

  .role-badge {
    grid-column: 2;
    justify-self: start;
  }
}
</style>