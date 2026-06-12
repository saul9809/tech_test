<template>
    <div class="projects-page">
        <div class="page-header">
            <h1>Proyectos</h1>
            <button v-if="canCreateProject" @click="showCreateModal = true" class="btn-primary">
                + Nuevo Proyecto
            </button>
        </div>
        
        <div v-if="loading" class="loading">Cargando proyectos...</div>
        
        <div v-else-if="error" class="error">{{ error }}</div>
        
        <div v-else class="projects-grid">
            <div v-for="project in projects" :key="project.id" class="project-card" @click="goToProject(project.id)">
                <h3>{{ project.name }}</h3>
                <p class="client">{{ project.client_name }}</p>
                <p class="status" :class="project.status">{{ project.status }}</p>
            </div>
        </div>
        
        <!-- Modal de creación -->
        <div v-if="showCreateModal" class="modal" @click.self="showCreateModal = false">
            <div class="modal-content">
                <h2>Crear Proyecto</h2>
                <form @submit.prevent="createProject">
                    <div class="form-group">
                        <label>Nombre del Proyecto</label>
                        <input v-model="newProject.name" required>
                    </div>
                    <div class="form-group">
                        <label>Cliente</label>
                        <input v-model="newProject.client_name" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">Cancelar</button>
                        <button type="submit" :disabled="creating" class="btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '../services/axios'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const projects = ref([])
const loading = ref(true)
const error = ref(null)
const showCreateModal = ref(false)
const creating = ref(false)
const newProject = ref({ name: '', client_name: '' })

const canCreateProject = authStore.hasRole(['admin', 'pm'])

const fetchProjects = async () => {
    try {
        const response = await axios.get('/v1/projects')
        projects.value = response.data.data.data
    } catch (err) {
        error.value = 'Error al cargar proyectos'
        console.error(err)
    } finally {
        loading.value = false
    }
}

const createProject = async () => {
    creating.value = true
    try {
        const response = await axios.post('/v1/projects', newProject.value)
        projects.value.unshift(response.data.data)
        showCreateModal.value = false
        newProject.value = { name: '', client_name: '' }
    } catch (err) {
        error.value = 'Error al crear proyecto'
    } finally {
        creating.value = false
    }
}

const goToProject = (id) => {
    router.push(`/projects/${id}`)
}

onMounted(fetchProjects)
</script>

<style scoped>
.projects-page {
    max-width: 1200px;
    margin: 0 auto;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.project-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.project-card h3 {
    margin: 0 0 0.5rem;
    color: #2d3748;
}
.client {
    color: #718096;
    margin-bottom: 0.5rem;
}
.status {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
}
.status.draft { background: #edf2f7; color: #4a5568; }
.status.discovery { background: #fefcbf; color: #975a16; }
.status.execution { background: #c6f6d5; color: #22543d; }
.status.delivered { background: #e9d8fd; color: #44337a; }
.loading, .error { text-align: center; padding: 2rem; }
.btn-primary {
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 500px;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
}
</style>