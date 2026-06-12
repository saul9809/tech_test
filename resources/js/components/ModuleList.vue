<template>
    <div class="modules-container">
        <div class="modules-header">
            <h2>Módulos del Proyecto</h2>
            <button v-if="canEdit" @click="$emit('create')" class="btn-primary">+ Nuevo Módulo</button>
        </div>
        <div v-if="modules.length === 0" class="empty-state">No hay módulos creados aún.</div>
        <div v-else class="modules-list">
            <div v-for="module in modules" :key="module.id" class="module-card" @click="$emit('edit', module)">
                <div class="module-header">
                    <h3>{{ module.name }}</h3>
                    <span :class="['status-badge', module.status]">{{ module.status }}</span>
                </div>
                <div class="module-info">
                    <p><strong>Domain:</strong> {{ module.domain }}</p>
                    <p><strong>Objective:</strong> {{ truncate(module.objective, 100) }}</p>
                </div>
                <div class="module-footer">
                    <div v-if="module.can_be_validated === false" class="validation-errors">
                        ⚠️ {{ module.validation_errors?.join(', ') }}
                    </div>
                    <button v-if="module.status === 'draft' && canValidate && module.can_be_validated"
                            @click.stop="$emit('validate', module)" class="btn-validate">Validar Módulo</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    modules: { type: Array, required: true },
    canEdit: { type: Boolean, default: false },
    canValidate: { type: Boolean, default: false }
})
const emit = defineEmits(['edit', 'create', 'validate'])
const truncate = (text, length) => text?.length > length ? text.substring(0, length) + '...' : text || ''
</script>