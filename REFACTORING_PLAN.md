# Refactoring Plan

## 目的

- AI 関連機能の保守性を上げる
- 画面とロジックの責務を分離する
- 不具合が起きたときの調査コストを下げる
- 今後の機能追加で同じような重複を増やさない

## 現状の主な課題

### 1. `Map/Index.vue` に責務が集中している

- 地図描画
- 都道府県選択
- AI プランナーのチャット UI
- メニュー表示制御
- JSON パース
- プラン保存
- ポーリング処理

上記が 1 ファイルに入っており、変更の影響範囲が広い。

### 2. AI 関連の責務分離がまだ不十分

OpenAI 呼び出しの共通化は `OpenAiClient` に寄せたが、以下はまだまとまり切っていない。

- プロンプト構築
- AI レスポンスのバリデーション
- 旅行提案生成フロー
- 要約生成フロー
- AI プランナーの会話履歴処理

### 3. コントローラが太い

特に以下のコントローラが、入力処理と業務ロジックを同時に持っている。

- `app/Http/Controllers/SuggestionController.php`
- `app/Http/Controllers/AiSummaryController.php`
- `app/Http/Controllers/AiPlannerController.php`

### 4. テストの追加余地が大きい

AI 周りの処理は分岐が多い一方で、今後のリファクタリングを支えるテストが不足している可能性が高い。

## リファクタリング方針

### 方針 1. UI とロジックを分ける

- Vue 側は「表示」と「ユーザー操作」に集中させる
- API 通信、ポーリング、メニュー位置制御などは composable に分離する

### 方針 2. AI 機能は用途ごとに service / action を分ける

- 旅行提案生成
- 旅行要約生成
- AI プランナーチャット

各用途で入力、プロンプト、レスポンス整形を分離する。

### 方針 3. コントローラは薄くする

- バリデーション
- 認可
- Action 呼び出し
- レスポンス返却

上記に責務を限定する。

### 方針 4. 先に土台を整えてから大きい分割に入る

いきなり巨大ファイルを切り刻まず、まず共通化しやすい箇所から進める。

## 実施フェーズ

## Phase 1: AI バックエンドの責務整理

### 目的

AI 関連の重複ロジックを減らし、今後の修正点を限定する。

### 対象

- `app/Http/Controllers/SuggestionController.php`
- `app/Http/Controllers/AiSummaryController.php`
- `app/Http/Controllers/AiPlannerController.php`
- `app/Services/AiPlannerService.php`
- `app/Services/AiChatService.php`
- `app/Services/OpenAiClient.php`
- `app/Support/TrixContentCleaner.php`

### 実施内容

- `SuggestionController` から旅行提案生成ロジックを Action に切り出す
- `AiSummaryController` から要約生成ロジックを Action に切り出す
- `AiPlannerController` から会話保存・履歴取得ロジックを service に切り出す
- AI プロンプト文字列を専用クラスへ移動する
- AI のレスポンス整形とバリデーションを専用クラスへ切り出す

### 完了条件

- コントローラ内に巨大な prompt 文字列が残っていない
- OpenAI を叩くコードが `OpenAiClient` 経由に統一されている
- 各 AI 機能が単体で読める構成になっている

## Phase 2: AI プランナー UI の分割

### 目的

`Map/Index.vue` から AI プランナー関連の複雑さを切り離す。

### 対象

- `resources/js/Pages/Map/Index.vue`

### 分割候補

- `resources/js/Components/AiPlanner/AiPlannerModal.vue`
- `resources/js/Components/AiPlanner/AiPlannerMessageList.vue`
- `resources/js/Components/AiPlanner/AiPlannerInput.vue`
- `resources/js/Components/AiPlanner/AiPlannerContextMenu.vue`
- `resources/js/Composables/useAiPlannerChat.js`

### 実施内容

- チャット履歴表示をコンポーネント化する
- 入力欄と送信処理を分離する
- コンテキストメニューを独立コンポーネント化する
- ポーリングと API 通信を composable にまとめる
- JSON メッセージのパース処理をユーティリティ化する

### 完了条件

- `Map/Index.vue` が地図表示とモーダル呼び出し中心のコードになる
- AI プランナー固有ロジックが別コンポーネントに分離されている
- メニュー位置制御のような UI ロジックが閉じた構造になっている

## Phase 3: Suggestion 機能の整理

### 目的

旅行提案機能の生成・保存・検索を分かりやすくする。

### 対象

- `app/Http/Controllers/SuggestionController.php`
- `resources/js/Pages/Suggestions/*`

### 実施内容

- 検索条件処理を query scope または専用クエリクラスへ移す
- `storeFromChat` のバリデーションを FormRequest 化する
- AI 生成用の入力整形処理を builder にまとめる
- 提案保存時の pinned location 更新を service に寄せる

### 完了条件

- `SuggestionController` が薄くなっている
- 提案検索・生成・保存の責務が分離されている

## Phase 4: テスト追加

### 目的

リファクタリング後の回 regressions を防ぐ。

### 優先テスト

- AI 提案生成の正常系
- AI 提案生成の API エラー時
- AI 要約生成の正常系
- AI プランナーの履歴取得
- AI プランナーのメッセージ保存
- JSON 形式レスポンスのバリデーション

### 実施内容

- OpenAI 呼び出しは `Http::fake()` で差し替える
- Action / Service 単位のテストを追加する
- 主要な controller / request の feature test を追加する

## 推奨実装順

1. Phase 1 の残りを完了する
2. `Map/Index.vue` の AI プランナー部分を分離する
3. Suggestion 機能の保存・検索まわりを整理する
4. テストを追加する

## すぐ着手する候補

### 候補 A

`SuggestionController` の AI 提案生成を `GenerateSuggestionAction` に切り出す

### 候補 B

`AiSummaryController` の要約生成を `GenerateTripSummaryAction` に切り出す

### 候補 C

`Map/Index.vue` から AI プランナーモーダルを `AiPlannerModal.vue` として分離する

## 補足

- すでに `OpenAiClient` と `TrixContentCleaner` は導入済み
- 今後モデルや timeout を変更する場合も、共通層に寄せる前提で進める
- 画面分割は見た目を変えずに、責務だけ切る方針で進める
