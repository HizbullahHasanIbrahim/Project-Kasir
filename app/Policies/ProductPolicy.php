<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Semua user bisa melihat daftar produk.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua user bisa melihat detail produk.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Hanya admin yang bisa membuat produk.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa mengupdate produk.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus produk.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus banyak produk sekaligus (bulk delete).
     */
    public function deleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa merestore produk.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus permanen produk.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
}
