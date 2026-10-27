
La estructura de carpetas sera un proyecto con arquitectura MVC en donde el frontend sera con react tailwing y generado con vite en donde la carpeta src tendra esta estructura de carpetas en donde tendra controllers, views, models y dentro de views una carpeta llamada components, todos los archivos y carpetas seran en ingles. Para el backend usaras php nativo sin librerias. aparte de la carpeta models views y controllers, crearas un carpeta que sera config, esa carpeta sera para realizar la conexion con la base de datos, un archivo .env que contendra datos que tu consideres necesarios, ahi irian tambien datos para la conexion con la base de datos mysql, tu en el .env dejaras con datos de ejemplo para probar en local. tambien generars una carpeta db y adentro contendra el archivo .sql que sera la estrucutra de la base de datos, la cual yo utilizare despues para copiar y pegar en un gestor de base de datos y solamente ejecutar y deberia funcionar y crearme toda la base de datos que utilizare.



en las view habra pagina de inicio: Presentación de Mindora Care, descripción de Mindi, explicación de cómo funciona y botones «Hablar con Mindi» e «Iniciar sesión».

view Inicio de sesión : Formulario de correo y contraseñay redirección según el rol.

Vista de chats: aqui si es usuario se mostrar sus chat con los psicologos que tenga o tambien se puede mostrar el chat con la IA, si es psicologo se mostrara sus chat que tenga con los usuarios, los psicologos no tendran chats con IA, solo el usuario puede tener chat con IA.

otra vista chat client: Conversación con Mindi, mensajes, campo de texto, al final cuando el frontend detecte una etiqueta que diga [CLOSE] significa que la conversacion termino y debes mostrar 3 psicologos disponibles para que el usuario pueda agendar una cita con alguno de ellos, al hacer click en alguno de ellas la cita estara registrada en la base de datos.

Cuando envias un mensaje a la IA siempre debes enviar una etiqueta que diga NAME en donde contendra el name del usuario que se ha logueado, te muestro un ejemplo

{"message":"Hola","userId":"018f3d6c-7a2e-7b12-8f90-4c2b9a1a3e8d","action":""}

vista Chat Patient: Asistente del paciente, historial conversacional y las acciones que realmente soporte el flujo Patient. Esto aparecera en la vista de chats donde ahi se ven todos los chats del paciente con todos los psicologos que tiene, en este caso sera un chat  entre el patient y la IA en donde el frontend cuando detecte que el usuario tiene citas que estan finalizadas entonces se abrira un nuevo chat con la IA que se pintara en el frontend en donde aparecera un mensaje al paciente que le dira Como te sientes despues de tu ultima terapia y aparecera los botones correspondientes que pueden ser FEEL_WORSE, WANT_TO_TALK, FEEL_BETTER, FEEL_SAME, NOT_NOW pero los botones estaran pintados en español.

Cuando envias un mensaje a la IA siempre debes enviar una etiqueta que diga NAME en donde contendra el name del usuario que se ha logueado, te muestro un ejemplo en done el NAME se debe enviar siempre en el action

{
  "message": "Me siento peor",
  "userId": "01923e5a-8b3f-7e8c-9a1b-3c4d5e6f7a8b",
  "action": "MINDI",
  "options": [
    "FEEL_WORSE",
    "[NAME=Carlos Ruiz]"
  ]
}

Aqui te muestro un ejemplo si el usuario presioanra Me siento Mejor, enviaria esto al endpoint del flujo client del backend
{"message":"Me siento mejor","userId":"01923e5a-8b3f-7e8c-9a1b-3c4d5e6f7a8b","action":"MINDI","options":["FEEL_BETTER","[NAME=Juan Pérez]"]}


Cuando el frontend detecte en la respuesta de la IA una etiqueta que diga close significa que la conversacion ha terminado. te voy a pasar algunos casos del flujo, para que en este chat de patient con IA, sepas que botones debes mostrar al inicio y dependiendo de lo que te responda la IA que botones moestrar tu en el frontend, estos casos eran para la IA sepa que enviarte al frontend, pero te los paso para que sepas segun lo que te envie tu que deberas mostrar, en options estan nombres de botones que tu pintaras en español en el frontend y cuando se presione envairas al backend en java el answer con la option correspondiente, OJO ten en cuenta que el backend en java ya esta hecho asi que tu no me generaras el backend en java solo te conectaras a el mediante endpoints, te paso el resource que tengo para mis 2 endpoints que te conectaras el flujo client y flujo patient

package io.mindoracare.chatbot.infrastructure.controller;

import jakarta.inject.Inject;
import jakarta.ws.rs.POST;
import jakarta.ws.rs.Path;
import jakarta.ws.rs.Consumes;
import jakarta.ws.rs.Produces;
import jakarta.ws.rs.core.MediaType;
import org.jboss.resteasy.reactive.RestStreamElementType;
import io.mindoracare.chatbot.application.ChatbotService;
import io.mindoracare.chatbot.application.MindiChatbotService;
import io.mindoracare.chatbot.application.FollowUpChatbotService;
import io.mindoracare.chatbot.domain.model.ChatRequest;
import io.mindoracare.chatbot.domain.model.ChatResponse;
import io.smallrye.mutiny.Multi;

@Path("/public/chat")
@Consumes(MediaType.APPLICATION_JSON)
@Produces(MediaType.APPLICATION_JSON)
public class ChatbotResource {

    @Inject
    ChatbotService chatbotService;

    @Inject
    MindiChatbotService mindiChatbotService;

    @Inject
    FollowUpChatbotService followUpChatbotService;

    // --- Endpoint Flujo Client (General) ---
    @POST
    public ChatResponse chat(ChatRequest request) {
        return chatbotService.send(request);
    }

    @POST
    @Path("/stream")
    @Produces(MediaType.SERVER_SENT_EVENTS)
    @RestStreamElementType(MediaType.APPLICATION_JSON)
    public Multi chatStream(ChatRequest request) {
        return chatbotService.stream(request);
    }

    // --- Endpoints Mindi ---
    @POST
    @Path("/mindi")
    public ChatResponse mindiChat(ChatRequest request) {
        return mindiChatbotService.send(request);
    }

    @POST
    @Path("/mindi/stream")
    @Produces(MediaType.SERVER_SENT_EVENTS)
    @RestStreamElementType(MediaType.APPLICATION_JSON)
    public Multi mindiChatStream(ChatRequest request) {
        return mindiChatbotService.stream(request);
    }

    // --- Endpoint Flujo Patient (Follow-Up) ---
    @POST
    @Path("/followup")
    public ChatResponse followUpChat(ChatRequest request) {
        return followUpChatbotService.send(request);
    }
}



y estos son los casos del flujo patient

**# Flujo Patient Follow-up**

**## Reglas generales**

\* El paciente inicia una conversación de seguimiento.
\* Cada caso mantiene la conversación hasta que el paciente indique que desea terminar.
\* Cuando la conversación termina, **\*\*no se debe hacer otra pregunta\*\***.
\* Las opciones de cierre dependen del caso.
\* \`NOT_NOW\` termina inmediatamente y no muestra opciones.
\* \`FEEL_WORSE\` muestra las 4 opciones emocionales para continuar.
\* Los demás casos continúan la conversación hasta detectar un cierre.

**---**

**## NOT_NOW**

**### Input**

\`\`\`json
{
  "message": "Ahora no",
  "options": ["NOT_NOW"]
}
\`\`\`

**### Comportamiento**

El paciente no desea conversar en este momento.

La conversación termina inmediatamente.

**### Output**

\`\`\`json
{
  "answer": "Está bien, no tienes que hacerlo ahora. Tómate el tiempo que necesites y cuídate mucho. Cuando quieras volver a conversar, aquí estaré.",
  "options": []
}
\`\`\`

**---**

**## FEEL_BETTER**

**### Input**

\`\`\`json
{
  "message": "Me siento mejor",
  "options": ["FEEL_BETTER"]
}
\`\`\`

**### Comportamiento**

La conversación continúa normalmente.

Cuando el paciente indique que desea terminar, se cierra la conversación.

**### Options al cerrar**

\`\`\`json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
\`\`\`

**---**

**## FEEL_SAME**

**### Input**

\`\`\`json
{
  "message": "Me siento igual",
  "options": ["FEEL_SAME"]
}
\`\`\`

**### Comportamiento**

La conversación continúa normalmente.

Cuando el paciente indique que desea terminar, se cierra la conversación.

**### Options al cerrar**

\`\`\`json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
\`\`\`

**---**

**## FEEL_WORSE**

**### Input**

\`\`\`json
{
  "message": "Me siento peor",
  "options": ["FEEL_WORSE"]
}
\`\`\`

**### Comportamiento**

El paciente indica que se siente peor.

Se responde con empatía y se muestran estas 4 opciones para continuar:

\`\`\`json
[
  "FEEL_ANXIOUS",
  "FEEL_CONFUSED",
  "FEEL_SAD",
  "PREFER_TO_WRITE"
]
\`\`\`

Cada opción abre su propio caso.

**---**

**## FEEL_ANXIOUS**

**### Input**

\`\`\`json
{
  "message": "Me siento más ansioso",
  "options": ["FEEL_ANXIOUS"]
}
\`\`\`

**### Comportamiento**

Se conversa sobre la ansiedad y se acompaña al paciente.

Cuando la conversación termina, se muestran:

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**---**

**## FEEL_CONFUSED**

**### Input**

\`\`\`json
{
  "message": "Me siento confundido",
  "options": ["FEEL_CONFUSED"]
}
\`\`\`

**### Comportamiento**

Se conversa sobre la confusión y se ayuda al paciente a ordenar sus pensamientos.

Cuando la conversación termina, se muestran:

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**---**

**## FEEL_SAD**

**### Input**

\`\`\`json
{
  "message": "Me siento más triste",
  "options": ["FEEL_SAD"]
}
\`\`\`

**### Comportamiento**

Se brinda contención y se conversa sobre la tristeza.

Cuando la conversación termina, se muestran:

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**---**

**## PREFER_TO_WRITE**

**### Input**

\`\`\`json
{
  "message": "Prefiero escribirlo",
  "options": ["PREFER_TO_WRITE"]
}
\`\`\`

**### Comportamiento**

El paciente prefiere expresarse por escrito.

Se continúa la conversación mediante mensajes escritos.

Cuando la conversación termina, se muestran:

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**---**

**## WANT_TO_TALK**

**### Input**

\`\`\`json
{
  "message": "Quiero conversar",
  "options": ["WANT_TO_TALK"]
}
\`\`\`

**### Comportamiento**

Se mantiene una conversación abierta para que el paciente pueda expresarse.

Cuando la conversación termina, la respuesta de cierre es:

\`\`\`json
{
  "answer": "Me alegra saber que pudiste expresarlo y que ahora te sientes un poco más tranquilo. Mereces contar con un espacio donde puedas expresarte con confianza y sin sentirte juzgado. Gracias por compartirlo conmigo.",
  "options": [
    "Guardar mis ideas para la proxima sesión",
    "Escribirle a mi psicólogo",
    "Ver disponibilidad para una nueva sesión",
    "Gracias, por ahora"
  ]
}
\`\`\`

**---**

**# Cierre de conversación**

Cuando el paciente indique que quiere terminar:

**### FEEL_ANXIOUS**

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**### FEEL_CONFUSED**

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**### FEEL_SAD**

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**### PREFER_TO_WRITE**

\`\`\`json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
\`\`\`

**### WANT_TO_TALK**

\`\`\`json
[
  "Guardar mis ideas para la proxima sesión",
  "Escribirle a mi psicólogo",
  "Ver disponibilidad para una nueva sesión",
  "Gracias, por ahora"
]
\`\`\`

**### FEEL_BETTER**

\`\`\`json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
\`\`\`

**### FEEL_SAME**

\`\`\`json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
\`\`\`

**### NOT_NOW**

\`\`\`json
[]
\`\`\`

**---**

**# Resumen de OPTIONS**

\| Caso              | Options                                                                                                                              |
\| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
\| \`NOT_NOW\`         | \`[]\`                                                                                                                                 |
\| \`FEEL_BETTER\`     | Registrar como me siento / Ver recursos recomendados / Escribirle a mi psicólogo / Gracias por ahora                                 |
\| \`FEEL_SAME\`       | Registrar como me siento / Ver recursos recomendados / Escribirle a mi psicólogo / Gracias por ahora                                 |
\| \`FEEL_WORSE\`      | FEEL_ANXIOUS / FEEL_CONFUSED / FEEL_SAD / PREFER_TO_WRITE                                                                            |
\| \`FEEL_ANXIOUS\`    | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
\| \`FEEL_CONFUSED\`   | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
\| \`FEEL_SAD\`        | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
\| \`PREFER_TO_WRITE\` | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
\| \`WANT_TO_TALK\`    | Guardar mis ideas para la proxima sesión / Escribirle a mi psicólogo / Ver disponibilidad para una nueva sesión / Gracias, por ahora |

vista crud de usuarios: esta sera la parte en que los usuarios que tengan rol admin puedan gestioanr los usuarios ya sean pacientes o psicologos, tambien se puede asignar cual usuario tiene chat con cual psicologo y poder eliminar ese chat, o tambien ver que psicolog tiene chat con que usuario y poder eliminar ese chat. tambien se puede ver la informacion del usuario y cada usuario tendra un boton editar que al presionar se abrira una nueva vista en donde se podra editar la informacion de ese usuario.