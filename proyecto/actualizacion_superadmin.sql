CREATE TABLE IF NOT EXISTS incidencias (
 id INT AUTO_INCREMENT PRIMARY KEY, empresa_id INT NULL, usuario_id INT NULL,
 titulo VARCHAR(150) NOT NULL, descripcion TEXT NOT NULL,
 prioridad ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
 estado ENUM('abierta','en_revision','resuelta') NOT NULL DEFAULT 'abierta',
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS sesiones_activas (
 id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, session_id VARCHAR(128) NOT NULL,
 ultima_actividad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ip VARCHAR(45) NULL, user_agent VARCHAR(255) NULL,
 UNIQUE KEY unique_session (session_id), FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS foro_temas (
 id INT AUTO_INCREMENT PRIMARY KEY, empresa_id INT NULL, usuario_id INT NULL,
 titulo VARCHAR(150) NOT NULL, mensaje TEXT NOT NULL,
 categoria ENUM('general','incidencia','mejora','soporte') NOT NULL DEFAULT 'general',
 estado ENUM('abierto','respondido','cerrado') NOT NULL DEFAULT 'abierto',
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS foro_respuestas (
 id INT AUTO_INCREMENT PRIMARY KEY, tema_id INT NOT NULL, usuario_id INT NULL, mensaje TEXT NOT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (tema_id) REFERENCES foro_temas(id) ON DELETE CASCADE,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Control de altas privadas:
-- No se necesita una tabla nueva. Se usa la columna rol de usuarios:
-- superadmin crea usuarios con rol 'jefe' desde crear_jefe.php.
-- jefe crea usuarios con rol 'trabajador' desde crear_trabajador.php.
-- registro.php queda cerrado para impedir altas públicas.

-- El email ya tiene índice único en la base de datos original.
-- No se vuelve a crear para evitar el error #1061: clave duplicada unique_email.

-- Módulos agrícolas para cumplir los objetivos del cliente
CREATE TABLE IF NOT EXISTS parcelas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 ubicacion VARCHAR(180) NULL,
 latitud DECIMAL(10,8) NULL,
 longitud DECIMAL(11,8) NULL,
 hectareas DECIMAL(10,2) DEFAULT 0,
 tipo_suelo VARCHAR(80) NULL,
 estado ENUM('activa','en_revision','inactiva') NOT NULL DEFAULT 'activa',
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cultivos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 parcela_id INT NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 variedad VARCHAR(120) NULL,
 fecha_siembra DATE NULL,
 fecha_cosecha_prevista DATE NULL,
 estado ENUM('siembra','crecimiento','cosecha','finalizado') NOT NULL DEFAULT 'crecimiento',
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
 FOREIGN KEY (parcela_id) REFERENCES parcelas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recursos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 tipo ENUM('maquinaria','herramienta','insumo','agua','personal') NOT NULL DEFAULT 'maquinaria',
 cantidad DECIMAL(10,2) DEFAULT 0,
 unidad VARCHAR(30) NULL,
 estado ENUM('disponible','en_uso','mantenimiento','agotado') NOT NULL DEFAULT 'disponible',
 notas TEXT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tareas_agricolas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 parcela_id INT NOT NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NULL,
 tipo ENUM('riego','siembra','tratamiento','revision','cosecha','otro') NOT NULL DEFAULT 'riego',
 fecha_programada DATE NULL,
 estado ENUM('pendiente','en_proceso','completada','incidencia','cancelada') NOT NULL DEFAULT 'pendiente',
 prioridad ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
 trabajador_id INT NULL,
 creado_por INT NULL,
 responsable VARCHAR(120) NULL,
 fecha_limite DATE NULL,
 observaciones_trabajador TEXT NULL,
 fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 fecha_completada DATETIME NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
 FOREIGN KEY (parcela_id) REFERENCES parcelas(id) ON DELETE CASCADE,
 FOREIGN KEY (trabajador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
 FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historico_actividades (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 parcela_id INT NULL,
 usuario_id INT NULL,
 tipo ENUM('riego','tratamiento','siembra','cosecha','incidencia','observacion') NOT NULL DEFAULT 'observacion',
 descripcion TEXT NOT NULL,
 fecha DATE NOT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
 FOREIGN KEY (parcela_id) REFERENCES parcelas(id) ON DELETE SET NULL,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Coordenadas de parcelas para mostrar el tiempo del cultivo aunque esté en otra zona
ALTER TABLE parcelas ADD COLUMN IF NOT EXISTS latitud DECIMAL(10,8) NULL AFTER ubicacion;
ALTER TABLE parcelas ADD COLUMN IF NOT EXISTS longitud DECIMAL(11,8) NULL AFTER latitud;


-- Mejoras profesionales del módulo de tareas
ALTER TABLE tareas_agricolas MODIFY estado ENUM('pendiente','en_proceso','completada','incidencia','cancelada') NOT NULL DEFAULT 'pendiente';
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS prioridad ENUM('baja','media','alta') NOT NULL DEFAULT 'media' AFTER estado;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS trabajador_id INT NULL AFTER parcela_id;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS creado_por INT NULL AFTER trabajador_id;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS fecha_limite DATE NULL AFTER fecha_programada;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS observaciones_trabajador TEXT NULL AFTER responsable;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER observaciones_trabajador;
ALTER TABLE tareas_agricolas ADD COLUMN IF NOT EXISTS fecha_completada DATETIME NULL AFTER fecha_actualizacion;
ALTER TABLE tareas_agricolas ADD INDEX IF NOT EXISTS idx_tareas_trabajador (trabajador_id);
ALTER TABLE tareas_agricolas ADD INDEX IF NOT EXISTS idx_tareas_empresa_estado (empresa_id, estado);

-- Grupos de trabajadores para que el jefe asigne tareas a equipos completos
CREATE TABLE IF NOT EXISTS grupos_trabajadores (
 id INT AUTO_INCREMENT PRIMARY KEY,
 empresa_id INT NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 descripcion TEXT NULL,
 creado_por INT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_grupo_empresa (empresa_id, nombre),
 FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
 FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS grupo_trabajador_miembros (
 id INT AUTO_INCREMENT PRIMARY KEY,
 grupo_id INT NOT NULL,
 trabajador_id INT NOT NULL,
 fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_miembro_grupo (grupo_id, trabajador_id),
 FOREIGN KEY (grupo_id) REFERENCES grupos_trabajadores(id) ON DELETE CASCADE,
 FOREIGN KEY (trabajador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
