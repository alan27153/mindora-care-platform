Quiero que realices una AUDITORÍA COMPLETA Y EXHAUSTIVA de mi workflow n8n como preparación para una futura migración de n8n a Java + Quarkus.

IMPORTANTE:

* En esta etapa NO quiero que migres nada a Java.
* NO quiero que generes código Java.
* NO quiero que modifiques ningún workflow.
* NO quiero que modifiques mi backend.
* NO quiero que modifiques ninguna skill.
* NO quiero que crees todavía el SPEC.md.
* Esta tarea es únicamente de ANÁLISIS Y DOCUMENTACIÓN DEL WORKFLOW.
* Debes preservar exactamente el comportamiento observado del workflow.
* No debes inventar información que no pueda determinarse del workflow, sus nodos, expresiones, credenciales configuradas, conexiones o archivos que explícitamente te indique revisar.

## ALCANCE DE ARCHIVOS

Trabaja únicamente con los archivos del workflow n8n que te voy a proporcionar o que te indique explícitamente.

NO explores ni uses como fuente:

* otras carpetas del workspace,
* otros proyectos,
* otros backends,
* repositorios anteriores,
* skills de otros proyectos,
* código Java existente,
* archivos que no te haya indicado explícitamente.

Si necesitas información que no está disponible, indícalo como:
[REQUIRES_CONFIRMATION]

No intentes rellenar el vacío con suposiciones.

## OBJETIVO

Quiero obtener una auditoría que permita posteriormente crear un SPEC.md preciso y, después, utilizar una skill independiente de migración n8n → Quarkus para implementar el backend.

Por lo tanto, debes reconstruir con precisión QUÉ HACE actualmente el workflow.

## ANALIZA COMO MÍNIMO

### 1. Información general

* Nombre del workflow.
* Objetivo funcional.
* Trigger.
* Entradas principales.
* Salidas principales.
* Dependencias externas.
* Credenciales utilizadas.
* Variables/configuración necesarias.
* Estado persistente utilizado.

### 2. Flujo completo de ejecución

Reconstruye el flujo de inicio a fin.

Para cada nodo identifica:

* nombre del nodo,
* tipo,
* propósito,
* entrada,
* salida,
* nodos anteriores,
* nodos siguientes,
* condiciones,
* transformaciones,
* errores,
* dependencias.

Explica también:

* ramas,
* condiciones,
* merges,
* loops,
* Loop Over Items,
* Split in Batches,
* procesamiento por elemento,
* acumulaciones,
* caminos alternativos,
* caminos de error.

### 3. Expresiones n8n

Analiza explícitamente todas las expresiones y referencias como:

* {{ ... }}
* $json
* $node[...]
* $input
* $items
* $now
* variables de entorno
* credenciales
* expresiones condicionales
* fallback chains como:
  a || b || ''
* conversiones y transformaciones.

Para cada expresión importante explica:

* qué valor obtiene,
* de dónde proviene,
* cómo afecta el comportamiento,
* qué tendría que reproducirse posteriormente en Java.

NO simplifiques una expresión solo porque parezca trivial.

### 4. Code nodes

Para cada Code node analiza:

* lógica,
* entradas,
* salidas,
* transformaciones,
* condiciones,
* loops,
* manejo de errores,
* valores por defecto,
* estructuras JSON producidas,
* dependencias implícitas del contexto n8n.

Explica qué comportamiento debe preservarse en Java.

### 5. SQL / Base de datos

Para cada nodo SQL/PostgreSQL:

* consulta completa,
* tablas utilizadas,
* columnas utilizadas,
* INSERT,
* UPDATE,
* SELECT,
* DELETE,
* JOIN,
* filtros,
* ordenamiento,
* límites,
* parámetros,
* valores generados,
* transacciones si existen,
* dependencia con nodos anteriores/posteriores.

NO inventes tablas ni columnas.

Si una estructura no puede determinarse:
[REQUIRES_CONFIRMATION]

### 6. APIs e integraciones externas

Para cada integración:

* servicio,
* endpoint,
* método HTTP,
* headers,
* autenticación,
* payload,
* parámetros,
* respuesta esperada,
* timeout,
* manejo de errores,
* retry,
* fallback,
* clasificación required/optional si puede determinarse.

Si una llamada externa es crítica para continuar, indícalo.

### 7. IA / LLM

Si existe IA, analiza:

* proveedor/modelo si está disponible,
* system prompt,
* prompts,
* variables utilizadas,
* contexto,
* memoria,
* tools,
* funciones,
* formato de salida,
* validaciones,
* fallback,
* errores,
* dependencia de respuestas estructuradas,
* comportamiento esperado ante respuestas inválidas.

No inventes proveedor, modelo ni configuración que no pueda observarse.

### 8. Credenciales y configuración

Identifica:

* credenciales utilizadas,
* variables de entorno,
* URLs configurables,
* secretos referenciados,
* nombres de configuración,
* valores que deben existir en el backend.

NO expongas secretos reales en el informe.

Solo indica que existe la dependencia y su nombre/tipo si es visible.

### 9. Estado y persistencia

Identifica cualquier estado que el workflow mantenga entre ejecuciones:

* base de datos,
* memoria,
* historial,
* variables,
* registros,
* cache,
* archivos,
* almacenamiento externo.

Explica qué información se persiste y cuándo.

### 10. Reglas de negocio

Extrae todas las reglas de negocio observables.

No las resumas demasiado.

Por ejemplo:

* si ocurre X → hacer Y,
* si X y Z → hacer A,
* si falla B → hacer C,
* si falta información → solicitarla,
* si se cumple una condición → cambiar estado.

Distingue claramente:

* regla explícita en el workflow,
* comportamiento derivado de una expresión,
* comportamiento que NO puede determinarse.

### 11. Manejo de errores

Identifica:

* errores de validación,
* errores HTTP,
* errores SQL,
* errores de Code nodes,
* errores de IA,
* timeouts,
* caminos alternativos,
* fallbacks,
* respuestas de error,
* nodos que continúan aunque una operación falle.

### 12. Decisión de migración por nodo

Para cada nodo clasifica:

MIGRA AL BACKEND
o
PERMANECE EN N8N

No asumas que absolutamente todo debe migrarse.

Si no puedes decidirlo únicamente con la información disponible:
[REQUIRES_CONFIRMATION]

Explica brevemente el motivo de cada clasificación.

### 13. Contrato externo

Si alguna parte permanece en n8n, documenta:

* qué responsabilidad conserva n8n,
* qué responsabilidad pasa al backend,
* qué recibe el backend,
* qué devuelve el backend,
* formato de entrada,
* formato de salida,
* errores,
* autenticación/configuración necesaria.

### 14. Requisitos técnicos para la futura migración

Sin implementar nada todavía, identifica:

* endpoints que probablemente necesitará el backend,
* casos de uso,
* servicios de aplicación,
* entidades/modelos,
* puertos,
* adaptadores,
* clientes externos,
* persistencia,
* configuración,
* manejo de errores,
* dependencias técnicas.

IMPORTANTE:
Esto es un análisis de necesidades, NO generación de arquitectura definitiva ni código.

### 15. Información faltante

Al final crea una sección:

## REQUIRES_CONFIRMATION

Lista absolutamente todo lo que no pueda determinarse con seguridad.

No inventes respuestas.

## FORMATO DEL INFORME

Entrega un informe estructurado con:

1. Resumen ejecutivo
2. Objetivo funcional
3. Entradas y salidas
4. Diagrama textual del flujo
5. Inventario completo de nodos
6. Análisis de expresiones n8n
7. Análisis de Code nodes
8. Análisis de SQL/Base de datos
9. Integraciones externas
10. IA/LLM
11. Credenciales y configuración
12. Estado y persistencia
13. Reglas de negocio
14. Manejo de errores
15. Decisión MIGRA AL BACKEND / PERMANECE EN N8N
16. Contratos externos
17. Requisitos para la futura implementación
18. REQUIRES_CONFIRMATION
19. Riesgos y puntos críticos
20. Checklist de comportamiento que debe preservarse

## REGLA FUNDAMENTAL

Este informe será utilizado posteriormente como fuente para construir un SPEC.md.

Por eso:

* No omitas detalles importantes.
* No simplifiques lógica compleja.
* No cambies comportamiento.
* No inventes requisitos.
* No inventes tablas.
* No inventes endpoints.
* No inventes respuestas.
* No inventes credenciales.
* No inventes proveedores.
* No asumas que todo debe migrarse.
* Diferencia siempre entre OBSERVADO, INFERIDO y DESCONOCIDO.

Al terminar, NO modifiques ningún archivo salvo que yo te lo pida explícitamente.
