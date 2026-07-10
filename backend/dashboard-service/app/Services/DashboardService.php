<?php

namespace App\Services;

use App\Repositories\DashboardRepository;

class DashboardService
{
    protected $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getMetrics($empleadoId)
    {
        $totalProspectos = $this->dashboardRepository->getProspectsCount($empleadoId);
        $prospectosEnProceso = $this->dashboardRepository->getProspectsInProcessCount($empleadoId);
        $prospectosPorEtapa = $this->dashboardRepository->getProspectsByStage($empleadoId);
        
        $salesSummary = $this->dashboardRepository->getSalesSummary($empleadoId);
        $insurancesSummary = $this->dashboardRepository->getInsurancesSummary($empleadoId);
        $conversionesPorVendedor = $this->dashboardRepository->getConversionForSeller($empleadoId);

        // 1. Calcular totales de ventas
        $ventasRealizadas = 0;
        $ventasFallidas = 0;
        $montoTotalVendido = 0;

        foreach ($salesSummary as $sale) {
            if ($sale->estado === 'efectiva') {
                $ventasRealizadas = $sale->total;
                $montoTotalVendido = $sale->total_monto ?? 0;
            } elseif ($sale->estado === 'fallida') {
                $ventasFallidas = $sale->total;
            }
        }

        // 2. Calcular tasa de conversión global
        $tasaConversionGlobal = $totalProspectos > 0 
            ? round(($ventasRealizadas / $totalProspectos) * 100, 2) 
            : 0;

        // 3. Calcular Seguros Vinculados
        $segurosVinculados = 0;
        $segurosProspectados = 0;
        $primaTotalVendida = 0;

        foreach ($insurancesSummary as $insurance) {
            if ($insurance->estado === 'vendido') {
                $segurosVinculados = $insurance->total;
                $primaTotalVendida = $insurance->total_prima_real ?? 0;
            } else {
                $segurosProspectados = $insurance->total;
            }
        }

        // 4. Formatear el embudo de ventas
        $etapaCounts = [
            'prospeccion' => 0,
            'calificacion' => 0,
            'negociacion' => 0,
            'cierre' => 0
        ];

        foreach ($prospectosPorEtapa as $item) {
            if (array_key_exists($item->etapa, $etapaCounts)) {
                $etapaCounts[$item->etapa] = $item->total;
            }
        }

        return [
            'kpis' => [
                'total_prospectos' => $totalProspectos,
                'prospectos_en_proceso' => $prospectosEnProceso,
                'ventas_realizadas' => $ventasRealizadas,
                'ventas_fallidas' => $ventasFallidas,
                'tasa_conversion' => $tasaConversionGlobal,
                'seguros_vinculados' => $segurosVinculados,
                'seguros_prospectados' => $segurosProspectados,
                'monto_total_vendido' => round($montoTotalVendido, 2),
                'prima_total_seguros' => round($primaTotalVendida, 2)
            ],
            'embudo' => [
                [
                    'etapa' => 'Prospección Inicial',
                    'cantidad' => $etapaCounts['prospeccion'],
                    'porcentaje' => $totalProspectos > 0 ? round(($etapaCounts['prospeccion'] / $totalProspectos) * 100, 1) : 0
                ],
                [
                    'etapa' => 'Calificación',
                    'cantidad' => $etapaCounts['calificacion'],
                    'porcentaje' => $totalProspectos > 0 ? round(($etapaCounts['calificacion'] / $totalProspectos) * 100, 1) : 0
                ],
                [
                    'etapa' => 'Negociación',
                    'cantidad' => $etapaCounts['negociacion'],
                    'porcentaje' => $totalProspectos > 0 ? round(($etapaCounts['negociacion'] / $totalProspectos) * 100, 1) : 0
                ],
                [
                    'etapa' => 'Cierre (Total)',
                    'cantidad' => $etapaCounts['cierre'],
                    'porcentaje' => $totalProspectos > 0 ? round(($etapaCounts['cierre'] / $totalProspectos) * 100, 1) : 0
                ]
            ],
            'vendedores' => $conversionesPorVendedor
        ];
    }
}
