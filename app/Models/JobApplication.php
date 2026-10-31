<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
 protected $fillable = ['job_listing_id', 'user_id', 'cover_letter', 'status'];

public function job() { return $this->belongsTo(JobListing::class, 'job_listing_id'); }
public function user() { return $this->belongsTo(User::class); }
}
