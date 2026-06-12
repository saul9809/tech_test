<template>
    <div class="artifact-edit">
        <div class="page-header">
            <h1>Editar {{ getArtifactLabel(artifactType) }}</h1>
            <button @click="goBack" class="btn-secondary">Volver</button>
        </div>
        
        <div v-if="loading" class="loading">Cargando...</div>
        <div v-else-if="error" class="error">{{ error }}</div>
        
        <form v-else @submit.prevent="save" class="artifact-form">
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
            
            <div class="form-divider">Contenido</div>
            
            <!-- Campos dinámicos según el tipo de artifact -->
            <div v-for="(fieldType, fieldName) in schema" :key="fieldName" class="form-group">
                <label>{{ formatFieldName(fieldName) }}</label>
                
                <textarea v-if="fieldType === 'text'" 
                          v-model="form.content_json[fieldName]" 
                          rows="4" 
                          class="textarea"></textarea>
                
                <div v-else-if="fieldType === 'array'" class="array-field">
                    <div v-for="(item, index) in getArrayValue(fieldName)" :key="index" class="array-item">
                        <input v-if="isSimpleArray(fieldName)" 
                               v-model="form.content_json[fieldName][index]" 
                               class="array-input">
                        <div v-else class="object-item">
                            <input v-model="form.content_json[fieldName][index].metric" placeholder="Métrica">
                            <input v-model="form.content_json[fieldName][index].target" placeholder="Objetivo">
                        </div>
                        <button type="button" @click="removeArrayItem(fieldName, index)" class="btn-icon">🗑️</button>
                    </div>
                    <button type="button" @click="addArrayItem(fieldName)" class="btn-add">+ Agregar</button>
                </div>
            </div>
            
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

const route = useRoute()
const router = useRouter()
const projectId = route.params.id
const artifactType = route.params.artifactType

const artifact = ref(null)
const users = ref([])
const schema = ref({})
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

const formatFieldName = (name) => {
    return name.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const isSimpleArray = (fieldName) => {
    return fieldName !== 'measurable_success'
}

const getArrayValue = (fieldName) => {
    if (!form.content_json[fieldName]) {
        form.content_json[fieldName] = []
    }
    return form.content_json[fieldName]
}

const addArrayItem = (fieldName) => {
    if (!form.content_json[fieldName]) {
        form.content_json[fieldName] = []
    }
    
    if (fieldName === 'measurable_success') {
        form.content_json[fieldName].push({ metric: '', target: '' })
    } else {
        form.content_json[fieldName].push('')
    }
}

const removeArrayItem = (fieldName, index) => {
    form.content_json[fieldName].splice(index, 1)
}

const fetchArtifact = async () => {
    try {
        // Buscar el artifact por proyecto y tipo
        const response = await axios.get(`/v1/projects/${projectId}/artifacts`)
        const found = response.data.data.find(a => a.type === artifactType)
        
        if (found) {
            artifact.value = found
            form.owner_user_id = found.owner_user_id
            form.status = found.status
            form.content_json = found.content_json || {}
        } else {
            error.value = 'Artifact no encontrado'
        }
    } catch (err) {
        error.value = 'Error al cargar el artifact'
    }
}

const fetchSchema = async () => {
    try {
        const response = await axios.get(`/v1/artifacts/schema/${artifactType}`)
        schema.value = response.data.data
        
        // Inicializar campos vacíos
        Object.keys(schema.value).forEach(field => {
            if (!form.content_json[field]) {
                if (schema.value[field] === 'array') {
                    form.content_json[field] = []
                } else {
                    form.content_json[field] = ''
                }
            }
        })
    } catch (err) {
        console.error('Error fetching schema:', err)
    }
}

const fetchUsers = async () => {
    try {
        // Nota: Necesitas crear un endpoint para listar usuarios
        // Por ahora, usamos un array vacío
        users.value = []
    } catch (err) {
        console.error('Error fetching users:', err)
    }
}

const save = async () => {
    saving.value = true
    try {
        await axios.put(`/v1/artifacts/${artifact.value.id}`, form)
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
    await Promise.all([fetchArtifact(), fetchSchema(), fetchUsers()])
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
.artifact-form {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.form-group select, .form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
}
.textarea {
    font-family: monospace;
}
.form-divider {
    margin: 1.5rem 0;
    font-weight: bold;
    border-bottom: 1px solid #e2e8f0;
}
.array-field {
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    padding: 0.5rem;
}
.array-item {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.array-input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
}
.object-item {
    flex: 1;
    display: flex;
    gap: 0.5rem;
}
.object-item input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
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
}
.btn-primary {
    background: #667eea;
    color: white;
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