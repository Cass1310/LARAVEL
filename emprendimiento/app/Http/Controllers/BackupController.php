<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupList();
        $diskSpace = $this->getDiskSpaceInfo();
        
        return view('admin.backups.index', compact('backups', 'diskSpace'));
    }
    public function create(Request $request)
    {
        $type = $request->get('type', 'db');

        try {
            if ($type === 'full') {
                Artisan::call('backup:run');
                $message = 'Backup completo creado exitosamente';
            } else {
                Artisan::call('backup:run', ['--only-db' => true]);
                $message = 'Backup de base de datos creado exitosamente';
            }

            return $this->redirectToBackups('success', $message);
        } catch (\Exception $e) {
            return $this->redirectToBackups('error', 'Error al crear backup: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $filePath = storage_path('app/Laravel/' . $filename);

        if (!file_exists($filePath)) {
            return $this->redirectToBackups('error', 'Archivo de backup no encontrado');
        }

        return response()->download($filePath);
    }

    public function delete($filename)
    {
        $filePath = storage_path('app/Laravel/' . $filename);

        if (!file_exists($filePath)) {
            return $this->redirectToBackups('error', 'Archivo de backup no encontrado');
        }

        unlink($filePath);

        return $this->redirectToBackups('success', 'Backup eliminado exitosamente');
    }

    public function clean()
    {
        try {
            Artisan::call('backup:clean');
            return $this->redirectToBackups('success', 'Backups antiguos eliminados exitosamente');
        } catch (\Exception $e) {
            return $this->redirectToBackups('error', 'Error al limpiar backups: ' . $e->getMessage());
        }
    }
    private function redirectToBackups($status, $message)
    {
        if (Route::has('admin.backups.index')) {
            return redirect()->route('admin.backups.index')->with($status, $message);
        }

        return redirect()->back()->with($status, $message);
    }

    private function getBackupList()
    {
        $backupPath = storage_path('app/Laravel');

        if (!is_dir($backupPath)) {
            return [];
        }

        $files = scandir($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filePath = $backupPath . '/' . $file;

                $backups[] = [
                    'name' => $file,
                    'size' => round(filesize($filePath) / 1024 / 1024, 2) . ' MB',
                    'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                ];
            }
        }

        // Ordenar por fecha (más recientes arriba)
        usort($backups, fn($a, $b) => strtotime($b['modified']) <=> strtotime($a['modified']));

        return $backups;
    }


    private function getDiskSpaceInfo()
    {
        $backupPath = storage_path('app/Laravel');
        $totalSize = 0;

        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/*.zip');
            foreach ($files as $file) {
                $totalSize += filesize($file);
            }
        }

        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());

        return [
            'backup_size' => $this->formatBytes($totalSize),
            'free_space' => $this->formatBytes($diskFree),
            'total_space' => $this->formatBytes($diskTotal),
            'used_percentage' => round(($diskTotal - $diskFree) / $diskTotal * 100, 2)
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}