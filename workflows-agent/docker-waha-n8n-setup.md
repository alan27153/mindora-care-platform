# Instalacion y Configuracion de WAHA + n8n mediante Docker Desktop

## Requisitos

* Docker Desktop instalado.
* WhatsApp disponible en un telefono movil.
* Navegador web.

---

# 1. Instalar Docker Desktop
Descargar e instalar Docker Desktop.

Una vez instalado, abrir la aplicacion y verificar que el servicio de Docker este en ejecucion.

---

# 2. Instalar n8n

En Docker Desktop:

1. Ir a **Images**.
2. Buscar la imagen:

```text
n8nio/n8n
```

3. Descargar (**Pull**) la ultima version disponible.

---

# 3. Crear el contenedor de n8n

Una vez descargada la imagen:

1. Ir a **Images**.
2. Seleccionar la imagen **n8nio/n8n**.
3. Hacer clic en **Run**.
4. Seleccionar **Optional Settings**.

Configurar:

### Container Name

```text
local
```

### Ports

Host Port

```text
5678
```

Container Port

```text
5678
```

### Volumes

Host Path

Seleccionar la ruta donde se almacenara el proyecto.

Ejemplo:

```text
D:\n8n
```

Container Path

```text
/home/node/.n8n
```

Finalmente hacer clic en **Run**.

---

# 4. Ingresar a n8n

Desde Docker Desktop:

1. Ir a **Containers**.
2. Abrir el contenedor recien creado.
3. Esperar entre 30 y 60 segundos hasta que termine de iniciar.
4. Abrir el navegador en:

```text
http://localhost:5678
```

5. Crear la cuenta o iniciar sesion.

---

# 5. Instalar el nodo WAHA

Dentro de n8n:

1. Ir a **Settings**.
2. Seleccionar **Usage and Plan**.
3. Entrar en **Community Nodes**.
4. Instalar el paquete:

```text
n8n-nodes-waha
```

Esperar a que finalice la instalacion.

---

# 6. Crear un Workflow

Crear un nuevo Workflow.

Agregar el nodo:

```text
WAHA Trigger
```

Entrar al nodo y copiar la **Production URL**, ya que sera utilizada posteriormente.

Ejemplo:

```text
http://localhost:5678/webhook/fcf0d150-a570-4c90-957d-e7fc540481c4/waha
```

---

# 7. Instalar WAHA

En Docker Desktop:

1. Ir a **Images**.
2. Buscar la imagen:

```text
devlikeapro/waha
```

3. Descargar (**Pull**) la ultima version.

---

# 8. Crear el contenedor de WAHA

Seleccionar la imagen descargada.

Hacer clic en **Run**.

Entrar a **Optional Settings**.

## Container Name

Puede ser cualquier nombre.

Ejemplo:

```text
waha
```

---

## Ports

Host Port

```text
3000
```

Container Port

```text
3000
```

---

## Volumes

Host Path

Seleccionar la carpeta donde se almacenaran los archivos del servidor WAHA.

Ejemplo:

```text
D:\waha-bot
```

Container Path

```text
/tmp/
```

---

## Variables de entorno

Agregar las siguientes variables.

### Motor

```text
WHATSAPP_DEFAULT_ENGINE=NOWEB
```

### Eventos

```text
WHATSAPP_HOOK_EVENTS=message
```

### Webhook

Copiar la **Production URL** obtenida anteriormente.

Ejemplo:

```text
http://localhost:5678/webhook/fcf0d150-a570-4c90-957d-e7fc540481c4/waha
```

Cambiar:

```text
localhost
```

por

```text
host.docker.internal
```

Quedando:

```text
WHATSAPP_HOOK_URL=http://host.docker.internal:5678/webhook/fcf0d150-a570-4c90-957d-e7fc540481c4/waha
```

Finalmente hacer clic en **Run**.

---

# 9. Guardar las credenciales de WAHA

Al iniciarse el contenedor apareceran las credenciales generadas automaticamente.

Crear un archivo llamado:

```text
.env
```

Guardar las credenciales.

Ejemplo:

```env
WAHA_API_KEY=5e1aa3a0e792495486e965913acee3da

WAHA_DASHBOARD_USERNAME=admin

WAHA_DASHBOARD_PASSWORD=34b55ca152b2459096b53808b439bcea

WHATSAPP_SWAGGER_USERNAME=admin

WHATSAPP_SWAGGER_PASSWORD=34b55ca152b2459096b53808b439bcea
```

Estas credenciales deberan conservarse, ya que seran utilizadas para acceder al Dashboard de WAHA.

---

# 10. Ingresar al Dashboard de WAHA

Abrir el navegador.

Ingresar a:

```text
http://localhost:3000
```

Iniciar sesion utilizando:

```text
WAHA_DASHBOARD_USERNAME
```

y

```text
WAHA_DASHBOARD_PASSWORD
```

---

# 11. Configurar el Worker

Dentro del Dashboard:

1. Entrar a **Workers**.
2. Seleccionar el Worker creado automaticamente.
3. Hacer clic en el boton **Editar**.

En el campo **API Key** colocar:

```text
WAHA_API_KEY
```

Guardar los cambios con **Save**.

---

# 12. Crear una sesion de WhatsApp

Ir a:

```text
Sessions
```

Seleccionar:

```text
Start New
```

Se abrira la ventana **Create Session**.

en la opcioon Name poner de nombre "default" este sera el nombre por defecto que tendra la sesion cuando la consulta al n8n sea via Web

Finalmente hacer clic en:

```text
Create & Start
```

---

# 13. Vincular WhatsApp

Dentro de la sesion creada:

Seleccionar:

```text
Scan QR
```

Se mostrara un codigo QR.

Desde el telefono:

WhatsApp

→ Dispositivos vinculados

→ Vincular dispositivo

Escanear el codigo QR.

Esperar hasta que la sesion cambie al estado **WORKING**.

---

# 14. Configurar el Webhook para pruebas

Volver a n8n.

Copiar el **Test URL** del nodo **WAHA Trigger**.

Ejemplo:

```text
http://localhost:5678/webhook-test/fcf0d150-a570-4c90-957d-e7fc540481c4/waha
```

Modificar:

```text
localhost
```

por

```text
host.docker.internal
```

Quedando:

```text
http://host.docker.internal:5678/webhook-test/fcf0d150-a570-4c90-957d-e7fc540481c4/waha
```

---

# 15. Agregar el Webhook a WAHA

Dentro del Dashboard:

Entrar en la sesion creada.

Seleccionar:

```text
Configuration
```

Despues:

```text
Add Webhook
```

En el campo **URL** pegar el **Test URL** modificado.

Guardar con:

```text
Update
```

---

# 16. Probar la integracion

Volver a n8n.

Ejecutar el nodo:

```text
WAHA Trigger
```

Enviar un mensaje al numero de WhatsApp vinculado.

Si la configuracion es correcta, el Trigger devolvera un JSON con la informacion del mensaje recibido.

---

# 17. Optimizar los eventos

Dentro del Dashboard de WAHA:

Entrar nuevamente a:

```text
Configuration
```

En la lista de eventos del Webhook eliminar el evento:

```text
session.status
```

Mantener unicamente el evento:

```text
message
```

De esta manera el Trigger solo recibira mensajes y el JSON sera mucho mas limpio y sencillo de procesar.

---


# Configuración y Despliegue del Workflow de Asistente IA (WhatsApp y Web)

A continuación, se describen los pasos secuenciales para realizar la configuración de los nodos necesarios en n8n y la creación del proyecto en la plataforma Supabase.

---

## 1. Configuración de Nodos de la Comunidad en n8n

1. El usuario debe ingresar a su instancia de **n8n**.
2. En el panel de navegación izquierdo, se debe hacer clic en la opción **Settings** (Configuración).
3. Dentro del menú de configuración, el usuario debe seleccionar **Community nodes**.
4. Se procede a realizar la instalación de los siguientes nodos requeridos para el workflow:
   * `n8n-nodes-globals`
   * `n8n-nodes-waha`
5. El usuario debe verificar que ambos nodos se hayan instalado de forma correcta en la plataforma.

---

## 2. Creación y Configuración del Proyecto en Supabase

1. El usuario debe ingresar al sitio web oficial de [Supabase](https://supabase.com).
2. Se debe crear una cuenta de usuario o iniciar sesión si ya se cuenta con una.
3. Tras acceder a la plataforma, el usuario procederá a crear una nueva **Organización**, completando los campos solicitados:
   * **Name**: Se ingresa el nombre de la organización (por ejemplo: `JOY IT Organization`).
   * **Type**: Se selecciona el tipo de entidad según corresponda.
   * **Plan**: Se elige el plan de trabajo deseado (por ejemplo, el plan gratuito *Free*).
4. A continuación, el sistema solicitará definir las credenciales de la base de datos PostgreSQL:
   * **Database Password**: El usuario debe ingresar o generar una contraseña segura y guardarla para futuras configuraciones de conexión.
5. Finalmente, se hace clic en el botón **Create Project** para iniciar el aprovisionamiento de la base de datos.

---

## 3. Obtención de Credenciales de PostgreSQL en Supabase

1. Dentro de la vista principal del proyecto en Supabase, el usuario debe hacer clic en el botón **Connect** ubicado en la barra superior.
2. En la ventana emergente, se selecciona la opción **Direct / Connection string**.
3. En el apartado *Connection Method*, el usuario debe seleccionar la opción **Transaction pooler**.
4. A continuación, en la sección de datos de conexión (*Connection parameters*), se deberán copiar los parámetros proporcionados (Host, Database name, Port, User) para utilizarlos posteriormente en n8n.

---

## 4. Configuración de Credenciales de Postgres en n8n

1. El usuario debe ingresar a la interfaz del flujo de trabajo en **n8n**.
2. Se selecciona o añade un nodo de **Postgres**.
3. En la sección de configuración del nodo, se accede a la opción para **crear una nueva credencial** de PostgreSQL.
4. El usuario debe completar los campos requeridos (*Host*, *Database*, *User*, *Port*, *Password*) utilizando la información copiada previamente desde Supabase y la contraseña configurada al crear el proyecto.
5. Finalmente, se debe guardar y probar la conexión para verificar que el enlace entre n8n y Supabase sea exitoso.

---

## 5. Creación de Tablas y Configuración de Base de Datos en Supabase

Para la estructuración de la base de datos y el correcto funcionamiento de los vectores de búsqueda en el asistente IA, se debe ejecutar un script de migración que habilita la extensión `pgvector`, genera la función de búsqueda semántica y crea el esquema de tablas requerido (`client`, `advisor`, `documents`, `ticket`, `inquiry`, `conversation_metric` y `conversation_analysis`).

1. El usuario debe ingresar a la consola de [Supabase](https://supabase.com).
2. En el panel lateral izquierdo, se hace clic en la opción **Projects** y se selecciona el proyecto creado previamente.
3. En el menú de navegación del proyecto (panel izquierdo), el usuario debe dirigirse a la sección **SQL Editor**.
4. A continuación, se abre una nueva pestaña de consulta y se pega la totalidad del código contenido en el archivo de migración SQL del repositorio (`migration.sql` / `schema.sql`).
5. Se hace clic en el botón **Run** (Ejecutar) ubicado en la esquina inferior derecha para ejecutar el script.
6. Una vez finalizada la ejecución sin errores, el usuario puede ir a la sección **Table Editor** en el panel izquierdo para verificar la correcta creación de todas las tablas y la extensión de vectores.

# 6. Configuración del Modelo de Inteligencia Artificial en Groq
Para el procesamiento de lenguaje natural y la generación de respuestas del asistente en n8n, se requiere la integración de un modelo LLM alojado en la plataforma de Groq. Esto permitirá que el AI Agent interprete los mensajes de los usuarios y ejecute las herramientas del flujo de trabajo de manera eficiente.

El usuario debe ingresar a la plataforma de Groq e iniciar sesión o registrarse utilizando una cuenta de Google.

En el panel de control de Groq, se debe navegar al menú lateral izquierdo y seleccionar la opción API Keys.

Se hace clic en el botón Create API Key. En el campo Display Name se ingresa un nombre identificador (por ejemplo, apikey1) y en la opción Expiration se selecciona No expiration.

A continuación, se hace clic en Submit para generar la clave, la cual debe ser copiada y guardada en un lugar seguro.

El usuario debe dirijirse a la interfaz de n8n, abrir el flujo de trabajo y seleccionar el nodo Groq Model vinculado al AI Agent.

En la sección Credential, se hace clic en la opción para crear una nueva credencial, se pega la clave generada en el campo API Key y se guarda la configuración haciendo clic en Save.

Finalmente, en el parámetro Model del nodo, se selecciona un modelo adecuado y gratuito, tal como llama-3.1-8b-instant.

# 7. Configuración del Modelo de Embeddings (Google Gemini API)
Para que el sistema de búsqueda vectorial (RAG) procese y convierta los documentos en vectores dentro de la base de datos de Supabase, se requiere configurar las credenciales del modelo de Embeddings nativo de Google Gemini.

El usuario debe ingresar a la plataforma de Google AI Studio e iniciar sesión utilizando su cuenta de Google.

En el panel principal o menú lateral, se hace clic en la opción Create API key (o Obtener clave de API).

Se selecciona la opción para generar una nueva clave, asignándole un nombre identificador (por ejemplo, Gemini API Key) y manteniendo los demás valores por defecto.

Se hace clic en Create API Key, se copia la clave generada y se guarda en un lugar seguro.

El usuario debe dirigirse a la interfaz de n8n, abrir el flujo de trabajo y seleccionar el nodo Embeddings Google Gemini conectado al AI Agent o al vector store.

En la sección de configuración del nodo, se ingresa a Set up credential (o crear nueva credencial).

En el campo Host se verifica que la URL sea [https://generativelanguage.googleapis.com](https://generativelanguage.googleapis.com), se pega la clave copiada en el campo API Key y se hace clic en Save para guardar la credencial.

# 8. Configuración de la Credencial de Supabase API en n8n
Para que las herramientas (tools) del AI Agent en n8n puedan interactuar directamente con la base de datos de Supabase, realizar búsquedas vectoriales y consultar registros, se debe configurar la credencial de conexión API.

El usuario debe ingresar a la consola de Supabase y seleccionar su proyecto.

En el panel lateral izquierdo, se hace clic en la opción Project Settings (icono de engranaje) y luego se selecciona Data API.

En la sección API URL, se copia la URL del proyecto hasta la extensión .supabase.co (por ejemplo, [https://xxxx.supabase.co](https://xxxx.supabase.co)).

En la interfaz de n8n, se abre el flujo de trabajo, se selecciona el nodo AI Agent y se hace clic en la herramienta (tool) de Supabase.

Se ingresa a la opción Set up credential (o crear nueva credencial) y se pega la URL previamente copiada en el campo Host.

Se regresa al panel de Supabase y, dentro de Project Settings, se selecciona la pestaña API Keys.

En la sección Secret keys, se copia la clave secreta disponible.

De vuelta en n8n, se pega dicha clave en el campo Secret Key y se hace clic en el botón Save para probar y guardar la credencial.

# 9. Configuración de Credenciales de WAHA (WhatsApp HTTP API)
Para permitir que los nodos del flujo (como el envío de mensajes de texto) puedan comunicarse con WhatsApp a través del servicio de WAHA, se deben vincular las credenciales de conexión entre n8n y el panel de WAHA.

En la interfaz de n8n, el usuario debe abrir el flujo de trabajo y hacer clic en cualquier nodo de tipo Send a text message (o nodo relacionado con WAHA).

En el panel de propiedades del nodo, se selecciona la opción Set up credential (o agregar nueva credencial).

En una nueva pestaña, el usuario debe ingresar al dashboard del servicio WAHA y dirigirse a la sección Workers.

En el worker previamente configurado, se hace clic en el ícono del lápiz (Edit worker).

Se copia la credencial del campo API URL y, de vuelta en n8n, se pega dentro del campo Host URL.

Se regresa al dashboard de WAHA para copiar la clave del campo API Key.

En n8n, se pega dicha clave en el campo API Key y se presiona el botón Save para verificar y guardar la conexión.

# 10. Configuración de Credenciales de Google Drive y Google Sheets en Google Cloud Console

Para que n8n pueda leer o escribir información en hojas de cálculo y gestionar archivos en Google Drive, se requiere la creación y autorización de una aplicación mediante la API de Google con OAuth2.

### Parte A: Crear el Proyecto y Habilitar la API

1. El usuario debe ingresar a [Google Cloud Console](https://cloud.google.com/) e iniciar sesión con su cuenta de Google.
2. Si es la primera vez que se accede, se deben aceptar los términos y condiciones.
3. En la barra superior, se hace clic en el selector de proyectos y se presiona el botón **Nuevo Proyecto**.
4. En el campo **Nombre del proyecto**, se ingresa un identificador (por ejemplo, `My Project`) y se hace clic en **Crear**.
5. En el panel lateral izquierdo, se navega a **APIs y servicios** $\rightarrow$ **APIs y servicios habilitados**.
6. Se presiona el botón **Habilitar APIs y servicios**, se busca `Google Drive API` y se hace clic en **Habilitar** (se puede repetir el mismo proceso para `Google Sheets API` si se requiere).

### Parte B: Configurar la Pantalla de Consentimiento de OAuth

7. En el menú lateral de la consola, se ingresa a **Pantalla de consentimiento de OAuth** y se presiona **Comenzar**.
8. En la configuración del proyecto:
   * **Nombre de la aplicación:** Se ingresa un nombre (por ejemplo, `Prueba1`).
   * **Correo electrónico de asistencia:** Se selecciona el correo de la cuenta de Google actual.
   * Se hace clic en **Siguiente**.
9. En el tipo de usuario, se marca la opción **Usuarios externos** y se presiona **Siguiente**.
10. En **Información de contacto**, se ingresa nuevamente el correo electrónico, se acepta la política de datos y se hace clic en **Continuar y Crear**.

### Parte C: Crear Clientes y Vincular con n8n

11. En la interfaz de n8n, se agrega un nodo de **Google Drive** o **Google Sheets**, se ingresa a **Set up credential** y se copia la URL proporcionada en el campo **OAuth Redirect URL**.
12. En Google Cloud Console, se navega a la sección **Credenciales**, se presiona **Crear credenciales** y se elige **ID de cliente de OAuth**.
13. En **Tipo de aplicación**, se selecciona **Aplicación web**.
14. En la sección **URIs de redireccionamiento autorizados**, se hace clic en **Agregar URI**, se pega la URL copiada desde n8n y se presiona **Crear**.
15. A continuación, se copiarán los valores generados de **ID de cliente** (*Client ID*) y **Secreto de cliente** (*Client Secret*).
16. En n8n, se pegan dichos valores en sus respectivos campos dentro de la ventana de credenciales.

### Parte D: Permisos y Usuarios de Prueba

17. En Google Cloud Console, en el menú lateral, se ingresa a **Acceso a los datos** $\rightarrow$ **Agregar o quitar permisos**, se marcan las APIs necesarias para otorgar todos los alcances requeridos y se presiona **Actualizar**.
18. En el menú lateral, se hace clic en **Audiencia / Usuarios de prueba**, se presiona **Add users** (Agregar usuarios) e ingresa el correo electrónico con el que se está realizando la prueba. Se presiona **Guardar**.
19. Finalmente, de regreso en la credencial de n8n, se hace clic en **Sign in with Google**, se inicia sesión con la cuenta registrada, se aceptan los permisos correspondientes y se guarda la credencial.

### Parte E: Habilitar Gmail API y Vincular Credenciales en n8n

20. En Google Cloud Console, en el panel lateral izquierdo, navega a **APIs y servicios** $\rightarrow$ **APIs y servicios habilitados**.
21. Haz clic en el botón **+ Habilitar APIs y servicios** (o usa la barra de búsqueda superior), busca `Gmail API` y presiona **Habilitar**.
22. De regreso en la interfaz de n8n, agrega el nodo de **Gmail** (o **Gmail Trigger**), abre la sección de configuración de credenciales y pega el **Client ID** y el **Client Secret** obtenidos previamente.
23. Haz clic en **Sign in with Google**, selecciona tu cuenta de correo registrada como usuario de prueba, acepta los permisos de acceso requeridos y guarda la credencial.



