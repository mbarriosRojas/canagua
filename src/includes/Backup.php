<?php

/**
 * Servicio de respaldo y restauración de base de datos para SACRGAPI.
 * Inspirado en sistema_canagua2/modules/backup, adaptado a la clase Database.
 */
class BackupService
{
    /**
     * Obtener ruta absoluta al directorio de backups y crearlo si no existe.
     */
    public static function getBackupDir(): string
    {
        $rootPath = dirname(__DIR__, 2); // .../SACRGAPI
        $backupDir = $rootPath . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        return $backupDir;
    }

    /**
     * Listar archivos de respaldo disponibles (.sql), ordenados por fecha descendente.
     *
     * @return array<int, array{name:string,size:int,date:int}>
     */
    public static function listBackups(): array
    {
        $backupDir = self::getBackupDir();
        $backups = [];

        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $filepath = $backupDir . '/' . $file;
                    $backups[] = [
                        'name' => $file,
                        'size' => @filesize($filepath) ?: 0,
                        'date' => @filemtime($filepath) ?: 0,
                    ];
                }
            }
        }

        usort($backups, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return $backups;
    }

    /**
     * Crear un nuevo respaldo completo de la base de datos.
     *
     * @return array{success:bool,message:string,filename?:string}
     */
    public static function createBackup(): array
    {
        try {
            $backupDir = self::getBackupDir();
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            /** @var Database $db */
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            @ini_set('memory_limit', '256M');

            // Obtener todas las tablas
            $tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $output = "-- Backup generado el " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Incluye estructura y todos los datos\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Estructura
                $output .= "-- Estructura de la tabla `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                $output .= $createRow[1] . ";\n\n";

                // Datos
                $output .= "-- Datos de la tabla `$table`\n";
                $dataStmt = $pdo->query("SELECT * FROM `$table`");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    $chunkSize = 200;
                    $chunks = array_chunk($rows, $chunkSize);
                    foreach ($chunks as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $vals = [];
                            foreach ($row as $val) {
                                $vals[] = $val === null ? 'NULL' : $pdo->quote($val);
                            }
                            $values[] = '(' . implode(',', $vals) . ')';
                        }
                        $output .= "INSERT INTO `$table` VALUES " . implode(",\n", $values) . ";\n";
                    }
                    $output .= "\n";
                }
            }

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($filepath, $output);

            return [
                'success' => true,
                'message' => "Respaldo creado exitosamente: $filename",
                'filename' => $filename,
            ];
        } catch (Exception $e) {
            error_log("Error creando respaldo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear el respaldo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Restaurar desde un archivo de respaldo existente en el directorio de backups.
     *
     * @param string $backupName Nombre de archivo (.sql)
     * @return array{success:bool,message:string}
     */
    public static function restoreFromBackupName(string $backupName): array
    {
        $backupDir = self::getBackupDir();
        $filename = basename($backupName);
        $filepath = $backupDir . '/' . $filename;

        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'El archivo de respaldo no existe.'];
        }

        $sql = @file_get_contents($filepath);
        if ($sql === false || $sql === '') {
            return ['success' => false, 'message' => 'El archivo de respaldo está vacío o no se pudo leer.'];
        }

        return self::restoreSqlContent($sql);
    }

    /**
     * Restaurar desde un archivo subido por el usuario.
     *
     * @param array $file Entrada de $_FILES['backup_file']
     * @return array{success:bool,message:string}
     */
    public static function restoreFromUploadedFile(array $file): array
    {
        if (!isset($file['error']) || !isset($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No se proporcionó archivo de respaldo.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'El archivo excede el tamaño máximo permitido.'];
            case UPLOAD_ERR_PARTIAL:
                return ['success' => false, 'message' => 'El archivo se subió solo parcialmente.'];
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'No se seleccionó ningún archivo.'];
            default:
                return ['success' => false, 'message' => 'Error al subir el archivo (código: ' . $file['error'] . ').'];
        }

        if (!is_uploaded_file($file['tmp_name']) || !is_readable($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No se pudo leer el archivo subido.'];
        }

        $sql = @file_get_contents($file['tmp_name']);
        if ($sql === false || $sql === '') {
            return ['success' => false, 'message' => 'El archivo de respaldo está vacío o no se pudo leer.'];
        }

        return self::restoreSqlContent($sql);
    }

    /**
     * Ejecutar contenido SQL de respaldo sobre la base de datos actual.
     *
     * @return array{success:bool,message:string}
     */
    private static function restoreSqlContent(string $sqlContent): array
    {
        try {
            /** @var Database $db */
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            @ini_set('memory_limit', '256M');
            @ini_set('max_execution_time', '300');

            $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

            // Eliminar todas las tablas actuales
            $tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`");
            }

            // Eliminar todas las vistas actuales (para evitar \"table or view already exists\")
            try {
                $views = [];
                $stmtViews = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
                while ($row = $stmtViews->fetch(PDO::FETCH_NUM)) {
                    $views[] = $row[0];
                }
                foreach ($views as $view) {
                    $pdo->exec("DROP VIEW IF EXISTS `" . str_replace('`', '``', $view) . "`");
                }
            } catch (Exception $e) {
                // Si falla la obtención/eliminación de vistas, se registra pero no se detiene el proceso
                error_log("Error eliminando vistas antes de restaurar: " . $e->getMessage());
            }

            $statements = self::splitSqlStatements($sqlContent);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                // Quitar comentarios iniciales de línea
                $stmtClean = preg_replace('/^(\s|--[^\r\n]*[\r\n])*/', '', $statement);
                $stmtClean = trim($stmtClean);
                if ($stmtClean === '') {
                    continue;
                }

                // Ignorar CREATE DATABASE / SCHEMA / USE
                if (preg_match('/^CREATE\\s+DATABASE/i', $stmtClean)
                    || preg_match('/^CREATE\\s+SCHEMA/i', $stmtClean)
                    || preg_match('/^USE\\s+/i', $stmtClean)) {
                    continue;
                }

                try {
                    $pdo->exec($stmtClean);
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    // Ignorar errores de tablas/vistas desconocidas y vistas no insertables / join views
                    if (
                        strpos($msg, 'Unknown table') === false &&
                        strpos($msg, 'not insertable-into') === false &&
                        strpos($msg, 'insert into join view') === false
                    ) {
                        throw $e;
                    }
                }
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

            return ['success' => true, 'message' => 'Base de datos restaurada exitosamente.'];
        } catch (Exception $e) {
            error_log("Error restaurando respaldo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al restaurar la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Dividir el contenido SQL en sentencias, respetando comillas y comentarios.
     *
     * @return string[]
     */
    private static function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $i = 0;
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;

        while ($i < $len) {
            $c = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

            if ($inBlockComment) {
                $current .= $c;
                if ($c === '*' && $next === '/') {
                    $current .= $next;
                    $i += 2;
                    $inBlockComment = false;
                } else {
                    $i++;
                }
                continue;
            }

            if ($inLineComment) {
                $current .= $c;
                if ($c === "\n" || $c === "\r") {
                    $inLineComment = false;
                }
                $i++;
                continue;
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($c === '/' && $next === '*') {
                    $current .= $c . $next;
                    $i += 2;
                    $inBlockComment = true;
                    continue;
                }
                if (($c === '-' && $next === '-') || ($c === '#')) {
                    $inLineComment = true;
                    $current .= $c;
                    if ($c === '-') {
                        $current .= $next;
                        $i++;
                    }
                    $i++;
                    continue;
                }
                if ($c === ';') {
                    $stmt = trim($current);
                    if ($stmt !== '') {
                        $statements[] = $stmt;
                    }
                    $current = '';
                    $i++;
                    continue;
                }
            }

            if ($c === "'" && !$inDoubleQuote) {
                if ($inSingleQuote && $next === "'") {
                    $current .= $c . $next;
                    $i += 2;
                    continue;
                }
                $inSingleQuote = !$inSingleQuote;
                $current .= $c;
                $i++;
                continue;
            }

            if ($c === '"' && !$inSingleQuote) {
                if ($inDoubleQuote && $next === '"') {
                    $current .= $c . $next;
                    $i += 2;
                    continue;
                }
                $inDoubleQuote = !$inDoubleQuote;
                $current .= $c;
                $i++;
                continue;
            }

            if ($inSingleQuote && $c === '\\\\' && $i + 1 < $len) {
                $current .= $c . $next;
                $i += 2;
                continue;
            }

            $current .= $c;
            $i++;
        }

        $stmt = trim($current);
        if ($stmt !== '') {
            $statements[] = $stmt;
        }

        return $statements;
    }

    /**
     * Obtener ruta absoluta de un archivo de respaldo por nombre (sanitizado).
     */
    public static function getBackupFilePath(string $filename): string
    {
        $backupDir = self::getBackupDir();
        // Asegurar nombre limpio (decodificar URL y quitar rutas)
        $name = basename(urldecode($filename));
        return $backupDir . '/' . $name;
    }

    /**
     * Eliminar un archivo de respaldo.
     *
     * @return array{success:bool,message:string}
     */
    public static function deleteBackup(string $filename): array
    {
        $filepath = self::getBackupFilePath($filename);

        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'Archivo de respaldo no encontrado.'];
        }
        if (pathinfo($filepath, PATHINFO_EXTENSION) !== 'sql') {
            return ['success' => false, 'message' => 'Archivo de respaldo inválido.'];
        }

        if (!@unlink($filepath)) {
            return ['success' => false, 'message' => 'No se pudo eliminar el archivo de respaldo.'];
        }

        return ['success' => true, 'message' => 'Respaldo eliminado exitosamente.'];
    }
}

