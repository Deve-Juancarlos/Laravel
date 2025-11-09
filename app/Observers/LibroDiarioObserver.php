<?php

namespace App\Observers;

use App\Models\LibroDiario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LibroDiarioObserver
{
    
    public function created(LibroDiario $libroDiario): void
    {
        $this->logAuditoria(
            'CREAR',
            $libroDiario->id,
            null, 
            $libroDiario->getAttributes() 
        );
    }

    
    public function updated(LibroDiario $libroDiario): void
    {
        $this->logAuditoria(
            'MODIFICAR',
            $libroDiario->id,
            $libroDiario->getOriginal(), 
            $libroDiario->getAttributes()  
        );
    }

    
    public function deleted(LibroDiario $libroDiario): void
    {
        $this->logAuditoria(
            'ELIMINAR',
            $libroDiario->id,
            $libroDiario->getAttributes(), 
            null 
        );
    }

    
    private function logAuditoria(string $accion, int $asientoId, ?array $anteriores, ?array $nuevos): void
    {
        DB::table('libro_diario_auditoria')->insert([
            'asiento_id' => $asientoId,
            'accion' => $accion,
            'datos_anteriores' => $anteriores ? json_encode($anteriores) : null,
            'datos_nuevos' => $nuevos ? json_encode($nuevos) : null,
            'usuario' => Auth::user()->usuario ?? 'SYSTEM', // O Auth::user()->name
            'fecha_hora' => now(),
            'ip_address' => Request::ip()
        ]);
    }
}