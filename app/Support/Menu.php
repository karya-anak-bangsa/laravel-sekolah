<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Route;

/**
 * Menyaring definisi menu di config/menu.php menurut route yang ada dan permission pengguna.
 */
class Menu
{
    /**
     * Menu admin untuk pengguna tertentu: grup kosong dibuang, item diberi 'url' dan 'active'.
     *
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public static function admin(?Authorizable $user): array
    {
        if ($user === null) {
            return [];
        }

        $groups = [];

        foreach (config('menu.admin', []) as $group) {
            $items = [];

            foreach ($group['items'] as $item) {
                // 'permission' boleh berupa daftar: item tampil bila pengguna punya salah satunya.
                $boleh = collect((array) $item['permission'])->contains(fn (string $izin) => $user->can($izin));

                if (! Route::has($item['route']) || ! $boleh) {
                    continue;
                }

                $items[] = [
                    ...$item,
                    'url' => route($item['route']),
                    'active' => request()->routeIs($item['active'] ?? $item['route']),
                ];
            }

            if ($items !== []) {
                $groups[] = ['label' => $group['label'], 'items' => $items];
            }
        }

        return $groups;
    }

    /**
     * Menu situs publik (hanya route yang sudah terdaftar).
     *
     * @return list<array{text: string, url: string, active: bool}>
     */
    public static function publik(): array
    {
        return collect(config('menu.public', []))
            ->filter(fn (array $item) => Route::has($item['route']))
            ->map(fn (array $item) => [
                'text' => $item['text'],
                'url' => route($item['route']),
                'active' => request()->routeIs($item['route']),
            ])
            ->values()
            ->all();
    }
}
