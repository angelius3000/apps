<?php
include('../../Connections/ConDB.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION)) {
    session_start();
}

function responderEncuestas(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function limpiarTextoEncuesta($valor, int $max): string
{
    $texto = trim((string)$valor);
    if ($texto === '') {
        return '';
    }

    $texto = strip_tags($texto);
    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $max);
    }

    return substr($texto, 0, $max);
}

function normalizarEstadoEncuesta(string $estado): string
{
    $permitidos = ['borrador', 'publicada', 'cerrada', 'archivada'];
    $estado = strtolower(trim($estado));
    return in_array($estado, $permitidos, true) ? $estado : 'borrador';
}

function normalizarTipoPregunta(string $tipo): string
{
    $permitidos = ['opcion_multiple', 'texto_corto', 'texto_largo', 'escala_agrupada'];
    $tipo = strtolower(trim($tipo));
    return in_array($tipo, $permitidos, true) ? $tipo : 'texto_corto';
}

function usuarioPuedeAdministrarEncuestas(): bool
{
    $permisos = $_SESSION['PermisosSecciones'] ?? [];
    $seccionHabilitada = !empty($permisos['encuestas']);
    if (!$seccionHabilitada) {
        return false;
    }

    $tipo = strtolower(trim((string)($_SESSION['TipoDeUsuario'] ?? '')));
    return in_array($tipo, ['administrador', 'soporte it', 'rh', 'recursos humanos'], true) || $tipo === '';
}

if (!$conn) {
    responderEncuestas(['success' => false, 'message' => 'No se pudo conectar a la base de datos.'], 500);
}

if (!isset($_SESSION['USUARIOID'])) {
    responderEncuestas(['success' => false, 'message' => 'Sesión no válida.'], 401);
}

if (!usuarioPuedeAdministrarEncuestas()) {
    responderEncuestas(['success' => false, 'message' => 'No tienes permiso para administrar encuestas.'], 403);
}

$action = trim((string)($_POST['action'] ?? 'list'));
$usuarioIdSesion = (int)$_SESSION['USUARIOID'];

if ($action === 'list') {
    $busqueda = limpiarTextoEncuesta($_POST['q'] ?? '', 100);
    $estado = normalizarEstadoEncuesta((string)($_POST['estado'] ?? 'borrador'));
    $estadoTodos = strtolower(trim((string)($_POST['estado'] ?? 'todos'))) === 'todos';

    $where = 'WHERE e.Eliminado = 0';
    $params = [];
    $types = '';

    if (!$estadoTodos) {
        $where .= ' AND e.Estado = ?';
        $params[] = $estado;
        $types .= 's';
    }

    if ($busqueda !== '') {
        $where .= ' AND e.Titulo LIKE ?';
        $params[] = '%' . $busqueda . '%';
        $types .= 's';
    }

    $sql = 'SELECT e.ENCUESTAID, e.Titulo, e.Descripcion, e.Estado, e.FechaCreacion, e.FechaPublicacion,
                   CONCAT_WS(" ", u.PrimerNombre, u.SegundoNombre, u.ApellidoPaterno, u.ApellidoMaterno) AS Creador,
                   (SELECT COUNT(*) FROM encuesta_respuestas r WHERE r.ENCUESTAID = e.ENCUESTAID) AS TotalRespuestas
            FROM encuestas e
            LEFT JOIN usuarios u ON u.USUARIOID = e.CreadoPor
            ' . $where . '
            ORDER BY e.ENCUESTAID DESC';

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        responderEncuestas(['success' => false, 'message' => 'No se pudo cargar el listado.'], 500);
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    if ($result instanceof mysqli_result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'ENCUESTAID' => (int)$row['ENCUESTAID'],
                'Titulo' => (string)$row['Titulo'],
                'Descripcion' => (string)($row['Descripcion'] ?? ''),
                'Estado' => (string)$row['Estado'],
                'FechaCreacion' => (string)($row['FechaCreacion'] ?? ''),
                'FechaPublicacion' => (string)($row['FechaPublicacion'] ?? ''),
                'Creador' => trim((string)($row['Creador'] ?? '')),
                'TotalRespuestas' => (int)($row['TotalRespuestas'] ?? 0),
            ];
        }

        mysqli_free_result($result);
    }

    mysqli_stmt_close($stmt);
    responderEncuestas(['success' => true, 'items' => $rows]);
}

if ($action === 'get') {
    $encuestaId = (int)($_POST['encuesta_id'] ?? 0);
    if ($encuestaId <= 0) {
        responderEncuestas(['success' => false, 'message' => 'Encuesta inválida.'], 422);
    }

    $stmt = mysqli_prepare($conn, 'SELECT * FROM encuestas WHERE ENCUESTAID = ? AND Eliminado = 0 LIMIT 1');
    if (!$stmt) {
        responderEncuestas(['success' => false, 'message' => 'No se pudo consultar la encuesta.'], 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $encuestaId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $encuesta = mysqli_fetch_assoc($result ?: null);
    mysqli_stmt_close($stmt);

    if (!$encuesta) {
        responderEncuestas(['success' => false, 'message' => 'No se encontró la encuesta.'], 404);
    }

    $preguntas = [];
    $stmtPreguntas = mysqli_prepare($conn, 'SELECT * FROM encuesta_preguntas WHERE ENCUESTAID = ? ORDER BY Orden ASC, PREGUNTAID ASC');
    if ($stmtPreguntas) {
        mysqli_stmt_bind_param($stmtPreguntas, 'i', $encuestaId);
        mysqli_stmt_execute($stmtPreguntas);
        $resultPreguntas = mysqli_stmt_get_result($stmtPreguntas);

        if ($resultPreguntas instanceof mysqli_result) {
            while ($pregunta = mysqli_fetch_assoc($resultPreguntas)) {
                $preguntaId = (int)$pregunta['PREGUNTAID'];
                $config = json_decode((string)($pregunta['ConfiguracionJSON'] ?? '{}'), true);
                if (!is_array($config)) {
                    $config = [];
                }

                $opciones = [];
                $stmtOpciones = mysqli_prepare($conn, 'SELECT OPCIONID, Texto, Valor, Orden, Activo FROM encuesta_opciones WHERE PREGUNTAID = ? ORDER BY Orden ASC, OPCIONID ASC');
                if ($stmtOpciones) {
                    mysqli_stmt_bind_param($stmtOpciones, 'i', $preguntaId);
                    mysqli_stmt_execute($stmtOpciones);
                    $resultOpciones = mysqli_stmt_get_result($stmtOpciones);
                    if ($resultOpciones instanceof mysqli_result) {
                        while ($opcion = mysqli_fetch_assoc($resultOpciones)) {
                            $opciones[] = [
                                'OPCIONID' => (int)$opcion['OPCIONID'],
                                'Texto' => (string)$opcion['Texto'],
                                'Valor' => (string)($opcion['Valor'] ?? ''),
                                'Orden' => (int)($opcion['Orden'] ?? 1),
                                'Activo' => (int)($opcion['Activo'] ?? 1),
                            ];
                        }
                        mysqli_free_result($resultOpciones);
                    }
                    mysqli_stmt_close($stmtOpciones);
                }

                $preguntas[] = [
                    'PREGUNTAID' => $preguntaId,
                    'Tipo' => (string)$pregunta['Tipo'],
                    'Titulo' => (string)$pregunta['Titulo'],
                    'Descripcion' => (string)($pregunta['Descripcion'] ?? ''),
                    'Requerida' => (int)$pregunta['Requerida'],
                    'Orden' => (int)$pregunta['Orden'],
                    'Configuracion' => $config,
                    'Opciones' => $opciones,
                ];
            }
            mysqli_free_result($resultPreguntas);
        }

        mysqli_stmt_close($stmtPreguntas);
    }

    $respuestasTotales = 0;
    $stmtCount = mysqli_prepare($conn, 'SELECT COUNT(*) FROM encuesta_respuestas WHERE ENCUESTAID = ?');
    if ($stmtCount) {
        mysqli_stmt_bind_param($stmtCount, 'i', $encuestaId);
        mysqli_stmt_execute($stmtCount);
        mysqli_stmt_bind_result($stmtCount, $respuestasTotales);
        mysqli_stmt_fetch($stmtCount);
        mysqli_stmt_close($stmtCount);
    }

    responderEncuestas([
        'success' => true,
        'item' => [
            'ENCUESTAID' => (int)$encuesta['ENCUESTAID'],
            'Titulo' => (string)$encuesta['Titulo'],
            'Descripcion' => (string)($encuesta['Descripcion'] ?? ''),
            'Estado' => (string)$encuesta['Estado'],
            'Categoria' => (string)($encuesta['Categoria'] ?? ''),
            'Anonima' => (int)$encuesta['Anonima'],
            'UnaRespuestaPorUsuario' => (int)$encuesta['UnaRespuestaPorUsuario'],
            'PermitirMultiplesRespuestas' => (int)$encuesta['PermitirMultiplesRespuestas'],
            'RequiereLogin' => (int)$encuesta['RequiereLogin'],
            'FechaInicio' => (string)($encuesta['FechaInicio'] ?? ''),
            'FechaFin' => (string)($encuesta['FechaFin'] ?? ''),
            'MensajeConfirmacion' => (string)($encuesta['MensajeConfirmacion'] ?? ''),
            'Configuracion' => json_decode((string)($encuesta['ConfiguracionJSON'] ?? '{}'), true) ?: [],
            'Preguntas' => $preguntas,
            'TotalRespuestas' => (int)$respuestasTotales,
        ],
    ]);
}

if ($action === 'save') {
    $raw = json_decode((string)($_POST['payload'] ?? '{}'), true);
    if (!is_array($raw)) {
        responderEncuestas(['success' => false, 'message' => 'Información inválida.'], 422);
    }

    $encuestaId = (int)($raw['encuesta_id'] ?? 0);
    $titulo = limpiarTextoEncuesta($raw['titulo'] ?? '', 200);
    $descripcion = limpiarTextoEncuesta($raw['descripcion'] ?? '', 2000);
    $categoria = limpiarTextoEncuesta($raw['categoria'] ?? '', 100);
    $anonima = !empty($raw['anonima']) ? 1 : 0;
    $unaRespuesta = !empty($raw['una_respuesta_por_usuario']) ? 1 : 0;
    $multiRespuestas = !empty($raw['permitir_multiples_respuestas']) ? 1 : 0;
    $requiereLogin = !empty($raw['requiere_login']) ? 1 : 0;
    $fechaInicio = limpiarTextoEncuesta($raw['fecha_inicio'] ?? '', 25);
    $fechaFin = limpiarTextoEncuesta($raw['fecha_fin'] ?? '', 25);
    $mensajeConfirmacion = limpiarTextoEncuesta($raw['mensaje_confirmacion'] ?? '', 1000);
    $preguntasInput = is_array($raw['preguntas'] ?? null) ? $raw['preguntas'] : [];

    if ($titulo === '') {
        responderEncuestas(['success' => false, 'message' => 'El título es obligatorio.'], 422);
    }

    if ($fechaInicio !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
        responderEncuestas(['success' => false, 'message' => 'La fecha de inicio no es válida.'], 422);
    }

    if ($fechaFin !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
        responderEncuestas(['success' => false, 'message' => 'La fecha de fin no es válida.'], 422);
    }

    if ($fechaInicio !== '' && $fechaFin !== '' && $fechaFin < $fechaInicio) {
        responderEncuestas(['success' => false, 'message' => 'La fecha de fin no puede ser menor que la de inicio.'], 422);
    }

    $preguntasNormalizadas = [];
    $orden = 1;

    foreach ($preguntasInput as $preguntaInput) {
        if (!is_array($preguntaInput)) {
            continue;
        }

        $tipo = normalizarTipoPregunta((string)($preguntaInput['tipo'] ?? 'texto_corto'));
        $tituloPregunta = limpiarTextoEncuesta($preguntaInput['titulo'] ?? '', 500);
        $descripcionPregunta = limpiarTextoEncuesta($preguntaInput['descripcion'] ?? '', 2000);
        $requerida = !empty($preguntaInput['requerida']) ? 1 : 0;

        if ($tituloPregunta === '') {
            responderEncuestas(['success' => false, 'message' => 'Todas las preguntas deben tener enunciado.'], 422);
        }

        $opciones = [];
        $configuracion = [];

        if ($tipo === 'opcion_multiple') {
            $opcionesInput = is_array($preguntaInput['opciones'] ?? null) ? $preguntaInput['opciones'] : [];
            foreach ($opcionesInput as $index => $textoOpcion) {
                $textoOpcion = limpiarTextoEncuesta($textoOpcion, 255);
                if ($textoOpcion === '') {
                    continue;
                }

                $opciones[] = [
                    'texto' => $textoOpcion,
                    'valor' => $textoOpcion,
                    'orden' => $index + 1,
                ];
            }

            if (count($opciones) < 2) {
                responderEncuestas(['success' => false, 'message' => 'Las preguntas de opción múltiple requieren al menos dos opciones.'], 422);
            }
        }

        if ($tipo === 'escala_agrupada') {
            $criteriosInput = is_array($preguntaInput['criterios'] ?? null) ? $preguntaInput['criterios'] : [];
            $opcionesEscalaInput = is_array($preguntaInput['opciones_escala'] ?? null) ? $preguntaInput['opciones_escala'] : [];

            $criterios = [];
            foreach ($criteriosInput as $criterio) {
                $criterioLimpio = limpiarTextoEncuesta($criterio, 255);
                if ($criterioLimpio !== '') {
                    $criterios[] = $criterioLimpio;
                }
            }

            $opcionesEscala = [];
            foreach ($opcionesEscalaInput as $opcionEscala) {
                $opcionEscalaLimpia = limpiarTextoEncuesta($opcionEscala, 255);
                if ($opcionEscalaLimpia !== '') {
                    $opcionesEscala[] = $opcionEscalaLimpia;
                }
            }

            if (count($criterios) === 0) {
                responderEncuestas(['success' => false, 'message' => 'La escala agrupada requiere al menos un criterio.'], 422);
            }

            if (count($opcionesEscala) < 2) {
                responderEncuestas(['success' => false, 'message' => 'La escala agrupada requiere al menos dos opciones de escala.'], 422);
            }

            $configuracion = [
                'criterios' => array_values($criterios),
                'opciones' => array_values($opcionesEscala),
                'permitir_otras' => !empty($preguntaInput['permitir_otras']) ? true : false,
            ];
        }

        $preguntasNormalizadas[] = [
            'tipo' => $tipo,
            'titulo' => $tituloPregunta,
            'descripcion' => $descripcionPregunta,
            'requerida' => $requerida,
            'orden' => $orden,
            'configuracion' => $configuracion,
            'opciones' => $opciones,
        ];

        $orden++;
    }

    if (count($preguntasNormalizadas) === 0) {
        responderEncuestas(['success' => false, 'message' => 'Debes agregar al menos una pregunta.'], 422);
    }

    mysqli_begin_transaction($conn);

    try {
        if ($encuestaId > 0) {
            $respuestasTotales = 0;
            $stmtRespuestas = mysqli_prepare($conn, 'SELECT COUNT(*) FROM encuesta_respuestas WHERE ENCUESTAID = ?');
            if (!$stmtRespuestas) {
                throw new RuntimeException('No se pudo validar respuestas.');
            }

            mysqli_stmt_bind_param($stmtRespuestas, 'i', $encuestaId);
            mysqli_stmt_execute($stmtRespuestas);
            mysqli_stmt_bind_result($stmtRespuestas, $respuestasTotales);
            mysqli_stmt_fetch($stmtRespuestas);
            mysqli_stmt_close($stmtRespuestas);

            if ((int)$respuestasTotales > 0) {
                throw new RuntimeException('La encuesta ya tiene respuestas, no se puede modificar su estructura. Duplica la encuesta para una nueva versión.');
            }

            $stmtUpdate = mysqli_prepare(
                $conn,
                'UPDATE encuestas SET Titulo = ?, Descripcion = ?, Categoria = ?, Anonima = ?, UnaRespuestaPorUsuario = ?, PermitirMultiplesRespuestas = ?, RequiereLogin = ?, FechaInicio = ?, FechaFin = ?, MensajeConfirmacion = ?, ConfiguracionJSON = ?, FechaActualizacion = NOW() WHERE ENCUESTAID = ? AND Eliminado = 0'
            );

            if (!$stmtUpdate) {
                throw new RuntimeException('No se pudo actualizar la encuesta.');
            }

            $configEncuesta = json_encode(['version' => 1], JSON_UNESCAPED_UNICODE);
            $fechaInicioDb = $fechaInicio !== '' ? $fechaInicio . ' 00:00:00' : null;
            $fechaFinDb = $fechaFin !== '' ? $fechaFin . ' 23:59:59' : null;

            mysqli_stmt_bind_param($stmtUpdate, 'sssiiiissssi', $titulo, $descripcion, $categoria, $anonima, $unaRespuesta, $multiRespuestas, $requiereLogin, $fechaInicioDb, $fechaFinDb, $mensajeConfirmacion, $configEncuesta, $encuestaId);
            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);

            mysqli_query($conn, 'DELETE FROM encuesta_opciones WHERE PREGUNTAID IN (SELECT PREGUNTAID FROM encuesta_preguntas WHERE ENCUESTAID = ' . (int)$encuestaId . ')');
            mysqli_query($conn, 'DELETE FROM encuesta_preguntas WHERE ENCUESTAID = ' . (int)$encuestaId);
        } else {
            $stmtInsert = mysqli_prepare(
                $conn,
                'INSERT INTO encuestas (Titulo, Descripcion, Estado, Categoria, Anonima, UnaRespuestaPorUsuario, PermitirMultiplesRespuestas, RequiereLogin, FechaInicio, FechaFin, MensajeConfirmacion, ConfiguracionJSON, CreadoPor) VALUES (?, ?, "borrador", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            if (!$stmtInsert) {
                throw new RuntimeException('No se pudo crear la encuesta.');
            }

            $configEncuesta = json_encode(['version' => 1], JSON_UNESCAPED_UNICODE);
            $fechaInicioDb = $fechaInicio !== '' ? $fechaInicio . ' 00:00:00' : null;
            $fechaFinDb = $fechaFin !== '' ? $fechaFin . ' 23:59:59' : null;

            mysqli_stmt_bind_param($stmtInsert, 'sssiiiissssi', $titulo, $descripcion, $categoria, $anonima, $unaRespuesta, $multiRespuestas, $requiereLogin, $fechaInicioDb, $fechaFinDb, $mensajeConfirmacion, $configEncuesta, $usuarioIdSesion);
            mysqli_stmt_execute($stmtInsert);
            $encuestaId = (int)mysqli_insert_id($conn);
            mysqli_stmt_close($stmtInsert);
        }

        $stmtPregunta = mysqli_prepare($conn, 'INSERT INTO encuesta_preguntas (ENCUESTAID, Tipo, Titulo, Descripcion, Requerida, Orden, ConfiguracionJSON) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmtOpcion = mysqli_prepare($conn, 'INSERT INTO encuesta_opciones (PREGUNTAID, Texto, Valor, Orden, Activo) VALUES (?, ?, ?, ?, 1)');

        if (!$stmtPregunta || !$stmtOpcion) {
            throw new RuntimeException('No se pudo guardar la estructura de preguntas.');
        }

        foreach ($preguntasNormalizadas as $pregunta) {
            $tipo = $pregunta['tipo'];
            $tituloPregunta = $pregunta['titulo'];
            $descripcionPregunta = $pregunta['descripcion'];
            $requerida = (int)$pregunta['requerida'];
            $ordenPregunta = (int)$pregunta['orden'];
            $configPregunta = json_encode($pregunta['configuracion'], JSON_UNESCAPED_UNICODE);

            mysqli_stmt_bind_param($stmtPregunta, 'isssiis', $encuestaId, $tipo, $tituloPregunta, $descripcionPregunta, $requerida, $ordenPregunta, $configPregunta);
            mysqli_stmt_execute($stmtPregunta);
            $preguntaId = (int)mysqli_insert_id($conn);

            if ($tipo === 'opcion_multiple' && !empty($pregunta['opciones'])) {
                foreach ($pregunta['opciones'] as $opcion) {
                    $texto = $opcion['texto'];
                    $valor = $opcion['valor'];
                    $ordenOpcion = (int)$opcion['orden'];
                    mysqli_stmt_bind_param($stmtOpcion, 'issi', $preguntaId, $texto, $valor, $ordenOpcion);
                    mysqli_stmt_execute($stmtOpcion);
                }
            }
        }

        mysqli_stmt_close($stmtPregunta);
        mysqli_stmt_close($stmtOpcion);

        mysqli_commit($conn);

        responderEncuestas([
            'success' => true,
            'message' => 'Encuesta guardada en borrador.',
            'encuesta_id' => $encuestaId,
        ]);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        responderEncuestas(['success' => false, 'message' => $e->getMessage()], 422);
    }
}

if ($action === 'update_status') {
    $encuestaId = (int)($_POST['encuesta_id'] ?? 0);
    $estado = normalizarEstadoEncuesta((string)($_POST['estado'] ?? 'borrador'));

    if ($encuestaId <= 0) {
        responderEncuestas(['success' => false, 'message' => 'Encuesta inválida.'], 422);
    }

    $fechaPublicacion = null;
    $publicadoPor = null;

    if ($estado === 'publicada') {
        $fechaPublicacion = date('Y-m-d H:i:s');
        $publicadoPor = $usuarioIdSesion;
    }

    $stmt = mysqli_prepare($conn, 'UPDATE encuestas SET Estado = ?, FechaPublicacion = COALESCE(?, FechaPublicacion), PublicadoPor = COALESCE(?, PublicadoPor), FechaActualizacion = NOW() WHERE ENCUESTAID = ? AND Eliminado = 0');
    if (!$stmt) {
        responderEncuestas(['success' => false, 'message' => 'No se pudo actualizar estado.'], 500);
    }

    mysqli_stmt_bind_param($stmt, 'ssii', $estado, $fechaPublicacion, $publicadoPor, $encuestaId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    responderEncuestas(['success' => true, 'message' => 'Estado actualizado.']);
}

if ($action === 'duplicate') {
    $encuestaId = (int)($_POST['encuesta_id'] ?? 0);
    if ($encuestaId <= 0) {
        responderEncuestas(['success' => false, 'message' => 'Encuesta inválida.'], 422);
    }

    $_POST['action'] = 'get';
    $stmt = mysqli_prepare($conn, 'SELECT Titulo, Descripcion, Categoria, Anonima, UnaRespuestaPorUsuario, PermitirMultiplesRespuestas, RequiereLogin, FechaInicio, FechaFin, MensajeConfirmacion FROM encuestas WHERE ENCUESTAID = ? AND Eliminado = 0 LIMIT 1');
    if (!$stmt) {
        responderEncuestas(['success' => false, 'message' => 'No se pudo duplicar.'], 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $encuestaId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $encuesta = mysqli_fetch_assoc($result ?: null);
    mysqli_stmt_close($stmt);

    if (!$encuesta) {
        responderEncuestas(['success' => false, 'message' => 'No existe la encuesta original.'], 404);
    }

    $stmtPreg = mysqli_prepare($conn, 'SELECT Tipo, Titulo, Descripcion, Requerida, Orden, ConfiguracionJSON, PREGUNTAID FROM encuesta_preguntas WHERE ENCUESTAID = ? ORDER BY Orden ASC');
    mysqli_stmt_bind_param($stmtPreg, 'i', $encuestaId);
    mysqli_stmt_execute($stmtPreg);
    $resPreg = mysqli_stmt_get_result($stmtPreg);
    $preguntas = [];
    while ($p = mysqli_fetch_assoc($resPreg)) {
        $pid = (int)$p['PREGUNTAID'];
        $opts = [];
        $resOpts = mysqli_query($conn, 'SELECT Texto FROM encuesta_opciones WHERE PREGUNTAID = ' . $pid . ' ORDER BY Orden ASC');
        if ($resOpts instanceof mysqli_result) {
            while ($o = mysqli_fetch_assoc($resOpts)) {
                $opts[] = $o['Texto'];
            }
            mysqli_free_result($resOpts);
        }

        $cfg = json_decode((string)$p['ConfiguracionJSON'], true) ?: [];
        $preguntas[] = [
            'tipo' => $p['Tipo'],
            'titulo' => $p['Titulo'],
            'descripcion' => $p['Descripcion'],
            'requerida' => (int)$p['Requerida'],
            'opciones' => $opts,
            'criterios' => $cfg['criterios'] ?? [],
            'opciones_escala' => $cfg['opciones'] ?? [],
            'permitir_otras' => !empty($cfg['permitir_otras']),
        ];
    }
    mysqli_stmt_close($stmtPreg);

    mysqli_begin_transaction($conn);

    try {
        $tituloCopia = $encuesta['Titulo'] . ' (Copia)';
        $descripcionCopia = (string)($encuesta['Descripcion'] ?? '');
        $categoriaCopia = (string)($encuesta['Categoria'] ?? '');
        $anonimaCopia = (int)($encuesta['Anonima'] ?? 0);
        $unaRespuestaCopia = (int)($encuesta['UnaRespuestaPorUsuario'] ?? 1);
        $multiCopia = (int)($encuesta['PermitirMultiplesRespuestas'] ?? 0);
        $requiereLoginCopia = (int)($encuesta['RequiereLogin'] ?? 1);
        $fechaInicioCopia = $encuesta['FechaInicio'] ?? null;
        $fechaFinCopia = $encuesta['FechaFin'] ?? null;
        $mensajeCopia = (string)($encuesta['MensajeConfirmacion'] ?? '');
        $configCopia = json_encode(['version' => 1], JSON_UNESCAPED_UNICODE);

        $stmtInsertEncuesta = mysqli_prepare(
            $conn,
            'INSERT INTO encuestas (Titulo, Descripcion, Estado, Categoria, Anonima, UnaRespuestaPorUsuario, PermitirMultiplesRespuestas, RequiereLogin, FechaInicio, FechaFin, MensajeConfirmacion, ConfiguracionJSON, CreadoPor) VALUES (?, ?, "borrador", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmtInsertEncuesta) {
            throw new RuntimeException('No se pudo duplicar la encuesta.');
        }
        mysqli_stmt_bind_param($stmtInsertEncuesta, 'sssiiiissssi', $tituloCopia, $descripcionCopia, $categoriaCopia, $anonimaCopia, $unaRespuestaCopia, $multiCopia, $requiereLoginCopia, $fechaInicioCopia, $fechaFinCopia, $mensajeCopia, $configCopia, $usuarioIdSesion);
        mysqli_stmt_execute($stmtInsertEncuesta);
        $nuevaEncuestaId = (int)mysqli_insert_id($conn);
        mysqli_stmt_close($stmtInsertEncuesta);

        $stmtInsertPregunta = mysqli_prepare($conn, 'INSERT INTO encuesta_preguntas (ENCUESTAID, Tipo, Titulo, Descripcion, Requerida, Orden, ConfiguracionJSON) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmtInsertOpcion = mysqli_prepare($conn, 'INSERT INTO encuesta_opciones (PREGUNTAID, Texto, Valor, Orden, Activo) VALUES (?, ?, ?, ?, 1)');
        if (!$stmtInsertPregunta || !$stmtInsertOpcion) {
            throw new RuntimeException('No se pudieron duplicar preguntas.');
        }

        foreach ($preguntas as $index => $pregunta) {
            $tipo = (string)$pregunta['tipo'];
            $tituloPregunta = (string)$pregunta['titulo'];
            $descripcionPregunta = (string)($pregunta['descripcion'] ?? '');
            $requeridaPregunta = (int)($pregunta['requerida'] ?? 0);
            $ordenPregunta = $index + 1;
            $configPregunta = json_encode([
                'criterios' => $pregunta['criterios'] ?? [],
                'opciones' => $pregunta['opciones_escala'] ?? [],
                'permitir_otras' => !empty($pregunta['permitir_otras']),
            ], JSON_UNESCAPED_UNICODE);

            if ($tipo !== 'escala_agrupada') {
                $configPregunta = json_encode([], JSON_UNESCAPED_UNICODE);
            }

            mysqli_stmt_bind_param($stmtInsertPregunta, 'isssiis', $nuevaEncuestaId, $tipo, $tituloPregunta, $descripcionPregunta, $requeridaPregunta, $ordenPregunta, $configPregunta);
            mysqli_stmt_execute($stmtInsertPregunta);
            $nuevaPreguntaId = (int)mysqli_insert_id($conn);

            if ($tipo === 'opcion_multiple') {
                foreach (($pregunta['opciones'] ?? []) as $indexOpcion => $textoOpcion) {
                    $textoOpcion = limpiarTextoEncuesta($textoOpcion, 255);
                    if ($textoOpcion === '') {
                        continue;
                    }
                    $valorOpcion = $textoOpcion;
                    $ordenOpcion = $indexOpcion + 1;
                    mysqli_stmt_bind_param($stmtInsertOpcion, 'issi', $nuevaPreguntaId, $textoOpcion, $valorOpcion, $ordenOpcion);
                    mysqli_stmt_execute($stmtInsertOpcion);
                }
            }
        }

        mysqli_stmt_close($stmtInsertPregunta);
        mysqli_stmt_close($stmtInsertOpcion);
        mysqli_commit($conn);

        responderEncuestas(['success' => true, 'message' => 'Encuesta duplicada.', 'encuesta_id' => $nuevaEncuestaId]);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        responderEncuestas(['success' => false, 'message' => $e->getMessage()], 422);
    }
}

if ($action === 'empleado_mes_template') {
    $criterios = [
        'Disponibilidad',
        'Actitud de servicio',
        'Iniciativa propia',
        'Desempeño',
        'Organización',
        'Trabajo en equipo',
        'Mejoras por la empresa',
        'Efectividad',
        'Puntualidad',
        'Compañerismo',
        'Uso del uniforme y gafete',
        'Asistencia',
        'Respeto',
        'Cumple con lineamientos',
    ];

    responderEncuestas([
        'success' => true,
        'item' => [
            'titulo' => 'Empleado del mes',
            'descripcion' => 'Evaluación interna mensual de desempeño por criterios estandarizados.',
            'categoria' => 'Recursos Humanos',
            'anonima' => 0,
            'una_respuesta_por_usuario' => 1,
            'permitir_multiples_respuestas' => 0,
            'requiere_login' => 1,
            'mensaje_confirmacion' => 'Gracias por completar la evaluación de Empleado del mes.',
            'preguntas' => [
                [
                    'tipo' => 'texto_corto',
                    'titulo' => 'Nombre del empleado',
                    'descripcion' => '',
                    'requerida' => 1,
                ],
                [
                    'tipo' => 'texto_corto',
                    'titulo' => 'Departamento',
                    'descripcion' => '',
                    'requerida' => 0,
                ],
                [
                    'tipo' => 'escala_agrupada',
                    'titulo' => 'Evaluación de desempeño',
                    'descripcion' => 'Selecciona una opción para cada criterio.',
                    'requerida' => 1,
                    'criterios' => $criterios,
                    'opciones_escala' => ['Sobresaliente', 'Destacado', 'Normal'],
                    'permitir_otras' => true,
                ],
                [
                    'tipo' => 'texto_corto',
                    'titulo' => 'Nombre de quien realizó la encuesta',
                    'descripcion' => '',
                    'requerida' => 1,
                ],
            ],
        ],
    ]);
}

responderEncuestas(['success' => false, 'message' => 'Acción no soportada.'], 404);
