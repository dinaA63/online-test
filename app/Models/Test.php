<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = ['title', 'description', 'created_by', 'time_limit', 'max_attempts', 'group_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeVisibleToStudent($query, User $user)
    {
        $groupIds = $user->groups()->pluck('groups.id');

        return $query->where(function ($q) use ($groupIds) {
            $q->whereNull('group_id');
            if ($groupIds->isNotEmpty()) {
                $q->orWhereIn('group_id', $groupIds);
            }
        });
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($user->role !== 'student') {
            return true;
        }

        if (!$this->group_id) {
            return true;
        }

        return $user->groups()->where('groups.id', $this->group_id)->exists();
    }
}