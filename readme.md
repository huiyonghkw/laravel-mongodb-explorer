# Laravel中使用MongoDB

## MongoDB实用场景

+ 产品用户访问日志，点击埋点统计信息
+ 业务系统环境参数配置信息
+ 业务系统运行时日志，如`laravel.log`，`nginx.log`


## 使用Homebrew在macoOS安装MongoDB PHP Driver 


在macOS中，MongoDB 扩展已经从Homebrew仓库中移除，需要通过pecl安装此扩展。

```bash
$ sudo pecl install mongodb -v
...

Build process completed successfully
Installing '/usr/local/Cellar/php@7.2/7.2.19/pecl/20170718/mongodb.so'
install ok: channel://pecl.php.net/mongodb-1.5.4
Extension mongodb enabled in php.ini
```

在项目中，使用`phpinfo()` 查询PHP扩展安装位置。

```bash
...
Configuration File (php.ini) Path   /usr/local/etc/php/7.2
Loaded Configuration File   /usr/local/etc/php/7.2/php.ini
Scan this dir for additional .ini files /usr/local/etc/php/7.2/conf.d
Additional .ini files parsed    /usr/local/etc/php/7.2/conf.d/ext-opcache.ini, /usr/local/etc/php/7.2/conf.d/php-memory-limits.ini
....
```

按照`ext-opcache.ini`配置，创建一个`ext-mongodb.ini`文件

```bash
touch /usr/local/etc/php/7.2/conf.d/ext-mongodb.ini
```

将`mongodb.so`扩展写入该文件

```bash
 [mongodb]
 extension=/usr/local/Cellar/php@7.2/7.2.19/pecl/20170718/mongodb.so
```

同时在`php.ini`中移除`mongodb.so`扩展

```bash
extension="mongodb.so" // remove
extension="php_mongodb.so" // remove 
```

重启一下PHP

```bash
sudo brew service restart --all
```

查看是否安装成功

```php
php -m|grep mongodb
```



## 在Laravel中使用MongoDB

使用`Composer`创建一个Laravel项目

```php
 composer create-project --prefer-dist laravel/laravel laravel-mongodb-exploer -vvv
```

成功后，再安装Laravel-MongoDB扩展

```php
composer require jenssegers/mongodb -vvv
```

按照扩展文档说明，我们添加一个MongoDB数据库连接

```php
//database.php
...
'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGODB_HOST', 'localhost'),
            'port'     => env('MONGODB_PORT', 27017),
            'database' => env('MONGODB_DATABASE'),
            'username' => env('MONGODB_USERNAME'),
            'password' => env('MONGODB_PASSWORD'),
            'options'  => [
                'database' => 'admin' // sets the authentication database required by mongo 3
            ]
        ],
...
  
  
//.env
...  
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=viewers
...
```

## 命令行创建MongoDB数据库

macOS中，在命令行执行`mongo`开启MongoDB Shell

```sh
./mongo
```

使用`show dbs`查看已有数据库

```sh
show dbs;

admin    0.000GB
config   0.000GB
local    0.000GB
viewers  0.000GB
```

如果没有发现`viewers`，则创建该数据库。注意只有`viewers`中存在`collection`时， 上面结果才会显示`viewers`

```sh
use viewers;
```

使用数据库后，需要创建`colleciton`

```sql
db.ad_clicks.insert({"ip":"201.35.63.14", "ad_index": 3, "created_at": "2019-06-10 11:34:12"})
```

使用`find`查询记录

```sh
> db.ad_clicks.find()
{ "_id" : ObjectId("5cf71b34e14620598643d23b"), "ip" : "201.34.46.3", "ad_index" : "2", "created_at" : "2019-06-05 11:34:53" }
{ "_id" : ObjectId("5cf71d3de14620598643d23d"), "ip" : "200.14.145.64", "ad_index" : 1, "created_at" : "2019-06-04 11:11:45" }
{ "_id" : ObjectId("5cf71d3ee14620598643d23e"), "ip" : "200.14.145.64", "ad_index" : 1, "created_at" : "2019-06-04 11:11:45" }
{ "_id" : ObjectId("5cf71d44e14620598643d23f"), "ip" : "200.14.145.64", "ad_index" : 1, "created_at" : "2019-06-04 11:11:45" }
{ "_id" : ObjectId("5cf71d45e14620598643d240"), "ip" : "200.14.145.64", "ad_index" : 1, "created_at" : "2019-06-04 12:34:12" }
{ "_id" : ObjectId("5cfe28823316506991c41786"), "ip" : "201.35.63.14", "ad_index" : 3, "created_at" : "2019-06-10 11:34:12" }
```

## 在Laravel DB中查询MongoDB

使用了Laravel-MongoDB扩展，可以基于Eloquent与Query Builder操作MySQL一样的数据`php artisan thinker`

查询`ad_clicks`集合所有记录

```php
DB::connection('mongodb')->table('ad_clicks')->get()
```

查询单个记录

```php
DB::connection('mongodb')->collection('ad_clicks')->find('5cf71b34e14620598643d23b')
```

修改某个记录

```php
DB::connection('mongodb')->collection('ad_clicks')->where('_id', '5cf71b34e14620598643d23b')->update(['ad_index'=>2]);
```

## 在Laravel ORM中查询MongoDB

在项目中，创建一个Model

```php
php artisan make:model Models/AdClick
```

修改继承父类和数据库连接，AdClick.php

```php
...
use Jenssegers\Mongodb\Eloquent\Model;

class AdClick extends Model
{
    protected $connection = 'mongodb';
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
```

继续在Thinker中，插入数据

```php
App\Models\AdClick::create(['ip' => '31.42.4.14', 'ad_index' => 4, 'created_at' => '2019-06-10 18:10:01', 'ip2long' => ip2long('31.42.4.14')]);
```

统计访问数据

```php
App\Models\AdClick::where('ip', '31.42.4.14')->count()
```


