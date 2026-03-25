<?php

namespace App\Providers;

use App\Models\COCO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Conteo unificado para el badge "Mis cotizaciones" en navbar:
        // - Incluye borradores (aunque no estén en Odoo)
        // - Para el resto: debe existir en Odoo y pertenecer al partner del usuario
        // - Deduplica por folio Odoo (conserva la más reciente)
        View::composer('layouts/sections/navbar/navbar', function ($view) {
            $totalCotizacionesNavbar = 0;

            if (Auth::check()) {
                $cotizaciones = COCO::where('COCO_usuario', Auth::id())
                    ->whereNotIn('COCO_estatus', ['cancelada', 'cancel', 'cancelled'])
                    ->orderBy('COCO_fecha', 'desc')
                    ->get()
                    ->filter(fn ($c) => $c->debeMostrarseEnMisCotizaciones())
                    ->values();

                $partnerId = !empty(Auth::user()->odoo_partner_id) ? (int) Auth::user()->odoo_partner_id : 0;

                if ($partnerId > 0) {
                    $orderIds = $cotizaciones
                        ->filter(function ($c) {
                            return COCO::estatusEsBorradorListado($c->COCO_estatus) === false
                                && !empty($c->COCO_odoo_cotizacion);
                        })
                        ->pluck('COCO_odoo_cotizacion')
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();

                    if (!empty($orderIds)) {
                        try {
                            $resp = Http::timeout(10)->post('http://127.0.0.1:3036/sale-orders-by-partner', [
                                'partner_id' => $partnerId,
                                'order_ids' => $orderIds,
                            ]);

                            if ($resp->successful()) {
                                $matching = array_map('intval', (array) ($resp->json('matching_order_ids') ?? []));
                                $cotizaciones = $cotizaciones
                                    ->filter(function ($c) use ($matching) {
                                        if (COCO::estatusEsBorradorListado($c->COCO_estatus)) {
                                            return true;
                                        }
                                        $oid = (int) ($c->COCO_odoo_cotizacion ?? 0);
                                        return in_array($oid, $matching, true);
                                    })
                                    ->values();
                            }
                        } catch (\Throwable) {
                            // Si falla la validación, no rompemos el navbar; mostramos lo local filtrado.
                        }
                    }
                }

                $borradores = $cotizaciones
                    ->filter(fn ($c) => COCO::estatusEsBorradorListado($c->COCO_estatus))
                    ->values();

                $otros = $cotizaciones
                    ->reject(fn ($c) => COCO::estatusEsBorradorListado($c->COCO_estatus))
                    ->sortByDesc('COCO_fecha')
                    ->unique(fn ($c) => (string) ($c->COCO_odoo_cotizacion ?? ''))
                    ->values();

                $totalCotizacionesNavbar = $borradores->merge($otros)->count();
            }

            $view->with('totalCotizacionesNavbar', $totalCotizacionesNavbar);
        });
    }
}
