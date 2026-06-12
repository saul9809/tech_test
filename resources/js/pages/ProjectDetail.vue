<template>
    <div class="project-detail" v-if="project">
        <!-- Header del Proyecto -->
        <div class="project-header">
            <div>
                <h1>{{ project.name }}</h1>
                <p class="client">Cliente: {{ project.client_name }}</p>
                <p class="created-by">Creado por: {{ project.created_by?.name || 'N/A' }}</p>
            </div>
            <div class="header-actions">
                <select 
                    v-model="project.status" 
                    @change="updateStatus" 
                    :disabled="!canManageProject"
                    class="status-select"
                >
                    <option value="draft">Draft</option>
                    <option value="discovery">Discovery</option>
                    <option value="execution">Execution</option>
                    <option value="delivered">Delivered</option>
                </select>
                <button 
                    v-if="canManageProject" 
                    @click="archiveProject" 
                    class="btn-danger"
                >
                    Archivar
                </button>
                <button @click="goBack" class="btn-secondary">
                    Volver
                </button>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <button 
                :class="{ active: activeTab === 'artifacts' }" 
                @click="activeTab = 'artifacts'"
            >
                📄 Artifacts ({{ artifacts.length }}/7)
            </button>
            <button 
                :class="{ active: activeTab === 'modules' }" 
                @click="activeTab = 'modules'"
            >
                🔧 Módulos ({{ modules.length }})
            </button>
            <button 
                :class="{ active: activeTab === 'audit' }" 
                @click="activeTab = 'audit'"
            >
                📋 Auditoría
            </button>
        </div>
        
        <!-- Tab: Artifacts - Usando ArtifactList.vue -->
        <div v-if="activeTab === 'artifacts'" class="tab-content">
            <ArtifactList 
                :artifacts="artifacts"
                :can-manage="canManageArtifacts"
                @edit="editArtifact"
                @mark-done="markArtifactDone"
            />
        </div>
        
        <!-- Tab: Modules - Usando ModuleList.vue -->
        <div v-if="activeTab === 'modules'" class="tab-content">
            <ModuleList 
                :modules="modules"
                :can-edit="canEditModules"
                :can-validate="canValidateModules"
                @edit="editModule"
                @create="createModule"
                @validate="validateModule"
            />
        </div>
        
        <!-- Tab: Audit - Usando AuditTimeline.vue -->
        <div v-if="activeTab === 'audit'" class="tab-content">
            <AuditTimeline :project-id="projectId" />
        </div>
    </div>
    
    <div v-else-if="loading" class="loading-full">
        <div class="spinner"></div>
        <p>Cargando proyecto...</p>
    </div>
    
    <div v-else-if="error" class="error-full">
        <p>❌ {{ error }}</p>
        <button @click="goBack" class="btn-secondary">Volver a proyectos</button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../services/axios'
import { useAuthStore } from '../stores/auth'
import ArtifactList from '../components/ArtifactList.vue'
import ModuleList from '../components/ModuleList.vue'
import AuditTimeline from '../components/AuditTimeline.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const projectId = route.params.id
const project = ref(null)
const artifacts = ref([])
const modules = ref([])
const loading = ref(true)
const error = ref(null)
const activeTab = ref('artifacts')

// Permisos basados en rol
const canManageProject = authStore.hasRole(['admin', 'pm'])
const canManageArtifacts = authStore.hasRole(['admin', 'pm'])
const canEditModules = authStore.hasRole(['admin', 'pm', 'engineer'])
const canValidateModules = authStore.hasRole(['admin', 'pm', 'engineer'])

// Funciones de API
const fetchProject = async () => {
    try {
        const response = await axios.get(`/v1/projects/${projectId}`)
        project.value = response.data.data
    } catch (err) {
        error.value = 'Error al cargar el proyecto'
        console.error(err)
    }
}

const fetchArtifacts = async () => {
    try {
        const response = await axios.get(`/v1/projects/${projectId}/artifacts`)
        artifacts.value = response.data.data
        console.log('Artifacts cargados:', artifacts.value.length)
    } catch (err) {
        console.error('Error fetching artifacts:', err)
    }
}

const fetchModules = async () => {
    try {
        const response = await axios.get(`/v1/projects/${projectId}/modules`)
        modules.value = response.data.data.data || []
        console.log('Módulos cargados:', modules.value.length)
    } catch (err) {
        console.error('Error fetching modules:', err)
    }
}

const updateStatus = async () => {
    try {
        await axios.put(`/v1/projects/${project.value.id}`, { status: project.value.status })
        console.log('Estado actualizado a:', project.value.status)
    } catch (err) {
        if (err.response?.status === 422) {
            alert(err.response.data.message)
            await fetchProject()
        } else {
            alert('Error al actualizar el estado')
        }
    }
}

const archiveProject = async () => {
    if (confirm('¿Estás seguro de archivar este proyecto?')) {
        try {
            await axios.patch(`/v1/projects/${project.value.id}/archive`)
            router.push('/projects')
        } catch (err) {
            alert('Error al archivar el proyecto')
        }
    }
}

const markArtifactDone = async (artifact) => {
    try {
        await axios.patch(`/v1/artifacts/${artifact.id}/mark-done`)
        await fetchArtifacts()
        alert(`✅ Artifact marcado como completado`)
    } catch (err) {
        alert(err.response?.data?.message || 'Error al marcar como completado')
    }
}

const editArtifact = (artifact) => {
    // Navegar a la página de edición de artifact
    router.push(`/projects/${projectId}/artifacts/${artifact.type}`)
}

const editModule = (module) => {
    router.push(`/projects/${projectId}/modules/${module.id}`)
}

const createModule = () => {
    router.push(`/projects/${projectId}/modules/new`)
}

const validateModule = async (module) => {
    try {
        await axios.post(`/v1/modules/${module.id}/validate`)
        await fetchModules()
        alert('✅ Módulo validado exitosamente')
    } catch (err) {
        alert(err.response?.data?.message || 'Error al validar el módulo')
    }
}

const goBack = () => {
    router.push('/projects')
}

const loadData = async () => {
    loading.value = true
    error.value = null
    try {
        await Promise.all([fetchProject(), fetchArtifacts(), fetchModules()])
    } catch (err) {
        error.value = 'Error al cargar los datos del proyecto'
        console.error(err)
    } finally {
        loading.value = false
    }
}

onMounted(loadData)
</script>

<style scoped>
.project-detail {
    max-width: 1400px;
    margin: 0 auto;
}

/* Header */
.project-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.project-header h1 {
    margin: 0 0 0.5rem;
    color: #1a202c;
    font-size: 2rem;
}

.client, .created-by {
    color: #718096;
    margin: 0.25rem 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.status-select {
    padding: 0.5rem 1rem;
    border: 1px solid #cbd5e0;
    border-radius: 0.375rem;
    background: white;
    font-size: 0.875rem;
    cursor: pointer;
}

.status-select:disabled {
    background: #edf2f7;
    cursor: not-allowed;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.tabs button {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    color: #718096;
    transition: all 0.2s;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}

.tabs button:hover {
    color: #4a5568;
}

.tabs button.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    min-height: 400px;
}

/* Buttons */
.btn-primary, .btn-secondary, .btn-danger {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
}

.btn-secondary {
    background: #cbd5e0;
    color: #2d3748;
}

.btn-secondary:hover {
    background: #a0aec0;
}

.btn-danger {
    background: #f56565;
    color: white;
}

.btn-danger:hover {
    background: #e53e3e;
}

/* Loading States */
.loading-full, .error-full {
    text-align: center;
    padding: 4rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-full {
    color: #e53e3e;
}
</style>