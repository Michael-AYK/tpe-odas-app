<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marchand extends Model
{
    use HasFactory;

    protected $fillable = [
        'raison_sociale',
        'adresse',
        'numero_momo',
        'email',
        'contact_principal',
    ];
}
