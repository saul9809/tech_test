<template>
    <div class="app">
        <nav class="navbar">
            <div class="nav-brand">TCG Engineering Hub</div>
            <div class="nav-links">
                <router-link to="/projects">Proyectos</router-link>
            </div>
            <div class="nav-user">
                <span class="user-name">{{ authStore.user?.name }}</span>
                <span class="user-role">{{ authStore.user?.role }}</span>
                <button @click="handleLogout" class="logout-btn">Cerrar Sesión</button>
            </div>
        </nav>
        <main class="main-content">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
}
</script>

<style scoped>
.app {
    min-height: 100vh;
}

.navbar {
    background: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-brand {
    font-size: 1.25rem;
    font-weight: bold;
    color: #667eea;
}

.nav-links a {
    margin: 0 1rem;
    text-decoration: none;
    color: #4a5568;
    transition: color 0.2s;
}

.nav-links a:hover {
    color: #667eea;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-name {
    font-weight: 500;
}

.user-role {
    color: #718096;
    font-size: 0.875rem;
}

.logout-btn {
    padding: 0.25rem 0.75rem;
    background: #e53e3e;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.logout-btn:hover {
    background: #c53030;
}

.main-content {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}
</style>