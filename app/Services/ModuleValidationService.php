<?php

namespace App\Services;

use App\Models\Module;

class ModuleValidationService
{
    /**
     * Regla de validación de módulos:
     * Un módulo solo puede pasar a validated si:
     * - objective no está vacío
     * - inputs tiene al menos 1 item
     * - outputs tiene al menos 1 item
     * - responsibility no está vacío
     */
    public function canBeValidated(Module $module): array
    {
        $errors = [];

        if (empty($module->objective)) {
            $errors[] = 'El campo "objective" es requerido';
        }

        $inputs = is_array($module->inputs) ? $module->inputs : json_decode($module->inputs, true);
        if (empty($inputs) || count($inputs) === 0) {
            $errors[] = 'El campo "inputs" debe tener al menos 1 elemento';
        }

        $outputs = is_array($module->outputs) ? $module->outputs : json_decode($module->outputs, true);
        if (empty($outputs) || count($outputs) === 0) {
            $errors[] = 'El campo "outputs" debe tener al menos 1 elemento';
        }

        if (empty($module->responsibility)) {
            $errors[] = 'El campo "responsibility" es requerido';
        }

        return [
            'can' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validar y actualizar el estado del módulo
     */
    public function validate(Module $module): array
    {
        $validation = $this->canBeValidated($module);

        if ($validation['can'] && $module->status === 'draft') {
            $oldStatus = $module->status;
            $module->status = 'validated';
            $module->save();

            return [
                'success' => true,
                'message' => 'Módulo validado correctamente',
                'old_status' => $oldStatus,
                'new_status' => $module->status,
            ];
        }

        return [
            'success' => false,
            'message' => 'No se puede validar el módulo',
            'errors' => $validation['errors'],
        ];
    }
}
