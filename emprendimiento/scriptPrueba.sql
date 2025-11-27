-- LIMPIAR DATOS EXISTENTES (opcional para pruebas)
DELETE FROM alerta WHERE id > 0;
DELETE FROM consumo_agua WHERE fecha_hora >= '2025-11-11';

-- PRUEBA 1: CONSUMO EXCESIVO
INSERT INTO consumo_agua (id_medidor, fecha_hora, volumen, tipo_registro, created_at, updated_at) 
VALUES 
(1, '2025-11-11 08:00:00', 150.00, 'transmision', NOW(), NOW());

-- PRUEBA 2: CONSUMO BRUSCO
-- Primero crear patrón normal
INSERT INTO consumo_agua (id_medidor, fecha_hora, volumen, tipo_registro, created_at, updated_at) 
VALUES 
(2, '2025-11-11 07:00:00', 3.20, 'transmision', NOW(), NOW()),
(2, '2025-11-11 07:15:00', 2.80, 'transmision', NOW(), NOW()),
(2, '2025-11-11 07:30:00', 4.10, 'transmision', NOW(), NOW()),
(2, '2025-11-11 07:45:00', 3.50, 'transmision', NOW(), NOW()),
(2, '2025-11-11 08:00:00', 2.90, 'transmision', NOW(), NOW());

-- Luego consumo brusco
INSERT INTO consumo_agua (id_medidor, fecha_hora, volumen, tipo_registro, created_at, updated_at) 
VALUES 
(2, '2025-11-11 08:15:00', 65.00, 'transmision', NOW(), NOW());

-- PRUEBA 3: FUGA
-- Crear lecturas consecutivas para fuga
INSERT INTO consumo_agua (id_medidor, fecha_hora, volumen, tipo_registro, created_at, updated_at) 
VALUES 
(3, '2025-11-11 06:00:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 06:15:00', 1.10, 'transmision', NOW(), NOW()),
(3, '2025-11-11 06:30:00', 1.30, 'transmision', NOW(), NOW()),
(3, '2025-11-11 06:45:00', 1.00, 'transmision', NOW(), NOW()),
(3, '2025-11-11 07:00:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 07:15:00', 1.30, 'transmision', NOW(), NOW()),
(3, '2025-11-11 07:30:00', 1.10, 'transmision', NOW(), NOW()),
(3, '2025-11-11 07:45:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 08:00:00', 1.40, 'transmision', NOW(), NOW()),
(3, '2025-11-11 08:15:00', 1.50, 'transmision', NOW(), NOW()),
(3, '2025-11-11 08:30:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 08:45:00', 1.10, 'transmision', NOW(), NOW()),
(3, '2025-11-11 09:00:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 09:15:00', 1.00, 'transmision', NOW(), NOW()),
(3, '2025-11-11 09:30:00', 1.30, 'transmision', NOW(), NOW()),
(3, '2025-11-11 09:45:00', 1.20, 'transmision', NOW(), NOW()),
(3, '2025-11-11 10:00:00', 1.10, 'transmision', NOW(), NOW()),
(3, '2025-11-11 10:15:00', 1.40, 'transmision', NOW(), NOW()),
(3, '2025-11-11 10:30:00', 1.30, 'transmision', NOW(), NOW()),
(3, '2025-11-11 10:45:00', 1.20, 'transmision', NOW(), NOW());

-- VERIFICAR ALERTAS GENERADAS
SELECT * FROM alerta ORDER BY fecha_hora DESC;