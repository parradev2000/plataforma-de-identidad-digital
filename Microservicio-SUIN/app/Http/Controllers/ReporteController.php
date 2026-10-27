<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    // Exportar todas las trazas del usuario a Pdf
    public static  function export_auditoria($auditoria){
        // return $html = View::make('auditoria', compact('auditoria'))->render();
        // return Helpers::exp_pdf($html, 'auditoria');

        $html = Pdf::loadView('auditoria', compact('auditoria'));
        return $html->download('Auditoria.pdf');
    }
}
