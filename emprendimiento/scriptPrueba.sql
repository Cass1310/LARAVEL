use emprendimiento;
-- ///////////////////////// PRUEBAS PARA FUGA ///////////////////////////
-- Limpiamos el medidor para mostrar que no hay ninguna fuga
DELETE FROM consumo_agua WHERE id_medidor = 6;
DELETE FROM alerta WHERE id_medidor = 6;

-- base de la configuracion
SET @fecha_base = NOW() - INTERVAL 5 HOUR;
SET @totalizador = 150.000;
SET @medidor_id = 6;

-- Insertar 21 lecturas con flow constante de ~1.5 L/min 
INSERT INTO consumo_agua (id_medidor, fecha_hora, totalizador_m3, flow_l_min, bateria, flags, consumo_intervalo_m3, tipo_registro, created_at, updated_at) VALUES
(@medidor_id, @fecha_base + INTERVAL 0 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 15 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 30 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 45 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 60 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 75 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 90 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 105 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 120 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 135 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 150 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 165 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 180 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 195 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 210 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 225 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 240 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 255 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 270 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 285 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW()),
(@medidor_id, @fecha_base + INTERVAL 300 MINUTE, @totalizador := @totalizador + 0.0225, 1.500, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.0225, 'transmision', NOW(), NOW());

-- Verificar alertas generadas
SELECT '=== ALERTAS GENERADAS ===' as '';
SELECT * FROM alerta WHERE id_medidor = 6 ORDER BY created_at DESC;

-- Verificar conteo de lecturas con flow constante
SELECT '=== VERIFICACIÓN DE LECTURAS ===' as '';
SELECT 
    COUNT(*) as total_lecturas_constantes,
    AVG(flow_l_min) as flow_promedio,
    MIN(fecha_hora) as primera_lectura,
    MAX(fecha_hora) as ultima_lectura,
    TIMESTAMPDIFF(MINUTE, MIN(fecha_hora), MAX(fecha_hora)) as minutos_totales
FROM consumo_agua 
WHERE id_medidor = 6 
    AND fecha_hora >= @fecha_base
    AND flow_l_min BETWEEN 0.5 AND 5.0;



-- ///////////////////////// PRUEBAS PARA FUGA NOCTURNA///////////////////////////
-- Limpiar datos existentes
DELETE FROM consumo_agua WHERE id_medidor = 6;
DELETE FROM alerta WHERE id_medidor = 6;

-- Simular consumo nocturno anómalo (02:00 AM - 200 litros)
SET @fecha_base = CURDATE() + INTERVAL 2 HOUR; -- Hoy a las 2:00 AM
SET @totalizador = 200.000;
SET @medidor_id = 6;

-- Consumo normal previo (21:00 - 23:45)
INSERT INTO consumo_agua (id_medidor, fecha_hora, totalizador_m3, flow_l_min, bateria, flags, consumo_intervalo_m3, tipo_registro, created_at, updated_at) VALUES
(@medidor_id, CURDATE() + INTERVAL 21 HOUR, @totalizador := @totalizador + 0.015, 1.000, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.015, 'transmision', NOW(), NOW()),
(@medidor_id, CURDATE() + INTERVAL 21 HOUR + INTERVAL 15 MINUTE, @totalizador := @totalizador + 0.008, 0.533, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.008, 'transmision', NOW(), NOW());

-- Madrugada: consumo normal muy bajo (00:00 - 01:45)
INSERT INTO consumo_agua (id_medidor, fecha_hora, totalizador_m3, flow_l_min, bateria, flags, consumo_intervalo_m3, tipo_registro, created_at, updated_at) VALUES
(@medidor_id, CURDATE() + INTERVAL 0 HOUR, @totalizador := @totalizador + 0.002, 0.133, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.002, 'transmision', NOW(), NOW()),
(@medidor_id, CURDATE() + INTERVAL 1 HOUR, @totalizador := @totalizador + 0.001, 0.067, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.001, 'transmision', NOW(), NOW());

-- ¡CONSUMO ANÓMALO NOCTURNO! (02:00 AM - 200 litros)
INSERT INTO consumo_agua (id_medidor, fecha_hora, totalizador_m3, flow_l_min, bateria, flags, consumo_intervalo_m3, tipo_registro, created_at, updated_at) VALUES
(@medidor_id, @fecha_base, @totalizador := @totalizador + 0.200, 13.333, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.200, 'transmision', NOW(), NOW());

-- Segundo consumo anómalo (02:15 AM - 150 litros) - Patrón sostenido
INSERT INTO consumo_agua (id_medidor, fecha_hora, totalizador_m3, flow_l_min, bateria, flags, consumo_intervalo_m3, tipo_registro, created_at, updated_at) VALUES
(@medidor_id, @fecha_base + INTERVAL 15 MINUTE, @totalizador := @totalizador + 0.150, 10.000, 95, '{"leak": false, "backflow": false, "tamper": false}', 0.150, 'transmision', NOW(), NOW());

-- Verificar alertas generadas
SELECT '=== ALERTAS DE CONSUMO NOCTURNO ===' as '';
SELECT tipo_alerta, valor_detectado, descripcion, fecha_hora 
FROM alerta 
WHERE id_medidor = 6 
AND tipo_alerta IN ('consumo_nocturno', 'fuga_nocturna')
ORDER BY fecha_hora DESC;