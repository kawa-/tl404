* ToDo

- Docker的な簡単デプロイ [2014-06-23]
- スケールするように [2014-06-23]
- 初期設定画面 [2014-06-23]
- WebUI [2014-06-23]
- 性能計測 [2014-06-23]
- get_registered_idsとかget_registered_tlidsとか(allや一部も) [2014-07-29]
- selective_publishがあるといいかも。$ curl -d 'method=spublish&sid=1&tid=2,3,4&tlid=0&elm=THIS IS AN ADD!' localhost:9080 みたいな感じ。[2014-07-29]
- workerたちのlog問題がある。fluentdなどにする？[2014-07-29]
- block/mute 機能を入れたいところ [2014-07-30]
- タイムライン取得時、空だとエラーになるが、一方で購読者を取得するときに空でもエラーにならず空集合が返ってくる。どうしましょう。[2014-07-31]
- delete_id、idの存在確認をしないために何度実行しても常にtrueである。これはバグぽい。直さねば。[2014-07-31]

** Done

- [2014-07-28] distributeを非同期に [2014-06-23]
- [2014-07-28] MessageQueue とワーカー [2014-06-23]
- [2014-07-29] お掃除するひと [2014-06-23]