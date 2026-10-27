# 1. Descripción

**Nombre:** Mindora Care.

**Objetivo:** Crear una aplicación web con React, Tailwind CSS, PHP nativo y MySQL, integrada con el backend Java existente.

**Problema:** Centralizar los chats con Mindi, el seguimiento de pacientes, las conversaciones con psicólogos, las solicitudes de citas y la administración de usuarios.

**Alcance:** Página de inicio, autenticación, chats, integración con los flujos Client y Patient, citas, administración de usuarios y asignaciones entre pacientes y psicólogos.

**Restricciones:** Trabajar exclusivamente en `frontend-agent/`. No modificar ni analizar otros proyectos. Conservar `dashboard-ejecutivo.html`. No generar backend Java. Utilizar PHP nativo sin librerías PHP externas y una base de datos MySQL con SQL ejecutable.

# 2. Archivos que se deben analizar

Proyecto existente: únicamente se conoce `dashboard-ejecutivo.html`, que debe conservarse intacto.

No analizar archivos externos. Los contratos Java necesarios están definidos en esta SPEC.

# 3. Estructura de carpetas y archivos

```text
frontend-agent/
├── dashboard-ejecutivo.html       (conservar)
├── README.md                      (CREAR)
├── db/
│   └── mindora_care.sql           (CREAR)
├── frontend/
│   ├── .env.example               (CREAR)
│   ├── .gitignore                 (CREAR)
│   ├── package.json               (CREAR)
│   ├── index.html                 (CREAR)
│   ├── vite.config.js             (CREAR)
│   └── src/
│       ├── main.jsx               (CREAR)
│       ├── controllers/
│       │   └── AppController.js   (CREAR)
│       ├── models/
│       │   └── api.js             (CREAR)
│       ├── views/
│       │   ├── AppView.jsx        (CREAR)
│       │   └── components/
│       │       └── ChatPanel.jsx  (CREAR)
│       └── styles.css             (CREAR)
└── backend/
    ├── .env.example               (CREAR)
    ├── .gitignore                 (CREAR)
    ├── public/
    │   └── index.php              (CREAR)
    ├── config/
    │   ├── Environment.php        (CREAR)
    │   └── Database.php           (CREAR)
    ├── controllers/
    │   └── ApiController.php      (CREAR)
    ├── models/
    │   └── AppModel.php           (CREAR)
    └── views/
        └── ApiView.php             (CREAR)
```

# 4. README.md

Ruta: `README.md`

Operación: CREAR

Responsabilidad: Documentar la instalación, configuración y ejecución del proyecto.

Implementación:
- Explicar cómo importar `db/mindora_care.sql`.
- Documentar la configuración de MySQL, PHP y React en Windows.
- Indicar los comandos para ejecutar ambos servidores.
- Documentar endpoints PHP, variables de entorno, usuarios iniciales y limitaciones conocidas.

Dependencias: Toda la aplicación.

Validaciones: Las instrucciones deben corresponder con los archivos y comandos reales.

# 5. mindora_care.sql

Ruta: `db/mindora_care.sql`

Operación: CREAR

Responsabilidad: Crear la base de datos y su esquema completo.

Implementación:
- Crear la base `mindora_care` con codificación `utf8mb4`.
- Crear las seis tablas: `users`, `psychologist_profiles`, `patient_psychologist`, `chats`, `chat_messages` y `appointments`.
- `users`: UUID, nombre, correo único, hash de contraseña, rol (`admin`, `patient`, `psychologist`), estado activo y fechas.
- `psychologist_profiles`: información profesional vinculada al usuario psicólogo.
- `patient_psychologist`: asignaciones únicas entre pacientes y psicólogos.
- `chats`: paciente propietario, tipo (`ai_client`, `ai_patient`, `psychologist`), psicólogo cuando corresponda, estado (`open`, `closed`) y fechas.
- `chat_messages`: conversación, remitente, contenido y fecha.
- `appointments`: paciente, psicólogo, fecha y hora solicitadas, estado (`pending`, `confirmed`, `completed`, `cancelled`) y fechas de registro.
- Definir claves foráneas, índices, restricciones de integridad y tipos compatibles con PHP.
- Usar identificadores compatibles con UUID. Los identificadores enviados a Java deben conservar el formato UUID esperado.
- No incluir contraseñas reales ni destruir tablas existentes.

Dependencias: `AppModel.php`, autenticación y servicios de citas.

Validaciones: El archivo debe ejecutarse desde un gestor MySQL compatible con MySQL 8 y crear todas las tablas requeridas.

# 6. package.json

Ruta: `frontend/package.json`

Operación: CREAR

Responsabilidad: Definir las dependencias y comandos del frontend.

Implementación:
- Configurar React, Vite y Tailwind CSS con versiones compatibles.
- Incluir scripts para desarrollo y compilación de producción.
- Evitar dependencias innecesarias.

Dependencias: `vite.config.js` y el código React.

Validaciones: `npm install` y `npm run build` deben funcionar.

# 7. vite.config.js

Ruta: `frontend/vite.config.js`

Operación: CREAR

Responsabilidad: Configurar Vite y el desarrollo del frontend.

Implementación:
- Configurar React y Tailwind CSS según las versiones elegidas.
- Permitir ejecutar el frontend localmente.
- No incluir secretos ni credenciales de MySQL.

Dependencias: `package.json`.

Validaciones: Vite debe iniciar y compilar la aplicación.

# 8. index.html

Ruta: `frontend/index.html`

Operación: CREAR

Responsabilidad: Proporcionar el documento HTML inicial de React.

Implementación:
- Definir idioma español, título Mindora Care y elemento raíz de React.

Dependencias: `main.jsx`.

Validaciones: Debe permitir el montaje de la aplicación.

# 9. main.jsx

Ruta: `frontend/src/main.jsx`

Operación: CREAR

Responsabilidad: Iniciar la aplicación React.

Implementación:
- Montar la vista principal y cargar los estilos globales.

Dependencias: `AppView.jsx`, `styles.css`.

Validaciones: La aplicación debe renderizarse sin errores.

# 10. api.js

Ruta: `frontend/src/models/api.js`

Operación: CREAR

Responsabilidad: Centralizar las solicitudes HTTP del frontend al backend PHP.

Implementación:
- Centralizar la URL base del backend PHP.
- Enviar y recibir JSON, manejar errores HTTP y utilizar cookies de sesión mediante solicitudes con credenciales.
- No acceder directamente a MySQL ni llamar a Java desde el navegador.
- Mantener separadas las operaciones de autenticación, usuarios, chats y citas.

Dependencias: API PHP.

Validaciones: Los errores de red y de autenticación deben tratarse sin exponer credenciales.

# 11. AppController.js

Ruta: `frontend/src/controllers/AppController.js`

Operación: CREAR

Responsabilidad: Coordinar el estado, navegación y acciones de la aplicación.

Implementación:
- Gestionar sesión, usuario autenticado, rol, carga de datos, navegación y formularios.
- Aplicar la navegación de inicio, login, chats, chat Client, seguimiento Patient, administración y edición de usuarios.
- Mostrar los chats de pacientes y los chats autorizados de psicólogos.
- No permitir acceso visual a funciones de otros roles, sin sustituir las validaciones del backend.
- Coordinar envío y recepción de mensajes, cierre de conversaciones, solicitudes de citas y operaciones administrativas.

Dependencias: `api.js`, `AppView.jsx`, `ChatPanel.jsx`.

Validaciones: Los cambios de vista deben respetar la sesión y los permisos conocidos.

# 12. AppView.jsx

Ruta: `frontend/src/views/AppView.jsx`

Operación: CREAR

Responsabilidad: Renderizar las páginas y vistas principales.

Implementación:
- Página de inicio con presentación de Mindora Care, descripción de Mindi, explicación del funcionamiento y botones «Hablar con Mindi» e «Iniciar sesión».
- Login con correo y contraseña; redirigir según el rol.
- Vista de chats: pacientes ven sus chats con psicólogos asignados y chats de IA disponibles; psicólogos ven solamente chats autorizados con pacientes.
- Vista administrativa para listar, crear, consultar, editar y desactivar usuarios; asignar y retirar relaciones paciente-psicólogo.
- Vista de edición de usuario.
- Integrar el chat y los componentes reutilizables.

Dependencias: `AppController.js`, `ChatPanel.jsx`, `styles.css`.

Validaciones: Los psicólogos nunca deben disponer de chats de IA. Las páginas privadas requieren sesión.

# 13. ChatPanel.jsx

Ruta: `frontend/src/views/components/ChatPanel.jsx`

Operación: CREAR

Responsabilidad: Mostrar y gestionar la interfaz de conversación.

Implementación:
- Mostrar historial, remitente, mensajes, entrada de texto, botones de opciones y estados de carga/error.
- Diferenciar chats Client, Patient y conversaciones con psicólogos.
- Para Client, al recibir `[CLOSE]` en `answer`, retirar la etiqueta del texto visible, guardar la respuesta, cerrar la conversación e impedir nuevos envíos. Mostrar hasta tres psicólogos seleccionables para solicitar una cita.
- La solicitud debe permitir elegir una fecha y hora futuras, guardar el estado inicial `pending` y aclarar que el horario es solicitado, no confirmado. No simular disponibilidad.
- Para Patient, mostrar inicialmente «¿Cómo te sientes después de tu última terapia?» con los botones `FEEL_WORSE`, `WANT_TO_TALK`, `FEEL_BETTER`, `FEEL_SAME` y `NOT_NOW`, con etiquetas en español.
- Mostrar las opciones recibidas de Java como fuente principal cuando existan. Traducir los códigos conocidos a etiquetas en español sin alterar los valores enviados.
- Respetar los casos del flujo Patient: `NOT_NOW` termina sin opciones posteriores; `FEEL_WORSE` ofrece `FEEL_ANXIOUS`, `FEEL_CONFUSED`, `FEEL_SAD`, `PREFER_TO_WRITE`; `FEEL_BETTER` y `FEEL_SAME` tienen las opciones finales indicadas en esta SPEC; `FEEL_ANXIOUS`, `FEEL_CONFUSED`, `FEEL_SAD` y `PREFER_TO_WRITE` tienen sus opciones finales indicadas; `WANT_TO_TALK` tiene sus opciones finales indicadas.
- Las opciones finales textuales de Patient son:
  - `FEEL_BETTER` / `FEEL_SAME`: «Registrar como me siento», «Ver recursos recomendados», «Escribirle a mi psicólogo», «Gracias por ahora».
  - `FEEL_ANXIOUS` / `FEEL_CONFUSED` / `FEEL_SAD` / `PREFER_TO_WRITE`: «Buscar una nueva cita», «Hablar con mi psicólogo», «Ahora no».
  - `WANT_TO_TALK`: «Guardar mis ideas para la próxima sesión», «Escribirle a mi psicólogo», «Ver disponibilidad para una nueva sesión», «Gracias, por ahora».
- No inventar códigos Java ni enviar mensajes a una conversación ya cerrada. Las acciones posteriores de las opciones finales que no estén definidas deben quedar identificadas como pendientes, sin simular resultados.
- En todas las solicitudes Java de Client y Patient, incluir `[NAME=Nombre Apellido]` dentro de `options`, obteniendo el nombre desde la sesión validada en PHP y preservando los códigos funcionales existentes.
- Para conversaciones de psicólogo, permitir el envío de mensajes entre usuarios reales, sin invocar los endpoints Java.

Dependencias: `AppController.js`, `api.js` y API PHP.

Validaciones: Los pacientes solo acceden a sus propias conversaciones; los psicólogos solo a conversaciones autorizadas. La interfaz no debe decidir por sí sola la autorización definitiva.

# 14. styles.css

Ruta: `frontend/src/styles.css`

Operación: CREAR

Responsabilidad: Definir el estilo global y los componentes visuales.

Implementación:
- Utilizar Tailwind CSS.
- Diseñar una interfaz responsive, accesible y coherente con Mindora Care.
- Diferenciar mensajes de usuario, IA y psicólogo; mostrar claramente los estados de cita y conversación.

Dependencias: Vistas React.

Validaciones: Diseño usable en escritorio y móvil.

# 15. .env.example del frontend

Ruta: `frontend/.env.example`

Operación: CREAR

Responsabilidad: Documentar las variables públicas de configuración del frontend.

Implementación:
- Definir únicamente la URL pública de la API PHP local.
- No incluir secretos ni datos de conexión MySQL.

Dependencias: `api.js`.

Validaciones: No debe contener credenciales privadas.

# 16. .gitignore del frontend

Ruta: `frontend/.gitignore`

Operación: CREAR

Responsabilidad: Excluir archivos generados y configuraciones locales.

Implementación:
- Excluir `node_modules`, `dist`, archivos `.env` locales y logs.

Dependencias: Proyecto Vite.

Validaciones: No excluir archivos fuente necesarios.

# 17. Environment.php

Ruta: `backend/config/Environment.php`

Operación: CREAR

Responsabilidad: Leer la configuración del backend PHP.

Implementación:
- Cargar variables del archivo `.env` usando PHP nativo, sin librerías externas.
- Permitir configurar MySQL, URL base del backend Java, origen CORS y parámetros de sesión.

Dependencias: `.env.example`, `Database.php`, `ApiController.php`.

Validaciones: Fallar con un mensaje controlado si faltan variables obligatorias; no mostrar contraseñas en errores.

# 18. Database.php

Ruta: `backend/config/Database.php`

Operación: CREAR

Responsabilidad: Crear y reutilizar la conexión PDO a MySQL.

Implementación:
- Obtener credenciales desde `Environment.php`.
- Configurar excepciones PDO y consultas preparadas.
- No almacenar credenciales en el código.

Dependencias: `Environment.php`.

Validaciones: Errores de conexión controlados y sin filtrar secretos.

# 19. AppModel.php

Ruta: `backend/models/AppModel.php`

Operación: CREAR

Responsabilidad: Centralizar el acceso a datos de usuarios, chats, mensajes, asignaciones y citas.

Implementación:
- Implementar consultas preparadas para autenticación, usuarios, conversaciones, mensajes, asignaciones y citas.
- Persistir realmente los mensajes y los estados de las conversaciones.
- Crear chats y registrar mensajes sin duplicarlos por reintentos evitables.
- Recuperar psicólogos activos para mostrar hasta tres opciones de solicitud.
- Comprobar que un paciente tenga al menos una cita `completed` antes de habilitar seguimiento Patient.
- Mantener el historial al retirar una asignación.
- Registrar las citas solicitadas como `pending`; no implementar una agenda ni afirmar disponibilidad real.
- Utilizar UUID coherentes con Java y MySQL.

Dependencias: `Database.php`, `mindora_care.sql`.

Validaciones: Las relaciones y los permisos deben respetarse en todas las consultas; no confiar en identificadores de propietario recibidos del navegador.

# 20. ApiView.php

Ruta: `backend/views/ApiView.php`

Operación: CREAR

Responsabilidad: Producir respuestas JSON uniformes para la API PHP.

Implementación:
- Emitir el código HTTP, encabezados y JSON de respuesta.
- Normalizar errores sin mostrar trazas, contraseñas ni detalles internos.

Dependencias: `ApiController.php`.

Validaciones: Todas las respuestas de la API deben ser JSON válido con códigos HTTP adecuados.

# 21. ApiController.php

Ruta: `backend/controllers/ApiController.php`

Operación: CREAR

Responsabilidad: Implementar las operaciones HTTP, la autenticación, autorización y coordinación entre modelos y Java.

Implementación:
- Gestionar login, logout, sesión actual, usuarios, asignaciones, chats, mensajes y citas.
- Usar `password_hash()` y `password_verify()`, regenerar la sesión al autenticar y validar permisos en cada operación.
- Implementar roles `admin`, `patient` y `psychologist`; el administrador puede crear, consultar, editar y desactivar usuarios y gestionar asignaciones.
- Validar que pacientes accedan solo a sus datos y que psicólogos accedan solo a pacientes asignados.
- Aplicar protección CSRF a operaciones que modifican datos, cookies de sesión seguras según el entorno y CORS restringido al origen configurado.
- Exponer únicamente los endpoints PHP necesarios para las operaciones descritas en esta SPEC.
- Invocar `POST /public/chat` para Client y `POST /public/chat/followup` para Patient mediante HTTP desde PHP, usando las estructuras `ChatRequest` y `ChatResponse` definidas en esta SPEC.
- Obtener `userId` y nombre de la sesión autenticada; nunca confiar en un identificador arbitrario enviado por React.
- Enviar el nombre como `[NAME=Nombre Apellido]` dentro de `options`, preservando las opciones del flujo.
- No modificar Java ni afirmar que la etiqueta NAME es interpretada por los flujos si no se puede verificar.
- Detectar `[CLOSE]` en `answer`, guardar la respuesta, retirar la etiqueta visible y cerrar la conversación. Un arreglo `options` vacío no significa cierre por sí solo.
- Aplicar la regla específica `NOT_NOW` tras procesar la respuesta correspondiente.
- Comprobar en el backend la elegibilidad para seguimiento Patient, las citas, la propiedad de chats y la asignación entre usuarios.
- Implementar las conversaciones con psicólogos exclusivamente mediante PHP y MySQL, sin invocar IA.
- Permitir que el psicólogo confirme o gestione una solicitud únicamente mediante operaciones autorizadas y realmente implementadas.

Dependencias: `Environment.php`, `Database.php`, `AppModel.php`, `ApiView.php`, `ChatRequest` y `ChatResponse` descritos en esta SPEC.

Validaciones: Validar método HTTP, entradas, sesión, CSRF, rol, propiedad y estado de conversación. Las operaciones no autorizadas deben rechazarse en el servidor.

# 22. .env.example del backend

Ruta: `backend/.env.example`

Operación: CREAR

Responsabilidad: Proporcionar la configuración de ejemplo para desarrollo local.

Implementación:
- Incluir host, puerto, nombre de base de datos, usuario y contraseña de ejemplo de MySQL.
- Incluir la URL base Java `http://localhost:8080` y el origen local de React.
- No incluir credenciales reales.

Dependencias: `Environment.php`.

Validaciones: Debe poder copiarse como `.env` y configurarse para el entorno local.

# 23. .gitignore del backend

Ruta: `backend/.gitignore`

Operación: CREAR

Responsabilidad: Excluir secretos y archivos temporales.

Implementación:
- Excluir `.env`, logs y archivos temporales.
- Mantener `.env.example` versionable.

Dependencias: Configuración PHP.

Validaciones: No excluir código ni archivos SQL necesarios.