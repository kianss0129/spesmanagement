<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AnnouncementRead;
use App\Models\User;

class Announcement extends Model
{
        protected $fillable = [
            'title',
            'content',
            'created_by',
            'image',
            'target_role', //  add this
        ];

        /**
         * Scope a query to include only announcements visible to the
         * given role (or to everybody).
         *
         * Example: Announcement::forRole('employer')->get();
         *
         * @param  \Illuminate\Database\Eloquent\Builder  $query
         * @param  string  $role
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeForRole($query, string $role)
        {
            return $query->where(function ($q) use ($role) {
                $q->where('target_role', 'all')
                  ->orWhere('target_role', $role);
            });
        }

        public function reads()
        {
            return $this->hasMany(AnnouncementRead::class);
        }

        public function isReadBy(User $user)
        {
            return $this->reads()->where('user_id', $user->id)->exists();
        }

        public function markAsReadBy(User $user)
        {
            if (!$this->isReadBy($user)) {
                $this->reads()->create(['user_id' => $user->id]);
            }
        }
    }
