<template>
    <div class="artifact-edit">
        <div class="page-header">
            <h1>Editar {{ getArtifactLabel(artifactType) }}</h1>
            <button @click="goBack" class="btn-secondary">Volver al Proyecto</button>
        </div>
        
        <div v-if="loading" class="loading">Cargando...</div>
        <div v-else-if="error" class="error">{{ error }}</div>
        
        <form v-else @submit.prevent="save" class="artifact-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Owner</label>
                    <select v-model="form.owner_user_id">
                        <option :value="null">Sin asignar</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }} ({{ user.role }})
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Estado</label>
                    <select v-model="form.status">
                        <option value="not_started">No Iniciado</option>
                        <option value="in_progress">En Progreso</option>
                        <option value="blocked">Bloqueado</option>
                        <option value="done">Completado</option>
                    </select>
                </div>
            </div>
            
            <div class="form-divider">Contenido</div>
            
            <!-- Strategic Alignment -->
            <template v-if="artifactType === 'strategic_alignment'">
                <div class="form-group">
                    <label>Transformation</label>
                    <textarea v-model="form.content_json.transformation" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Supported Decisions</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.content_json.supported_decisions" :key="idx" class="array-item">
                            <input v-model="form.content_json.supported_decisions[idx]">
                            <button type="button" @click="removeArrayItem('supported_decisions', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('supported_decisions')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Measurable Success</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.content_json.measurable_success" :key="idx" class="array-item-object">
                            <input v-model="form.content_json.measurable_success[idx].metric" placeholder="Métrica">
                            <input v-model="form.content_json.measurable_success[idx].target" placeholder="Objetivo">
                            <button type="button" @click="removeArrayItem('measurable_success', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addMeasurableSuccess" class="btn-add">+ Agregar Métrica</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Out of Scope</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.content_json.out_of_scope" :key="idx" class="array-item">
                            <input v-model="form.content_json.out_of_scope[idx]">
                            <button type="button" @click="removeArrayItem('out_of_scope', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('out_of_scope')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
            </template>
            
            <!-- Big Picture -->
            <template v-if="artifactType === 'big_picture'">
                <div class="form-group">
                    <label>Ecosystem Vision</label>
                    <textarea v-model="form.content_json.ecosystem_vision" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Impacted Domains</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.content_json.impacted_domains" :key="idx" class="array-item">
                            <input v-model="form.content_json.impacted_domains[idx]">
                            <button type="button" @click="removeArrayItem('impacted_domains', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('impacted_domains')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Success Definition</label>
                    <textarea v-model="form.content_json.success_definition" rows="4"></textarea>
                </div>
            </template>
            
            <!-- Domain Breakdown -->
            <template v-if="artifactType === 'domain_breakdown'">
                <div class="form-group">
                    <label>Domains</label>
                    <div class="array-field">
                        <div v-for="(domain, idx) in form.content_json.domains" :key="idx" class="domain-item">
                            <input v-model="form.content_json.domains[idx].name" placeholder="Nombre del dominio">
                            <textarea v-model="form.content_json.domains[idx].objective" placeholder="Objetivo" rows="2"></textarea>
                            <input v-model="form.content_json.domains[idx].owner_user_id" placeholder="Owner ID">
                            <button type="button" @click="removeArrayItem('domains', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addDomain" class="btn-add">+ Agregar Dominio</button>
                    </div>
                </div>
            </template>
            
            <!-- Module Matrix -->
            <template v-if="artifactType === 'module_matrix'">
                <div class="form-group">
                    <label>Modules Overview</label>
                    <div class="array-field">
                        <div v-for="(mod, idx) in form.content_json.modules_overview" :key="idx" class="module-matrix-item">
                            <input v-model="form.content_json.modules_overview[idx].name" placeholder="Nombre">
                            <input v-model="form.content_json.modules_overview[idx].domain" placeholder="Dominio">
                            <input v-model="form.content_json.modules_overview[idx].priority" placeholder="Prioridad">
                            <input v-model="form.content_json.modules_overview[idx].phase" placeholder="Fase">
                            <button type="button" @click="removeArrayItem('modules_overview', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addModuleOverview" class="btn-add">+ Agregar Módulo</button>
                    </div>
                </div>
            </template>
            
            <!-- System Architecture -->
            <template v-if="artifactType === 'system_architecture'">
                <div class="form-group">
                    <label>Auth Model</label>
                    <textarea v-model="form.content_json.auth_model" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>API Style</label>
                    <textarea v-model="form.content_json.api_style" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Data Model Notes</label>
                    <textarea v-model="form.content_json.data_model_notes" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Scalability Notes</label>
                    <textarea v-model="form.content_json.scalability_notes" rows="3"></textarea>
                </div>
            </template>
            
            <!-- Phase Scope -->
            <template v-if="artifactType === 'phase_scope'">
                <div class="form-group">
                    <label>Included Modules (IDs)</label>
                    <div class="array-field">
                        <div v-for="(modId, idx) in form.content_json.included_modules" :key="idx" class="array-item">
                            <input v-model="form.content_json.included_modules[idx]" placeholder="Module ID">
                            <button type="button" @click="removeArrayItem('included_modules', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('included_modules')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Excluded Items</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.content_json.excluded_items" :key="idx" class="array-item">
                            <input v-model="form.content_json.excluded_items[idx]">
                            <button type="button" @click="removeArrayItem('excluded_items', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('excluded_items')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Acceptance Criteria</label>
                    <div class="array-field">
                        <div v-for="(criteria, idx) in form.content_json.acceptance_criteria" :key="idx" class="array-item">
                            <input v-model="form.content_json.acceptance_criteria[idx]">
                            <button type="button" @click="removeArrayItem('acceptance_criteria', idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem('acceptance_criteria')" class="btn-add">+ Agregar</button>
                    </div>
                </div>
            </template>
            
            <div class="form-actions">
                <button type="button" @click="goBack" class="btn-secondary">Cancelar</button>
                <button type="submit" :disabled="saving" class="btn-primary">
                    {{ saving ? 'Guardando...' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../services/axios'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const projectId = route.params.id
const artifactType = route.params.artifactType

const artifact = ref(null)
const users = ref([])
const loading = ref(true)
const saving = ref(false)
const error = ref(null)

const form = reactive({
    owner_user_id: null,
    status: 'not_started',
    content_json: {}
})

const getArtifactLabel = (type) => {
    const labels = {
        strategic_alignment: 'Strategic Alignment',
        big_picture: 'Big Picture',
        domain_breakdown: 'Domain Breakdown',
        module_matrix: 'Module Matrix',
        module_engineering: 'Module Engineering',
        system_architecture: 'System Architecture',
        phase_scope: 'Phase Scope'
    }
    return labels[type] || type
}

const addArrayItem = (field) => {
    if (!form.content_json[field]) {
        form.content_json[field] = []
    }
    form.content_json[field].push('')
}

const removeArrayItem = (field, index) => {
    form.content_json[field].splice(index, 1)
}

const addMeasurableSuccess = () => {
    if (!form.content_json.measurable_success) {
        form.content_json.measurable_success = []
    }
    form.content_json.measurable_success.push({ metric: '', target: '' })
}

const addDomain = () => {
    if (!form.content_json.domains) {
        form.content_json.domains = []
    }
    form.content_json.domains.push({ name: '', objective: '', owner_user_id: null })
}

const addModuleOverview = () => {
    if (!form.content_json.modules_overview) {
        form.content_json.modules_overview = []
    }
    form.content_json.modules_overview.push({ name: '', domain: '', priority: '', phase: '' })
}

const fetchArtifact = async () => {
    const response = await axios.get(`/v1/projects/${projectId}/artifacts`)
    const found = response.data.data.find(a => a.type === artifactType)
    
    if (found) {
        artifact.value = found
        form.owner_user_id = found.owner_user_id
        form.status = found.status
        form.content_json = found.content_json || {}
        
        // Inicializar arrays vacíos
        const defaultEmptyArrays = ['supported_decisions', 'out_of_scope', 'impacted_domains', 
                                      'included_modules', 'excluded_items', 'acceptance_criteria']
        defaultEmptyArrays.forEach(field => {
            if (!form.content_json[field]) form.content_json[field] = []
        })
        
        if (!form.content_json.measurable_success) form.content_json.measurable_success = []
        if (!form.content_json.domains) form.content_json.domains = []
        if (!form.content_json.modules_overview) form.content_json.modules_overview = []
    } else {
        error.value = 'Artifact no encontrado'
    }
}

const fetchUsers = async () => {
    try {
        const response = await axios.get('/v1/users')
        users.value = response.data.data
    } catch (err) {
        console.error('Error fetching users:', err)
    }
}

const save = async () => {
    saving.value = true
    try {
        await axios.put(`/v1/artifacts/${artifact.value.id}`, {
            owner_user_id: form.owner_user_id,
            status: form.status,
            content_json: form.content_json
        })
        router.push(`/projects/${projectId}`)
    } catch (err) {
        alert('Error al guardar el artifact')
    } finally {
        saving.value = false
    }
}

const goBack = () => {
    router.push(`/projects/${projectId}`)
}

onMounted(async () => {
    loading.value = true
    await Promise.all([fetchArtifact(), fetchUsers()])
    loading.value = false
})
</script>

<style scoped>
.artifact-edit {
    max-width: 800px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #2d3748;
}

.artifact-form {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #4a5568;
}

.form-group select, .form-group textarea, .form-group input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    font-family: inherit;
}

.form-group textarea {
    resize: vertical;
}

.form-divider {
    margin: 1.5rem 0;
    font-weight: bold;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.array-field {
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    padding: 0.75rem;
}

.array-item {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.array-item input {
    flex: 1;
}

.array-item-object {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.array-item-object input {
    flex: 1;
}

.domain-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f7fafc;
    border-radius: 0.25rem;
}

.module-matrix-item {
    display: grid;
    grid-template-columns: 1fr 1fr 0.5fr 0.5fr auto;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.btn-icon {
    padding: 0 0.5rem;
    background: #f56565;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
}

.btn-add {
    margin-top: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: #48bb78;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #5a67d8;
}

.btn-secondary {
    background: #cbd5e0;
    color: #2d3748;
}

.loading, .error {
    text-align: center;
    padding: 2rem;
}
</style>