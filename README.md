# Laravel-Province-City-Area   全国省市县数据(数据来源于京东，内置爬虫，可自行获取最新数据)

这是一个提供全国省市县数据的轮子

试过了很多数据来源(国家统计局、网上其他开发者提供的json等),发现还是会有小部分省市有遗漏。

这个包的数据是**来自于京东**，相对来说会更准确、详尽一些。


> 内部自带京东省市县数据爬虫，用户可以自行运行爬取最新的省市县数据


 
 

#### 添加provider（laravel 版本 < 5.5）
将`Aoxiang\Pca\ProvinceCityAreaServiceProvider::class`复制到`config.php`内`providers`数组内

#### 生成数据库迁移文件：

```
php artisan vendor:publish --provider="Aoxiang\Pca\ProvinceCityAreaServiceProvider" --tag="migrations"
```

#### 执行数据库迁移
```
php artisan migrate
```

#### 最后一步:从京东获取新的省市县数据
```
php artisan pca:refreshData
```

```
获取数据成功:新疆阿勒泰地区
获取数据成功:新疆五家渠市
获取数据成功:新疆阿拉尔市
获取数据成功:新疆图木舒克市
获取数据成功:新疆铁门关市
获取数据成功:新疆昆玉市
获取数据成功:台湾台湾
获取数据成功:钓鱼岛钓鱼岛
获取数据成功:港澳香港特别行政区
获取数据成功:港澳澳门特别行政区
正在插入数据库
 5252/5662 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░]  92%

执行完成就可以用了
```

####  Demo

```php
<?php

namespace App\Http\Controllers;

use Aoxiang\Pca\ProvinceCityArea;
use Illuminate\Routing\Controller as BaseController;

class Controller  extends BaseController{
    public function getProvinceList()
    {
        return response()->json(ProvinceCityArea::getProvinceList());
    }
    
    public function getCityList()
    {
        ProvinceCityArea::getCityList(1);
    }
    
    public function test()
    {
        echo ProvinceCityArea::getName(21, 1827, 40847);
        //echo "江西南昌市红谷滩新区";
    }
}

```


