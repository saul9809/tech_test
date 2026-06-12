<template>
    <div class="audit-timeline">
        <div v-if="loading" class="loading">Cargando auditoría...</div>
        <div v-else-if="events.length === 0" class="empty">No hay eventos registrados</div>
        <div v-else class="timeline">
            <div v-for="event in events" :key="event.id" class="timeline-item">
                <div class="timeline-icon" :class="getIconClass(event.action)"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong>{{ event.actor }}</strong>
                        <span class="role">({{ event.actor_role }})</span>
                        <span class="action">{{ event.action_label }}</span>
                        <span class="entity">{{ event.entity_type }}</span>
                    </div>
                    <div class="timeline-date">{{ event.created_at_human }}</div>
                    <div v-if="event.changes.length" class="changes">
                        <strong>Cambios:</strong>
                        <ul>
                            <li v-for="change in event.changes" :key="change.field">
                                {{ change.field }}: {{ change.from }} → {{ change.to }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
        deleted: 'icon-delete'
    }
    return icons[action] || 'icon-default'
}

const fetchAudit = async () => {
    try {
        const response = await axios.get(`/v1/projects/${props.projectId}/audit`)
        events.value = response.data.data
    } catch (error) {
        console.error('Error fetching audit:', error)
    } finally {
        loading.value = false
    }
}

onMounted(fetchAudit)
</script>

<style scoped>
.audit-timeline { padding: 1rem; }
.timeline { display: flex; flex-direction: column; gap: 1rem; }
.timeline-item { display: flex; gap: 1rem; padding: 1rem; border-left: 2px solid #e2e8f0; }
.timeline-icon { width: 12px; height: 12px; border-radius: 50%; margin-top: 4px; }
.icon-create { background: #48bb78; }
.icon-update { background: #4299e1; }
.icon-validate { background: #ed8936; }
.icon-complete { background: #38b2ac; }
.icon-delete { background: #f56565; }
.timeline-content { flex: 1; }
.timeline-header { margin-bottom: 0.25rem; }
.role, .entity { font-size: 0.75rem; color: #a0aec0; }
.action { display: inline-block; margin-left: 0.5rem; padding: 0.125rem 0.375rem; background: #edf2f7; border-radius: 0.25rem; font-size: 0.75rem; }
.timeline-date { font-size: 0.75rem; color: #a0aec0; margin-bottom: 0.5rem; }
.changes { margin-top: 0.5rem; padding: 0.5rem; background: #f7fafc; border-radius: 0.25rem; font-size: 0.875rem; }
.changes ul { margin: 0.25rem 0 0 1rem; }
.loading, .empty { text-align: center; padding: 2rem; color: #a0aec0; }
</style>