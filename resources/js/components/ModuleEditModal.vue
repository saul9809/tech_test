<template>
    <div class="modal" @click.self="close">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>{{ module.id ? 'Editar Módulo' : 'Nuevo Módulo' }}</h2>
                <button @click="close" class="close-btn">&times;</button>
            </div>
            
            <form @submit.prevent="save">
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
                            <div v-for="(item, index) in form.inputs" :key="index" class="array-item">
                                <input v-model="form.inputs[index]" class="array-input">
                                <button type="button" @click="removeInput(index)" class="btn-icon">🗑️</button>
                            </div>
                            <button type="button" @click="addInput" class="btn-add">+ Agregar Input</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Outputs * (al menos 1)</label>
                        <div class="array-field">
                            <div v-for="(item, index) in form.outputs" :key="index" class="array-item">
                                <input v-model="form.outputs[index]" class="array-input">
                                <button type="button" @click="removeOutput(index)" class="btn-icon">🗑️</button>
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
                    <label>Data Structure</label>
                    <textarea v-model="dataStructureText" rows="4" placeholder="JSON" class="textarea"></textarea>
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
                    <label>Dependencies (IDs de otros módulos)</label>
                    <input v-model="dependenciesText" placeholder="Ej: 1,2,3">
                </div>
                
                <div class="form-group">
                    <label>Version Note</label>
                    <input v-model="form.version_note">
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
import { ref, reactive, computed, watch } from 'vue'
import axios from '../services/axios'

const props = defineProps({
    module: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'saved'])

const saving = ref(false)

const form = reactive({
    name: props.module.name || '',
    domain: props.module.domain || '',
    objective: props.module.objective || '',
    inputs: props.module.inputs || [],
    data_structure: props.module.data_structure || {},
    logic_rules: props.module.logic_rules || '',
    outputs: props.module.outputs || [],
    responsibility: props.module.responsibility || '',
    failure_scenarios: props.module.failure_scenarios || '',
    audit_trail_requirements: props.module.audit_trail_requirements || '',
    dependencies: props.module.dependencies || [],
    version_note: props.module.version_note || ''
})

const dataStructureText = computed({
    get: () => JSON.stringify(form.data_structure, null, 2),
    set: (value) => {
        try {
            form.data_structure = JSON.parse(value)
        } catch (e) {
            // Invalid JSON, keep as is
        }
    }
})

const dependenciesText = computed({
    get: () => form.dependencies.join(', '),
    set: (value) => {
        form.dependencies = value.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id))
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

const save = async () => {
    saving.value = true
    try {
        const url = props.module.id 
            ? `/v1/modules/${props.module.id}` 
            : `/v1/projects/${props.module.project_id}/modules`
        const method = props.module.id ? 'put' : 'post'
        
        await axios[method](url, form)
        emit('saved')
        close()
    } catch (err) {
        alert('Error al guardar el módulo')
    } finally {
        saving.value = false
    }
}

const close = () => {
    emit('close')
}
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
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-large {
    max-width: 900px;
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    padding: 0 1.5rem;
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

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
}

.textarea {
    font-family: monospace;
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