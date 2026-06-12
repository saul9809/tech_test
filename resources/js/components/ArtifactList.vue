<template>
    <div class="artifacts-grid">
        <div v-for="artifact in artifacts" :key="artifact.id" class="artifact-card" @click="$emit('edit', artifact)">
            <div class="artifact-header">
                <h3>{{ getLabel(artifact.type) }}</h3>
                <span :class="['status-badge', artifact.status]">{{ artifact.status }}</span>
            </div>
            <div v-if="artifact.owner" class="artifact-owner">Owner: {{ artifact.owner.name }}</div>
            <div v-if="artifact.block_reason" class="block-reason">⚠️ {{ artifact.block_reason }}</div>
            <div class="artifact-footer">
                <button v-if="artifact.status !== 'done' && canManage && artifact.can_be_completed"
                        @click.stop="$emit('mark-done', artifact)"
                        class="btn-complete">Marcar como Done</button>
                <span v-else-if="artifact.status !== 'done' && !artifact.can_be_completed" class="blocked-info">Bloqueado</span>
                <span v-else-if="artifact.status === 'done'" class="completed-info">✅ Completado {{ formatDate(artifact.completed_at) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    artifacts: { type: Array, required: true },
    canManage: { type: Boolean, default: false }
})
const emit = defineEmits(['edit', 'mark-done'])

const getLabel = (type) => {
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

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : ''
</script>