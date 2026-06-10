<?php
function agro_volver_panel() {
    $rol = $_SESSION['rol'] ?? '';
    if ($rol === 'superadmin') return 'superadmin.php';
    if ($rol === 'jefe') return 'jefe.php';
    return 'trabajador.php';
}
function agro_tabla_existe($conn, $tabla) {
    $tabla = $conn->real_escape_string($tabla);
    $res = $conn->query("SHOW TABLES LIKE '$tabla'");
    return $res && $res->num_rows > 0;
}
function agro_columna_existe($conn, $tabla, $columna) {
    $tabla = $conn->real_escape_string($tabla);
    $columna = $conn->real_escape_string($columna);
    $res = $conn->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
    return $res && $res->num_rows > 0;
}
function agro_empresas_visibles($conn) {
    $rol = $_SESSION['rol'] ?? '';
    $empresa_id = intval($_SESSION['empresa_id'] ?? 0);
    if ($rol === 'superadmin') return $conn->query("SELECT id, nombre FROM empresas ORDER BY nombre ASC");
    $stmt = $conn->prepare("SELECT id, nombre FROM empresas WHERE id=?");
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    return $stmt->get_result();
}
function agro_filtro_empresa_sql($alias = '') {
    $rol = $_SESSION['rol'] ?? '';
    $empresa_id = intval($_SESSION['empresa_id'] ?? 0);
    $prefix = $alias ? $alias.'.' : '';
    if ($rol === 'superadmin') return ['', []];
    return [" WHERE {$prefix}empresa_id = ? ", [$empresa_id]];
}
function agro_bind_params($stmt, $types, $params) {
    if (!$params) return;
    $stmt->bind_param($types, ...$params);
}

function agro_crud_botones($tabla, $id, $volver) {
    $rol = $_SESSION['rol'] ?? '';
    if (!in_array($rol, ['superadmin','jefe'])) return '';
    $permitidas = ['empresas','usuarios','parcelas','cultivos','recursos','incidencias','tareas_agricolas','historico_actividades','grupos_trabajadores'];
    if (!in_array($tabla, $permitidas)) return '';
    $id = intval($id);
    $volver = urlencode($volver);
    $tablaUrl = urlencode($tabla);
    return '<div class="crud-actions"><a class="crud-btn crud-btn--edit" href="edit_record.php?tabla='.$tablaUrl.'&id='.$id.'&volver='.$volver.'">Editar</a><a class="crud-btn crud-btn--delete" href="delete_record.php?tabla='.$tablaUrl.'&id='.$id.'&volver='.$volver.'">Borrar</a></div>';
}
?>
