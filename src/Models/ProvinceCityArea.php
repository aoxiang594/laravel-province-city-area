<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-04
 * Time: 22:34.
 */

namespace Aoxiang\Pca\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceCityArea extends Model
{
    public $table = 'province_city_area';
    public $fillable = ['id', 'name', 'parent_id', 'type'];

    const TYPE_PROVINCE = 'province';
    const TYPE_CITY = 'city';
    const TYPE_AREA = 'area';
    const TYPE_STREET = 'street';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function isProvince()
    {
        return $this->type === self::TYPE_PROVINCE;
    }

    public function isCity()
    {
        return $this->type === self::TYPE_CITY;
    }

    public function isArea()
    {
        return $this->type === self::TYPE_AREA;
    }

    public function isStreet()
    {
        return $this->type === self::TYPE_STREET;
    }
}
