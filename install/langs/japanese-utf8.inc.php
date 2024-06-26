<?php
/**
 * MODX language File
 *
 * @author MEGU, yamamoto, TxO
 * @package MODX
 * @subpackage installer_translations
 * @version 1.0.7J-r1
 *
 * Filename:       /install/lang/japanese-utf8/japanese-utf8.inc.php
 * Language:       Japanese
 * Encoding:       utf-8
 */
$_lang["agree_to_terms"] = 'ライセンスが規定する諸条件を確認しインストールを実行してください';
$_lang["alert_database_test_connection"] = 'データベースを作成、またはデータベースの接続テストを行う必要があります';
$_lang["alert_database_test_connection_failed"] = 'データベースに接続できません。';
$_lang["alert_enter_adminconfirm"] = '管理者パスワードと確認パスワードが一致しません。';
$_lang["alert_enter_adminlogin"] = '管理者のユーザー名を入力してください';
$_lang["alert_enter_adminpassword"] = '管理者のパスワードを入力してください';
$_lang["alert_enter_database_name"] = 'データベース名を入力してください';
$_lang["alert_enter_host"] = 'データベースサーバのホスト名を入力してください';
$_lang["alert_enter_login"] = 'データベースのユーザー名を入力してください';
$_lang["alert_server_test_connection"] = 'サーバー接続をテストしてください';
$_lang["alert_server_test_connection_failed"] = 'サーバー接続テストが失敗しました';
$_lang["alert_table_prefixes"] = 'テーブルのプレフィックスはアルファベットのみ使えます。';
$_lang["all"] = '全て選択';
$_lang["and_try_again"] = 'これらのエラーを修正し、右下の「再チェック」ボタンをクリックしてください。';
$_lang["and_try_again_plural"] = 'これらのエラーを修正し、右下の「再チェック」ボタンをクリックしてください。'; //Plural form
$_lang["begin"] = '開始';
$_lang["btnback_value"] = '戻る';
$_lang["btnclose_value"] = 'インストール終了';
$_lang["btnnext_value"] = '進む';
$_lang["cant_write_config_file"] = '設定ファイルを生成できませんでした。以下をコピーしてconfig.inc.phpに反映してください ';
$_lang["cant_write_config_file_note"] = '実行後は、サイト名/manager/ にアクセスすることで管理画面にログインできます。';
$_lang["checkbox_select_options"] = '拡張機能の選択:';
$_lang["checking_if_cache_exist"] = '<span class="mono">/temp/cache</span>ディレクトリの存在チェック(なければ転送に失敗しています): ';
$_lang["checking_if_cache_file_writable"] = '<span class="mono">/temp/cache/siteCache.idx.php</span>の書き込み属性: ';
$_lang["checking_if_cache_file2_writable"] = '<span class="mono">/temp/cache/basicConfig.php</span>の書き込み属性: ';
$_lang["checking_if_cache_writable"] = '<span class="mono">/temp/cache</span>ディレクトリの書き込み属性: ';
$_lang["checking_if_config_exist_and_writable"] = '<span class="mono">/manager/includes/config.inc.php</span>の存在と書き込み属性: ';
$_lang["checking_if_export_exists"] = '<span class="mono">/temp/export</span>ディレクトリの存在(なければ転送に失敗しています): ';
$_lang["checking_if_export_writable"] = '<span class="mono">/temp/export</span>ディレクトリの書き込み属性: ';
$_lang["checking_if_images_exist"] = '<span class="mono">/content/images</span>,<span class="mono">/content/files</span>,<span class="mono">/content/media</span>ディレクトリの存在(なければ転送に失敗しています): ';
$_lang["checking_if_images_writable"] = '<span class="mono">/content/images</span>,<span class="mono">/content/files</span>,<span class="mono">/content/media</span>ディレクトリの書き込み属性: ';
$_lang["checking_sql_version"] = 'SQLのバージョン: ';
$_lang["checking_php_version"] = 'PHPのバージョンチェック: ';
$_lang["checking_sessions"] = 'セッション情報が正常に構成されるかどうか: ';
$_lang["checking_table_prefix"] = 'Tableプレフィックスの設定 `';
$_lang["chunks"] = 'チャンク';
$_lang["config_permissions_note"] = '<span class="mono">config.inc.php</span>という名前の空ファイルを作って<span class="mono">/manager/includes/</span>ディレクトリに転送するか、すでに転送済みのconfig.inc.php.blankをリネームするなどし、パーミッションを606などに設定してください。';
$_lang["config_permissions_upg_note"] = '<span class="mono">/manager/includes/config.inc.php</span>のパーミッションを書き込み可能(606など)に設定してください。';
$_lang["connection_screen_collation"] = 'コレーション(照合順序):';
$_lang["connection_screen_connection_method"] = '接続時の文字セットの扱い:';
$_lang["connection_screen_database_connection_information"] = '利用するデータベースとTableプレフィックスの設定';
$_lang["connection_screen_database_connection_note"] = 'データベース名を入力してください。Tableプレフィックスを変更すると、ひとつのデータベースで複数のMODXを運用できます。';
$_lang["connection_screen_database_host"] = 'データベースホスト名:';
$_lang["connection_screen_database_info"] = 'データベース設定';
$_lang["connection_screen_database_login"] = 'データベース接続ログイン名:';
$_lang["connection_screen_database_name"] = 'データベース名:';
$_lang["connection_screen_database_pass"] = 'データベース接続パスワード:';
$_lang["connection_screen_database_test_connection"] = 'ここをクリックしてデータベースのテストを行なってください。（※権限がある場合はこの操作でデータベースを作成します）';
$_lang["connection_screen_default_admin_email"] = 'メールアドレス:';
$_lang["connection_screen_default_admin_login"] = 'ログイン名(半角英数字):';
$_lang["connection_screen_default_admin_note"] = 'デフォルトの管理アカウントを作成します。';
$_lang["connection_screen_default_admin_password"] = 'パスワード:';
$_lang["connection_screen_default_admin_password_confirm"] = 'パスワード(確認入力):';
$_lang["connection_screen_default_admin_user"] = 'デフォルトの管理アカウント作成';
$_lang["connection_screen_defaults"] = '管理アカウントの初期設定';
$_lang["connection_screen_server_connection_information"] = 'データベースホストへの接続';
$_lang["connection_screen_server_connection_note"] = 'ポート番号を指定する場合は「ホスト名:ポート番号」としてください(例：localhost:3306)。';
$_lang["connection_screen_server_test_connection"] = 'ここをクリックして、接続テストを行ってください。';
$_lang["connection_screen_table_prefix"] = 'Tableプレフィックス:';
$_lang["creating_database_connection"] = 'データベース接続: ';
$_lang["database_alerts"] = 'データベースの警告';
$_lang["database_connection_failed"] = 'データベース接続に異常があります';
$_lang["database_connection_failed_note"] = 'データベースのログイン設定を確認し、再びチェックを試してください。';
$_lang["database_use_failed"] = 'データベースを選択できません。データベース名を確認してください。';
$_lang["database_use_failed_note"] = 'データベースのユーザー権限を確認してください。';
$_lang["default_language"] = '管理画面で使用する言語';
$_lang["default_language_description"] = '管理画面で使用する言語を選択してください。(※Japanese-eucを選択するとプリインストールされるサンプルコンテンツやアドオンの説明文は文字化けしますのでご注意ください。アドオンの機能は利用できます。)';
$_lang["during_execution_of_sql"] = ' during the execution of SQL statement ';
$_lang["encoding"] = 'utf-8'; //charset encoding for html header
$_lang["error"] = 'エラー';
$_lang["errors"] = 'エラー';
$_lang["failed"] = '確認してください';
$_lang["help"] = 'Help!';
$_lang["help_link_new"] = 'http://modx.jp/docs/install.html';
$_lang["help_link_upd"] = 'http://modx.jp/docs/update.html';
$_lang["help_title"] = 'インストールで困ったらこのページを見てください';
$_lang["iagree_box"] = '<b><a href="license.txt" target="_blank">このライセンス(GPL2)</a>で規定される諸条件に同意します。</b></p><p><a href="http://www.opensource.jp/gpl/gpl.ja.html" target="_blank">GPL2ライセンスの日本語訳はこちらにあります。</a>この翻訳には法的効力はないため、法的検証が必要な場合は英語の原文をご確認ください。';
$_lang["install"] = 'インストール';
$_lang["install_overwrite"] = 'インストール - ';
$_lang["install_results"] = 'インストールを実行しました。';
$_lang["install_update"] = '';
$_lang["installation_error_occured"] = '<span style="font-weight:bold;color:red;">インストール中に以下のエラーが発生しました。</span>';
$_lang["installation_install_new_copy"] = '新規インストール - ';
$_lang["installation_install_new_note"] = 'MODXを新規インストールします。';
$_lang["installation_mode"] = 'インストールの選択';
$_lang["installation_new_installation"] = '新規インストール';
$_lang["installation_note"] = '<strong>はじめに:</strong>管理画面にログインできたら、まずは管理画面右上のヘルプをご確認ください。';
$_lang["installation_successful"] = '<span style="color:#080;font-size:22px;">インストールは無事に成功しました。</span>';
$_lang["installation_upgrade_advanced"] = 'カスタムアップデート<br /><small>(データベース設定をアップデートできます)</small>';
$_lang["installation_upgrade_advanced_note"] = 'データベースを変更した場合などにこのオプションを選択してconfig.inc.phpを更新してください。';
$_lang["installation_upgrade_existing"] = 'アップデート';
$_lang["installation_upgrade_existing_note"] = 'データベースをアップデートします。作業を始める前に<a href="http://modx.jp/docs/update.html" target="_blank">手順・要点</a>をチェック！<br /><b style="color:red;">【注意】</b>データベースのバックアップはお済みですか？まだの場合は管理画面「バックアップ・リストア」でスナップショットを追加しておくことをおすすめします。';
$_lang["installed"] = 'インストールしました';
$_lang["installing_demo_site"] = 'サンプルサイトのインストール: ';
$_lang["language_code"] = 'ja';
$_lang["loading"] = '処理中...';
$_lang["modules"] = 'モジュール';
$_lang["modx_footer1"] = '&copy; 2005-[+year+] the <a href="http://modx.com/" target="_blank">MODX</a> Content Management Framework (CMF) project. All rights reserved. MODX is licensed under the GNU GPL.';
$_lang["modx_footer2"] = 'MODX is free software.  We encourage you to be creative and make use of MODX in any way you see fit. Just make sure that if you do make changes and decide to redistribute your modified MODX, that you keep the source code free!';
$_lang["modx_install"] = 'MODX &raquo; インストール';
$_lang["modx_requires_php"] = ', PHP 5.3.0以上が必要です。';
$_lang["sql_version_is"] = ' Version ';
$_lang["no"] = 'いいえ';
$_lang["none"] = '全ての選択を解除';
$_lang["not_found"] = '見つかりません';
$_lang["ok"] = '問題なし';
$_lang["optional_items"] = 'インストールオプションの選択';
$_lang["optional_items_new_note"] = '<b>オプションを選択してください</b><br />初めてMODXを試す場合は、全てチェックを入れましょう。';
$_lang["optional_items_upd_note"] = '<b>オプションを選択してください</b><br /><b>【アップデート時の注意】</b> プラグインに関しては、インストール時に既存プラグインを無効にします。';
$_lang["php_security_notice"] = '<legend>セキュリティ警告</legend><p>このサーバ上で稼働しているPHPにはセキュリティ上の問題があります。バージョン4.4.8より古いPHPは深刻な脆弱性を抱えており、MODXの稼働自体には問題はありませんが、この機会にPHPのアップデートをおすすめします。</p>';
$_lang["please_correct_error"] = 'があります。';
$_lang["please_correct_errors"] = 'があります。';
$_lang["plugins"] = 'プラグイン';
$_lang["preinstall_validation"] = 'インストール前の状態確認';
$_lang["remove_install_folder_auto"] = 'インストールディレクトリを自動的に削除する<br />※この操作はサーバ設定によっては実行されないことがあります。<br />削除できなかった場合は、管理画面ログイン時に太文字で警告が表示されますので、手作業で削除してください。';
$_lang["remove_install_folder_manual"] = '管理画面にログインする前に、&quot;<b>install</b>&quot; フォルダを必ず削除してください。';
$_lang["retry"] = '再チェック';
$_lang["running_database_updates"] = '実行中のデータベースのアップデート: ';
$_lang["sample_web_site"] = 'サンプルサイト';
$_lang["sample_web_site_note"] = '<span style="font-style:normal;">簡易なテンプレート・新着情報一覧・ナビゲーション・パン屑リスト・問い合わせフォームの実装サンプルが含まれています。</span>';
$_lang["session_problem"] = 'サーバーのセッション設定に問題があります。';
$_lang["session_problem_try_again"] = '問題を修正できたら再試行';
$_lang["setup_cannot_continue"] = '問題点があるため、セットアップを継続できません。';
$_lang["setup_couldnt_install"] = '選択されたテーブルをインストール/変更できませんでした。';
$_lang["setup_database"] = 'セットアップ結果<br />';
$_lang["setup_database_create_connection"] = 'データベース接続: ';
$_lang["setup_database_create_connection_failed"] = 'データベース接続に失敗しました!';
$_lang["setup_database_create_connection_failed_note"] = 'データベースのログイン情報を確認して再試行してください。';
$_lang["setup_database_creating_tables"] = '必要なテーブルの作成: ';
$_lang["setup_database_creation"] = 'Creating database `';
$_lang["setup_database_creation_failed"] = 'データベース作成に失敗しました';
$_lang["setup_database_creation_failed_note"] = ' - データベースを作成できませんでした';
$_lang["setup_database_creation_failed_note2"] = '指定の名前のデータベースが見つからなかったためデータベースの作成を試みましたが、作成できませんでした。ホスティング会社がデータベースの作成を許可していないようです。ホスティング会社の手順に従ってデータベースを作成し、セットアップを再開してください。';
$_lang["setup_database_selection"] = 'データベース選択 `';
$_lang["setup_database_selection_failed"] = 'データベース選択が失敗しました';
$_lang["setup_database_selection_failed_note"] = 'データベースが存在しません。データベースの作成を試行します。';
$_lang["snippets"] = 'スニペット';
$_lang["some_tables_not_updated"] = 'いくつかのテーブルはアップデートされませんでした。修正などに起因しているようです。';
$_lang["status_checking_database"] = 'データベースとTableプレフィックスのチェック: ';
$_lang["status_connecting"] = ' データベースホストとの接続テストの結果: ';
$_lang["status_failed"] = '接続できません';
$_lang["status_failed_could_not_create_database"] = '存在しないデータベースです。またはデータベースを作成できません';
$_lang["status_failed_database_collation_does_not_match"] = '問題があります - データベース側の照合順序のデフォルト値が「%s」になっています。phpMyAdminが利用できる場合は、該当データベースの「操作」タブで照合順序のデフォルト値を変更してください。';
$_lang["status_failed_table_prefix_already_in_use"] = 'Tableプレフィックスが重複しています。既存のTableを削除するか、異なるTableプレフィックスを指定してください。';
$_lang["status_passed"] = '問題ありません';
$_lang["status_passed_database_created"] = 'データベースを作成しました。';
$_lang["status_passed_server"] = '接続できます';
$_lang["summary_setup_check"] = '<strong>インストール実行前の最終チェックです。</strong>';
$_lang["table_prefix_already_inuse"] = ' - このTableプレフィックスはすでに使われています。';
$_lang["table_prefix_already_inuse_note"] = '異なるテーブルプレフィックスを指定するか、phpMyAdminなどを利用し関連テーブルを削除し、再びインストールを試してみてください。';
$_lang["table_prefix_not_exist"] = ' - 指定されたTableプレフィックスがデータベース内に存在していなかったため、インストールが完了しませんでした。正しいテーブルプレフィックスを指定し、再度実行してください。';
$_lang["templates"] = 'テンプレート';
$_lang["to_log_into_content_manager"] = 'おつかれさまでした。「インストール終了」ボタンをクリックすると、管理画面のログインページ(manager/index.php)にアクセスします。';
$_lang["toggle"] = '選択状態を反転';
$_lang['tvs'] = 'テンプレート変数';
$_lang["unable_install_chunk"] = 'チャンクをインストールできません';
$_lang["unable_install_module"] = 'モジュールをインストールできません';
$_lang["unable_install_plugin"] = 'プラグインをインストールできません';
$_lang["unable_install_snippet"] = 'スニペットをインストールできません';
$_lang["unable_install_template"] = 'テンプレートをインストールできません';
$_lang["upgrade_note"] = '<strong>注意:</strong>管理画面に無事にログインできたら、リソースおよび各種設定を日本語を含めて編集・保存し、文字化けが起きないかどうかを確認してください。また管理画面内の「イベントログ」を開き、エラーの有無をご確認ください。';
$_lang["upgraded"] = 'アップデートしました';
$_lang["visit_forum"] = '';
$_lang["warning"] = '注意 ';
$_lang["welcome_message_start"] = '';
$_lang["welcome_message_text"] = 'MODXのインストールは簡単。インストーラの説明に従って進めてください。';
$_lang["welcome_message_welcome"] = 'MODXのインストールを開始します。';
$_lang["writing_config_file"] = 'config.inc.phpへの書き込み(設定情報): ';
$_lang["yes"] = 'はい';
$_lang["you_running_php"] = ' - You are running on PHP ';

$_lang['checking_if_backup_exists'] = '<span class="mono">/temp/backup</span>ディレクトリの存在(なければ転送に失敗しています): ';
$_lang['checking_if_backup_writable'] = '<span class="mono">/temp/backup</span>ディレクトリの書き込み属性: ';
$_lang['no_update_options'] = 'アップデート対象のオプションはありません。config.inc.phpの「$lastInstallTime」のみを更新します。';

$_lang['checking_if_content_exists'] = '<span class="mono">/content</span>ディレクトリの存在(なければ転送に失敗しています): ';
$_lang['checking_if_content_writable'] = '<span class="mono">/content</span>ディレクトリの書き込み属性: ';

$_lang['checking_if_temp_exists'] = '<span class="mono">/temp</span>ディレクトリの存在(なければ転送に失敗しています): ';
$_lang['checking_if_temp_writable'] = '<span class="mono">/temp</span>ディレクトリの書き込み属性: ';

$_lang["welcome_message_upd_text"] = 'MODXのアップデートは簡単。インストーラの説明に従って進めてください。';
$_lang["welcome_message_upd_welcome"] = 'MODXのアップデートを開始します。';
$_lang["begin_install_msg"] = '<p>MODXがインストールされていないか設定ファイルが見つかりません。</p><p>今すぐインストールしますか？</p>';
