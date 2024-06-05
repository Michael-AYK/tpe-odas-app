<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaiementController extends Controller
{
    //
    public function getDashboardData() 
    {
        // Récupérer l'utilisateur authentifié (l'agent)
        $agent = Auth::user();

        // Requête pour les paiements d'aujourd'hui du marchand
        $marchandPaymentsToday = Paiement::where('marchand_id', $agent->marchand_id)
                                        ->whereDate('created_at', now()->today());

        // Calculer les statistiques pour l'agent
        $agentTotal = $agent->paiements()
                            ->whereDate('created_at', now()->today())
                            ->sum('montant');
        $agentCount = $agent->paiements()
                            ->whereDate('created_at', now()->today())
                            ->count();

        // Calculer les statistiques pour le marchand
        $marchandTotal = $marchandPaymentsToday->sum('montant');
        $marchandCount = $marchandPaymentsToday->count();

        return response()->json([
            'agent_total' => $agentTotal,
            'agent_count' => $agentCount, 
            'marchand_total' => $marchandTotal,
            'marchand_count' => $marchandCount,
        ]);
    }

    public function getPaiementsMarchand($marchandId)
    {
        $paiements = Paiement::where('marchand_id', $marchandId)->with("agent")->get();

        return response()->json($paiements);
    }


    public function getPaymentsForGraph(Request $request)
    {
        // Récupérer l'ID du marchand de l'agent connecté
        $marchandId = Auth::user()->marchand_id;

        // Déterminer la période en fonction de la requête du client
        switch ($request->input('period')) {
            case '7days':
                $periodStart = now()->subDays(6)->startOfDay();
                $periodEnd = now()->endOfDay();
                break;
            case '30days':
                $periodStart = now()->subDays(29)->startOfDay();
                $periodEnd = now()->endOfDay();
                break;
            case '3months':
                $periodStart = now()->subMonths(3)->startOfMonth();
                $periodEnd = now()->endOfDay();
                break;
            default: // Par défaut : 7 derniers jours
                $periodStart = now()->subDays(6)->startOfDay();
                $periodEnd = now()->endOfDay();
        }

        // Requête pour obtenir le nombre de paiements par jour
        $paymentsPerDay = Paiement::where('marchand_id', $marchandId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupBy('date')
            ->orderBy('date')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            ]);

        // Créer un tableau avec tous les jours/mois de la période
        $period = $this->generateDateArray($periodStart, $periodEnd);

        // Fusionner les données des paiements avec le tableau des jours/mois
        $data = $period->merge($paymentsPerDay->pluck('count', 'date'));

        return response()->json($data);
    }

    // Fonction pour générer le tableau de dates en fonction de la période
    private function generateDateArray($periodStart, $periodEnd)
    {
        if ($periodStart->diffInMonths($periodEnd) >= 3) {
            // Période de plusieurs mois : grouper par mois
            return collect(range(0, $periodEnd->diffInMonths($periodStart)))
                ->mapWithKeys(function ($month) use ($periodStart) {
                    $date = $periodStart->copy()->addMonths($month);
                    return [$date->format('Y-m') => 0]; 
                });
        } else {
            // Période de jours : grouper par jour
            return collect(range(0, $periodEnd->diffInDays($periodStart)))
                ->mapWithKeys(function ($day) use ($periodStart) {
                    $date = $periodStart->copy()->addDays($day);
                    return [$date->format('Y-m-d') => 0]; 
                });
        }
    }
}
