import { createRouter, createWebHistory } from 'vue-router';
import { isLoggedIn } from '../services/authService';

import HomeView from '../views/HomeView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import DashboardView from '../views/DashboardView.vue';
import GroupsView from '../views/GroupsView.vue';
import GroupDetailView from '../views/GroupDetailView.vue';
import TasksView from '../views/TasksView.vue';
import GoalsView from '../views/GoalsView.vue';
import SprintsView from '../views/SprintsView.vue';
import MeetingsView from '../views/MeetingsView.vue';

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/dashboard', name: 'dashboard', component: DashboardView },
  { path: '/groups', name: 'groups', component: GroupsView },
  { path: '/groups/:id', name: 'group-detail', component: GroupDetailView , meta: { requiresAuth: true } },
  { path: '/tasks', name: 'tasks', component: TasksView },
  { path: '/goals', name: 'goals', component: GoalsView },
  { path: '/sprints', name: 'sprints', component: SprintsView },
  { path: '/meetings', name: 'meetings', component: MeetingsView },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// --- Navigation Guard (Login-Schutz) ---
// Diese Funktion wird vor JEDEM Seitenwechsel aufgerufen
//to ist das Ziel, from ist die aktuelle Seite, next() leitet weiter
//from is the current route, and next() is a function that must be called to resolve the navigation. It can be called with or without arguments to control the navigation flow.

router.beforeEach((to, from, next) => {
  // 1. Definiere öffentliche Routen (kein Login nötig)
  const publicRoutes = ['/', '/login', '/register'];
  
  // 2. Prüfe, ob der User einen Token hat (eingeloggt ist)
  const loggedIn = isLoggedIn();

  // 3. Wenn die Route NICHT öffentlich ist und der User NICHT eingeloggt ist:
  if (!publicRoutes.includes(to.path) && !loggedIn) {
    next('/login'); // Umleiten zur Login-Seite
    return;
  }

  // 4. Wenn der User eingeloggt ist und versucht auf Login/Register zu gehen:
  if ((to.path === '/login' || to.path === '/register') && loggedIn) {
    next('/dashboard'); // Umleiten zum Dashboard
    return;
  }

  // 5. Ansonsten: Lass den User durch zur gewünschten Seite
  next();
});

export default router;
