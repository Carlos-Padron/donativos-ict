<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimenFiscal extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'appict';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dss_cat_regimenes";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id_regimen', 'n_regimen', 'que', 'quien', 'cuando'];

}
