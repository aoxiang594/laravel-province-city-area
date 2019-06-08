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
use Illuminate\Support\Facades\Cache;

class RefreshData extends Command
{
    protected $signature = 'pca:refreshData {disableCache?}';
    protected $description = '从京东获取最新的省市县数据';
    public $client = null, $provinceList = null, $url = '';
    public $result = [];
    public $headers = ["accept"                    => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                       "accept-encoding"           => "gzip, deflate, br",
                       "accept-language"           => "zh-CN,zh;q=0.9,en;q=0.8",
                       "cache-control"             => "max-age=0",
                       "cookie"                    => "wxa_level=1; retina=1; cid=9; wqmnx1=MDEyNjM3MnR0ZHIxNnR6Lm9VZV8gIGx0LlRrb2kwZTJpMWZkLTVRT0YmKQ%3D%3D; webp=1; __jda=122270672.1559637044910143121828.1559637044.1559637044.1559637044.1; __jdv=122270672%7Cdirect%7C-%7Cnone%7C-%7C1559637044913; __jdc=122270672; mba_muid=1559637044910143121828; __wga=1559637047072.1559637047072.1559637047072.1559637047072.1.1; PPRD_P=UUID.1559637044910143121828; sc_width=375; shshshfp=827a04050b2522269f28c6d0dd11fcf2; shshshfpa=d6f7d411-30ed-7855-3a58-d47d262384ac-1559637047; shshshfpb=pisuk%2Fwyhm%2FErXKDTfJs9%2FQ%3D%3D; sk_history=100000068472%2C; wq_addr=0%7C84_1310_53281_0%7C%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B%u5168%u533A_%7C%7C; jdAddrId=84_1310_53281_0; jdAddrName=%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B_%u9493%u9C7C%u5C9B%u5168%u533A_; mitemAddrId=84_1310_53281_0; mitemAddrName=; wq_logid=1559637176.1241587375",
                       "upgrade-insecure-requests" => "1",
                       "user-agent"                => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36",
    ];
    public $count = 0;

    public function handle()
    {
//        dump($this->arguments());
//        $params = $this->argument('disableCache');
//        if (isset($params)) {
//            dump();
//        }
//        exit;
        if (PCAModel::count() > 0) {
            $this->error("您已经获取过最新的数据，如果再次执行，有可能会出现id有变化，导致您之前的数据不准确");
            $this->error("如果确认要执行，请运行php artisan pca:clearData 命令，这会将之前省市县数据清空，然后再执行php artisan pcm:refreshData获取最新的数据");
            exit;
        }

        $this->provinceList = [
            '1'     => '北京',
            '2'     => '上海',
            '3'     => '天津',
            '4'     => '重庆',
            '5'     => '河北',
            '6'     => '山西',
            '7'     => '河南',
            '8'     => '辽宁',
            '9'     => '吉林',
            '10'    => '黑龙江',
            '11'    => '内蒙古',
            '12'    => '江苏',
            '13'    => '山东',
            '14'    => '安徽',
            '15'    => '浙江',
            '16'    => '福建',
            '17'    => '湖北',
            '18'    => '湖南',
            '19'    => '广东',
            '20'    => '广西',
            '21'    => '江西',
            '22'    => '四川',
            '23'    => '海南',
            '24'    => '贵州',
            '25'    => '云南',
            '26'    => '西藏',
            '27'    => '陕西',
            '28'    => '甘肃',
            '29'    => '青海',
            '30'    => '宁夏',
            '31'    => '新疆',
            '32'    => '台湾',
            '84'    => '钓鱼岛',
            '52993' => '港澳',

//            '53283' => '海外',
        ];
        $this->result       = Cache::get('provinceCityAreaStreet');
        $this->result       = json_decode($this->result, true);

        if ($this->argument('disableCache') == 'true' || !is_array($this->result) || empty($this->result)) {
            $this->result = [];
            foreach ($this->provinceList as $id => $province) {
                $this->result[$id] = [
                    'id'        => $id,
                    'name'      => $province,
                    'parent_id' => 0,
                    'city_list' => [],
                ];
                //try {
                $cityList  = $this->getCity($id);
                $_cityList = [];
                if ($cityList !== false) {
                    foreach ($cityList as $city) {
                        $this->line("获取数据成功:" . $province . $city['name']);
                        $_cityList[$city['id']] = [
                            'id'        => $city['id'],
                            'name'      => $city['name'],
                            'parent_id' => $id,
                            'area_list' => [],
                        ];
                        $areaList               = $this->getArea($city['id']);
                        $_areaList              = [];
                        if ($areaList !== false) {
                            foreach ($areaList as $area) {
                                $this->line("获取数据成功:" . $province . $city['name'] . $area['name']);
                                $_areaList[$area['id']] = [
                                    'id'          => $area['id'],
                                    'name'        => $area['name'],
                                    'parent_id'   => $city['id'],
                                    'street_list' => [],
                                ];
                                $streetList             = $this->getStreet($area['id']);
                                if ($streetList !== false) {
                                    foreach ($streetList as &$street) {
                                        $street['parent_id'] = $area['id'];
                                        $this->line("获取数据成功:" . $province . $city['name'] . $area['name'] . $street['name']);
                                        unset($street['areaCode']);
                                    }
                                }
                                $_areaList[$area['id']]['street_list'] = array_values($streetList);
                            }
                            $_cityList[$city['id']]['area_list'] = array_values($_areaList);
                        }
                    }
                }
                $this->result[$id]['city_list'] = array_values($_cityList);

//
            }
            $this->result = array_values($this->result);
            Cache::forever('provinceCityAreaStreet', json_encode($this->result));
            Cache::forever('provinceCityAreaStreetCount', $this->count);
        } else {
            $this->count = Cache::get('provinceCityAreaStreetCount');
        }
        $this->insertToDb();


    }

    public function insertToDb()
    {
        DB::beginTransaction();

        try {
            $this->line("正在插入数据库");
            $bar = $this->output->createProgressBar(count($this->result));
            foreach ($this->result as $province) {
                $provinceList[] = [
                    'id'        => $province['id'],
                    'name'      => $province['name'],
                    'parent_id' => $province['parent_id'],
                ];
                $cityList       = [];
                foreach ($province['city_list'] as $city) {
                    $cityList[] = [
                        'id'        => $city['id'],
                        'name'      => $city['name'],
                        'parent_id' => $city['parent_id'],
                    ];
                    $areaList = [];
                    foreach ($city['area_list'] as $area) {
                        $areaList[]   = [
                            'id'        => $area['id'],
                            'name'      => $area['name'],
                            'parent_id' => $area['parent_id'],
                        ];
                        $streetList = [];
                        foreach ($area['street_list'] as $street) {
                            $streetList[] = [
                                'id'        => $street['id'],
                                'name'      => $street['name'],
                                'parent_id' => $street['parent_id'],
                            ];
                        }
                        DB::table('province_city_area')->insert($streetList);
                    }
                    DB::table('province_city_area')->insert($areaList);
                }

                DB::table('province_city_area')->insert($cityList);
                $bar->advance();
            }
            DB::table('province_city_area')->insert($provinceList);
            $bar->finish();
            $this->info("数据已更新完成");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("更新省市县数据失败.");
            $this->error($e->getMessage());
            $this->error($e->getLine());
        }

        DB::commit();
    }

    public function getCity($id)
    {
        return $this->parseData($id);
    }

    public function getArea($id)
    {
        return $this->parseData($id);
    }

    public function getStreet($id)
    {
        return $this->parseData($id);
    }

    public function parseData($id)
    {
        $data = $this->getData($id);
        if ($data === false) {
            $this->error("获取数据失败.");
        } else {
            $data = $this->parseJson($data);
            if ($data === false) {
                $this->error($this->url);
                $this->error("解析数据失败.");
            } else {
                return $data;
            }

        }
    }

    public function getData($id)
    {
        $this->url    = 'https://fts.jd.com/area/get?fid=' . $id . '&callback=getAreaList_callbackF&sceneval=2';
        $this->client = is_null($this->client) ? new Client() : $this->client;
        $response     = $this->client->request('get', $this->url, $this->headers);
        if ($response->getStatusCode() == 200) {
            return $response->getBody()->getContents();
        } else {
            return false;
        }
    }

    public function parseJson($data = '')
    {
        $data = preg_replace('/^getAreaList_callback(\w)\(/', '', $data);
        $data = preg_replace('/\)$/', '', $data);

        $data = json_decode($data, true);
        if (is_array($data)) {
            return $data;
        } else {

            return false;
        }

    }

}