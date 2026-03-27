# Integración de `escala_agrupada` en el módulo Encuestas

Este documento describe **cómo** integrar correctamente el bloque de evaluación compartida para el caso "Empleado del mes", sin modelarlo como 15 preguntas independientes.

## 1) Modelo de datos recomendado

### Definición de encuesta (estructura)
- Tabla: `encuesta_preguntas`
- Registro único para el bloque:
  - `Tipo = escala_agrupada`
  - `ConfiguracionJSON` con:

```json
{
  "schema_version": 1,
  "criterios": [
    {"id": "criterio_1", "label": "Disponibilidad"},
    {"id": "criterio_2", "label": "Actitud de servicio"}
  ],
  "opciones": ["Sobresaliente", "Destacado", "Normal"],
  "permitir_otras": true,
  "etiqueta_otras": "Otras"
}
```

### Captura de respuestas (datos)
- Tabla: `encuesta_respuesta_detalle`
- Una fila por criterio contestado dentro del bloque:
  - `PREGUNTAID` = id del bloque escala
  - `Criterio` = label del criterio (ej. "Disponibilidad")
  - `OpcionTexto` = opción elegida (ej. "Destacado" u "Otras")
  - `ValorTexto` = detalle cuando se selecciona "Otras"

## 2) Validaciones backend mínimas

Para `escala_agrupada`:
1. Si el bloque es obligatorio, todos los criterios deben tener una respuesta.
2. Solo se acepta una opción por criterio.
3. Si opción = "Otras", `ValorTexto` es obligatorio.
4. La opción enviada debe existir en `opciones` o ser `etiqueta_otras`.
5. Sanitizar todos los textos (criterios, opciones, detalle de otras).

## 3) Flujo UI recomendado

Renderizar en lista por criterio:

- Disponibilidad
  - ( ) Sobresaliente
  - ( ) Destacado
  - ( ) Normal
  - ( ) Otras [input]

- Actitud de servicio
  - ( ) Sobresaliente
  - ( ) Destacado
  - ( ) Normal
  - ( ) Otras [input]

Comportamiento:
- El input de "Otras" se habilita solo cuando la opción seleccionada es "Otras".
- Mostrar errores por criterio cuando falte respuesta o detalle de "Otras".

## 4) Resultados por criterio

Para cada criterio del bloque:
- Conteo por opción.
- Porcentaje por opción.
- Listado de comentarios de "Otras".

Ejemplo:

- Disponibilidad
  - Sobresaliente: 10 (50%)
  - Destacado: 7 (35%)
  - Normal: 2 (10%)
  - Otras: 1 (5%)
  - Comentarios Otras:
    - "Siempre cubre turnos extra"

## 5) Plantilla "Empleado del mes"

Estructura sugerida:

1. Nombre del empleado (texto, requerido)
2. Departamento (texto o dropdown)
3. Bloque `escala_agrupada` con 14 criterios
4. Nombre de quien responde (texto, requerido)

Con configuración:
- `una_respuesta_por_usuario = true`
- `permitir_multiples_respuestas = false`
- resultados visibles para administración/RH

## 6) Qué **no** hacer

- No crear una pregunta por criterio.
- No duplicar opciones por cada criterio.
- No mezclar definición del bloque con respuestas en la misma tabla.

