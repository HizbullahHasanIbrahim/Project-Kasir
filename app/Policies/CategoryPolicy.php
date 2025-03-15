<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Semua user bisa melihat daftar kategori.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua user bisa melihat detail kategori.
     */
    public function view(User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Hanya admin yang bisa membuat kategori.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa mengupdate kategori.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus satu kategori.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }

    /**
     * **Tambahkan ini** untuk mencegah petugas menghapus banyak kategori.
     */
    public function deleteAny(User $user): bool
    {
        return $user->role === 'admin'; // Hanya admin yang bisa deleteAny
    }

    /**
     * Hanya admin yang bisa merestore kategori.
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya admin yang bisa menghapus permanen kategori.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }
}
