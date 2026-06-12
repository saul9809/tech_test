<template>
    <div class="artifacts-container">
        <div v-if="artifacts.length === 0" class="empty-state">
            No hay artifacts disponibles.
        </div>
        
        <div v-else class="artifacts-grid">
            <div 
                v-for="artifact in artifacts" 
                :key="artifact.id" 
                class="artifact-card"
                @click="$emit('edit', artifact)"
            >
                <div class="artifact-header">
                    <h3>{{ getArtifactLabel(artifact.type) }}</h3>
                    <span :class="['status-badge', artifact.status]">
                        {{ getStatusLabel(artifact.status) }}
                    </span>
                </div>
                
                <div class="artifact-owner" v-if="artifact.owner">
                    <strong>Owner:</strong> {{ artifact.owner.name }}
                </div>
                <div class="artifact-owner" v-else>
                    <strong>Owner:</strong> Sin asignar
                </div>
                
                <div v-if="artifact.block_reason" class="block-reason">
                    ⚠️ {{ artifact.block_reason }}
                </div>
                
                <div class="artifact-footer">
                    <button 
                        v-if="artifact.status !== 'done' && canManage && artifact.can_be_completed"
                        @click.stop="$emit('mark-done', artifact)"
                        class="btn-complete"
                    >
                        Marcar como Done
                    </button>
                    <span v-else-if="artifact.status !== 'done' && !artifact.can_be_completed" class="blocked-info">
                        🔒 Bloqueado
                    </span>
                    <span v-else-if="artifact.status === 'done'" class="completed-info">
                        ✅ Completado
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    artifacts: {
        type: Array,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['edit', 'mark-done'])

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

const getStatusLabel = (status) => {
    const labels = {
        not_started: 'No Iniciado',
        in_progress: 'En Progreso',
        blocked: 'Bloqueado',
        done: 'Completado'
    }
    return labels[status] || status
}
</script>

<style scoped>
.artifacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.artifact-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
}

.artifact-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.artifact-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.artifact-header h3 {
    margin: 0;
    color: #2d3748;
    font-size: 1rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 600;
}

.status-badge.done {
    background: #c6f6d5;
    color: #22543d;
}

.status-badge.in_progress {
    background: #fefcbf;
    color: #975a16;
}

.status-badge.blocked {
    background: #fed7d7;
    color: #9b2c2c;
}

.status-badge.not_started {
    background: #edf2f7;
    color: #4a5568;
}

.artifact-owner {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 0.75rem;
}

.block-reason {
    margin: 0.75rem 0;
    padding: 0.5rem;
    background: #fed7d7;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #9b2c2c;
}

.artifact-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.btn-complete {
    padding: 0.5rem 1rem;
    background: #48bb78;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.btn-complete:hover {
    background: #38a169;
}

.blocked-info, .completed-info {
    font-size: 0.875rem;
}

.blocked-info {
    color: #e53e3e;
}

.completed-info {
    color: #48bb78;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #a0aec0;
}
</style>