<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // protected $fillable = ['title', 'body', 'website_id'];
    protected $guarded = ['id'];

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_code', 'code');
    }
}
