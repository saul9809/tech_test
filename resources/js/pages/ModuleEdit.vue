<template>
    <div class="module-edit">
        <div class="page-header">
            <h1>{{ isEdit ? 'Editar Módulo' : 'Nuevo Módulo' }}</h1>
            <button @click="goBack" class="btn-secondary">Volver al Proyecto</button>
        </div>
        
        <div v-if="loading" class="loading">Cargando...</div>
        
        <form v-else @submit.prevent="save" class="module-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input v-model="form.name" required>
                </div>
                <div class="form-group">
                    <label>Domain *</label>
                    <input v-model="form.domain" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Objective *</label>
                <textarea v-model="form.objective" rows="3" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Inputs * (al menos 1)</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.inputs" :key="idx" class="array-item">
                            <input v-model="form.inputs[idx]" placeholder="Input">
                            <button type="button" @click="removeInput(idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addInput" class="btn-add">+ Agregar Input</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Outputs * (al menos 1)</label>
                    <div class="array-field">
                        <div v-for="(item, idx) in form.outputs" :key="idx" class="array-item">
                            <input v-model="form.outputs[idx]" placeholder="Output">
                            <button type="button" @click="removeOutput(idx)" class="btn-icon">🗑️</button>
                        </div>
                        <button type="button" @click="addOutput" class="btn-add">+ Agregar Output</button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Responsibility *</label>
                <textarea v-model="form.responsibility" rows="2" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Data Structure (JSON)</label>
                <textarea v-model="dataStructureText" rows="4" class="textarea" placeholder='{"key": "value"}'></textarea>
            </div>
            
            <div class="form-group">
                <label>Logic Rules</label>
                <textarea v-model="form.logic_rules" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Failure Scenarios</label>
                <textarea v-model="form.failure_scenarios" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>Audit Trail Requirements</label>
                <textarea v-model="form.audit_trail_requirements" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>Dependencies (IDs de otros módulos, separados por coma)</label>
                <input v-model="dependenciesText" placeholder="Ej: 1,2,3">
            </div>
            
            <div class="form-group">
                <label>Version Note</label>
                <input v-model="form.version_note" placeholder="v1.0.0">
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../services/axios'

const route = useRoute()
const router = useRouter()

const projectId = route.params.id
const moduleId = route.params.moduleId
const isEdit = moduleId && moduleId !== 'new'

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    name: '',
    domain: '',
    objective: '',
    inputs: [],
    data_structure: {},
    logic_rules: '',
    outputs: [],
    responsibility: '',
    failure_scenarios: '',
    audit_trail_requirements: '',
    dependencies: [],
    version_note: ''
})

const dataStructureText = computed({
    get: () => JSON.stringify(form.data_structure, null, 2),
    set: (value) => {
        try {
            form.data_structure = JSON.parse(value)
        } catch (e) {
            // Mantener el valor anterior si JSON inválido
        }
    }
})

const dependenciesText = computed({
    get: () => form.dependencies.join(', '),
    set: (value) => {
        form.dependencies = value.split(',')
            .map(id => parseInt(id.trim()))
            .filter(id => !isNaN(id))
    }
})

const addInput = () => {
    form.inputs.push('')
}

const removeInput = (index) => {
    form.inputs.splice(index, 1)
}

const addOutput = () => {
    form.outputs.push('')
}

const removeOutput = (index) => {
    form.outputs.splice(index, 1)
}

const fetchModule = async () => {
    if (isEdit) {
        const response = await axios.get(`/v1/modules/${moduleId}`)
        const module = response.data.data
        Object.assign(form, module)
        form.data_structure = module.data_structure || {}
        form.dependencies = module.dependencies || []
        form.inputs = module.inputs || []
        form.outputs = module.outputs || []
    }
    loading.value = false
}

const save = async () => {
    saving.value = true
    try {
        if (isEdit) {
            await axios.put(`/v1/modules/${moduleId}`, form)
        } else {
            await axios.post(`/v1/projects/${projectId}/modules`, form)
        }
        router.push(`/projects/${projectId}`)
    } catch (err) {
        alert('Error al guardar el módulo')
    } finally {
        saving.value = false
    }
}

const goBack = () => {
    const tab = route.query.tab || 'modules' // Por defecto a modules
    router.push(`/projects/${projectId}?tab=${tab}`)
}

onMounted(fetchModule)
</script>

<style scoped>
.module-edit {
    max-width: 900px;
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

.module-form {
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

.form-group input, .form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    font-family: inherit;
}

.form-group textarea {
    resize: vertical;
}

.textarea {
    font-family: monospace;
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

.loading {
    text-align: center;
    padding: 2rem;
}
</style>