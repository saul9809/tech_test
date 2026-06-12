<!-- Reemplazar la sección de auditoría por: -->
<template v-if="activeTab === 'audit'">
    <AuditTimeline :project-id="projectId" />
</template>

<!-- Agregar la importación -->
<script setup>
import AuditTimeline from '../components/AuditTimeline.vue'
// ... resto de imports
</script>