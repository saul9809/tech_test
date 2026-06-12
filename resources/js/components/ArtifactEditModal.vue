<template>
    <div class="modal" @click.self="close">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Editar {{ getArtifactLabel(artifact.type) }}</h2>
                <button @click="close" class="close-btn">&times;</button>
            </div>
            
            <form @submit.prevent="save">
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
                    <label>Status</label>
                    <select v-model="form.status">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="blocked">Blocked</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                
                <div class="form-divider">Contenido</div>
                
                <!-- Campos dinámicos según schema -->
                <div v-for="(fieldType, fieldName) in schema" :key="fieldName" class="form-group">
                    <label>{{ formatFieldName(fieldName) }}</label>
                    
                    <!-- Text field -->
                    <textarea v-if="fieldType === 'text'" 
                              v-model="form.content_json[fieldName]" 
                              rows="4"
                              class="textarea"></textarea>
                    
                    <!-- Array field -->
                    <div v-else-if="fieldType === 'array'" class="array-field">
                        <div v-for="(item, index) in getArrayValue(fieldName)" :key="index" class="array-item">
                            <input v-if="isSimpleArray(fieldName)" 
                                   v-model="form.content_json[fieldName][index]" 
                                   class="array-input">
                            <div v-else class="object-item">
                                <!-- Para measurable_success que es array de objetos -->
                                <input v-model="form.content_json[fieldName][index].metric" placeholder="Metric">
                                <input v-model="form.content_json[fieldName][index].target" placeholder="Target">
                            </div>
                            <button type="button" @click="removeArrayItem(fieldName, index)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addArrayItem(fieldName)" class="btn-add">+ Agregar</button>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" @click="close" class="btn-secondary">Cancelar</button>
                    <button type="submit" :disabled="saving" class="btn-primary">
                        {{ saving ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '../services/axios'

const props = defineProps({
    artifact: {
        type: Object,
        required: true
    },
    schema: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'saved'])

const users = ref([])
const saving = ref(false)

const form = reactive({
    owner_user_id: props.artifact.owner_user_id,
    status: props.artifact.status,
    content_json: JSON.parse(JSON.stringify(props.artifact.content_json || {}))
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
    // measurable_success es array de objetos, otros son strings simples
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
        await axios.put(`/v1/artifacts/${props.artifact.id}`, form)
        emit('saved')
        close()
    } catch (err) {
        alert('Error al guardar el artifact')
    } finally {
        saving.value = false
    }
}

const close = () => {
    emit('close')
}

onMounted(fetchUsers)
</script>

<style scoped>
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
    border-radius: 0.5rem;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-large {
    max-width: 800px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
}

.form-group {
    padding: 0 1.5rem;
    margin-bottom: 1rem;
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
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    font-family: monospace;
}

.form-divider {
    padding: 0 1.5rem;
    margin: 1rem 0;
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

.modal-actions {
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
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
</style>