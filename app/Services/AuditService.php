<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Project;

class AuditService
{
    /**
     * Registrar un evento de auditoría
     */
    public function log(
        int $actorUserId,
        string $entityType,
        int $entityId,
        string $action,
        $before = null,
        $after = null
    ): AuditEvent {
        return AuditEvent::create([
            'actor_user_id' => $actorUserId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_json' => $before ? $this->filterSensitiveData($before) : null,
            'after_json' => $after ? $this->filterSensitiveData($after) : null,
        ]);
    }

    /**
     * Obtener el timeline de auditoría para un proyecto
     */
    public function getProjectTimeline(int $projectId): array
    {
        // Obtener IDs de artifacts y modules del proyecto
        $project = Project::with(['artifacts', 'modules'])->find($projectId);

        $entityIds = [$projectId];
        $entityIds = array_merge($entityIds, $project->artifacts->pluck('id')->toArray());
        $entityIds = array_merge($entityIds, $project->modules->pluck('id')->toArray());

        // Buscar eventos de auditoría
        $events = AuditEvent::with('actor')
            ->where(function ($query) use ($entityIds) {
                $query->where('entity_type', 'project')
                    ->whereIn('entity_id', [$entityIds[0]]);
            })
            ->orWhere(function ($query) use ($entityIds) {
                $query->where('entity_type', 'artifact')
                    ->whereIn('entity_id', array_slice($entityIds, 1, count($project->artifacts)));
            })
            ->orWhere(function ($query) use ($project) {
                $moduleIds = $project->modules->pluck('id')->toArray();
                if (! empty($moduleIds)) {
                    $query->where('entity_type', 'module')
                        ->whereIn('entity_id', $moduleIds);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Formatear eventos para el frontend
        return $events->map(function ($event) {
            return [
                'id' => $event->id,
                'actor' => $event->actor->name ?? 'Unknown',
                'actor_role' => $event->actor->role ?? 'unknown',
                'entity_type' => $event->entity_type,
                'entity_id' => $event->entity_id,
                'action' => $event->action,
                'action_label' => $this->getActionLabel($event->action),
                'changes' => $this->extractChanges($event->before_json, $event->after_json),
                'created_at' => $event->created_at->toISOString(),
                'created_at_human' => $event->created_at->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Obtener etiqueta legible para la acción
     */
    private function getActionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            'status_changed' => 'Cambio de estado',
            'validated' => 'Validación',
            'completed' => 'Completado',
            default => ucfirst($action),
        };
    }

    /**
     * Extraer cambios relevantes entre before y after
     */
    private function extractChanges($before, $after): array
    {
        if (! $before || ! $after) {
            return [];
        }

        $changes = [];

        // Verificar cambios en status
        if (isset($before['status']) && isset($after['status']) && $before['status'] !== $after['status']) {
            $changes[] = [
                'field' => 'status',
                'from' => $before['status'],
                'to' => $after['status'],
            ];
        }

        // Verificar cambios en campos importantes de módulos
        $importantFields = ['objective', 'responsibility', 'version_note'];
        foreach ($importantFields as $field) {
            if (isset($before[$field]) && isset($after[$field]) && $before[$field] !== $after[$field]) {
                $changes[] = [
                    'field' => $field,
                    'from' => $this->truncate($before[$field], 50),
                    'to' => $this->truncate($after[$field], 50),
                ];
            }
        }

        return $changes;
    }

    /**
     * Filtrar datos sensibles antes de guardar
     */
    private function filterSensitiveData(array $data): array
    {
        // Eliminar campos que no queremos auditar
        $exclude = ['password', 'remember_token', 'api_token'];

        foreach ($exclude as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * Truncar texto largo para mostrar en cambios
     */
    private function truncate(?string $text, int $length = 50): string
    {
        if (! $text) {
            return '';
        }

        return strlen($text) > $length ? substr($text, 0, $length).'...' : $text;
    }
}
