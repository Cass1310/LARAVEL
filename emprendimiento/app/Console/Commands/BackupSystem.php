<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupSystem extends Command
{
    protected $signature = 'system:backup 
                            {--full : Realizar backup completo (base de datos + archivos)}
                            {--db-only : Realizar solo backup de la base de datos}
                            {--list : Listar backups disponibles}';

    protected $description = 'Gestionar backups del sistema';

    public function handle()
    {
        if ($this->option('list')) {
            return $this->listBackups();
        }

        if ($this->option('db-only')) {
            $this->info('Iniciando backup de base de datos...');
            $this->call('backup:run', ['--only-db' => true]);
            $this->info('Backup de base de datos completado.');
            return;
        }

        if ($this->option('full')) {
            $this->info('Iniciando backup completo...');
            $this->call('backup:run');
            $this->info('Backup completo completado.');
            return;
        }

        $this->info('Selecciona una opción:');
        $this->info('  php artisan system:backup --db-only    (Solo base de datos)');
        $this->info('  php artisan system:backup --full       (Backup completo)');
        $this->info('  php artisan system:backup --list       (Listar backups)');
    }

    protected function listBackups()
    {
        $backupPath = storage_path('app/backups');
        
        if (!is_dir($backupPath)) {
            $this->error('No hay backups disponibles.');
            return;
        }

        $backups = glob($backupPath . '/*.zip');
        
        if (empty($backups)) {
            $this->error('No hay backups disponibles.');
            return;
        }

        $this->info('Backups disponibles:');
        $this->info('');

        $backupList = [];
        foreach ($backups as $backup) {
            $filename = basename($backup);
            $size = filesize($backup);
            $modified = date('Y-m-d H:i:s', filemtime($backup));
            
            $backupList[] = [
                'Archivo' => $filename,
                'Tamaño' => $this->formatBytes($size),
                'Modificado' => $modified,
            ];
        }

        $this->table(['Archivo', 'Tamaño', 'Modificado'], $backupList);
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}