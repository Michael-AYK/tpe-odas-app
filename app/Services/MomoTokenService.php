<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MomoTokenService implements MomoTokenServiceInterface
{
    protected $tokenCacheKey = 'momo_access_token';

    public function getToken(): ?string
    {
        return Cache::get($this->tokenCacheKey);
    }

    public function refreshToken(): ?string
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => config('momo.subscription_key'),
            'Authorization' => 'Basic ' . base64_encode(config('momo.api_user') . ':' . config('momo.api_key')),
            'Content-Type' => 'application/json',
        ])->post('https://sandbox.momodeveloper.mtn.com/disbursement/token');

        $data = $response->json();
        $token = $data['access_token'] ?? null;

        if ($token) {
            Cache::put($this->tokenCacheKey, $token, now()->addSeconds($data['expires_in'] ?? 3600));
        }

        return $token;
    }
}
