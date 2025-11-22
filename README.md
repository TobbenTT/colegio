# 🎓 ColegioApp - Sistema de Gestión Escolar Integral (LMS + ERP)

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.10-3776AB?style=for-the-badge&logo=python&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

> **Una plataforma educativa completa, modular y potenciada con Inteligencia Artificial para el análisis de riesgo escolar.**

---

## 🚀 Descripción del Proyecto

**ColegioApp** es una solución web full-stack diseñada para modernizar la administración académica. A diferencia de los sistemas tradicionales, este proyecto integra **Inteligencia Artificial** para predecir riesgos de repitencia y utiliza tecnologías modernas como **Códigos QR** para la asistencia.

El sistema está construido con **PHP Nativo (Vanilla)** utilizando el patrón MVC simplificado, garantizando un rendimiento alto sin la sobrecarga de frameworks pesados, ideal para despliegue en VPS.

---

## ✨ Características Principales

### 🤖 Módulo de Inteligencia Artificial (Python)
* **Predicción de Riesgo:** Algoritmo en Python que analiza en tiempo real las notas y asistencia de los alumnos.
* **Dashboard Predictivo:** Alerta a la dirección sobre alumnos en situación crítica antes de que reprueben.

### 👨‍🏫 Gestión Docente Avanzada
* **Asistencia Biométrica/QR:** Sistema rápido de toma de asistencia mediante escaneo de códigos QR desde el celular o webcam.
* **Libro de Clases Digital:** Registro de anotaciones (positivas/negativas) que notifica automáticamente a los apoderados.
* **Evaluaciones Ponderadas:** Cálculo automático de promedios basado en el peso (%) de cada evaluación.

### 🎓 Portal del Alumno
* **Aula Virtual:** Descarga de material de estudio y subida de tareas con drag-and-drop.
* **Feedback en Vivo:** Visualización de notas, promedios y asistencia en tiempo real con gráficos.
* **Horario Inteligente:** Visualización de clases con alertas de exámenes próximos o clases suspendidas.

### 🏢 Administración y Dirección
* **Business Intelligence:** Gráficos interactivos (Chart.js) para visualizar la salud académica del colegio.
* **Reportes Exportables:** Generación de informes en Excel con filtros dinámicos.
* **Gestión Total:** CRUD completo de usuarios, cursos, matrículas y asignación de carga académica.

### 🔔 Interactividad
* **Sistema de Notificaciones:** Alertas tipo "Campanita" en tiempo real para notas, mensajes y anuncios.
* **Mensajería Interna:** Chat privado entre profesores, alumnos y dirección.

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8.x (PDO, POO), Python (Scripts de análisis).
* **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (Fetch API).
* **Base de Datos:** MySQL / MariaDB (Modelo Relacional Complejo).
* **Librerías:** * `Chart.js` (Gráficos).
    * `html5-qrcode` (Escáner).
    * `Animate.css` (Micro-interacciones).

---

## 📸 Capturas de Pantalla

| Dashboard Alumno | Dashboard Director |
|:---:|:---:|
| ![Alumno] | ![Director] |

| Escáner QR | IA de Riesgo |
|:---:|:---:|
| ![QR] | ![IA] |

---

## ⚙️ Instalación y Despliegue

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/TU_USUARIO/colegio.git](https://github.com/TU_USUARIO/colegio.git)
    ```

2.  **Base de Datos:**
    * Importar el archivo `BD/colegio_bd.sql` en tu gestor MySQL (phpMyAdmin o Workbench).

3.  **Configuración:**
    * Editar `config/db.php` con tus credenciales:
    ```php
    $host = 'localhost';
    $db   = 'colegio_bd';
    $user = 'root';
    $pass = '';
    ```

4.  **Dependencias de Python (Opcional para módulo IA):**
    * Asegúrate de tener Python instalado y accesible desde el PATH.

5.  **¡Listo!** Accede desde `http://localhost/colegio`.

---

## 🔐 Credenciales de Prueba (Demo)

| Rol | Usuario | Contraseña |
| :--- | :--- | :--- |
| **Admin** | `admin@cole.cl` | `12345` |
| **Director** | `director@cole.cl` | `12345` |
| **Profesor** | `matematica@cole.cl` | `12345` |
| **Alumno** | `pepito@cole.cl` | `12345` |

---

Hecho con ❤️ por **TobbenT**.
