<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-05
 * Time: 11:35
 */

namespace Aoxiang\Pca\Commands;

use Illuminate\Console\Command;
use Aoxiang\Pca\Models\ProvinceCityArea as PCAModel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class ClearData extends Command
{
    protected $signature = 'pca:clearData';
    protected $description = '清空province_city_area表中省市县数据';

    public function handle()
    {
        PCAModel::where('id', '>', 0)->delete();
    }
}