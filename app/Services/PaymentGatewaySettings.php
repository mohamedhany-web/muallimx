<?php

namespace App\Services;

use App\Models\Setting;

class PaymentGatewaySettings
{
    public const SETTING_KEY = 'fawaterak_gateway_enabled';

    public static function isFawaterakEnabled(): bool
    {
        return Setting::getValue(self::SETTING_KEY) === '1';
    }
}
