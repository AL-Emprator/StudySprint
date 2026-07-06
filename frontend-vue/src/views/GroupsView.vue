<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  getGroups,
  createGroup,
  deleteGroup,
} from '../services/groupService'

const groups = ref([])
const isLoading = ref(false)
const isCreating = ref(false)
const errorMessage = ref('')
/*
const groups = ref([
  {
    id: 1,
    name: 'WT2 Projektgruppe',
    description: 'Zusammen das Semester-Projekt entwickeln.'
  },
  {
    id: 2,
    name: 'Mathe Lerngruppe',
    description: 'Gemeinsam für die Mathe-Prüfung lernen.'
  },
  {
    id: 3,
    name: 'Algorithmen AG',
    description: 'Übungsaufgaben aus der Algorithmik besprechen.'
  }
])
*/


const form = reactive({
  name: '',
  description: ''
})

async function loadGroups() {
  isLoading.value = true
  errorMessage.value = ''

  try {
     const data = await getGroups()
     groups.value = Array.isArray(data) ? data : []

  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Du bist nicht angemeldet.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Gruppen konnten nicht geladen werden.'
    }

    groups.value = []

  } finally {
    isLoading.value = false
  }
}



async function handleCreateGroup() {

  errorMessage.value = ''

  if (!form.name.trim()) {
    errorMessage.value = 'Bitte gib einen Gruppennamen ein.'
    return
  }

  isCreating.value = true

  try {
    const newGroup = await createGroup({
      name: form.name.trim(),
      description: form.description.trim() || null,
    })

    groups.value.push(newGroup)
    
    resetForm()

  } catch (error) {
    console.error('Fehler beim Erstellen der Gruppe:', error)

     errorMessage.value =
      error.response?.data?.errors?.name?.[0] ??
      error.response?.data?.message ??
      'Die Gruppe konnte nicht erstellt werden.'
  }finally {
    isCreating.value = false
  }


  /*
  groups.value.push({
    id: Date.now(),
    name: form.name,
    description: form.description
  })
*/

}

async function handleDeleteGroup(groupId) {
  const confirmed = window.confirm(
    'Möchtest du diese Gruppe wirklich löschen?'
  )

  if (!confirmed) {
    return
  }

  errorMessage.value = ''

  try {
    await deleteGroup(groupId)

    groups.value = groups.value.filter(
      group => group.id !== groupId
    )
  } catch (error) {
    if (error.response?.status === 404) {
      errorMessage.value = 'Die Gruppe wurde nicht gefunden.'
    } else {
      errorMessage.value =
        error.response?.data?.message ??
        'Die Gruppe konnte nicht gelöscht werden.'
    }
  }
}

function resetForm() {
  form.name = ''
  form.description = ''
}

onMounted(() => {
  loadGroups()
})

</script>

<template>
  <div>
    <div class="page-header">
      <h1>Gruppen</h1>
      <p>Hier kannst du Gruppen anlegen und verwalten.</p>
    </div>

    <div class="layout-grid">
      <section class="panel">
        <h2>Neue Gruppe erstellen</h2>

        <form class="form" @submit.prevent="handleCreateGroup">
          <label for="name">Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
          />

          <label for="description">Beschreibung</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
          ></textarea>

          <button type="submit" :disabled="isCreating">
            {{ isCreating ? 'Bitte warten...' : 'Gruppe erstellen' }}
          </button>
        </form>


      </section>

      <section class="panel">
        <h2>Meine Gruppen</h2>

        <div v-if="groups.length === 0" class="info-box">
          Noch keine Gruppen vorhanden.
        </div>

        <div v-else class="group-list">
          <article
            v-for="group in groups"
            :key="group.id"
            class="group-card"
          >
            <h3>{{ group.name }}</h3>

            <p>
              {{ group.description || 'Keine Beschreibung vorhanden.' }}
            </p>

            <div class="group-actions">
              <RouterLink
                :to="`/groups/${group.id}`"
                class="detail-link"
              >
                Details
              </RouterLink>

              <button
                class="danger-button"
                @click="handleDeleteGroup(group.id)"
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



<style scoped>
.page-header {
  margin-bottom: 24px;
}

.page-header p {
  color: #6b7280;
}

.layout-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.panel {
  background: white;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

input,
textarea {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font: inherit;
  box-sizing: border-box;
}

button {
  border: none;
  border-radius: 8px;
  padding: 12px;
  background: #2563eb;
  color: white;
  cursor: pointer;
  font-size: 14px;
}

button:hover {
  background: #1d4ed8;
}

.group-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.group-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
}

.group-card h3 {
  margin-top: 0;
  margin-bottom: 8px;
}

.group-card p {
  color: #6b7280;
  margin-bottom: 0;
}

.group-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 12px;
}

.detail-link {
  display: inline-block;
  background: #2563eb;
  color: white;
  text-decoration: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 14px;
}

.danger-button {
  background: #dc2626;
}

.danger-button:hover {
  background: #b91c1c;
}

.info-box {
  background: #f9fafb;
  padding: 16px;
  border-radius: 10px;
  color: #6b7280;
}

@media (max-width: 900px) {
  .layout-grid {
    grid-template-columns: 1fr;
  }
}
</style>