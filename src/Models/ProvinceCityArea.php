<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 22:34
 */

namespace Aoxiang\Pca\Models;


use Illuminate\Database\Eloquent\Model;

class ProvinceCityArea extends Model
{
    public $table = 'province_city_area';
    public $fillable = ['id', 'name', 'parent_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }


}