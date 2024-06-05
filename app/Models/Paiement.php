<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'marchand_id',
        'agent_id',
        'reference_id',
        'montant',
        // Ajoutez d'autres champs si nécessaire
    ];

    /**
     * Get the marchand that owns the paiement.
     */
    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
    }

    /**
     * Get the agent that owns the paiement.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
