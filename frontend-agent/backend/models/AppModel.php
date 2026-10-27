<?php

namespace Models;

use Config\Database;
use PDO;

class AppModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // --- Métodos de Autenticación y Usuarios ---

    public function findUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($userId)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, is_active FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($id, $name, $email, $passwordHash, $role)
    {
        $stmt = $this->conn->prepare("INSERT INTO users (id, name, email, password_hash, role) VALUES (:id, :name, :email, :password_hash, :role)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    public function updateUser($userId, $name, $email, $role, $isActive)
    {
        $stmt = $this->conn->prepare("UPDATE users SET name = :name, email = :email, role = :role, is_active = :is_active WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function updatePassword($userId, $newPasswordHash)
    {
        $stmt = $this->conn->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':password_hash', $newPasswordHash);
        return $stmt->execute();
    }

    public function getAllUsers($limit = 10, $offset = 0)
    {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, is_active, created_at FROM users LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    public function deleteUser($userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // --- Métodos de Perfiles de Psicólogos ---

    public function createPsychologistProfile($userId, $specialization, $licenseNumber, $bio)
    {
        $stmt = $this->conn->prepare("INSERT INTO psychologist_profiles (user_id, specialization, license_number, bio) VALUES (:user_id, :specialization, :license_number, :bio)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':license_number', $licenseNumber);
        $stmt->bindParam(':bio', $bio);
        return $stmt->execute();
    }

    public function updatePsychologistProfile($userId, $specialization, $licenseNumber, $bio)
    {
        $stmt = $this->conn->prepare("UPDATE psychologist_profiles SET specialization = :specialization, license_number = :license_number, bio = :bio WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':license_number', $licenseNumber);
        $stmt->bindParam(':bio', $bio);
        return $stmt->execute();
    }

    public function getPsychologistProfile($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM psychologist_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllPsychologists()
    {
        $stmt = $this->conn->query("SELECT u.id, u.name, u.email, pp.specialization FROM users u JOIN psychologist_profiles pp ON u.id = pp.user_id WHERE u.role = 'psychologist' AND u.is_active = TRUE");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Métodos de Asignaciones Paciente-Psicólogo ---

    public function assignPsychologistToPatient($patientId, $psychologistId)
    {
        // Evitar duplicados
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM patient_psychologist WHERE patient_id = :patient_id AND psychologist_id = :psychologist_id");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return true; // Ya existe la asignación
        }

        $stmt = $this->conn->prepare("INSERT INTO patient_psychologist (patient_id, psychologist_id) VALUES (:patient_id, :psychologist_id)");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        return $stmt->execute();
    }

    public function removePsychologistFromPatient($patientId, $psychologistId)
    {
        $stmt = $this->conn->prepare("DELETE FROM patient_psychologist WHERE patient_id = :patient_id AND psychologist_id = :psychologist_id");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        return $stmt->execute();
    }

    public function getAssignedPsychologistsForPatient($patientId)
    {
        $stmt = $this->conn->prepare("SELECT u.id, u.name, u.email FROM patient_psychologist pp JOIN users u ON pp.psychologist_id = u.id WHERE pp.patient_id = :patient_id AND u.is_active = TRUE");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignedPatientsForPsychologist($psychologistId)
    {
        $stmt = $this->conn->prepare("SELECT u.id, u.name, u.email FROM patient_psychologist pp JOIN users u ON pp.patient_id = u.id WHERE pp.psychologist_id = :psychologist_id AND u.is_active = TRUE");
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isPatientAssignedToPsychologist($patientId, $psychologistId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM patient_psychologist WHERE patient_id = :patient_id AND psychologist_id = :psychologist_id");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // --- Métodos de Chats ---

    public function createChat($id, $patientId, $type, $psychologistId = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO chats (id, patient_id, psychologist_id, type) VALUES (:id, :patient_id, :psychologist_id, :type)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->bindParam(':type', $type);
        return $stmt->execute();
    }

    public function getChatById($chatId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM chats WHERE id = :id");
        $stmt->bindParam(':id', $chatId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserChats($userId, $role)
    {
        if ($role === 'patient') {
            $stmt = $this->conn->prepare("SELECT * FROM chats WHERE patient_id = :user_id");
        } elseif ($role === 'psychologist') {
            $stmt = $this->conn->prepare("SELECT c.* FROM chats c JOIN patient_psychologist pp ON c.patient_id = pp.patient_id WHERE pp.psychologist_id = :user_id AND c.type = 'psychologist'");
        } else {
            return []; // Admins don't have chats directly
        }
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closeChat($chatId)
    {
        $stmt = $this->conn->prepare("UPDATE chats SET status = 'closed' WHERE id = :id");
        $stmt->bindParam(':id', $chatId);
        return $stmt->execute();
    }

    // --- Métodos de Mensajes de Chat ---

    public function addChatMessage($id, $chatId, $senderId, $content)
    {
        $stmt = $this->conn->prepare("INSERT INTO chat_messages (id, chat_id, sender_id, content) VALUES (:id, :chat_id, :sender_id, :content)");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->bindParam(':sender_id', $senderId);
        $stmt->bindParam(':content', $content);
        return $stmt->execute();
    }

    public function getChatMessages($chatId)
    {
        $stmt = $this->conn->prepare("SELECT cm.*, u.name as sender_name FROM chat_messages cm JOIN users u ON cm.sender_id = u.id WHERE chat_id = :chat_id ORDER BY sent_at ASC");
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Métodos de Citas ---

    public function requestAppointment($id, $patientId, $psychologistId, $requestedDate, $requestedTime)
    {
        $stmt = $this->conn->prepare("INSERT INTO appointments (id, patient_id, psychologist_id, requested_date, requested_time, status) VALUES (:id, :patient_id, :psychologist_id, :requested_date, :requested_time, 'pending')");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->bindParam(':requested_date', $requestedDate);
        $stmt->bindParam(':requested_time', $requestedTime);
        return $stmt->execute();
    }

    public function getPatientAppointments($patientId)
    {
        $stmt = $this->conn->prepare("SELECT a.*, u.name as psychologist_name FROM appointments a JOIN users u ON a.psychologist_id = u.id WHERE a.patient_id = :patient_id ORDER BY requested_date DESC, requested_time DESC");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPsychologistAppointments($psychologistId)
    {
        $stmt = $this->conn->prepare("SELECT a.*, u.name as patient_name FROM appointments a JOIN users u ON a.patient_id = u.id WHERE a.psychologist_id = :psychologist_id ORDER BY requested_date DESC, requested_time DESC");
        $stmt->bindParam(':psychologist_id', $psychologistId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAppointmentStatus($appointmentId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        $stmt->bindParam(':id', $appointmentId);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function hasCompletedTherapy($patientId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = :patient_id AND status = 'completed'");
        $stmt->bindParam(':patient_id', $patientId);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getAppointmentById($appointmentId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM appointments WHERE id = :id");
        $stmt->bindParam(':id', $appointmentId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
