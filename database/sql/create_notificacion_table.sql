-- Script de Creación de la Tabla NOTIFICACION en PostgreSQL

CREATE TABLE notificacion (
    "Id_notificacion" SERIAL,
    tipo_notificacion VARCHAR(50) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    destinatario VARCHAR(150) NOT NULL,
    correo_destinatario VARCHAR(150) NOT NULL,
    fecha_envio DATE NOT NULL DEFAULT CURRENT_DATE,
    hora_envio TIME NOT NULL DEFAULT CURRENT_TIME,
    estado_envio VARCHAR(20) NOT NULL,
    
    CONSTRAINT pk_notificacion PRIMARY KEY ("Id_notificacion"),
    CONSTRAINT chk_notificacion_estado CHECK (estado_envio IN ('enviado', 'fallido', 'pendiente'))
);
