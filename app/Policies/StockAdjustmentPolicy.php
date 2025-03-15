<?php

namespace App\Policies;

use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockAdjustmentPolicy
{
    /**
     * Hanya admin yang bisa melihat daftar penyesuaian stok.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa melihat detail penyesuaian stok.
     */
    public function view(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa membuat penyesuaian stok.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa mengupdate penyesuaian stok.
     */
    public function update(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus penyesuaian stok.
     */
    public function delete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus banyak penyesuaian stok sekaligus (bulk delete).
     */
    public function deleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa merestore penyesuaian stok.
     */
    public function restore(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus permanen penyesuaian stok.
     */
    public function forceDelete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }
}
