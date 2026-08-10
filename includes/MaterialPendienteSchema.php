<?php

/**
 * Relaciona cada partida de material pendiente con el folio que la originó.
 *
 * DocumentoMP no puede usarse como relación porque un documento eliminado puede
 * volver a capturarse bajo un folio distinto. La migración conserva los datos
 * existentes y asigna cada partida al folio más reciente con el mismo estado.
 */
function asegurarRelacionFolioMaterialPendiente(mysqli $conn, string $baseDatos): void
{
    if ($baseDatos === '') {
        return;
    }

    $stmtColumna = mysqli_prepare(
        $conn,
        'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );

    if (!$stmtColumna) {
        return;
    }

    $tabla = 'materialpendiente';
    $columna = 'FacturaMPID';
    mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
    mysqli_stmt_execute($stmtColumna);
    mysqli_stmt_store_result($stmtColumna);
    $existeColumna = mysqli_stmt_num_rows($stmtColumna) > 0;
    mysqli_stmt_close($stmtColumna);

    if (!$existeColumna) {
        @mysqli_query($conn, 'ALTER TABLE materialpendiente ADD COLUMN FacturaMPID INT NULL AFTER MaterialPendienteID');
    }

    @mysqli_query($conn, 'ALTER TABLE materialpendiente ADD INDEX idx_materialpendiente_folio (FacturaMPID)');

    @mysqli_query(
        $conn,
        "UPDATE materialpendiente mp
         SET mp.FacturaMPID = (
             SELECT f.FacturaMPID
             FROM facturamp f
             WHERE f.DocumentoFMP = mp.DocumentoMP
               AND f.ActivoFMP = mp.ActivoMP
               AND f.FechaFMP <= mp.FechaMP
             ORDER BY f.FechaFMP DESC, f.FacturaMPID DESC
             LIMIT 1
         )
         WHERE mp.FacturaMPID IS NULL"
    );

    @mysqli_query(
        $conn,
        "UPDATE materialpendiente mp
         SET mp.FacturaMPID = (
             SELECT f.FacturaMPID
             FROM facturamp f
             WHERE f.DocumentoFMP = mp.DocumentoMP
             ORDER BY f.FacturaMPID DESC
             LIMIT 1
         )
         WHERE mp.FacturaMPID IS NULL"
    );

    if (!$existeColumna) {
        // La versión anterior podía reactivar folios antiguos al reutilizar un documento.
        @mysqli_query(
            $conn,
            "UPDATE materialpendiente mp
             INNER JOIN facturamp f ON f.FacturaMPID = mp.FacturaMPID
             SET mp.ActivoMP = 0
             WHERE f.ActivoFMP = 1
               AND EXISTS (
                   SELECT 1 FROM facturamp reciente
                   WHERE reciente.DocumentoFMP = f.DocumentoFMP
                     AND reciente.ActivoFMP = 1
                     AND reciente.FacturaMPID > f.FacturaMPID
               )"
        );

        @mysqli_query(
            $conn,
            "UPDATE facturamp f
             INNER JOIN facturamp reciente
                ON reciente.DocumentoFMP = f.DocumentoFMP
               AND reciente.ActivoFMP = 1
               AND reciente.FacturaMPID > f.FacturaMPID
             SET f.ActivoFMP = 0
             WHERE f.ActivoFMP = 1"
        );
    }
}
