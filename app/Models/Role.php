<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];
    protected $casts = ['permissions' => 'array', 'is_sistem' => 'boolean'];
    public function users() { return $this->hasMany(User::class, 'role_id'); }
}
