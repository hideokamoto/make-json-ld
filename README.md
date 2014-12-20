Make JSON-LD for WordPress With Custom Fields
============

#このプラグインについて
このプラグインでは、WordPressのカスタムフィールドを用いてJSON-LDファイルを作成することができます。

#インストール方法
ZIPファイルをDLし、WordPressのプラグインとしてインストールさせてください。  
有効化することでJSON-LDファイルを出力するようになります。

#JSON-LDのファイルパス
- http://[ドメイン]/json/：全記事分のJSON-LD
- http://[ドメイン]/json/?max=5：5記事分のJSON-LD
- http://[ドメイン]/[固定ページ・投稿パーマリンク]/json/：該当記事のJSON-LD

#使用可能な語彙
[Yokohama Art Navi 場所LOD](http://fp.yafjp.org/yokohama_art_lod/place_rdf)と同じです。

#使用するには
カスタムフィールドのフィールド名を「語彙:タイプ」に設定してください。

例：schemaのnameを使う->フィールド名「schema:name」
