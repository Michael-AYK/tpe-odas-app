<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marchand;
use App\Models\Paiement;
use Illuminate\Support\Facades\Http;
use Bmatovu\MtnMomo\Products\Disbursement;

class MarchandController extends Controller
{

   /**
     * Liste tous les marchands.
     */
    public function index()
    {
        $marchands = Marchand::all();
        return response()->json($marchands);
    }

    /**
     * Stocke un nouveau marchand.
     */
    public function store(Request $request)
    {
        $request->validate([
            'raison_sociale' => 'required',
            'adresse' => 'required',
            'numero_momo' => 'required|unique:marchands',
            'email' => 'required|email|unique:marchands',
            'contact_principal' => 'nullable'
        ]);

        $marchand = new Marchand([
            'raison_sociale' => $request->raison_sociale,
            'adresse' => $request->adresse,
            'numero_momo' => $request->numero_momo,
            'email' => $request->email,
            'contact_principal' => $request->contact_principal
        ]);

        $marchand->save();

        return response()->json([
            'message' => 'Marchand créé avec succès',
            'marchand' => $marchand
        ], 201);
    }

    /**
     * Effectue un transfert Mobile Money vers un marchand.
     */

    public function transfer(Request $request)
    {
        $request->validate([
            'marchand_id' => 'required|exists:marchands,id',
            'montant' => 'required|numeric'
        ]);

        $marchand = Marchand::findOrFail($request->marchand_id);
        $numeroMomo = $marchand->numero_momo;

        $referenceId = $this->generateReferenceId(); 

        $disbursement = new Disbursement();
        $disbursementResponse = $disbursement->transfer($referenceId, $numeroMomo, $request->montant);

        Paiement::create([
            'marchand_id' => $marchand->id,
            'agent_id' => auth()->id(),
            'reference_id' => $referenceId,
            'montant' => $request->montant,
        ]);

        return response()->json([
            'message' => 'Transfert réussi vers le marchand',
        ]);
    }



    /**
     * Génère un UUID (Universally Unique Identifier) de version 4.
     *
     * @return string UUID généré
     */
    private function generateReferenceId()
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
}
