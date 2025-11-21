import sys
import json
import base64 # <--- IMPORTANTE

try:
    # Recibir string codificado en Base64
    input_b64 = sys.argv[1]
    
    # Decodificar Base64 a JSON String
    input_json = base64.b64decode(input_b64).decode('utf-8')
    
    # Convertir JSON a lista de Python
    alumnos = json.loads(input_json)

except Exception as e:
    # Si falla, devolver error en formato JSON
    print(json.dumps({"error": str(e)}))
    sys.exit()

resultados = []

for alumno in alumnos:
    nombre = alumno['nombre']
    
    # Convertir valores (manejar posibles vacíos)
    try:
        promedio = float(alumno['promedio']) if alumno['promedio'] else 0.0
        asistencia = int(alumno['asistencia']) if alumno['asistencia'] else 0
    except:
        promedio = 0.0
        asistencia = 0

    # --- ALGORITMO ---
    risk_score = 0
    
    # Factor Notas (0 a 7.0)
    if promedio == 0:
        risk_score += 0 # Sin datos, no sumamos riesgo artificialmente
    elif promedio < 4.0:
        risk_score += 60
    elif promedio < 5.0:
        risk_score += 30
    
    # Factor Asistencia (0 a 100)
    if asistencia == 0:
        pass
    elif asistencia < 75:
        risk_score += 40
    elif asistencia < 85:
        risk_score += 20

    # Clasificación
    estado = "Seguro"
    color = "success"
    mensaje = "Rendimiento estable."

    if risk_score >= 60:
        estado = "CRÍTICO"
        color = "danger"
        mensaje = "Riesgo alto de reprobación. Citar apoderado."
    elif risk_score >= 30:
        estado = "Alerta"
        color = "warning"
        mensaje = "Rendimiento inestable."

    resultados.append({
        "id": alumno['id'],
        "nombre": nombre,
        "promedio": promedio,
        "asistencia": asistencia,
        "score": risk_score,
        "estado": estado,
        "color": color,
        "mensaje_ia": mensaje
    })

# Imprimir resultado final
print(json.dumps(resultados))