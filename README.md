# StudySprint – Clean Projektpaket


## Inhalt

- `backend-laravel/`
  - Models
  - Controller
  - Migrations
  - API-Routen
- `frontend-vue/`
  - Views
  - Services
  - Router
  - App-Struktur
- `StudySprint_WT2_Praesentation.pptx`
  - Präsentation aus dem bisherigen Projektstand

## Technik

- Backend: Laravel
- Frontend: Vue 3
- Kommunikation: REST API
- Authentifizierung: Laravel Sanctum
- Datenbank: SQLite, MariaDB oder PostgreSQL

## Startreihenfolge

### Backend
1. Laravel-Projekt anlegen
2. Dateien aus `backend-laravel/` in das Projekt kopieren
3. `.env` anpassen
4. `php artisan install:api`
5. `php artisan migrate`
6. `php artisan serve`

### Frontend
1. Vue-Projekt anlegen
2. Dateien aus `frontend-vue/` in das Projekt kopieren
3. `npm install`
4. `npm install vue-router`
5. `npm run dev`

## Enthaltene Hauptfunktionen

- Registrierung und Login
- Gruppen erstellen und bearbeiten
- Mitglieder hinzufügen und entfernen
- Lernziele anlegen, bearbeiten und löschen
- Aufgaben anlegen, bearbeiten, löschen und Status ändern
- Sprints anlegen, bearbeiten und löschen
- Termine anlegen, bearbeiten und löschen
- Dashboard mit Kennzahlen
