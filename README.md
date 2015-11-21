OpenData Generator v2.0alpha
============

#このプラグインについて
このプラグインでは、WordPressのカスタムフィールドを用いてJSON-LDファイルを作成することができます。

#インストール方法
ZIPファイルをDLし、WordPressのプラグインとしてインストールさせてください。  
有効化することでJSON-LDファイルを出力するようになります。

#JSON-LDのファイルパス
- http://[ドメイン]/json-ld/：全記事分のJSON-LD
- http://[ドメイン]/[カテゴリー名]/json-ld/：該当カテゴリ全記事分のJSON-LD
- http://[ドメイン]/[固定ページ・投稿パーマリンク]/json-ld/：該当記事のJSON-LD

#記事の絞り込み方法について

WP-APIに準拠した記事の絞込が行えます。

例：/?filter[パラメータ１]=値&filter[パラメータ２]=値

##利用可能なパラメータ

パラメータの詳細は[Codex](http://wpdocs.osdn.jp/%E9%96%A2%E6%95%B0%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/WP_Query)を参照してください。

- m
- p
- posts
- w
- cat
- withcomments
- withoutcomments
- s
- search
- exact
- sentence
- calendar
- page
- paged
- more
- tb
- pb
- author
- order
- orderby
- year
- monthnum
- day
- hour
- minute
- second
- name
- category_name
- tag
- feed
- author_name
- static
- pagename
- page_id
- error
- comments_popup
- attachment
- attachment_id
- subpost
- subpost_id
- preview
- robots
- taxonomy
- term
- cpage
- post_type
- posts_per_page

#使用可能な語彙
デフォルトでは「[Schema.org](http://schema.org/)」のみ設定されています。

[Yokohama Art Navi 場所LOD](http://fp.yafjp.org/yokohama_art_lod/place_rdf)などを参考にしてください。

#使用するには
カスタムフィールドのフィールド名を「語彙:タイプ」に設定してください。

例：schemaのnameを使う->フィールド名「schema:name」

#ライセンス
GPLです。  
LICENSE.mdをご覧ください。

#UpdateLog
- 2.0 リメイク（v1との後方互換性なし）
- 1.5 WP_Queryのparamに対応
