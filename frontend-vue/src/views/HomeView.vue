<template>
  <div class="home-page">
    <!-- Willkommens-Bereich -->
    <section class="hero-section">
      <h1>
        Gemeinsam lernen mit
        <span class="highlight">StudySprint</span>
      </h1>

      <p class="hero-text">
        StudySprint ist eine Web-App für Lerngruppen. Ihr könnt gemeinsam
        Aufgaben verwalten, Lernziele setzen, Sprints planen und Termine
        koordinieren.
      </p>

      <div class="hero-buttons">
        <RouterLink to="/register" class="btn btn-primary">
          Jetzt starten
        </RouterLink>

        <RouterLink to="/login" class="btn btn-outline">
          Einloggen
        </RouterLink>
      </div>
    </section>

    <!-- Feature-Übersicht -->
    <section class="features-section">
      <h2>Was kann StudySprint?</h2>

      <div class="features-grid">
        <div class="feature-card">
          <span class="feature-icon">👥</span>
          <h3>Gruppen</h3>
          <p>Erstelle Lerngruppen und lade Kommilitonen ein.</p>
        </div>

        <div class="feature-card">
          <span class="feature-icon">✅</span>
          <h3>Aufgaben</h3>
          <p>
            Verwalte Aufgaben mit Priorität, Status und Fälligkeitsdatum.
          </p>
        </div>

        <div class="feature-card">
          <span class="feature-icon">🎯</span>
          <h3>Lernziele</h3>
          <p>Setze klare Lernziele und verfolge deinen Fortschritt.</p>
        </div>

        <div class="feature-card">
          <span class="feature-icon">🚀</span>
          <h3>Sprints</h3>
          <p>Plane Lernphasen mit Start- und Enddatum.</p>
        </div>

        <div class="feature-card">
          <span class="feature-icon">📅</span>
          <h3>Termine</h3>
          <p>Koordiniere Gruppentreffen online oder vor Ort.</p>
        </div>

        <div class="feature-card">
          <span class="feature-icon">📊</span>
          <h3>Dashboard</h3>
          <p>Behalte den Überblick mit allen wichtigen Kennzahlen.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.home-page {
  max-width: 900px;
  margin: 0 auto;
}

/* Hero-Bereich */
.hero-section {
  text-align: center;
  padding: 60px 20px 40px;
}

.hero-section h1 {
  font-size: 42px;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 16px;
}

.highlight {
  color: #2563eb;
}

.hero-text {
  font-size: 18px;
  color: #6b7280;
  max-width: 600px;
  margin: 0 auto 32px;
  line-height: 1.6;
}

.hero-buttons {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn {
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-outline {
  background: white;
  color: #2563eb;
  border: 2px solid #2563eb;
}

.btn-outline:hover {
  background: #eff6ff;
}

/* Feature-Bereich */
.features-section {
  padding: 40px 20px;
}

.features-section h2 {
  text-align: center;
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 32px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.feature-card {
  background: white;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  text-align: center;
}

.feature-icon {
  font-size: 32px;
  display: block;
  margin-bottom: 12px;
}

.feature-card h3 {
  font-size: 17px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.feature-card p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.5;
}

/* Responsive für Handys */
@media (max-width: 768px) {
  .features-grid {
    grid-template-columns: 1fr;
  }

  .hero-section h1 {
    font-size: 28px;
  }
}
</style>