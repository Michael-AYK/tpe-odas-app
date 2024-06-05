<?php
// app/Services/MomoTokenServiceInterface.php

namespace App\Services;

interface MomoTokenServiceInterface
{
    public function getToken(): ?string;
    public function refreshToken(): ?string;
}
