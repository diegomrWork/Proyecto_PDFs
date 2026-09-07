<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf; // Importamos la librería de PDF

class InmatriculacionController extends Controller
{
    public function index()
    {
        return Inertia::render('Inmatriculacion');
    }

    public function generarPdf(Request $request)
    {
        // 1. Recibimos todos los datos del formulario de Vue
        $datos = $request->all();
        $tipo = $datos['tipo_cliente'];

        // 2. Decidimos qué diseño de PDF usar según el cliente
        if ($tipo === 'Juridica') {
            // Busca una vista en resources/views/pdf/juridica.blade.php
            $pdf = Pdf::loadView('pdf.juridica', ['datos' => $datos]);
        } elseif ($tipo === 'Natural') {
            $pdf = Pdf::loadView('pdf.natural', ['datos' => $datos]);
        } else {
            $pdf = Pdf::loadView('pdf.copropiedad', ['datos' => $datos]);
        }

        // 3. Devolvemos el PDF para que se descargue o se abra en el navegador
        return $pdf->stream('Cartas_Inmatriculacion.pdf');
    }

    public function consultarDni($dni) {
        $respuesta = Http::withToken(env('API_PERU_TOKEN'))
            ->withoutVerifying()
            ->get("https://apiperu.dev/api/dni/{$dni}");
        return response()->json($respuesta->json());
    }

    public function consultarRuc($ruc) {
        $respuesta = Http::withToken(env('API_PERU_TOKEN'))
            ->withoutVerifying()
            ->get("https://apiperu.dev/api/ruc/{$ruc}");
        return response()->json($respuesta->json());
    }
}