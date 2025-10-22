<?php

declare(strict_types=1);

/**
 * Utility helpers to manage database backups within the application.
 */

/**
 * Returns the absolute path to the directory where database backups are stored.
 */
function dbBackupDirectory(): string
{
    return __DIR__ . '/../storage/backups';
}

/**
 * Ensures the backup directory exists and is writable.
 */
function dbBackupEnsureDirectory(): bool
{
    $directory = dbBackupDirectory();
    if (is_dir($directory)) {
        return is_writable($directory);
    }

    return @mkdir($directory, 0775, true);
}

/**
 * Builds a safe, absolute path for a backup file name.
 */
function dbBackupResolvePath(string $fileName): ?string
{
    $normalized = trim($fileName);
    if ($normalized === '') {
        return null;
    }

    $normalized = basename($normalized);
    if (!preg_match('/^[A-Za-z0-9_.-]+$/', $normalized)) {
        return null;
    }

    $path = dbBackupDirectory() . DIRECTORY_SEPARATOR . $normalized;
    if (!is_file($path)) {
        return null;
    }

    return $path;
}

/**
 * Returns a list of backup files stored on the server.
 *
 * Each entry contains the keys: name, path, size, and mtime.
 */
function dbBackupListFiles(): array
{
    $directory = dbBackupDirectory();
    if (!is_dir($directory)) {
        return [];
    }

    $files = scandir($directory);
    if ($files === false) {
        return [];
    }

    $backups = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path) || !preg_match('/\.sql$/i', $file)) {
            continue;
        }

        $backups[] = [
            'name' => $file,
            'path' => $path,
            'size' => filesize($path),
            'mtime' => filemtime($path),
        ];
    }

    usort($backups, static function (array $a, array $b): int {
        return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
    });

    return $backups;
}

/**
 * Creates a new SQL dump of the database using the provided connection.
 *
 * @return array{0:bool,1:string,2:?string}
 */
function dbBackupCreate(mysqli $connection): array
{
    if (!dbBackupEnsureDirectory()) {
        return [false, 'No fue posible preparar el directorio de respaldos en el servidor.', null];
    }

    $timestamp = date('Ymd_His');
    $fileName = 'backup_' . $timestamp . '.sql';
    $filePath = dbBackupDirectory() . DIRECTORY_SEPARATOR . $fileName;

    $fileHandle = @fopen($filePath, 'w');
    if ($fileHandle === false) {
        return [false, 'No fue posible crear el archivo de respaldo en el servidor.', null];
    }

    $metadataQuery = @mysqli_query($connection, 'SELECT DATABASE() AS db');
    $databaseName = 'base_de_datos';
    if ($metadataQuery instanceof mysqli_result) {
        $row = mysqli_fetch_assoc($metadataQuery);
        if ($row && !empty($row['db'])) {
            $databaseName = (string) $row['db'];
        }
        mysqli_free_result($metadataQuery);
    }

    $header = sprintf(
        "-- Respaldo generado automáticamente el %s\n-- Base de datos: %s\n\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n",
        date('Y-m-d H:i:s'),
        $databaseName
    );

    fwrite($fileHandle, $header);
    @set_time_limit(0);

    $tablesResult = @mysqli_query($connection, 'SHOW FULL TABLES');
    if (!$tablesResult instanceof mysqli_result) {
        fclose($fileHandle);
        @unlink($filePath);
        return [false, 'No fue posible obtener las tablas de la base de datos para generar el respaldo.', null];
    }

    while ($tableRow = mysqli_fetch_row($tablesResult)) {
        if (!$tableRow || !isset($tableRow[0])) {
            continue;
        }

        $tableName = (string) $tableRow[0];
        $tableType = isset($tableRow[1]) ? strtoupper((string) $tableRow[1]) : 'BASE TABLE';

        if ($tableType === 'VIEW') {
            $createViewResult = @mysqli_query($connection, 'SHOW CREATE VIEW `' . $tableName . '`');
            if (!$createViewResult instanceof mysqli_result) {
                fclose($fileHandle);
                mysqli_free_result($tablesResult);
                @unlink($filePath);
                return [false, 'No fue posible obtener la definición de la vista ' . $tableName . '.', null];
            }

            $createViewRow = mysqli_fetch_assoc($createViewResult);
            mysqli_free_result($createViewResult);

            if (!isset($createViewRow['Create View'])) {
                fclose($fileHandle);
                mysqli_free_result($tablesResult);
                @unlink($filePath);
                return [false, 'La vista ' . $tableName . ' no devolvió información de creación.', null];
            }

            fwrite($fileHandle, "-- Vista: `{$tableName}`\n");
            fwrite($fileHandle, 'DROP VIEW IF EXISTS `' . $tableName . '`;' . "\n");
            fwrite($fileHandle, $createViewRow['Create View'] . ';' . "\n\n");
            continue;
        }

        $createTableResult = @mysqli_query($connection, 'SHOW CREATE TABLE `' . $tableName . '`');
        if (!$createTableResult instanceof mysqli_result) {
            fclose($fileHandle);
            mysqli_free_result($tablesResult);
            @unlink($filePath);
            return [false, 'No fue posible obtener la estructura de la tabla ' . $tableName . '.', null];
        }

        $createTableRow = mysqli_fetch_assoc($createTableResult);
        mysqli_free_result($createTableResult);

        if (!isset($createTableRow['Create Table'])) {
            fclose($fileHandle);
            mysqli_free_result($tablesResult);
            @unlink($filePath);
            return [false, 'La tabla ' . $tableName . ' no devolvió información de creación.', null];
        }

        fwrite($fileHandle, "-- Tabla: `{$tableName}`\n");
        fwrite($fileHandle, 'DROP TABLE IF EXISTS `' . $tableName . '`;' . "\n");
        fwrite($fileHandle, $createTableRow['Create Table'] . ';' . "\n\n");

        $dataResult = @mysqli_query($connection, 'SELECT * FROM `' . $tableName . '`');
        if (!$dataResult instanceof mysqli_result) {
            fclose($fileHandle);
            mysqli_free_result($tablesResult);
            @unlink($filePath);
            return [false, 'No fue posible obtener los datos de la tabla ' . $tableName . '.', null];
        }

        if (mysqli_num_rows($dataResult) > 0) {
            $fields = mysqli_fetch_fields($dataResult);
            $columnNames = array_map(static function ($field): string {
                return '`' . $field->name . '`';
            }, $fields);
            $columnList = implode(', ', $columnNames);

            while ($rowData = mysqli_fetch_row($dataResult)) {
                $values = [];
                foreach ($rowData as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                        continue;
                    }

                    $escaped = mysqli_real_escape_string($connection, (string) $value);
                    $escaped = str_replace(["\r", "\n"], ['\\r', '\\n'], $escaped);
                    $values[] = "'" . $escaped . "'";
                }

                $insertStatement = 'INSERT INTO `' . $tableName . '` (' . $columnList . ') VALUES (' . implode(', ', $values) . ');';
                fwrite($fileHandle, $insertStatement . "\n");
            }
            fwrite($fileHandle, "\n");
        }

        mysqli_free_result($dataResult);
    }

    mysqli_free_result($tablesResult);
    fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fileHandle);

    return [true, 'El respaldo se generó correctamente.', $fileName];
}

/**
 * Creates a new SQL dump containing only the requested table.
 *
 * @return array{0:bool,1:string,2:?string}
 */
function dbBackupCreateTable(mysqli $connection, string $tableName): array
{
    $normalizedTable = trim($tableName);
    if ($normalizedTable === '') {
        return [false, 'Selecciona una tabla válida para generar el respaldo.', null];
    }

    $normalizedTable = str_replace('`', '', $normalizedTable);
    if (!preg_match('/^[A-Za-z0-9_]+$/', $normalizedTable)) {
        return [false, 'El nombre de la tabla no es válido para crear un respaldo.', null];
    }

    $stmtVerify = @mysqli_prepare(
        $connection,
        'SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.tables WHERE table_schema = DATABASE() AND TABLE_NAME = ? LIMIT 1'
    );

    if (!$stmtVerify) {
        return [false, 'No fue posible verificar la tabla seleccionada para generar el respaldo.', null];
    }

    $nombreTablaDb = '';
    $tipoTablaDb = '';

    if (
        !@mysqli_stmt_bind_param($stmtVerify, 's', $normalizedTable)
        || !@mysqli_stmt_execute($stmtVerify)
        || !@mysqli_stmt_bind_result($stmtVerify, $nombreTablaDb, $tipoTablaDb)
    ) {
        mysqli_stmt_close($stmtVerify);
        return [false, 'Ocurrió un error al preparar la información de la tabla a respaldar.', null];
    }

    $tablaEncontrada = @mysqli_stmt_fetch($stmtVerify) === true;
    mysqli_stmt_close($stmtVerify);

    if (!$tablaEncontrada) {
        return [false, 'La tabla seleccionada no existe en la base de datos.', null];
    }

    $tipoTablaNormalizado = strtoupper((string) $tipoTablaDb);
    if ($tipoTablaNormalizado !== 'BASE TABLE') {
        return [false, 'Solo es posible generar respaldos individuales para tablas de datos.', null];
    }

    if (!dbBackupEnsureDirectory()) {
        return [false, 'No fue posible preparar el directorio de respaldos en el servidor.', null];
    }

    $timestamp = date('Ymd_His');
    $fileName = 'backup_' . $normalizedTable . '_' . $timestamp . '.sql';
    $filePath = dbBackupDirectory() . DIRECTORY_SEPARATOR . $fileName;

    $fileHandle = @fopen($filePath, 'w');
    if ($fileHandle === false) {
        return [false, 'No fue posible crear el archivo de respaldo en el servidor.', null];
    }

    $header = sprintf(
        "-- Respaldo de la tabla %s generado el %s\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n",
        '`' . $normalizedTable . '`',
        date('Y-m-d H:i:s')
    );

    fwrite($fileHandle, $header);
    @set_time_limit(0);

    $createTableResult = @mysqli_query($connection, 'SHOW CREATE TABLE `' . $normalizedTable . '`');
    if (!$createTableResult instanceof mysqli_result) {
        fclose($fileHandle);
        @unlink($filePath);
        return [false, 'No fue posible obtener la estructura de la tabla seleccionada.', null];
    }

    $createTableRow = mysqli_fetch_assoc($createTableResult);
    mysqli_free_result($createTableResult);

    if (!isset($createTableRow['Create Table'])) {
        fclose($fileHandle);
        @unlink($filePath);
        return [false, 'La tabla seleccionada no devolvió información de creación.', null];
    }

    fwrite($fileHandle, "DROP TABLE IF EXISTS `{$normalizedTable}`;\n");
    fwrite($fileHandle, $createTableRow['Create Table'] . ';' . "\n\n");

    $dataResult = @mysqli_query($connection, 'SELECT * FROM `' . $normalizedTable . '`');
    if (!$dataResult instanceof mysqli_result) {
        fclose($fileHandle);
        @unlink($filePath);
        return [false, 'No fue posible obtener los datos de la tabla seleccionada.', null];
    }

    if (mysqli_num_rows($dataResult) > 0) {
        $fields = mysqli_fetch_fields($dataResult);
        $columnNames = array_map(static function ($field): string {
            return '`' . $field->name . '`';
        }, $fields);
        $columnList = implode(', ', $columnNames);

        while ($rowData = mysqli_fetch_row($dataResult)) {
            $values = [];
            foreach ($rowData as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                    continue;
                }

                $escaped = mysqli_real_escape_string($connection, (string) $value);
                $escaped = str_replace(["\r", "\n"], ['\\r', '\\n'], $escaped);
                $values[] = "'" . $escaped . "'";
            }

            $insertStatement = 'INSERT INTO `' . $normalizedTable . '` (' . $columnList . ') VALUES (' . implode(', ', $values) . ');';
            fwrite($fileHandle, $insertStatement . "\n");
        }
        fwrite($fileHandle, "\n");
    }

    mysqli_free_result($dataResult);

    fwrite($fileHandle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fileHandle);

    return [true, 'El respaldo de la tabla se generó correctamente.', $fileName];
}

/**
 * Restores the database by executing the SQL statements from the provided file path.
 *
 * @return array{0:bool,1:string}
 */
function dbBackupRestoreFromFile(mysqli $connection, string $filePath): array
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return [false, 'El archivo de respaldo no está disponible para su lectura.'];
    }

    $sql = file_get_contents($filePath);
    if ($sql === false || trim($sql) === '') {
        return [false, 'El archivo de respaldo está vacío o no se pudo leer.'];
    }

    @set_time_limit(0);

    if (!@mysqli_multi_query($connection, $sql)) {
        return [false, 'Ocurrió un error al restaurar la base de datos: ' . mysqli_error($connection)];
    }

    do {
        if ($result = mysqli_store_result($connection)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($connection) && mysqli_next_result($connection));

    return [true, 'La base de datos se restauró correctamente a partir del respaldo seleccionado.'];
}

/**
 * Stores an uploaded backup file inside the backup directory.
 *
 * @param array $uploadedFile Structure from $_FILES.
 * @return array{0:bool,1:string,2:?string,3:?string}
 */
function dbBackupStoreUploaded(array $uploadedFile): array
{
    if (!isset($uploadedFile['tmp_name'], $uploadedFile['name'], $uploadedFile['error'])) {
        return [false, 'No se recibió ningún archivo para restaurar.', null, null];
    }

    if ((int) $uploadedFile['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Ocurrió un error al cargar el archivo de respaldo (código ' . $uploadedFile['error'] . ').', null, null];
    }

    if (!is_uploaded_file($uploadedFile['tmp_name'])) {
        return [false, 'El archivo recibido no es válido para restaurar la base de datos.', null, null];
    }

    $originalName = basename((string) $uploadedFile['name']);
    if (!preg_match('/\.sql$/i', $originalName)) {
        return [false, 'El archivo de respaldo debe tener la extensión .sql.', null, null];
    }

    if (!dbBackupEnsureDirectory()) {
        return [false, 'No fue posible preparar el directorio de respaldos para almacenar el archivo cargado.', null, null];
    }

    $sanitizedOriginal = preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
    $storedName = 'upload_' . date('Ymd_His') . '_' . $sanitizedOriginal;
    $destination = dbBackupDirectory() . DIRECTORY_SEPARATOR . $storedName;

    if (!@move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
        return [false, 'No se pudo almacenar el archivo de respaldo cargado en el servidor.', null, null];
    }

    return [true, 'El archivo de respaldo se cargó correctamente.', $destination, $storedName];
}
