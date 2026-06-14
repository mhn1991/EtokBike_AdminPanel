<?php

namespace App\Support\Customers;

use App\Models\CustomerProfile;
use App\Models\User;

class CustomerProfileUpdater
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(?User $user, array $data): ?CustomerProfile
    {
        $name = trim((string) ($data['customer_name'] ?? $data['name'] ?? $user?->name ?? ''));
        $email = trim((string) ($data['customer_email'] ?? $data['email'] ?? $user?->email ?? ''));
        $phone = trim((string) ($data['customer_phone'] ?? $data['phone'] ?? ''));
        $address = trim((string) ($data['delivery_address'] ?? $data['address'] ?? ''));

        if ($name === '' && $email === '' && $phone === '' && $address === '') {
            return null;
        }

        $profile = $this->findProfile($user, $email, $phone) ?? new CustomerProfile();

        $profile->fill([
            'user_id' => $user?->id ?? $profile->user_id,
            'name' => $name !== '' ? $name : ($profile->name ?: 'EtokBike customer'),
            'email' => $email !== '' ? $email : $profile->email,
            'phone' => $phone !== '' ? $phone : $profile->phone,
            'delivery_address' => $address !== '' ? $address : $profile->delivery_address,
            'is_active' => true,
        ]);

        $profile->save();

        return $profile;
    }

    private function findProfile(?User $user, string $email, string $phone): ?CustomerProfile
    {
        if ($user) {
            $profile = CustomerProfile::query()
                ->where('user_id', $user->id)
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        if ($email !== '') {
            $profile = CustomerProfile::query()
                ->where('email', $email)
                ->first();

            if ($profile) {
                return $profile;
            }
        }

        if ($phone !== '') {
            return CustomerProfile::query()
                ->where('phone', $phone)
                ->first();
        }

        return null;
    }
}
