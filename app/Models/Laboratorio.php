<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Laboratorio extends Model
{
    protected $table = 'Laboratorios';
    protected $primaryKey = 'CodLab'; // char(4)
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}