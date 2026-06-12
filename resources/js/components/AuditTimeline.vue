<template>
    <div class="audit-timeline">
        <div v-if="loading" class="loading">Cargando auditoría...</div>
        
        <div v-else-if="!projectId" class="error">
            Error: No se especificó un proyecto
        </div>
        
        <div v-else-if="events.length === 0" class="empty">
            No hay eventos registrados para este proyecto.
        </div>
        
        <div v-else class="timeline">
            <div v-for="event in events" :key="event.id" class="timeline-item">
                <div :class="['timeline-icon', getIconClass(event.action)]"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong>{{ event.actor }}</strong>
                        <span class="actor-role">({{ event.actor_role }})</span>
                        <span class="timeline-action">{{ event.action_label }}</span>
                        <span class="timeline-entity">{{ event.entity_type }}</span>
                    </div>
                    <div class="timeline-date">{{ event.created_at_human }}</div>
                    <div v-if="event.changes && event.changes.length > 0" class="timeline-changes">
                        <strong>Cambios:</strong>
                        <ul>
                            <li v-for="change in event.changes" :key="change.field">
                                {{ change.field }}: "{{ change.from }}" → "{{ change.to }}"
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from '../services/axios'

const props = defineProps({
    projectId: {
        type: [Number, String],
        required: true
    }
})

const events = ref([])
const loading = ref(true)

const getIconClass = (action) => {
    const icons = {
        created: 'icon-create',
        updated: 'icon-update',
        validated: 'icon-validate',
        completed: 'icon-complete',
        deleted: 'icon-delete',
        status_changed: 'icon-status'
    }
    return icons[action] || 'icon-default'
}

const fetchAudit = async () => {
    if (!props.projectId) {
        loading.value = false
        return
    }
    
    loading.value = true
    try {
        const response = await axios.get(`/v1/projects/${props.projectId}/audit`)
        events.value = response.data.data
    } catch (error) {
        console.error('Error fetching audit:', error)
        events.value = []
    } finally {
        loading.value = false
    }
}

onMounted(fetchAudit)
watch(() => props.projectId, fetchAudit)
</script>

<style scoped>
.audit-timeline {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-left: 2px solid #e2e8f0;
    position: relative;
}

.timeline-icon {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 4px;
    flex-shrink: 0;
}

.icon-create { background: #48bb78; }
.icon-update { background: #4299e1; }
.icon-validate { background: #ed8936; }
.icon-complete { background: #38b2ac; }
.icon-delete { background: #f56565; }
.icon-status { background: #805ad5; }
.icon-default { background: #a0aec0; }

.timeline-content {
    flex: 1;
}

.timeline-header {
    margin-bottom: 0.25rem;
}

.actor-role {
    font-size: 0.75rem;
    color: #a0aec0;
    margin-left: 0.25rem;
}

.timeline-action {
    display: inline-block;
    margin-left: 0.5rem;
    padding: 0.125rem 0.375rem;
    background: #edf2f7;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.timeline-entity {
    margin-left: 0.5rem;
    font-size: 0.75rem;
    color: #a0aec0;
}

.timeline-date {
    font-size: 0.75rem;
    color: #a0aec0;
    margin-bottom: 0.5rem;
}

.timeline-changes {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #f7fafc;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.timeline-changes ul {
    margin: 0.25rem 0 0 1rem;
}

.loading, .empty, .error {
    text-align: center;
    padding: 2rem;
    color: #a0aec0;
}

.error {
    color: #e53e3e;
}
</style>