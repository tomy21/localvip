<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefGate extends Model
{
    use HasFactory;
    protected $table = 'refGate';
    public $timestamps = true;
    protected $fillable = [
        'CodeGate',
        'LocationCode',
        'VihiclePlate',
        'Duration',
        'InTime',
        'OutTime',
        'status',
    ];
}
