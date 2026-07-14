<?php
// login.php: Sistema de Autenticación Bicapa
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure'   => true,   // Forzar HTTPS
        'cookie_httponly' => true,   // Mitigar XSS
        'cookie_samesite' => 'Strict' // Mitigar CSRF
    ]);
}

// Requiere el módulo PDO creado por el Estudiante 2
require_once 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario_ingresado = trim($_POST['username'] ?? '');
    $password_ingresada = trim($_POST['password'] ?? '');

    try {
        // Uso de sentencias preparadas (PDO) - Mitigación absoluta de SQLi
        $sql = "SELECT id, username, password FROM usuarios WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $usuario_ingresado]);

        $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validación criptográfica estricta con el hash de la BD
        if ($usuario_db && password_verify($password_ingresada, $usuario_db['password'])) {

            // Regeneración para evitar la vulnerabilidad de Session Fixation
            session_regenerate_id(true);

            // Inicio de sesión segura
            $_SESSION['user_id'] = $usuario_db['id'];
            $_SESSION['username'] = $usuario_db['username'];
            $_SESSION['logged_in'] = true;

            echo "¡Autenticación exitosa! Sesión iniciada para: " . htmlspecialchars($_SESSION['username']);
            exit();

        } else {
            // Mensaje genérico (Prevención de ataques de enumeración de usuarios)
            echo "Credenciales inválidas.";
        }

    } catch (\PDOException $e) {
        // Auditoría Técnica: Registro de error interno aislándolo del cliente
        error_log("Error de BD en Autenticación: " . $e->getMessage()); 
        echo "Error interno del servidor. Intente más tarde.";
    }
}
?>
