<template>
    <div class="modules-container">
        <div class="modules-header">
            <h2>Módulos del Proyecto</h2>
            <button v-if="canEdit" @click="$emit('create')" class="btn-primary">
                + Nuevo Módulo
            </button>
        </div>
        
        <div v-if="modules.length === 0" class="empty-state">
            No hay módulos creados aún.
        </div>
        
        <div v-else class="modules-list">
            <div 
                v-for="module in modules" 
                :key="module.id" 
                class="module-card"
                @click="$emit('edit', module)"
            >
                <div class="module-header">
                    <div>
                        <h3>{{ module.name }}</h3>
                        <span class="domain">{{ module.domain }}</span>
                    </div>
                    <span :class="['status-badge', module.status]">
                        {{ getStatusLabel(module.status) }}
                    </span>
                </div>
                
                <div class="module-info">
                    <p><strong>Objective:</strong> {{ truncate(module.objective, 100) }}</p>
                    <p><strong>Responsibility:</strong> {{ truncate(module.responsibility, 100) }}</p>
                </div>
                
                <div class="module-footer">
                    <div v-if="module.can_be_validated === false" class="validation-errors">
                        ⚠️ No puede ser validado: {{ module.validation_errors?.join(', ') }}
                    </div>
                    <button 
                        v-if="module.status === 'draft' && canValidate && module.can_be_validated"
                        @click.stop="$emit('validate', module)"
                        class="btn-validate"
                    >
                        Validar Módulo
                    </button>
                    <span v-else-if="module.status === 'validated'" class="validated-info">
                        ✅ Validado
                    </span>
                    <span v-else-if="module.status === 'ready_for_build'" class="ready-info">
                        🚀 Listo para construir
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    modules: {
        type: Array,
        required: true
    },
    canEdit: {
        type: Boolean,
        default: false
    },
    canValidate: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['edit', 'create', 'validate'])

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Borrador',
        validated: 'Validado',
        ready_for_build: 'Listo para Build'
    }
    return labels[status] || status
}

const truncate = (text, length) => {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}
</script>

<style scoped>
.modules-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modules-header h2 {
    color: #2d3748;
}

.modules-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.module-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
}

.module-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.module-header h3 {
    margin: 0 0 0.25rem;
    color: #2d3748;
}

.domain {
    font-size: 0.75rem;
    color: #718096;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 600;
}

.status-badge.draft {
    background: #edf2f7;
    color: #4a5568;
}

.status-badge.validated {
    background: #c6f6d5;
    color: #22543d;
}

.status-badge.ready_for_build {
    background: #bee3f8;
    color: #2c5282;
}

.module-info {
    margin: 1rem 0;
    font-size: 0.875rem;
    color: #4a5568;
}

.module-info p {
    margin: 0.25rem 0;
}

.module-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.validation-errors {
    margin: 0.5rem 0;
    padding: 0.5rem;
    background: #fed7d7;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #9b2c2c;
}

.btn-validate {
    padding: 0.5rem 1rem;
    background: #ed8936;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.btn-validate:hover {
    background: #dd6b20;
}

.validated-info, .ready-info {
    font-size: 0.875rem;
}

.validated-info {
    color: #48bb78;
}

.ready-info {
    color: #4299e1;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #a0aec0;
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #5a67d8;
}
</style>