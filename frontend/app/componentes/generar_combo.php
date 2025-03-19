<?php

function generarCombo(array $data, $idSeleccionado = null): string {
    // Verifica que la estructura de $data sea válida
    if (empty($data['success']) || !isset($data['response'])) {
        return '<option value="">No hay datos disponibles</option>';
    }

    $results = $data['response'];
    $html = '<option value="">Seleccione una opción</option>'; // Opción inicial

    foreach ($results as $item) {
        if (isset($item['id']) && isset($item['nombre'])) {
            $selected = ($idSeleccionado !== null && $item['id'] == $idSeleccionado) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($item['id']) . '"' . $selected . '>' . htmlspecialchars($item['nombre']) . '</option>';
        }
    }

    return $html;
}