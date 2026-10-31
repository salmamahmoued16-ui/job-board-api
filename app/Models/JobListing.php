<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
protected $fillable = ['company_id', 'title', 'description', 'location', 'type', 'salary', 'is_active'];

public function company() { return $this->belongsTo(Company::class); }
public function applications() { return $this->hasMany(JobApplication::class); }
}
