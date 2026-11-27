<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\Reportes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reportes del panel (requerimiento 9): citas por período e ingresos por
 * servicio, con gráficas (Chart.js) y exportación a PDF (dompdf) y CSV para Excel.
 *
 * Todos los reportes usan los filtros desde, hasta (AAAA-MM-DD) y servicio
 * (id, opcional) y, en el de citas, estado. Sin filtros se muestra el mes en
 * curso con todos los servicios.
 */
class ReporteController extends Controller
{
    /**
     * Rango máximo de un reporte, en días.
     */
    public const DIAS_MAXIMOS = 366;

    public function __construct(private Reportes $reportes)
    {
        //
    }

    /**
     * Pantalla de reportes: resumen, gráficas y tablas del período.
     */
    public function index(Request $request): View
    {
        [$desde, $hasta, $estado, $servicio] = $this->periodo($request);

        return view('admin.reportes.index', [
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'servicio' => $servicio,
            'servicios' => Service::orderBy('nombre')->get(['id', 'nombre']),
            'resumen' => $this->reportes->resumen($desde, $hasta, $servicio?->id),
            'porServicio' => $this->reportes->ingresosPorServicio($desde, $hasta, $servicio?->id),
            'porDia' => $this->reportes->porDia($desde, $hasta, $servicio?->id),
            'citas' => $this->reportes->citas($desde, $hasta, $estado, $servicio?->id),
        ]);
    }

    /**
     * Reporte de citas del período en PDF.
     */
    public function citasPdf(Request $request): Response
    {
        [$desde, $hasta, $estado, $servicio] = $this->periodo($request);

        return Pdf::loadView('admin.reportes.pdf.citas', [
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'servicio' => $servicio,
            'resumen' => $this->reportes->resumen($desde, $hasta, $servicio?->id),
            'citas' => $this->reportes->citas($desde, $hasta, $estado, $servicio?->id),
        ])->setPaper('a4', 'landscape')->download($this->nombreArchivo('citas', $desde, $hasta, 'pdf'));
    }

    /**
     * Reporte de ingresos por servicio y por día en PDF.
     */
    public function ingresosPdf(Request $request): Response
    {
        [$desde, $hasta, , $servicio] = $this->periodo($request);

        return Pdf::loadView('admin.reportes.pdf.ingresos', [
            'desde' => $desde,
            'hasta' => $hasta,
            'servicio' => $servicio,
            'resumen' => $this->reportes->resumen($desde, $hasta, $servicio?->id),
            'porServicio' => $this->reportes->ingresosPorServicio($desde, $hasta, $servicio?->id),
            'porDia' => array_values(array_filter(
                $this->reportes->porDia($desde, $hasta, $servicio?->id),
                fn (array $dia) => $dia['ingresos'] > 0
            )),
        ])->setPaper('a4')->download($this->nombreArchivo('ingresos', $desde, $hasta, 'pdf'));
    }

    /**
     * Citas del período en CSV (se abre en Excel).
     */
    public function citasCsv(Request $request): StreamedResponse
    {
        [$desde, $hasta, $estado, $servicio] = $this->periodo($request);
        $citas = $this->reportes->citas($desde, $hasta, $estado, $servicio?->id);

        $filas = $citas->map(fn (Appointment $c) => [
            $c->id,
            $c->fecha->format('d/m/Y'),
            $c->horaCorta(),
            $c->horaFin(),
            $c->duracion,
            $c->usuario?->nombreCompleto() ?? 'Usuario eliminado',
            $c->usuario?->email ?? '',
            $c->usuario?->telefono ?? '',
            $c->servicios->pluck('nombre')->join(', '),
            $this->numero($c->total),
            $c->estado,
        ]);

        return $this->csv(
            $this->nombreArchivo('citas', $desde, $hasta, 'csv'),
            ['Id', 'Fecha', 'Hora inicio', 'Hora fin', 'Duración (min)', 'Cliente', 'Correo', 'Teléfono', 'Servicios', 'Total', 'Estado'],
            $filas,
        );
    }

    /**
     * Ingresos por servicio del período en CSV (se abre en Excel).
     */
    public function ingresosCsv(Request $request): StreamedResponse
    {
        [$desde, $hasta, , $servicio] = $this->periodo($request);
        $servicios = $this->reportes->ingresosPorServicio($desde, $hasta, $servicio?->id);

        $filas = collect($servicios)->map(fn (array $s) => [
            $s['servicio'], $s['veces'], $this->numero($s['ingresos']), $this->numero($s['porcentaje'], 1),
        ]);
        $filas->push(['TOTAL', collect($servicios)->sum('veces'), $this->numero(collect($servicios)->sum('ingresos')), $this->numero(100, 1)]);

        return $this->csv(
            $this->nombreArchivo('ingresos', $desde, $hasta, 'csv'),
            ['Servicio', 'Veces realizado', 'Ingresos', 'Porcentaje'],
            $filas,
        );
    }

    /**
     * Valida y devuelve el período [desde, hasta], el estado opcional y el
     * servicio opcional. Por defecto, el mes en curso y todos los servicios.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string|null, 3: Service|null}
     */
    private function periodo(Request $request): array
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'estado' => ['nullable', Rule::in(Appointment::ESTADOS)],
            'servicio' => ['nullable', 'integer', 'exists:servicios,id'],
        ], [
            'servicio.integer' => 'El servicio elegido no es válido.',
            'servicio.exists' => 'El servicio elegido no existe.',
            'desde.date_format' => 'La fecha "desde" debe tener el formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha "hasta" debe tener el formato AAAA-MM-DD.',
            'hasta.after_or_equal' => 'La fecha "hasta" debe ser igual o posterior a la fecha "desde".',
            'estado.in' => 'El estado elegido no es válido.',
        ]);

        $desde = isset($datos['desde']) ? Carbon::parse($datos['desde']) : today()->startOfMonth();
        $hasta = isset($datos['hasta']) ? Carbon::parse($datos['hasta']) : today()->endOfMonth()->startOfDay();

        if ($desde->gt($hasta)) {
            $hasta = $desde->copy()->endOfMonth()->startOfDay();
        }

        if ($desde->diffInDays($hasta) >= self::DIAS_MAXIMOS) {
            throw ValidationException::withMessages([
                'hasta' => 'El período no puede ser mayor a '.self::DIAS_MAXIMOS.' días.',
            ]);
        }

        $servicio = isset($datos['servicio']) ? Service::find($datos['servicio']) : null;

        return [$desde, $hasta, $datos['estado'] ?? null, $servicio];
    }

    /**
     * Respuesta CSV para Excel en español: UTF-8 con BOM (para que respete las
     * tildes), separador punto y coma y decimales con coma.
     *
     * @param  list<string>  $encabezados
     * @param  iterable<array<int, mixed>>  $filas
     */
    private function csv(string $archivo, array $encabezados, iterable $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($encabezados, $filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $encabezados, ';', '"', '');

            foreach ($filas as $fila) {
                fputcsv($salida, array_map(fn ($v) => $this->sinFormula((string) $v), $fila), ';', '"', '');
            }

            fclose($salida);
        }, $archivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Evita que Excel interprete un texto como fórmula (inyección CSV): si
     * empieza por = + - @ se le antepone un apóstrofo. Los números no cambian.
     */
    private function sinFormula(string $valor): string
    {
        if ($valor !== '' && in_array($valor[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric(str_replace(',', '.', $valor))) {
            return "'".$valor;
        }

        return $valor;
    }

    /**
     * Número con coma decimal (57,50).
     */
    private function numero(float|string|null $valor, int $decimales = 2): string
    {
        return number_format((float) $valor, $decimales, ',', '');
    }

    /**
     * Nombre del archivo descargado: reporte-citas_2026-11-01_2026-11-30.pdf
     */
    private function nombreArchivo(string $tipo, Carbon $desde, Carbon $hasta, string $extension): string
    {
        return "reporte-{$tipo}_{$desde->toDateString()}_{$hasta->toDateString()}.{$extension}";
    }
}
