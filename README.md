# TL404

タイムライン生成支援の API

## 動作環境

- [Redis](http://redis.io/)
- [RabbitMQ](http://www.rabbitmq.com/)
- [PHP](http://www.php.net/) (>= 5.3)
- [jq](http://stedolan.github.io/jq/) (optional)

## インストール方法

~~~
##### Redis をインストールして立ち上げ #####
$ cd tl404
$ mkdir redis
$ wget http://download.redis.io/releases/redis-2.8.13.tar.gz
$ tar zxvf redis-2.8.13.tar.gz
$ rm *.gz
$ cd redis-2.8.13
$ make
$ ./src/redis-server --port 9736

##### RabbitMQ をインストールして立ち上げ #####
# Mac OS X での方法を以下に掲載。Linux 等その他 OS については公式ドキュメントを参照 (http://www.rabbitmq.com/download.html)
$ cd tl404
$ mkdir rabbitmq
$ cd rabbitmq/
$ wget http://www.rabbitmq.com/releases/rabbitmq-server/v3.3.4/rabbitmq-server-mac-standalone-3.3.4.tar.gz
$ tar zxvf rabbitmq-server-mac-standalone-3.3.4.tar.gz
$ rm *.gz
$ cd rabbitmq_server-3.3.4
$ ./sbin/rabbitmq-server

##### PHP の環境構築 #####
# ライブラリのインストール
$ sudo pecl install redis
# 存在しているか確認
$ $ php -m | grep redis
# amqplib のインストール (事前に composer の設定が必要)
$ cd tl404/lib
$ vi composer.json
$ cat composer.json
{
  "require": {
	"videlalvaro/php-amqplib": "2.2.*",
	"phpunit/phpunit": "4.1.*"
  }
}
$ composer.phar install

##### API を立ち上げ #####
$ cd tl404
$ cd pub
$ php -S localhost:9080

##### worker/distributer.php を立ち上げ (複数ワーカーOK) #####
$ cd tl404/worker
$ php distributer.php

##### worker/trimer.php を立ち上げ (1ワーカーを推奨。複数ワーカーは非推奨) #####
$ cd tl404/worker
$ php trimmer.php

##### テスト #####
$ cd tl404/test
$ phpunit generalTest.php 
PHPUnit 4.1.4 by Sebastian Bergmann.

..................

Time: 1.22 seconds, Memory: 4.00Mb

OK (18 tests, 39 assertions)

##### 終了 #####
# RabbitMQ の終了
$ ./sbin/rabbitmqctl stop
~~~

## API

### 一覧

API 一覧を示す。sid = Source ID, tid = Target ID, lim = Limit, tlid = Timeline ID, ofs = Offset, cnt = Count の意味である。tlid については、たとえば 0:ホームタイムライン、1:シングルタイムライン、2:お気に入りタイムライン、3:イベントタイムライン、などと割り当てることを想定している。

|method|動作|必須パラメータ|追加パラメータ|
|:-:|:-:|:-:|:-:|
|barusu|全てのデータを削除|barusu||
|subscribe|sid が tid を購読|sid, tid||
|unsubscribe|sid が tid の購読を解除|sid, tid||
|get_all_subscriptions|sid が購読している ID をすべて取得|sid||
|get_all_subscribers|sid を購読している ID をすべて取得|sid||
|get_subscriptions|sid が 購読している ID を ofs から cnt 件だけ取得|sid|ofs, cnt|
|get_subscribers|sid を 購読している ID を ofs から cnt 件だけ取得|sid|ofs, cnt|
|get_number_of_subscriptions|sid の購読数|sid||
|get_number_of_subscribers|sid の購読者数|sid||
|get_timeline|sid の tlid 番のタイムラインを取得|sid, tlid||
|is_my_subscription|tid は sid が購読しているかどうか|sid, tid||
|is_my_subscriber|tid は sid の購読者かどうか|sid, tid||
|put|sid の tlid 番のタイムラインに elm を追加|sid, tlid, elm||
|publish|sid を購読している id の tlid 番のタイムラインに elm を追加|sid, tlid, elm||
|delete_id|sid の 全てのタイムライン、購読・購読者関係を削除|sid||
|delete_tl|sid の tlid番のタイムラインを削除|sid, tlid||
|register_id|sid を登録して、掃除対象とする|sid||
|register_tlid|tlid を登録して、掃除対象とする|tlid||
|unregister_tlid|tlid の登録を解除|tlid||

### 15分ツアー

ひとまず全ての API を使ってみて全容を把握する具体例。ターミナルで以下のようにコマンドを打っていけばよい。

~~~
########## 全てのデータを削除 (barusu) ##########
$ curl -d 'method=barusu&barusu=BARUSU!!!' localhost:9080 | jq "."
{
  "result": "BARUSU!!!",
  "message": "OK",
  "code": 20000
}

########## sid:1 が tid:2,3,4,5 を購読 (subscribe) ##########
$ curl -d 'method=subscribe&sid=1&tid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=3' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=4' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=subscribe&sid=1&tid=5' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## sid:1 が tid:5 の購読をやめる (unsubscribe) ##########
$ curl -d 'method=unsubscribe&sid=1&tid=5' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## sid:1 が購読している ID をすべて取得 (get_all_subscriptions) ##########
$ curl -d 'method=get_all_subscriptions&sid=1' localhost:9080 | jq "."
{
  "result": [
    2,
    3,
    4
  ],
  "message": "OK",
  "code": 20000
}

########## sid:2 を購読している ID をすべて取得 (get_all_subscribers) ##########
$ curl -d 'method=get_all_subscribers&sid=2' localhost:9080 | jq "."
{
  "result": [
    1
  ],
  "message": "OK",
  "code": 20000
}

########## sid:1 が購読している ID の最新を高々2件を取得 (get_subscriptions) ##########
$ curl -d 'method=get_subscriptions&sid=1&ofs=0&cnt=2' localhost:9080 | jq "."
{
  "result": [
    4,
    3
  ],
  "message": "OK",
  "code": 20000
}

########## sid:2 を購読している ID の最新を高々2件取得 (get_random_subscribers) ##########
$ curl -d 'method=get_subscribers&sid=2&ofs=0&cnt=2' localhost:9080 | jq "."
{
  "result": [
    1
  ],
  "message": "OK",
  "code": 20000
}


########## id:1 の購読数 (get_number_of_subscriptions) ##########
$ curl -d 'method=get_number_of_subscriptions&sid=1' localhost:9080 | jq "."
{
  "result": 3,
  "message": "OK",
  "code": 20000
}

########## id:2 の購読者数 (get_number_of_subscribers) ##########
$ curl -d 'method=get_number_of_subscribers&sid=2' localhost:9080 | jq "."
{
  "result": 1,
  "message": "OK",
  "code": 20000
}

########## id:1 は id:2 の購読者かどうか (is_my_subscriber) ##########
$ curl -d 'method=is_my_subscriber&sid=2&tid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## tid は sid が購読しているかどうか (is_my_subscription) ##########
$ curl -d 'method=is_my_subscription&sid=1&tid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番のタイムラインに ##########
##########  elm: "uniqid:53a8f8d8e6453" を追加 (put) ##########
$ curl -d 'method=put&sid=1&tlid=1&elm=uniqid:53a8f8d8e6453' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

##########  もう一度、elm: "uniqid:53a8f8d8e6453" を追加 (put) ##########
$ curl -d 'method=put&sid=1&tlid=1&elm=uniqid:53a8f8fe0e95c' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番のタイムラインを取得 (get_timeline) ##########
########## (先の2回の put が反映されているか確認) ##########
$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": [
    "uniqid:53a8f8d8e6453",
    "uniqid:53a8f8fe0e95c"
  ],
  "message": "OK",
  "code": 20000
}

######### id:4 を購読している id群(今回は id:1 のみ) の tlid 番のタイムラインに elm を追加 (publish) ##########
$ curl -d 'method=publish&sid=4&tlid=1&elm=uniqid:53a8fc605a7cd' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番のタイムラインを取得 (get_timeline) ##########
########## (先の2回の put と1回の publish が反映されているか確認) ##########
$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": [
    "uniqid:53a8f8d8e6453",
    "uniqid:53a8f8fe0e95c",
    "uniqid:53a8fc605a7cd"
  ],
  "message": "OK",
  "code": 20000
}

######### sid の tlid番のタイムラインを削除 (delete_tl) #########
$ curl -d 'method=delete_tl&sid=1&tlid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 の tlid:1 番のタイムラインを取得 (get_timeline)	##########
########## (先の削除が反映されているか確認)	##########

$ curl -d 'method=get_timeline&sid=1&tlid=1' localhost:9080 | jq "."
{
  "message": "An empty timeline.",
  "code": 60200
}

########## id:1 の 全てのタイムライン、購読・購読者関係を削除 (delete_id) ##########
$ curl -d 'method=delete_id&sid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## id:1 は id:2 の購読者かどうか判定 (is_my_subscriber) ##########
########## (id:1 は削除されたので、購読者ではないはず) #########
$ curl -d 'method=is_my_subscriber&sid=2&tid=1' localhost:9080 | jq "."
{
  "result": false,
  "message": "OK",
  "code": 20000
}

########## sidを登録して、掃除対象にする (register された ID 一覧は現時点では取れない。削除については delete_id で反映) #########
$ curl -d 'method=register_id&sid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=register_id&sid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=register_id&sid=3' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## tlid を登録して、掃除対象に設定 ##########
$ curl -d 'method=register_tlid&tlid=0' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=register_tlid&tlid=1' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
$ curl -d 'method=register_tlid&tlid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}

########## tlid の登録を解除 ##########
$ curl -d 'method=unregister_tlid&tlid=2' localhost:9080 | jq "."
{
  "result": true,
  "message": "OK",
  "code": 20000
}
~~~

### 設定

#### アプリケーション

./lib/bootstap.php で様々な設定が可能。

|項目|説明|例 (開発)|例 (運用)|
|:-:|:-:|:-:|:-:|
|IS_DEBUG|デバッグモードかどうか。デバッグモードだと全削除コマンド(barusu)が使用可能|define('IS_DEBUG', TRUE)|define('IS_DEBUG', FALSE)|
|LIMIT_COUNT|get_subscriptions/get_subscribers で一度に取得可能な要素の数の制限|define('LIMIT_COUNT', 5)|define('LIMIT_COUNT', 100)|
|LIMIT_TL_ELMS|一つのタイムラインで保持する要素数。trimmer は、この値を見てタイムラインを定期的にトリミング|define('LIMIT_TL_ELMS', 5)|define('LIMIT_TL_ELMS', 800)|
|MAX_ELEMENT_SIZE|put や publish の elm 要素の長さの制限|define('MAX_ELEMENT_SIZE', 256)|define('MAX_ELEMENT_SIZE', 64)|
|MAX_SUBSCRIPTIONS|一つの ID が購読できる要素数|define('MAX_SUBSCRIPTIONS', 10)|define('MAX_SUBSCRIPTIONS', 1024)|
|INTERVAL_SECONDS|trimmer の実行周期|define('INTERVAL_SECONDS', 3)|define('INTERVAL_SECONDS', 3)|

#### Redis

Redis の設定次第では、メモリ使用量を3割以上削減できることがある。TL404 に関連する Redis の設定項目は以下。

- redis.conf の設定

|項目|説明|デフォルト|推奨|
|:-:|:-:|:-:|:-:|
|list-max-ziplist-entries|List が圧縮される場合の要素数の閾値|512|1024|
|list-max-ziplist-value|List が圧縮される場合の要素のバイト数の閾値|64|64|
|set-max-intset-entries|Set が圧縮される場合の要素数の閾値|512|8192|
|zset-max-ziplist-entries|Sorted Set が圧縮される場合の要素数の閾値|128|8192|
|zset-max-ziplist-value|Sorted Set が圧縮される場合の要素のバイト数の閾値|64|64|

- 設定を反映した立ち上げ

~~~
$ redis-server redis.conf
~~~

### API 詳細

それぞれの API について述べる。

#### barusu

全てのデータを削除する。危険なのでデバッグモードのときのみ動作する。さらにパラメータ「barusu」の value が「BARUSU!!!」のときに呪文がはなたれる。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|barusu|誤って実行するのを防ぐための冗長なパラメータ|BARUSU!!!|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。データは削除された|
|70100|失敗。barusu はデバッグモードでのみ有効|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|

#### subscribe

sid が tid を購読する。フォローとも言える。sid と tid が等しい場合は失敗する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|
|tid|対象のID。正の整数のみ|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が tid を購読|
|40001|失敗。パラメータが不正|
|40100|失敗。sid は すでに tid を購読済|
|40102|失敗。sid は、MAX_SUBSCRIPTIONS 以上に購読しようとした|
|59000|失敗。内部 DB でエラーが発生|

#### unsubscribe

sid が tid の購読を解除する。アンフォローとも言える。sid と tid が等しい場合は失敗する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|
|tid|対象のID。正の整数のみ|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が tid を購読解除|
|40001|失敗。パラメータが不正|
|40101|失敗。そもそも sid は tid を購読していない|
|59000|失敗。内部 DB でエラーが発生|

#### get_all_subscriptions

sid が購読している ID をすべて取得する。購読が多くなると時間がかかるので、1000件以上が予測される場合は、後述の get_subscriptions で件数を絞って取得することを推奨する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が購読している ID が配列として返却|
|40001|失敗。パラメータが不正|
|60100|失敗。sid は何も購読していない|
|59000|失敗。内部 DB でエラーが発生|

#### get_all_subscribers

sid を購読している ID をすべて取得する。購読が多くなると時間がかかるので、1000件以上が予測される場合は、後述の get_subscribers で件数を絞って取得することを推奨する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid を購読している ID が配列として返却|
|40001|失敗。パラメータが不正|
|60101|失敗。sid は誰にも購読されていない|
|59000|失敗。内部 DB でエラーが発生|

#### get_subscriptions

sid が購読している ID を ofs から cnt だけ取得する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

|追加パラメータ|説明|例|
|:-:|:-:|:-:|
|ofs|offset。何番目から取得するか。指定しなければ0番目から取得する|0|
|cnt|取得件数。指定しなければ、設定項目の LIMIT_COUNT 件だけ取得する|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid が購読している ID を、ofs 番目から cnt 件だけ返却。空集合の場合もある|
|40001|失敗。パラメータが不正|
|40003|失敗。ofs が不正|
|40004|失敗。cnt が不正|
|59000|失敗。内部 DB でエラーが発生|

#### get_subscribers

sid を購読している ID を ofs から cnt だけ取得する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

|追加パラメータ|説明|例|
|:-:|:-:|:-:|
|ofs|offset。何番目から取得するか。指定しなければ0番目から取得する|0|
|cnt|取得件数。指定しなければ、設定項目の LIMIT_COUNT 件だけ取得する|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid を購読している ID を、ofs番目からcnt件だけ返却。空集合の場合もある|
|40001|失敗。パラメータが不正|
|40003|失敗。ofs が不正|
|40004|失敗。cnt が不正|
|59000|失敗。内部DBでエラーが発生|

#### get_number_of_subscriptions

購読数を取得。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid の購読数が返却される|
|40001|失敗。パラメータが不正|
|59000|失敗。内部DBでエラーが発生|

#### get_number_of_subscribers

購読者数を取得。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。sid の購読者数が返却される|
|40001|失敗。パラメータが不正|
|59000|失敗。内部DBでエラーが発生|

#### get_timeline

タイムラインを取得

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|
|tlid|タイムラインの ID。正の整数のみ|0|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。タイムラインが返却|
|40001|失敗。パラメータが不正|
|60200|失敗。タイムラインが空|
|59000|失敗。内部DBでエラーが発生|



#### is_my_subscription

tid は sid が購読しているかどうか

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|
|tid|対象のID。正の整数のみ|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。返却値が TRUE ならば購読している、FALSE ならしていない|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|


#### is_my_subscriber

tid は sid の購読者かどうか

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となるID。正の整数のみ|1|
|tid|対象のID。正の整数のみ|2|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。返却値が TRUE ならば購読者、FALSE ならそうではない|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|

#### put

sid の tlid 番のタイムラインに elm を追加。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となる ID。正の整数のみ|1|
|tlid|タイムラインの ID。正の整数のみ|0|
|elm|挿入する要素。MAX_ELEMENT_SIZE で指定された長さ以下であること|"Hello"|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。挿入された|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|

#### publish

sid を購読している id の tlid 番のタイムラインに elm を追加。put と異なる点は、put は sid と tlid で指定されたタイムライン一つだけに要素を挿入するのに対して、publish は sid の購読者全員の tlid のタイムラインに要素を挿入する。また、publish は非同期で実行される。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となる ID。正の整数のみ|1|
|tlid|タイムラインの ID。正の整数のみ|0|
|elm|挿入する要素。MAX_ELEMENT_SIZE で指定された長さ以下であること|"Hello"|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。キューに格納され、後に実行される|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|

#### delete_id

sid の全てのタイムライン、購読・購読者関係を削除。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となる ID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。削除された|
|40001|失敗。パラメータが不正|
|59000|失敗。内部 DB でエラーが発生|

#### delete_tl

sid の tlid番のタイムラインを削除。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となる ID。正の整数のみ|1|
|tlid|タイムラインの ID。正の整数のみ|0|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。削除された|
|40001|失敗。パラメータが不正|
|60201|失敗。そのタイムラインは存在しない|
|59000|失敗。内部 DB でエラーが発生|

#### register_id

sid を登録して、trimmer の掃除対象とする。登録しないと掃除対象とならず、タイムラインが肥大化してメモリを圧迫する。しかしメモリ限界までは正常に動作する。

推奨としては、新しく id が加わるとき (新規ユーザー作成時など) に、このメソッドを実行して登録することである。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|sid|基準となる ID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。登録された|
|40001|失敗。パラメータが不正|
|40200|失敗。既にその sid は登録済|
|59000|失敗。内部 DB でエラーが発生|

#### register_tlid

tlid を登録して、trimmer の掃除対象とする。登録しなかった場合、trimmer の掃除対象とはならず、メモリが肥大化する。しかしメモリ限界までは正常に動作する。

冒頭でも述べたように、tlid は、たとえば 0:シングルタイムライン、1:マルチタイムライン、2:お気に入りタイムライン、3:イベントタイムライン、などとして使う。サービス提供者が任意で tlid を設定することで、新たなタイムラインを構築できる。新しく tlid を割り当てる際は、このメソッドで登録すると、掃除対象となってメモリ効率がよくなる。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|tlid|タイムラインの ID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。登録された|
|40001|失敗。パラメータが不正|
|40300|失敗。既にその tlid は登録済|
|59000|失敗。内部 DB でエラーが発生|

#### unregister_tlid

tlid の登録を解除。先の register_tlid で登録された tlid を登録解消する。

- send

|パラメータ|説明|例|
|:-:|:-:|:-:|
|tlid|タイムラインの ID。正の整数のみ|1|

- receive

|返却値|説明|
|:-:|:-:|
|20000|正常。登録解除された|
|40001|失敗。パラメータが不正|
|40301|失敗。その tlid は登録されていない|
|59000|失敗。内部 DB でエラーが発生|

## ログ

- 2014-06-24
  - このプロジェクトを作成開始
- 2014-07-28
  - DB との入出力を DBH.php で一元化
  - 購読者の保存を、Redis の Set から Sorted Set に変更 (時系列で取得出来るように)
  - random で取得できる系のメソッドを廃止し、任意の場所から任意の数だけ取れるメソッドを用意
  - RabbitMQ と連携した。worker も作成済
  - ドキュメントを改善
  - テストを整備
  - プロジェクト名を TL404 に決定。以前は mute city という名。
- 2014-07-29
  - タイムラインの要素数を監視する、trimmer を作成した
  - タイムラインの trimer.php のために、ID と TLID を登録できるようにした。登録したものは掃除される。
  - ドキュメントを大きく拡充した
  - 購読数の制限を設けた
