# Flujo Patient Follow-up

## Reglas generales

* El paciente inicia una conversación de seguimiento.
* Cada caso mantiene la conversación hasta que el paciente indique que desea terminar.
* Cuando la conversación termina, **no se debe hacer otra pregunta**.
* Las opciones de cierre dependen del caso.
* `NOT_NOW` termina inmediatamente y no muestra opciones.
* `FEEL_WORSE` muestra las 4 opciones emocionales para continuar.
* Los demás casos continúan la conversación hasta detectar un cierre.

---

## NOT_NOW

### Input

```json
{
  "message": "Ahora no",
  "options": ["NOT_NOW"]
}
```

### Comportamiento

El paciente no desea conversar en este momento.

La conversación termina inmediatamente.

### Output

```json
{
  "answer": "Está bien, no tienes que hacerlo ahora. Tómate el tiempo que necesites y cuídate mucho. Cuando quieras volver a conversar, aquí estaré.",
  "options": []
}
```

---

## FEEL_BETTER

### Input

```json
{
  "message": "Me siento mejor",
  "options": ["FEEL_BETTER"]
}
```

### Comportamiento

La conversación continúa normalmente.

Cuando el paciente indique que desea terminar, se cierra la conversación.

### Options al cerrar

```json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
```

---

## FEEL_SAME

### Input

```json
{
  "message": "Me siento igual",
  "options": ["FEEL_SAME"]
}
```

### Comportamiento

La conversación continúa normalmente.

Cuando el paciente indique que desea terminar, se cierra la conversación.

### Options al cerrar

```json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
```

---

## FEEL_WORSE

### Input

```json
{
  "message": "Me siento peor",
  "options": ["FEEL_WORSE"]
}
```

### Comportamiento

El paciente indica que se siente peor.

Se responde con empatía y se muestran estas 4 opciones para continuar:

```json
[
  "FEEL_ANXIOUS",
  "FEEL_CONFUSED",
  "FEEL_SAD",
  "PREFER_TO_WRITE"
]
```

Cada opción abre su propio caso.

---

## FEEL_ANXIOUS

### Input

```json
{
  "message": "Me siento más ansioso",
  "options": ["FEEL_ANXIOUS"]
}
```

### Comportamiento

Se conversa sobre la ansiedad y se acompaña al paciente.

Cuando la conversación termina, se muestran:

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

---

## FEEL_CONFUSED

### Input

```json
{
  "message": "Me siento confundido",
  "options": ["FEEL_CONFUSED"]
}
```

### Comportamiento

Se conversa sobre la confusión y se ayuda al paciente a ordenar sus pensamientos.

Cuando la conversación termina, se muestran:

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

---

## FEEL_SAD

### Input

```json
{
  "message": "Me siento más triste",
  "options": ["FEEL_SAD"]
}
```

### Comportamiento

Se brinda contención y se conversa sobre la tristeza.

Cuando la conversación termina, se muestran:

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

---

## PREFER_TO_WRITE

### Input

```json
{
  "message": "Prefiero escribirlo",
  "options": ["PREFER_TO_WRITE"]
}
```

### Comportamiento

El paciente prefiere expresarse por escrito.

Se continúa la conversación mediante mensajes escritos.

Cuando la conversación termina, se muestran:

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

---

## WANT_TO_TALK

### Input

```json
{
  "message": "Quiero conversar",
  "options": ["WANT_TO_TALK"]
}
```

### Comportamiento

Se mantiene una conversación abierta para que el paciente pueda expresarse.

Cuando la conversación termina, la respuesta de cierre es:

```json
{
  "answer": "Me alegra saber que pudiste expresarlo y que ahora te sientes un poco más tranquilo. Mereces contar con un espacio donde puedas expresarte con confianza y sin sentirte juzgado. Gracias por compartirlo conmigo.",
  "options": [
    "Guardar mis ideas para la proxima sesión",
    "Escribirle a mi psicólogo",
    "Ver disponibilidad para una nueva sesión",
    "Gracias, por ahora"
  ]
}
```

---

# Cierre de conversación

Cuando el paciente indique que quiere terminar:

### FEEL_ANXIOUS

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

### FEEL_CONFUSED

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

### FEEL_SAD

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

### PREFER_TO_WRITE

```json
[
  "Buscar una nueva cita",
  "Hablar con mi psicólogo",
  "Ahora no"
]
```

### WANT_TO_TALK

```json
[
  "Guardar mis ideas para la proxima sesión",
  "Escribirle a mi psicólogo",
  "Ver disponibilidad para una nueva sesión",
  "Gracias, por ahora"
]
```

### FEEL_BETTER

```json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
```

### FEEL_SAME

```json
[
  "Registrar como me siento",
  "Ver recursos recomendados",
  "Escribirle a mi psicólogo",
  "Gracias por ahora"
]
```

### NOT_NOW

```json
[]
```

---

# Resumen de OPTIONS

| Caso              | Options                                                                                                                              |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| `NOT_NOW`         | `[]`                                                                                                                                 |
| `FEEL_BETTER`     | Registrar como me siento / Ver recursos recomendados / Escribirle a mi psicólogo / Gracias por ahora                                 |
| `FEEL_SAME`       | Registrar como me siento / Ver recursos recomendados / Escribirle a mi psicólogo / Gracias por ahora                                 |
| `FEEL_WORSE`      | FEEL_ANXIOUS / FEEL_CONFUSED / FEEL_SAD / PREFER_TO_WRITE                                                                            |
| `FEEL_ANXIOUS`    | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
| `FEEL_CONFUSED`   | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
| `FEEL_SAD`        | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
| `PREFER_TO_WRITE` | Buscar una nueva cita / Hablar con mi psicólogo / Ahora no                                                                           |
| `WANT_TO_TALK`    | Guardar mis ideas para la proxima sesión / Escribirle a mi psicólogo / Ver disponibilidad para una nueva sesión / Gracias, por ahora |
