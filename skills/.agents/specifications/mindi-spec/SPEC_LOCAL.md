# SPEC — Mindora Care (Generación desde cero)

## 1. Descripción

- **Nombre:** Mindora Care.
- **Objetivo:** Crear una aplicación web con React, Tailwind CSS, PHP nativo y MySQL, integrada con un backend Java existente.
- **Problema:** Centralizar la autenticación, los chats con Mindi, el seguimiento de pacientes, las conversaciones con psicólogos, las solicitudes de citas y la administración de usuarios.
- **Alcance:** Página de inicio, autenticación, chats, flujos Client y Patient, conversaciones entre pacientes y psicólogos, citas, administración de usuarios y asignaciones.
- **Restricciones:**
  - Generar el proyecto completo desde cero, con todos los archivos necesarios.
  - Frontend: React, Vite y Tailwind CSS.
  - Backend: PHP nativo, sin frameworks ni librerías PHP externas.
  - Base de datos: MySQL 8, con SQL completo e importable.
  - No generar ni modificar el backend Java.
  - No inventar disponibilidad de citas ni comportamientos no definidos.
  - Utilizar nombres de archivos y carpetas en inglés.

## 2. Archivos a analizar

No hay archivos locales que analizar. Los contratos de integración Java y los requisitos están definidos en esta SPEC.

## 3. Estructura de carpetas y archivos

```text
mindora-care/
├── README.md
├── db/
│   └── mindora_care.sql
├── frontend/
│   ├── .env.example
│   ├── .gitignore
│   ├── package.json
│   ├── index.html
│   ├── vite.config.js
│   └── src/
│       ├── main.jsx
│       ├── controllers/
│       │   └── AppController.js
│       ├── models/
│       │   └── api.js
│       ├── views/
│       │   ├── AppView.jsx
│       │   └── components/
│       │       └── ChatPanel.jsx
│       └── styles.css
└── backend/
    ├── .env.example
    ├── .gitignore
    ├── public/
    │   └── index.php
    ├── config/
    │   ├── Environment.php
    │   └── Database.php
    ├── controllers/
    │   └── ApiController.php
    ├── models/
    │   └── AppModel.php
    └── views/
        └── ApiView.php
```

## 4. README.md

- **Acción:** CREAR.
- **Responsabilidad:** Documentar instalación, configuración y ejecución.
- **Implementación:** Explicar requisitos, importación SQL, configuración `.env`, ejecución de React y PHP, creación segura del primer administrador, rutas de la API y limitaciones.
- **Validación:** Las instrucciones deben coincidir con los archivos generados.

## 5. db/mindora_care.sql

- **Acción:** CREAR.
- **Responsabilidad:** Definir la base de datos completa.
- **Implementación:**
  - Crear la base `mindora_care` con `utf8mb4`.
  - Crear las tablas `users`, `psychologist_profiles`, `patient_psychologist`, `chats`, `chat_messages` y `appointments`.
  - `users`: UUID, nombre, correo único, hash de contraseña, rol (`admin`, `patient`, `psychologist`), estado activo y fechas.
  - `psychologist_profiles`: información profesional vinculada al usuario psicólogo.
  - `patient_psychologist`: asignaciones entre pacientes y psicólogos.
  - `chats`: paciente propietario, tipo (`ai_client`, `ai_patient`, `psychologist`), psicólogo cuando corresponda, estado (`open`, `closed`) y fechas.
  - `chat_messages`: chat, remitente, contenido y fecha.
  - `appointments`: paciente, psicólogo, fecha y hora solicitadas, estado (`pending`, `confirmed`, `completed`, `cancelled`) y fechas.
  - Definir claves foráneas, índices y restricciones de integridad.
  - Utilizar UUID compatibles con el backend Java.
  - No incluir contraseñas reales ni instrucciones destructivas innecesarias.
- **Validación:** SQL completo e importable en MySQL 8.

## 6. Frontend: configuración y entrada

### 6.1. frontend/package.json

Crear la configuración de React, Vite y Tailwind CSS con versiones compatibles y scripts `dev` y `build`.

### 6.2. frontend/vite.config.js

Configurar Vite y los complementos necesarios para React y Tailwind CSS.

### 6.3. frontend/index.html

Definir el idioma español, título Mindora Care y elemento raíz de React.

### 6.4. frontend/src/main.jsx

Montar la vista principal y cargar los estilos globales.

### 6.5. frontend/src/styles.css

Implementar estilos responsive, accesibles y coherentes con Mindora Care utilizando Tailwind CSS.

## 7. Frontend: lógica y vistas

### 7.1. frontend/src/models/api.js

Centralizar las solicitudes HTTP hacia PHP. Gestionar JSON, cookies de sesión y errores. No acceder directamente a MySQL ni llamar a Java desde el navegador.

### 7.2. frontend/src/controllers/AppController.js

Gestionar estado, navegación, formularios, sesión, usuarios, chats y citas. La autorización definitiva debe realizarse en PHP.

### 7.3. frontend/src/views/AppView.jsx

Implementar:

- Página de inicio con presentación de Mindora Care, descripción de Mindi y botones «Hablar con Mindi» e «Iniciar sesión».
- Login con correo y contraseña.
- Listado de chats según el rol.
- Administración de usuarios: crear, consultar, editar y desactivar.
- Asignación y retirada de relaciones paciente-psicólogo.

Los psicólogos no deben tener acceso a chats de IA. Las vistas privadas requieren sesión autenticada.

### 7.4. frontend/src/views/components/ChatPanel.jsx

Gestionar los chats Client, Patient y las conversaciones con psicólogos.

**Client:**
- Utilizar el endpoint PHP que invoca `POST /public/chat`.
- Al recibir `[CLOSE]` en `answer`, guardar la respuesta, eliminar la etiqueta de la presentación, cerrar el chat e impedir nuevos envíos.
- Mostrar hasta tres psicólogos activos para solicitar una cita.
- Permitir seleccionar una fecha y hora futura solicitada por el paciente.
- Indicar que la solicitud no confirma la cita ni garantiza disponibilidad.

**Patient:**
- Habilitar el flujo solo si el paciente tiene al menos una cita `completed`.
- Mostrar el mensaje inicial «¿Cómo te sientes después de tu última terapia?».
- Mostrar las opciones `FEEL_WORSE`, `WANT_TO_TALK`, `FEEL_BETTER`, `FEEL_SAME` y `NOT_NOW`, con etiquetas en español.
- Utilizar el endpoint PHP que invoca `POST /public/chat/followup`.
- `NOT_NOW` finaliza tras procesar la respuesta correspondiente, sin opciones posteriores.
- `FEEL_WORSE` ofrece `FEEL_ANXIOUS`, `FEEL_CONFUSED`, `FEEL_SAD` y `PREFER_TO_WRITE`.
- Opciones finales:
  - `FEEL_BETTER` y `FEEL_SAME`: «Registrar cómo me siento», «Ver recursos recomendados», «Escribirle a mi psicólogo», «Gracias por ahora».
  - `FEEL_ANXIOUS`, `FEEL_CONFUSED`, `FEEL_SAD` y `PREFER_TO_WRITE`: «Buscar una nueva cita», «Hablar con mi psicólogo», «Ahora no».
  - `WANT_TO_TALK`: «Guardar mis ideas para la próxima sesión», «Escribirle a mi psicólogo», «Ver disponibilidad para una nueva sesión», «Gracias, por ahora».
- Cuando Java devuelva opciones, utilizarlas como fuente principal y traducir únicamente las etiquetas conocidas, preservando los valores originales.
- No interpretar `options: []` como cierre, salvo la regla específica de `NOT_NOW`.
- No inventar acciones para las opciones finales cuya funcionalidad no esté definida.
- No enviar mensajes a conversaciones cerradas.

**Nombre en las solicitudes Java:**

PHP debe obtener el UUID y el nombre de la sesión autenticada e incluir `[NAME=Nombre Apellido]` dentro de `options`, preservando los códigos funcionales existentes. No confiar en datos de identidad enviados por el navegador. La interpretación de esta etiqueta por Java no está verificada.

**Conversaciones con psicólogos:**

Guardar y recuperar los mensajes mediante PHP y MySQL, sin invocar IA. Verificar la asignación y autorización en el servidor. Retirar una asignación no debe borrar el historial.

## 8. Backend PHP

### 8.1. backend/config/Environment.php

Leer las variables de `.env` utilizando PHP nativo, sin dependencias externas. Configurar MySQL, URL base Java, origen CORS y parámetros de sesión.

### 8.2. backend/config/Database.php

Crear la conexión PDO con MySQL, excepciones y consultas preparadas.

### 8.3. backend/models/AppModel.php

Implementar las consultas y operaciones de persistencia para usuarios, perfiles, asignaciones, chats, mensajes y citas. Validar relaciones y propiedad de los recursos. Mantener el historial y registrar las nuevas citas como `pending`.

### 8.4. backend/views/ApiView.php

Generar respuestas JSON con códigos HTTP apropiados, sin exponer trazas ni información sensible.

### 8.5. backend/controllers/ApiController.php

Implementar la API PHP para autenticación, sesión, usuarios, asignaciones, chats, mensajes y citas.

Requisitos:

- Usar `password_hash()`, `password_verify()` y `session_regenerate_id(true)`.
- Validar roles y permisos en el servidor.
- Proteger operaciones de modificación con CSRF.
- Configurar cookies de sesión y CORS de forma segura.
- Obtener identidad desde la sesión, nunca desde un UUID arbitrario del navegador.
- Invocar únicamente los endpoints Java especificados para Client y Patient.
- Guardar las respuestas, gestionar `[CLOSE]` y bloquear nuevos mensajes en chats cerrados.
- Verificar elegibilidad Patient, propiedad de chats y asignaciones.
- Gestionar los chats con psicólogos mediante MySQL, sin IA.
- No confirmar citas automáticamente ni simular disponibilidad.
- Definir rutas PHP coherentes para todas las operaciones y documentarlas en README.

**Contrato Java de solicitud (`ChatRequest`):**

```json
{
  "message": "string",
  "userId": "UUID",
  "action": "string",
  "options": ["string"]
}
```

**Contrato Java de respuesta (`ChatResponse`):**

```json
{
  "answer": "string",
  "options": ["string"]
}
```

No modificar Java ni asumir comportamientos internos que no estén confirmados.

## 9. Configuración y exclusiones

### 9.1. frontend/.env.example

Definir la URL pública de la API PHP, sin secretos.

### 9.2. frontend/.gitignore

Excluir `node_modules`, `dist`, `.env` locales y logs.

### 9.3. backend/.env.example

Incluir valores de ejemplo para MySQL, URL Java `http://localhost:8080`, origen CORS y configuración de sesión. No incluir credenciales reales.

### 9.4. backend/.gitignore

Excluir `.env`, logs y archivos temporales, conservando `.env.example`.

## 10. Seguridad y criterios de aceptación

- Validar autenticación, autorización, propiedad y estado de cada chat en PHP.
- No exponer credenciales MySQL al frontend.
- No borrar el historial al desactivar usuarios o retirar asignaciones.
- No permitir chats de IA a psicólogos.
- No habilitar Patient sin una cita completada.
- Guardar las solicitudes de cita como `pending`.
- Cerrar los chats al recibir `[CLOSE]`, retirar la etiqueta visible y bloquear nuevos mensajes.
- Generar SQL importable en MySQL 8.
- Permitir instalar y compilar el frontend y ejecutar el backend PHP.
- Documentar comandos y configuración reales en README.
- No inventar funcionalidades fuera del alcance definido.