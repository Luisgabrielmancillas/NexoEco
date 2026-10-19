<?php

namespace App\Http\Controllers;

use App\Services\SellerAnalytics;
use App\Services\SellerBenefits;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerAnalyticsController extends Controller
{
    private function dates(Request $request, SellerBenefits $benefits): array
    {
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d']]);
        if ($request->filled('from') || $request->filled('to')) {
            $benefits->require($request->user(), true);
        }
        $from = Carbon::createFromFormat('!Y-m-d', $request->query('from') ?: now('America/Mexico_City')->subDays(29)->toDateString(), 'America/Mexico_City');
        $to = Carbon::createFromFormat('!Y-m-d', $request->query('to') ?: now('America/Mexico_City')->toDateString(), 'America/Mexico_City');
        if ($from->greaterThan($to) || $from->diffInDays($to) > 365 || $to->greaterThan(now('America/Mexico_City')->endOfDay())) {
            throw ValidationException::withMessages(['fechas' => 'Elige un periodo de hasta 366 días que termine como máximo hoy.']);
        }

        return [$from, $to];
    }

    public function index(Request $request, SellerBenefits $benefits, SellerAnalytics $analytics)
    {
        $subscription = $benefits->subscription($request->user());
        [$from, $to] = $this->dates($request, $benefits);
        $summary = $subscription ? $analytics->summary($request->user(), $from, $to) : null;
        $products = $subscription?->plan === 'pro' ? $analytics->products($request->user(), $from, $to)->orderByDesc('interest_visits')->orderBy('id_producto')->paginate(20)->withQueryString() : null;
        $daily = $subscription?->plan === 'pro' ? DB::table('seller_daily_metrics')->where('seller_id', $request->user()->id)->where('kind', 'product')->whereBetween('day', [$from->toDateString(), $to->toDateString()])->selectRaw('day, SUM(visits) as visits')->groupBy('day')->orderBy('day')->get() : collect();

        return view('vendedor.estadisticas', compact('subscription', 'from', 'to', 'summary', 'products', 'daily'));
    }

    public function export(Request $request, SellerBenefits $benefits, SellerAnalytics $analytics)
    {
        $benefits->require($request->user(), true);
        [$from, $to] = $this->dates($request, $benefits);
        $products = $analytics->products($request->user(), $from, $to)->orderBy('id_producto');

        return response()->streamDownload(function () use ($products, $from, $to) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Desde', 'Hasta', 'Producto', 'Publicación', 'Visitas', 'Conversaciones iniciadas', 'Tasa de contacto (%)', 'Guardados vigentes del periodo', 'Solicitudes de apartado', 'Anticipos confirmados vigentes (MXN)', 'Ventas declaradas (sin verificación)', 'Importe declarado (MXN)'], ';', '"', '');
            foreach ($products->cursor() as $product) {
                $name = $product->nombre_producto;
                if (preg_match('/^[\s]*[=+\-@]|^[\t\r\n]/u', $name)) {
                    $name = "'".$name;
                }
                fputcsv($file, [$from->toDateString(), $to->toDateString(), $name, $product->trashed() ? 'Retirada' : 'Publicada', $product->interest_visits, $product->interest_contacts, $product->interest_visits > 0 ? round($product->interest_contacts / $product->interest_visits * 100, 1) : '', $product->interest_favorites, $product->interest_reservations, $product->verified_deposits, $product->reported_sales, $product->reported_amount], ';', '"', '');
            }
            fclose($file);
        }, 'nexoeco-reporte-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
