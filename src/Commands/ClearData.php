<?php
/**
 * Created by PhpStorm.
 * User: aoxiang
 * Date: 2019-06-05
 * Time: 11:35.
 */

namespace Aoxiang\Pca\Commands;

use Aoxiang\Pca\Models\ProvinceCityArea as PCAModel;
use Illuminate\Console\Command;

class ClearData extends Command
{
    protected $signature = 'pca:clearData';
    protected $description = '清空province_city_area表中省市县数据';

    public function handle()
    {
        $this->line('开始清空数据');
        PCAModel::query()->truncate();
        $this->info('数据已清空');
    }
}
