import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../pages/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/',
        component: () => import('../layouts/MainLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/projects'
            },
            {
                path: 'projects',
                name: 'Projects',
                component: () => import('../pages/Projects.vue')
            },
            {
                path: 'projects/:id',
                name: 'ProjectDetail',
                component: () => import('../pages/ProjectDetail.vue')
            },
            {
                path: 'projects/:id/artifacts/:artifactType',
                name: 'ArtifactEdit',
                component: () => import('../pages/ArtifactEdit.vue')
            },
            {
                path: 'projects/:id/modules/:moduleId?',
                name: 'ModuleEdit',
                component: () => import('../pages/ModuleEdit.vue')
            }
        ]
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login')
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next('/')
    } else {
        next()
    }
})

export default routes