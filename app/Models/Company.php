<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'location'];

public function user() { return $this->belongsTo(User::class); }
public function jobs() { return $this->hasMany(JobListing::class); }
}
