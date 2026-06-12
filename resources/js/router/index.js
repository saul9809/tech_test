import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    { path: '/login', component: () => import('../pages/Login.vue'), meta: { guest: true } },
    { path: '/', component: () => import('../layouts/MainLayout.vue'), meta: { requiresAuth: true }, children: [
        { path: '', redirect: '/projects' },
        { path: 'projects', component: () => import('../pages/Projects.vue') },
        { path: 'projects/:id', component: () => import('../pages/ProjectDetail.vue') },
        { path: 'projects/:id/artifacts/:artifactType', component: () => import('../pages/ArtifactEdit.vue') },
        { path: 'projects/:id/modules/:moduleId', component: () => import('../pages/ModuleEdit.vue') },
    ] },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default routes;