<?php

namespace Controllers;

use Config\Environment;
use Models\AppModel;
use Views\ApiView;

class ApiController
{
    private $model;
    private $view;
    private $env;

    public function __construct()
    {
        $this->model = new AppModel();
        $this->view = new ApiView();
        $this->env = Environment::getInstance();

        // Configurar CORS
        $this->configureCORS();
        
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_name($this->env->get('SESSION_NAME', 'MINDI_SESSION'));
            session_set_cookie_params($this->env->get('SESSION_LIFETIME', 3600), '/', '', false, true);
            session_start();
        }
    }

    private function configureCORS()
    {
        $allowedOrigin = $this->env->getRequired('CORS_ALLOWED_ORIGIN');
        header("Access-Control-Allow-Origin: $allowedOrigin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }
    }

    private function generateUuid(): string
    {
        // Generar un UUID v4 (RFC 4122)
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    private function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    private function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }

    private function isAdmin()
    {
        return $this->isAuthenticated() && $this->getUserRole() === 'admin';
    }

    private function isPatient()
    {
        return $this->isAuthenticated() && $this->getUserRole() === 'patient';
    }

    private function isPsychologist()
    {
        return $this->isAuthenticated() && $this->getUserRole() === 'psychologist';
    }

    private function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->view->sendError('No autenticado', 401);
        }
    }

    private function requireRole($role)
    {
        $this->requireAuth();
        if ($this->getUserRole() !== $role) {
            $this->view->sendError('Acceso denegado', 403);
        }
    }

    private function requireAdmin()
    {
        $this->requireRole('admin');
    }

    private function requirePatient()
    {
        $this->requireRole('patient');
    }

    private function requirePsychologist()
    {
        $this->requireRole('psychologist');
    }

    private function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->view->sendError('Token CSRF inválido', 403);
        }
    }

    // --- Endpoints de Autenticación ---

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->view->sendError('Correo electrónico y contraseña son requeridos', 400);
        }

        $user = $this->model->findUserByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash']) || !$user['is_active']) {
            $this->view->sendError('Credenciales inválidas o usuario inactivo', 401);
        }

        // Regenerar ID de sesión para prevenir ataques de fijación de sesión
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $this->view->sendSuccess('Login exitoso', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'csrf_token' => $this->generateCsrfToken() // Enviar CSRF token al cliente
        ]);
    }

    public function logout()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        // No hay necesidad de validar CSRF en logout si no se modifica estado sensible
        // $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/'); // Eliminar cookie de sesión

        $this->view->sendSuccess('Sesión cerrada exitosamente');
    }

    public function getCurrentUser()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $userId = $this->getUserId();
        $user = $this->model->getUserById($userId);

        if (!$user) {
            $this->view->sendError('Usuario no encontrado', 404);
        }

        $this->view->sendSuccess('Usuario actual', [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_active' => $user['is_active'],
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    // --- Endpoints de Administración de Usuarios (Admin) ---

    public function getUsers()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $users = $this->model->getAllUsers($limit, $offset);
        $total = $this->model->countUsers();

        $this->view->sendSuccess('Lista de usuarios', [
            'users' => $users,
            'total' => $total
        ]);
    }

    public function createUser()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $id = $this->generateUuid();
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $this->view->sendError('Todos los campos son requeridos', 400);
        }

        if (!in_array($role, ['admin', 'patient', 'psychologist'])) {
            $this->view->sendError('Rol inválido', 400);
        }

        if ($this->model->findUserByEmail($email)) {
            $this->view->sendError('El correo electrónico ya está registrado', 409);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->model->conn->beginTransaction();
        try {
            if (!$this->model->createUser($id, $name, $email, $passwordHash, $role)) {
                throw new \Exception('Error al crear usuario');
            }

            if ($role === 'psychologist') {
                if (!$this->model->createPsychologistProfile($id, null, null, null)) {
                    throw new \Exception('Error al crear perfil de psicólogo');
                }
            }
            $this->model->conn->commit();
            $this->view->sendSuccess('Usuario creado exitosamente', ['id' => $id]);
        } catch (\Exception $e) {
            $this->model->conn->rollBack();
            error_log($e->getMessage());
            $this->view->sendError('Error interno al crear usuario', 500);
        }
    }

    public function updateUser($userId)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $role = $input['role'] ?? '';
        $isActive = $input['is_active'] ?? true;
        $password = $input['password'] ?? null;

        if (empty($name) || empty($email) || empty($role)) {
            $this->view->sendError('Nombre, correo y rol son requeridos', 400);
        }

        if (!in_array($role, ['admin', 'patient', 'psychologist'])) {
            $this->view->sendError('Rol inválido', 400);
        }

        $existingUser = $this->model->getUserById($userId);
        if (!$existingUser) {
            $this->view->sendError('Usuario no encontrado', 404);
        }

        $userWithSameEmail = $this->model->findUserByEmail($email);
        if ($userWithSameEmail && $userWithSameEmail['id'] !== $userId) {
            $this->view->sendError('El correo electrónico ya está registrado por otro usuario', 409);
        }

        $this->model->conn->beginTransaction();
        try {
            if (!$this->model->updateUser($userId, $name, $email, $role, $isActive)) {
                throw new \Exception('Error al actualizar usuario');
            }

            if ($password) {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
                if (!$this->model->updatePassword($userId, $newPasswordHash)) {
                    throw new \Exception('Error al actualizar contraseña');
                }
            }

            // Gestionar perfil de psicólogo si el rol cambia
            if ($existingUser['role'] !== 'psychologist' && $role === 'psychologist') {
                $this->model->createPsychologistProfile($userId, null, null, null);
            } elseif ($existingUser['role'] === 'psychologist' && $role !== 'psychologist') {
                // Opcional: eliminar perfil de psicólogo si ya no lo es (la restricción FOREIGN KEY ON DELETE CASCADE en psychologist_profiles manejará esto si se elimina el usuario, pero no si cambia de rol directamente)
                // Considerar si es necesario borrar el perfil o simplemente dejarlo inactivo/sin usar
            }

            $this->model->conn->commit();
            $this->view->sendSuccess('Usuario actualizado exitosamente', ['id' => $userId]);
        } catch (\Exception $e) {
            $this->model->conn->rollBack();
            error_log($e->getMessage());
            $this->view->sendError('Error interno al actualizar usuario', 500);
        }
    }

    public function deleteUser($userId)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        if ($userId === $this->getUserId()) {
            $this->view->sendError('No puedes eliminar tu propio usuario', 403);
        }

        if (!$this->model->getUserById($userId)) {
            $this->view->sendError('Usuario no encontrado', 404);
        }

        if ($this->model->deleteUser($userId)) {
            $this->view->sendSuccess('Usuario eliminado exitosamente', ['id' => $userId]);
        }
        $this->view->sendError('Error al eliminar usuario', 500);
    }

    // --- Endpoints de Asignaciones (Admin) ---

    public function getPatientsByPsychologist($psychologistId)
    {
        $this->requireAdmin(); // O psicólogo para sus propios pacientes

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $patients = $this->model->getAssignedPatientsForPsychologist($psychologistId);
        $this->view->sendSuccess('Pacientes asignados', ['patients' => $patients]);
    }

    public function assignPatientToPsychologist()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $patientId = $input['patient_id'] ?? '';
        $psychologistId = $input['psychologist_id'] ?? '';

        if (empty($patientId) || empty($psychologistId)) {
            $this->view->sendError('IDs de paciente y psicólogo son requeridos', 400);
        }

        // Validar que ambos IDs corresponden a usuarios existentes y con roles correctos
        $patient = $this->model->getUserById($patientId);
        $psychologist = $this->model->getUserById($psychologistId);

        if (!$patient || $patient['role'] !== 'patient') {
            $this->view->sendError('Paciente no válido', 400);
        }
        if (!$psychologist || $psychologist['role'] !== 'psychologist') {
            $this->view->sendError('Psicólogo no válido', 400);
        }

        if ($this->model->assignPsychologistToPatient($patientId, $psychologistId)) {
            $this->view->sendSuccess('Paciente asignado exitosamente');
        }
        $this->view->sendError('Error al asignar paciente', 500);
    }

    public function removePatientFromPsychologist()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $patientId = $input['patient_id'] ?? '';
        $psychologistId = $input['psychologist_id'] ?? '';

        if (empty($patientId) || empty($psychologistId)) {
            $this->view->sendError('IDs de paciente y psicólogo son requeridos', 400);
        }

        if ($this->model->removePsychologistFromPatient($patientId, $psychologistId)) {
            $this->view->sendSuccess('Asignación removida exitosamente');
        }
        $this->view->sendError('Error al remover asignación', 500);
    }

    // --- Endpoints de Chats ---

    public function getChats()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $userId = $this->getUserId();
        $role = $this->getUserRole();

        $chats = $this->model->getUserChats($userId, $role);
        $this->view->sendSuccess('Chats del usuario', ['chats' => $chats]);
    }

    public function getChatMessages($chatId)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $chat = $this->model->getChatById($chatId);
        if (!$chat) {
            $this->view->sendError('Chat no encontrado', 404);
        }

        // Validar propiedad/autorización del chat
        $userId = $this->getUserId();
        $userRole = $this->getUserRole();

        $isAuthorized = false;
        if ($userRole === 'patient' && $chat['patient_id'] === $userId) {
            $isAuthorized = true;
        } elseif ($userRole === 'psychologist' && $chat['psychologist_id'] === $userId) {
            // Para chats de psicólogos, asegurarse que el psicólogo está asignado al paciente
            if ($chat['type'] === 'psychologist' && $this->model->isPatientAssignedToPsychologist($chat['patient_id'], $userId)) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            $this->view->sendError('Acceso denegado al chat', 403);
        }

        $messages = $this->model->getChatMessages($chatId);
        $this->view->sendSuccess('Mensajes del chat', ['messages' => $messages]);
    }

    public function sendChatMessage($chatId)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $senderId = $this->getUserId();
        $content = $input['content'] ?? '';

        if (empty($content)) {
            $this->view->sendError('El contenido del mensaje no puede estar vacío', 400);
        }

        $chat = $this->model->getChatById($chatId);
        if (!$chat) {
            $this->view->sendError('Chat no encontrado', 404);
        }

        // Validar propiedad/autorización y que el chat no esté cerrado
        $userRole = $this->getUserRole();
        $isAuthorized = false;

        if ($userRole === 'patient' && $chat['patient_id'] === $senderId && ($chat['type'] === 'ai_client' || $chat['type'] === 'ai_patient')) {
            // Paciente puede enviar a chats de IA
            $isAuthorized = true;
        } elseif ($userRole === 'psychologist' && $chat['psychologist_id'] === $senderId && $chat['type'] === 'psychologist') {
            // Psicólogo puede enviar a chats con pacientes asignados
            $isAuthorized = $this->model->isPatientAssignedToPsychologist($chat['patient_id'], $senderId);
        } elseif ($userRole === 'patient' && $chat['patient_id'] === $senderId && $chat['type'] === 'psychologist' && $chat['psychologist_id'] !== null) {
            // Paciente puede enviar a chats con psicólogos asignados
             $isAuthorized = $this->model->isPatientAssignedToPsychologist($senderId, $chat['psychologist_id']);
        }


        if (!$isAuthorized || $chat['status'] === 'closed') {
            $this->view->sendError('Acceso denegado o chat cerrado', 403);
        }

        $messageId = $this->generateUuid();
        if ($this->model->addChatMessage($messageId, $chatId, $senderId, $content)) {
            // Si es un chat de IA, invocar al backend Java
            if ($chat['type'] === 'ai_client' || $chat['type'] === 'ai_patient') {
                $this->callJavaChatApi($chat, $senderId, $content);
            }
            $this->view->sendSuccess('Mensaje enviado', ['id' => $messageId]);
        }
        $this->view->sendError('Error al enviar mensaje', 500);
    }

    private function callJavaChatApi($chat, $senderId, $userMessage)
    {
        $javaApiBaseUrl = $this->env->getRequired('JAVA_API_BASE_URL');
        $endpoint = '';
        $chatType = $chat['type'];

        $userName = $_SESSION['user_name'] ?? 'Usuario Desconocido';
        $options = sprintf('[NAME=%s]', $userName);

        $requestBody = [
            'userId' => $senderId,
            'message' => $userMessage,
            'options' => $options
        ];

        if ($chatType === 'ai_client') {
            $endpoint = $javaApiBaseUrl . '/public/chat';
        } elseif ($chatType === 'ai_patient') {
            $endpoint = $javaApiBaseUrl . '/public/chat/followup';
            // Para Patient, el mensaje inicial debe ser gestionado por el frontend con opciones fijas
            // Aquí solo se envía el seguimiento
        } else {
            return; // No llamar a Java para chats de psicólogo
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Error al llamar a la API de Java (cURL): " . $curlError);
            // Podríamos añadir un mensaje al chat indicando que la IA no respondió
            $this->model->addChatMessage($this->generateUuid(), $chat['id'], 'system', 'La IA no pudo responder en este momento.');
            return;
        }

        if ($httpCode !== 200) {
            error_log("Error de la API de Java. Código: {$httpCode}, Respuesta: {$response}");
            $this->model->addChatMessage($this->generateUuid(), $chat['id'], 'system', 'La IA respondió con un error. Inténtelo de nuevo.');
            return;
        }

        $javaResponse = json_decode($response, true);
        $answer = $javaResponse['answer'] ?? 'Lo siento, no pude procesar tu solicitud.';
        $options = $javaResponse['options'] ?? '';

        // Procesar [CLOSE]
        if (strpos($answer, '[CLOSE]') !== false) {
            $answer = str_replace('[CLOSE]', '', $answer);
            $this->model->closeChat($chat['id']);
        }

        // Guardar la respuesta de la IA
        $this->model->addChatMessage($this->generateUuid(), $chat['id'], 'ai', $answer);

        // TODO: Manejar opciones subsiguientes en el frontend, como la elección de psicólogos para citas
    }

    public function createAIChat($type)
    {
        $this->requirePatient();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $patientId = $this->getUserId();

        if ($type === 'ai_patient') {
            if (!$this->model->hasCompletedTherapy($patientId)) {
                $this->view->sendError('Debes tener al menos una cita completada para iniciar este chat.', 403);
            }
        }

        $chatId = $this->generateUuid();
        if ($this->model->createChat($chatId, $patientId, $type)) {
            $this->view->sendSuccess('Chat de IA creado', ['chat_id' => $chatId]);
        }
        $this->view->sendError('Error al crear chat de IA', 500);
    }

    // --- Endpoints de Citas ---

    public function getPsychologistsForAppointment()
    {
        $this->requirePatient();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $psychologists = $this->model->getAllPsychologists();
        $this->view->sendSuccess('Psicólogos disponibles', ['psychologists' => $psychologists]);
    }

    public function requestAppointment()
    {
        $this->requirePatient();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $patientId = $this->getUserId();
        $psychologistId = $input['psychologist_id'] ?? '';
        $requestedDate = $input['requested_date'] ?? '';
        $requestedTime = $input['requested_time'] ?? '';

        if (empty($psychologistId) || empty($requestedDate) || empty($requestedTime)) {
            $this->view->sendError('Psicólogo, fecha y hora son requeridos', 400);
        }

        // Validar que el psicólogo existe y es activo
        $psychologist = $this->model->getUserById($psychologistId);
        if (!$psychologist || $psychologist['role'] !== 'psychologist' || !$psychologist['is_active']) {
            $this->view->sendError('Psicólogo no válido o inactivo', 400);
        }

        // Validar que la fecha y hora sean futuras
        $now = new \DateTime();
        $requestedDateTime = \DateTime::createFromFormat('Y-m-d H:i', "{$requestedDate} {$requestedTime}");

        if (!$requestedDateTime || $requestedDateTime <= $now) {
            $this->view->sendError('La fecha y hora de la cita deben ser futuras', 400);
        }

        $appointmentId = $this->generateUuid();
        if ($this->model->requestAppointment($appointmentId, $patientId, $psychologistId, $requestedDate, $requestedTime)) {
            $this->view->sendSuccess('Cita solicitada exitosamente', ['appointment_id' => $appointmentId]);
        }
        $this->view->sendError('Error al solicitar cita', 500);
    }

    public function getMyAppointments()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->view->sendError('Método no permitido', 405);
        }

        $userId = $this->getUserId();
        $role = $this->getUserRole();

        if ($role === 'patient') {
            $appointments = $this->model->getPatientAppointments($userId);
        } elseif ($role === 'psychologist') {
            $appointments = $this->model->getPsychologistAppointments($userId);
        } else {
            $this->view->sendError('Acceso denegado', 403);
            return;
        }

        $this->view->sendSuccess('Mis citas', ['appointments' => $appointments]);
    }

    public function updateAppointmentStatus($appointmentId)
    {
        $this->requirePsychologist();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->view->sendError('Método no permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $this->validateCsrfToken($input['csrf_token'] ?? '');

        $status = $input['status'] ?? '';

        if (empty($status) || !in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            $this->view->sendError('Estado de cita inválido', 400);
        }

        // Validar que el psicólogo tiene derecho a modificar esta cita
        $appointment = $this->model->getAppointmentById($appointmentId);
        if (!$appointment || $appointment['psychologist_id'] !== $this->getUserId()) {
            $this->view->sendError('Acceso denegado a la cita', 403);
        }

        if ($this->model->updateAppointmentStatus($appointmentId, $status)) {
            $this->view->sendSuccess('Estado de cita actualizado', ['appointment_id' => $appointmentId]);
        }
        $this->view->sendError('Error al actualizar estado de cita', 500);
    }
}
