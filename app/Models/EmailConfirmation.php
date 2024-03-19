<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfirmation extends Model
{
    use HasFactory;

    protected $table = 'email_confirmations';

    protected $fillable = ['token,user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
