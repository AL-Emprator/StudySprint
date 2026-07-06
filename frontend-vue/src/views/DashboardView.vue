<template>
  <main class="dashboard-page">
    <!-- Kopfbereich -->
    <header class="dashboard-hero">
      <div class="hero-content">
        <span class="hero-label">StudySprint Übersicht</span>

        <h1>Dashboard</h1>

        <p>
          Behalte den Fortschritt, die Aufgaben und die nächsten
          Aktivitäten deiner Lerngruppe im Blick.
        </p>
      </div>

      <div class="group-selection">
        <label for="dashboardGroup">
          Aktuelle Gruppe
        </label>

        <div class="select-wrapper">
          <select
            id="dashboardGroup"
            v-model="selectedGroupId"
            :disabled="isLoadingGroups"
          >
            <option value="" disabled>
              Gruppe auswählen
            </option>

            <option
              v-for="groupItem in groups"
              :key="groupItem.id"
              :value="groupItem.id"
            >
              {{ groupItem.name }}
            </option>
          </select>
        </div>

        <span
          v-if="isLoadingGroups"
          class="selection-info"
        >
          Gruppen werden geladen...
        </span>
      </div>
    </header>

    <!-- Fehlermeldung -->
    <div
      v-if="errorMessage"
      class="message error-message"
    >
      <span class="message-icon">!</span>

      <span>{{ errorMessage }}</span>
    </div>

    <!-- Ladezustand -->
    <div
      v-if="isLoadingGroups || isLoadingStats"
      class="state-card loading-state"
    >
      <span class="loader"></span>

      <div>
        <h2>Dashboard wird geladen</h2>
        <p>Die aktuellen Gruppendaten werden abgerufen.</p>
      </div>
    </div>

    <!-- Keine Gruppen -->
    <div
      v-else-if="groups.length === 0"
      class="state-card empty-state"
    >
      <div class="state-icon">G</div>

      <div>
        <h2>Noch keine Gruppe vorhanden</h2>
        <p>
          Du bist aktuell in keiner Lerngruppe.
          Erstelle eine Gruppe oder tritt einer bestehenden Gruppe bei.
        </p>
      </div>
    </div>

    <!-- Keine Gruppe ausgewählt -->
    <div
      v-else-if="!selectedGroupId"
      class="state-card empty-state"
    >
      <div class="state-icon">↓</div>

      <div>
        <h2>Gruppe auswählen</h2>
        <p>
          Wähle oben eine Gruppe aus, um ihre Kennzahlen anzuzeigen.
        </p>
      </div>
    </div>

    <!-- Dashboard-Inhalt -->
    <template v-else>
      <!-- Gruppenübersicht -->
      <section class="group-overview">
        <div class="group-information">
          <span class="section-label">
            Ausgewählte Lerngruppe
          </span>

          <h2>{{ selectedGroup?.name }}</h2>

          <p>
            {{
              selectedGroup?.description ||
              'Für diese Gruppe wurde noch keine Beschreibung hinterlegt.'
            }}
          </p>
        </div>

        <div class="group-summary">
          <div class="member-avatars">
            <span class="avatar avatar-one">A</span>
            <span class="avatar avatar-two">B</span>
            <span class="avatar avatar-three">C</span>
          </div>

          <div>
            <strong>
              {{ stats.members }}
              {{ stats.members === 1 ? 'Mitglied' : 'Mitglieder' }}
            </strong>

            <span>arbeiten in dieser Gruppe</span>
          </div>
        </div>
      </section>

      <!-- Hauptübersicht -->
      <section class="dashboard-main-grid">
        <!-- Fortschritt -->
        <article class="progress-card">
          <div class="card-heading">
            <div>
              <span class="section-label">
                Gesamtfortschritt
              </span>

              <h2>Aufgabenfortschritt</h2>
            </div>

            <span class="status-indicator">
              Aktuell
            </span>
          </div>

          <div class="progress-content">
            <div
              class="progress-circle"
              :style="{ '--progress': `${stats.progress * 3.6}deg` }"
            >
              <div class="progress-circle-inner">
                <strong>{{ stats.progress }}%</strong>
                <span>erledigt</span>
              </div>
            </div>

            <div class="progress-details">
              <p>
                Du hast
                <strong>{{ stats.doneTasks }}</strong>
                von
                <strong>{{ stats.tasks }}</strong>
                Aufgaben abgeschlossen.
              </p>

              <div class="progress-bar">
                <div
                  class="progress-bar-value"
                  :style="{ width: `${stats.progress}%` }"
                ></div>
              </div>

              <div class="progress-legend">
                <span>
                  <i class="legend-dot done-dot"></i>
                  {{ stats.doneTasks }} erledigt
                </span>

                <span>
                  <i class="legend-dot open-dot"></i>
                  {{ stats.openTasks }} offen
                </span>
              </div>
            </div>
          </div>
        </article>

        <!-- Schnelle Übersicht -->
        <article class="activity-card">
          <div class="card-heading">
            <div>
              <span class="section-label">
                Arbeitsstand
              </span>

              <h2>Aktuelle Übersicht</h2>
            </div>
          </div>

          <div class="activity-list">
            <div class="activity-item">
              <span class="activity-icon task-icon">
                ✓
              </span>

              <div>
                <strong>{{ stats.openTasks }}</strong>
                <span>offene Aufgaben</span>
              </div>
            </div>

            <div class="activity-item">
              <span class="activity-icon sprint-icon">
                S
              </span>

              <div>
                <strong>{{ stats.activeSprints }}</strong>
                <span>aktive Sprints</span>
              </div>
            </div>

            <div class="activity-item">
              <span class="activity-icon meeting-icon">
                T
              </span>

              <div>
                <strong>{{ stats.meetings }}</strong>
                <span>geplante Termine</span>
              </div>
            </div>
          </div>
        </article>
      </section>

      <!-- Kennzahlen -->
      <section class="statistics-section">
        <div class="section-heading">
          <div>
            <span class="section-label">
              Kennzahlen
            </span>

            <h2>Deine Gruppe auf einen Blick</h2>
          </div>

          <p>
            Alle wichtigen Werte der ausgewählten Lerngruppe.
          </p>
        </div>

        <div class="stats-grid">
          <article class="stat-card stat-card-members">
            <div class="stat-card-top">
              <span class="stat-icon">M</span>
              <span class="stat-label">Mitglieder</span>
            </div>

            <strong class="stat-value">
              {{ stats.members }}
            </strong>

            <p>Personen in der Lerngruppe</p>
          </article>

          <article class="stat-card stat-card-tasks">
            <div class="stat-card-top">
              <span class="stat-icon">A</span>
              <span class="stat-label">Aufgaben</span>
            </div>

            <strong class="stat-value">
              {{ stats.tasks }}
            </strong>

            <p>Aufgaben insgesamt</p>
          </article>

          <article class="stat-card stat-card-open">
            <div class="stat-card-top">
              <span class="stat-icon">O</span>
              <span class="stat-label">Offen</span>
            </div>

            <strong class="stat-value">
              {{ stats.openTasks }}
            </strong>

            <p>Noch zu bearbeitende Aufgaben</p>
          </article>

          <article class="stat-card stat-card-done">
            <div class="stat-card-top">
              <span class="stat-icon">E</span>
              <span class="stat-label">Erledigt</span>
            </div>

            <strong class="stat-value">
              {{ stats.doneTasks }}
            </strong>

            <p>Abgeschlossene Aufgaben</p>
          </article>

          <article class="stat-card stat-card-goals">
            <div class="stat-card-top">
              <span class="stat-icon">L</span>
              <span class="stat-label">Lernziele</span>
            </div>

            <strong class="stat-value">
              {{ stats.goals }}
            </strong>

            <p>Festgelegte Lernziele</p>
          </article>

          <article class="stat-card stat-card-sprints">
            <div class="stat-card-top">
              <span class="stat-icon">S</span>
              <span class="stat-label">Sprints</span>
            </div>

            <strong class="stat-value">
              {{ stats.activeSprints }}
            </strong>

            <p>Aktuell laufende Sprints</p>
          </article>

          <article class="stat-card stat-card-meetings">
            <div class="stat-card-top">
              <span class="stat-icon">T</span>
              <span class="stat-label">Termine</span>
            </div>

            <strong class="stat-value">
              {{ stats.meetings }}
            </strong>

            <p>Geplante Gruppentermine</p>
          </article>

          <article class="stat-card stat-card-progress">
            <div class="stat-card-top">
              <span class="stat-icon">%</span>
              <span class="stat-label">Fortschritt</span>
            </div>

            <strong class="stat-value">
              {{ stats.progress }}%
            </strong>

            <p>Gesamter Aufgabenfortschritt</p>
          </article>
        </div>
      </section>
    </template>
  </main>
</template>


<script setup>
import {
  computed,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'

import {
  getGroup,
  getGroups,
} from '../services/groupService'

const groups = ref([])
const selectedGroupId = ref('')
const groupDetails = ref(null)

const isLoadingGroups = ref(false)
const isLoadingStats = ref(false)
const errorMessage = ref('')

const stats = reactive({
  members: 0,
  tasks: 0,
  openTasks: 0,
  doneTasks: 0,
  goals: 0,
  activeSprints: 0,
  meetings: 0,
  progress: 0,
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

    if (groups.value.length > 0) {
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

async function loadDashboardStats() {
  resetStats()
  groupDetails.value = null
  errorMessage.value = ''

  if (!selectedGroupId.value) {
    return
  }

  isLoadingStats.value = true

  try {
    const data = await getGroup(
      selectedGroupId.value
    )

    groupDetails.value = data

    calculateStats(data)
  } catch (error) {
    console.error(
      'Fehler beim Laden der Dashboard-Daten:',
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
        'Die Dashboard-Daten konnten nicht geladen werden.'
    }
  } finally {
    isLoadingStats.value = false
  }
}

function calculateStats(group) {
  const users = Array.isArray(group.users)
    ? group.users
    : []

  const tasks = Array.isArray(group.tasks)
    ? group.tasks
    : []

  const goals = Array.isArray(group.goals)
    ? group.goals
    : []

  const sprints = Array.isArray(group.sprints)
    ? group.sprints
    : []

  const meetings = Array.isArray(group.meetings)
    ? group.meetings
    : []

  const doneTasks = tasks.filter(
    task => task.status === 'done'
  ).length

  const openTasks = tasks.filter(
    task =>
      task.status === 'open' ||
      task.status === 'in_progress'
  ).length

  const activeSprints = sprints.filter(
    sprint => sprint.status === 'active'
  ).length

  stats.members = users.length
  stats.tasks = tasks.length
  stats.openTasks = openTasks
  stats.doneTasks = doneTasks
  stats.goals = goals.length
  stats.activeSprints = activeSprints
  stats.meetings = meetings.length

  stats.progress = tasks.length > 0
    ? Math.round(
        (doneTasks / tasks.length) * 100
      )
    : 0
}

function resetStats() {
  stats.members = 0
  stats.tasks = 0
  stats.openTasks = 0
  stats.doneTasks = 0
  stats.goals = 0
  stats.activeSprints = 0
  stats.meetings = 0
  stats.progress = 0
}

watch(selectedGroupId, async () => {
  await loadDashboardStats()
})

onMounted(async () => {
  await loadGroups()
})
</script>


<style scoped>
.dashboard-page {
  width: 100%;
  max-width: 1440px;
  margin: 0 auto;
  padding: 32px;
  box-sizing: border-box;
}

/* Hero-Bereich */

.dashboard-hero {
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 32px;
  padding: 34px;
  margin-bottom: 26px;
  overflow: hidden;
  color: #ffffff;
  background:
    linear-gradient(
      135deg,
      #1d4ed8 0%,
      #2563eb 55%,
      #4f46e5 100%
    );
  border-radius: 22px;
  box-shadow: 0 16px 40px rgba(37, 99, 235, 0.2);
}

.dashboard-hero::before,
.dashboard-hero::after {
  position: absolute;
  content: '';
  border-radius: 50%;
  pointer-events: none;
}

.dashboard-hero::before {
  top: -90px;
  right: 25%;
  width: 230px;
  height: 230px;
  background: rgba(255, 255, 255, 0.08);
}

.dashboard-hero::after {
  right: -80px;
  bottom: -120px;
  width: 280px;
  height: 280px;
  background: rgba(255, 255, 255, 0.08);
}

.hero-content,
.group-selection {
  position: relative;
  z-index: 1;
}

.hero-content {
  max-width: 680px;
}

.hero-label,
.section-label {
  display: block;
  margin-bottom: 7px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.hero-label {
  color: #bfdbfe;
}

.hero-content h1 {
  margin: 0;
  font-size: clamp(34px, 5vw, 48px);
  line-height: 1.1;
}

.hero-content p {
  max-width: 620px;
  margin: 12px 0 0;
  color: #dbeafe;
  font-size: 17px;
  line-height: 1.6;
}

/* Gruppenauswahl */

.group-selection {
  width: min(100%, 320px);
  padding: 16px;
  background: rgba(255, 255, 255, 0.13);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 15px;
  backdrop-filter: blur(8px);
}

.group-selection label {
  display: block;
  margin-bottom: 8px;
  color: #eff6ff;
  font-size: 13px;
  font-weight: 700;
}

.select-wrapper {
  position: relative;
}

.select-wrapper select {
  width: 100%;
  min-height: 48px;
  padding: 11px 38px 11px 13px;
  color: #111827;
  background: #ffffff;
  border: 0;
  border-radius: 10px;
  outline: none;
  font: inherit;
  cursor: pointer;
  box-sizing: border-box;
}

.select-wrapper select:focus {
  box-shadow: 0 0 0 3px rgba(191, 219, 254, 0.45);
}

.select-wrapper select:disabled {
  color: #6b7280;
  background: #f3f4f6;
  cursor: not-allowed;
}

.selection-info {
  display: block;
  margin-top: 7px;
  color: #dbeafe;
  font-size: 12px;
}

/* Gruppenübersicht */

.group-overview {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  padding: 26px;
  margin-bottom: 24px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.group-information {
  min-width: 0;
}

.section-label {
  color: #2563eb;
}

.group-information h2,
.card-heading h2,
.section-heading h2 {
  margin: 0;
  color: #0f172a;
}

.group-information h2 {
  font-size: 27px;
}

.group-information p {
  max-width: 740px;
  margin: 7px 0 0;
  color: #64748b;
  line-height: 1.6;
}

.group-summary {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  gap: 14px;
  padding: 13px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 13px;
}

.group-summary strong,
.group-summary span {
  display: block;
}

.group-summary strong {
  color: #0f172a;
  font-size: 14px;
}

.group-summary div > span {
  margin-top: 2px;
  color: #64748b;
  font-size: 12px;
}

.member-avatars {
  display: flex;
  padding-left: 14px;
}

.avatar {
  display: flex !important;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  margin-left: -14px;
  color: #ffffff;
  border: 2px solid #ffffff;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 800;
}

.avatar-one {
  background: #2563eb;
}

.avatar-two {
  background: #7c3aed;
}

.avatar-three {
  background: #0891b2;
}

/* Hauptkarten */

.dashboard-main-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.7fr);
  gap: 24px;
  margin-bottom: 28px;
}

.progress-card,
.activity-card {
  min-width: 0;
  padding: 26px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.card-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 18px;
  margin-bottom: 22px;
  border-bottom: 1px solid #e2e8f0;
}

.card-heading h2 {
  font-size: 21px;
}

.status-indicator {
  padding: 6px 10px;
  color: #166534;
  background: #dcfce7;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
}

.progress-content {
  display: flex;
  align-items: center;
  gap: 30px;
}

.progress-circle {
  --progress: 0deg;

  display: grid;
  flex-shrink: 0;
  width: 155px;
  height: 155px;
  padding: 12px;
  background:
    conic-gradient(
      #2563eb var(--progress),
      #e2e8f0 0
    );
  border-radius: 50%;
  place-items: center;
}

.progress-circle-inner {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  width: 100%;
  height: 100%;
  background: #ffffff;
  border-radius: 50%;
}

.progress-circle-inner strong {
  color: #0f172a;
  font-size: 29px;
}

.progress-circle-inner span {
  color: #64748b;
  font-size: 12px;
}

.progress-details {
  flex: 1;
  min-width: 0;
}

.progress-details p {
  margin: 0 0 17px;
  color: #475569;
  line-height: 1.6;
}

.progress-details strong {
  color: #0f172a;
}

.progress-bar {
  width: 100%;
  height: 10px;
  overflow: hidden;
  background: #e2e8f0;
  border-radius: 999px;
}

.progress-bar-value {
  height: 100%;
  background:
    linear-gradient(
      90deg,
      #2563eb,
      #4f46e5
    );
  border-radius: inherit;
  transition: width 0.4s ease;
}

.progress-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin-top: 13px;
}

.progress-legend span {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #64748b;
  font-size: 12px;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.done-dot {
  background: #2563eb;
}

.open-dot {
  background: #cbd5e1;
}

/* Aktivitäten */

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 13px;
}

.activity-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 11px;
  font-size: 14px;
  font-weight: 800;
}

.task-icon {
  color: #1d4ed8;
  background: #dbeafe;
}

.sprint-icon {
  color: #92400e;
  background: #fef3c7;
}

.meeting-icon {
  color: #6d28d9;
  background: #ede9fe;
}

.activity-item strong,
.activity-item div span {
  display: block;
}

.activity-item strong {
  color: #0f172a;
  font-size: 20px;
}

.activity-item div span {
  margin-top: 2px;
  color: #64748b;
  font-size: 13px;
}

/* Kennzahlen */

.statistics-section {
  margin-top: 4px;
}

.section-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 18px;
}

.section-heading h2 {
  font-size: 24px;
}

.section-heading > p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.stats-grid {
  display: grid;
  grid-template-columns:
    repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.stat-card {
  position: relative;
  min-width: 0;
  padding: 20px;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.045);
  transition:
    transform 0.2s ease,
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.stat-card::after {
  position: absolute;
  right: -24px;
  bottom: -30px;
  width: 92px;
  height: 92px;
  content: '';
  background: currentColor;
  border-radius: 50%;
  opacity: 0.045;
}

.stat-card:hover {
  transform: translateY(-3px);
  border-color: #bfdbfe;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
}

.stat-card-top {
  display: flex;
  align-items: center;
  gap: 10px;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 800;
}

.stat-label {
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.stat-value {
  display: block;
  margin-top: 18px;
  color: #0f172a;
  font-size: 30px;
  line-height: 1;
}

.stat-card p {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 12px;
  line-height: 1.5;
}

.stat-card-members {
  color: #2563eb;
}

.stat-card-members .stat-icon {
  color: #1d4ed8;
  background: #dbeafe;
}

.stat-card-tasks {
  color: #4f46e5;
}

.stat-card-tasks .stat-icon {
  color: #4338ca;
  background: #e0e7ff;
}

.stat-card-open {
  color: #d97706;
}

.stat-card-open .stat-icon {
  color: #92400e;
  background: #fef3c7;
}

.stat-card-done {
  color: #16a34a;
}

.stat-card-done .stat-icon {
  color: #166534;
  background: #dcfce7;
}

.stat-card-goals {
  color: #7c3aed;
}

.stat-card-goals .stat-icon {
  color: #6d28d9;
  background: #ede9fe;
}

.stat-card-sprints {
  color: #0891b2;
}

.stat-card-sprints .stat-icon {
  color: #0e7490;
  background: #cffafe;
}

.stat-card-meetings {
  color: #db2777;
}

.stat-card-meetings .stat-icon {
  color: #be185d;
  background: #fce7f3;
}

.stat-card-progress {
  color: #2563eb;
}

.stat-card-progress .stat-icon {
  color: #1d4ed8;
  background: #dbeafe;
}

/* Zustände und Meldungen */

.state-card,
.message {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 22px;
  background: #ffffff;
  border-radius: 16px;
}

.state-card {
  min-height: 100px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.state-card h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
}

.state-card p {
  margin: 5px 0 0;
  color: #64748b;
  line-height: 1.5;
}

.state-icon,
.message-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-weight: 800;
}

.state-icon {
  width: 48px;
  height: 48px;
  color: #1d4ed8;
  background: #dbeafe;
  border-radius: 13px;
}

.message {
  margin-bottom: 20px;
}

.error-message {
  color: #991b1b;
  background: #fef2f2;
  border: 1px solid #fecaca;
}

.message-icon {
  width: 28px;
  height: 28px;
  color: #ffffff;
  background: #dc2626;
  border-radius: 50%;
}

.loader {
  width: 28px;
  height: 28px;
  border: 3px solid #dbeafe;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Tablet */

@media (max-width: 1100px) {
  .stats-grid {
    grid-template-columns:
      repeat(2, minmax(0, 1fr));
  }

  .dashboard-main-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 850px) {
  .dashboard-hero {
    align-items: stretch;
    flex-direction: column;
  }

  .group-selection {
    width: 100%;
    box-sizing: border-box;
  }

  .group-overview {
    align-items: flex-start;
    flex-direction: column;
  }

  .group-summary {
    width: 100%;
    box-sizing: border-box;
  }

  .section-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 6px;
  }
}

/* Smartphone */

@media (max-width: 640px) {
  .dashboard-page {
    padding: 18px 14px;
  }

  .dashboard-hero {
    padding: 24px 20px;
    border-radius: 17px;
  }

  .hero-content h1 {
    font-size: 33px;
  }

  .hero-content p {
    font-size: 15px;
  }

  .group-overview,
  .progress-card,
  .activity-card {
    padding: 20px;
    border-radius: 15px;
  }

  .group-information h2 {
    font-size: 23px;
  }

  .progress-content {
    align-items: flex-start;
    flex-direction: column;
  }

  .progress-circle {
    align-self: center;
    width: 140px;
    height: 140px;
  }

  .progress-details {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-card {
    padding: 18px;
  }

  .state-card {
    align-items: flex-start;
  }
}
</style>