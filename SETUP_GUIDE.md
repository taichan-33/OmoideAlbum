# プロジェクトセットアップおよび起動ガイド

このプロジェクトは Laravel (PHP) と Vite (Vue.js / Tailwind CSS) を使用して構築されています。
開発環境の構築には、Docker (Laravel Sail) を使用することを推奨します。

## 🚀 1. Docker (Laravel Sail) を使用した環境構築・起動（推奨）

Laravel Sail を使用すると、PHPやNode.jsをローカルマシンに直接インストールすることなく、Docker上で開発環境を完結できます。

### 初回セットアップ

1. **環境変数の準備**
   ```bash
   cp .env.example .env
   ```

2. **Composer パッケージのインストール**
   Sail を起動する前に、初回はローカルのPHPか、一時的なDockerコンテナを使って依存関係をインストールします。
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php84-composer:latest \
       composer install --ignore-platform-reqs
   ```

3. **Docker コンテナの起動**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **アプリケーション・キーの生成とマイグレーション**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate
   ```

5. **フロントエンドパッケージのインストールとビルド**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

### 普段の起動・停止コマンド

*   **起動:**
    ターミナルを2つ開くか、バックグラウンドで実行します。
    ```bash
    # コンテナの起動
    ./vendor/bin/sail up -d
    
    # Viteの開発サーバーの起動 (リアルタイムでフロントエンドの変更を反映)
    ./vendor/bin/sail npm run dev
    ```
*   **停止:**
    ```bash
    ./vendor/bin/sail down
    ```

---

## 💻 2. ローカル環境で直接起動する場合

PHP (8.4推奨), Composer, Node.js, npm がローカルにインストールされている場合の手順です。

### 初回セットアップ

1. **環境変数の準備**
   ```bash
   cp .env.example .env
   ```
2. **依存関係のインストール**
   ```bash
   composer install
   npm install
   ```
3. **アプリケーション・キーの生成**
   ```bash
   php artisan key:generate
   ```
4. **データベースの準備**
   `.env` 内の `DB_CONNECTION` などのデータベース設定をご自身のローカル環境に合わせて修正し、その後マイグレーションを実行します。
   ```bash
   php artisan migrate
   ```

### 普段の起動コマンド

バックエンド（Laravel）とフロントエンド（Vite）の開発サーバーを両方起動する必要があります。それぞれ別のターミナルで実行してください。

**ターミナル1: バックエンド**
```bash
php artisan serve
```

**ターミナル2: フロントエンド**
```bash
npm run dev
```

---

## 🛠️ その他のよく使うコマンド

*   **マイグレーションの実行**
    ```bash
    # Sail
    ./vendor/bin/sail artisan migrate

    # ローカル
    php artisan migrate
    ```

*   **データベースのロールバックと再マイグレーション（初期化）**
    ```bash
    ./vendor/bin/sail artisan migrate:fresh --seed
    ```

*   **テストの実行**
    ```bash
    ./vendor/bin/sail artisan test
    ```

*   **キャッシュのクリア**
    設定やルートなどを変更した際に反映されない場合に実行します。
    ```bash
    ./vendor/bin/sail artisan optimize:clear
    ```

*   **本番用フロントエンドのビルド**
    ```bash
    ./vendor/bin/sail npm run build
    ```

> **Tip:** Sailのコマンド (`./vendor/bin/sail`) が長い場合は、エイリアスを設定しておくと便利です。
> `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'`
