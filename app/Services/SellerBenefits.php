<?php

namespace App\Services;

use App\Models\SellerSubscription;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SellerBenefits
{
    public function subscription(User $user): ?SellerSubscription
    {
        if (! $user->activo || ! $user->tieneTipo('vendedor')) {
            return null;
        }

        return $user->sellerSubscriptions()->entitled()->latest()->first();
    }

    public function require(User $user, bool $pro = false): SellerSubscription
    {
        $subscription = $this->subscription($user);
        if (! $subscription || ($pro && $subscription->plan !== 'pro')) {
            throw ValidationException::withMessages(['plan' => $pro ? 'Esta herramienta requiere un plan Pro activo.' : 'Esta herramienta requiere un plan Impulso o Pro activo.']);
        }

        return $subscription;
    }

    public function limit(SellerSubscription $subscription, string $feature): ?int
    {
        return $subscription->planDetails()[$feature.'_limit'] ?? null;
    }

    public function reached(SellerSubscription $subscription, string $feature, int $count): bool
    {
        $limit = $this->limit($subscription, $feature);

        return $limit !== null && $count >= $limit;
    }
}
