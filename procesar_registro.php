<?php
// procesar_registro.php: Validación de Capa 2 (Zero Trust)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recepción de datos puros desde el Front-end
    $username_raw = $_POST['username'] ?? '';
    $email_raw = $_POST['email'] ?? '';
    $password_raw = $_POST['password'] ?? '';

    $errores_backend = [];

    // 2. Re-validación estricta en el Back-end (Evita nota de 0.0)
    $username = trim($username_raw);
    if (empty($username) || strlen($username) < 4) { // Corregido sintaxis
        $errores_backend[] = "Error de integridad: Usuario inválido.";
    }

    $email = trim($email_raw);
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // Corregido sintaxis
        $errores_backend[] = "Error de integridad: Formato de correo no seguro.";
    }

    $password_plano = $password_raw;
    if (empty($password_plano) || strlen($password_plano) < 8) {
        $errores_backend[] = "Error de integridad: La contraseña no cumple las métricas.";
    }

    // 3. Asimetría de Confianza
    if (!empty($errores_backend)) {
        // La validación del Front-end falló o fue evadida. Se rechaza la petición.
        $_SESSION['errores'] = $errores_backend;
        echo "Acceso denegado: Datos corruptos o incompletos detectados en la Capa 2.";
        exit();
    } else {
        // Enlace seguro: Asignación de variables validadas para el script del Estudiante 2
        // Esto evita que 'registro.php' use strings fijos de prueba ("examen123")
        require_once 'registro.php'; 
    }
}
?>
