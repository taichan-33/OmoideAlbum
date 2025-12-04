@extends('layouts.app')

@section('styles')
<style>
    /* マップコンテナ */
    #map-wrapper {
        position: relative;
        width: 100%;
        height: 600px;
        background-color: #eaf2f8;
        /* 海の色 */
        border-radius: 8px;
        overflow: hidden;
        /* はみ出し防止 */
        border: 1px solid #dee2e6;
    }

    #map-container {
        width: 100%;
        height: 100%;
    }

    /* 凡例（レジェンド）エリア */
    .legend-container {
        background: white;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
        border-radius: 0 0 8px 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin-right: 1.5rem;
        font-size: 0.9rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 0.5rem;
        border: 1px solid #ccc;
    }

    /* ズームリセットボタン */
    #reset-zoom {
        position: absolute;
        bottom: 20px;
        right: 20px;
        z-index: 10;
        background: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* ツールチップのスタイル微調整 */
    .google-visualization-tooltip {
        padding: 10px 12px !important;
        border-radius: 4px !important;
        border: 1px solid #ccc !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-map-fill"></i> 都道府県 制覇マップ</h5>

                    {{-- 地図切り替えボタン --}}
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="mapRegion" id="regionJP" value="JP" checked autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="regionJP">日本</label>
                        <input type="radio" class="btn-check" name="mapRegion" id="regionWorld" value="world" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="regionWorld">世界</label>
                    </div>
                </div>

                {{-- マップエリア --}}
                <div id="map-wrapper">
                    {{-- マップ描画本体 --}}
                    <div id="map-container"></div>

                    {{-- ズームリセットボタン --}}
                    <button id="reset-zoom" class="btn btn-sm btn-light">
                        <i class="bi bi-arrows-fullscreen"></i> 位置リセット
                    </button>
                </div>

                {{-- 凡例（注釈）エリア --}}
                <div class="legend-container">
                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> 訪問回数による色分け</h6>
                    <div class="d-flex flex-wrap">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #ffffff;"></div>
                            <span>未訪問</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #9ec5fe;"></div>
                            <span>1回</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #6ea8fe;"></div>
                            <span>2回</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #0d6efd;"></div>
                            <span>3回</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #ffc107;"></div>
                            <span>4回以上</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Google Charts ローダー --}}
<script src="https://www.gstatic.com/charts/loader.js"></script>

{{-- svg-pan-zoom ライブラリ --}}
<script src="https://cdn.jsdelivr.net/npm/svg-pan-zoom@3.6.1/dist/svg-pan-zoom.min.js"></script>

<script>
    // ★ (追加) JPコードと県名の対応表
    const regionNames = {
        'JP-01': '北海道',
        'JP-02': '青森県',
        'JP-03': '岩手県',
        'JP-04': '宮城県',
        'JP-05': '秋田県',
        'JP-06': '山形県',
        'JP-07': '福島県',
        'JP-08': '茨城県',
        'JP-09': '栃木県',
        'JP-10': '群馬県',
        'JP-11': '埼玉県',
        'JP-12': '千葉県',
        'JP-13': '東京都',
        'JP-14': '神奈川県',
        'JP-15': '新潟県',
        'JP-16': '富山県',
        'JP-17': '石川県',
        'JP-18': '福井県',
        'JP-19': '山梨県',
        'JP-20': '長野県',
        'JP-21': '岐阜県',
        'JP-22': '静岡県',
        'JP-23': '愛知県',
        'JP-24': '三重県',
        'JP-25': '滋賀県',
        'JP-26': '京都府',
        'JP-27': '大阪府',
        'JP-28': '兵庫県',
        'JP-29': '奈良県',
        'JP-30': '和歌山県',
        'JP-31': '鳥取県',
        'JP-32': '島根県',
        'JP-33': '岡山県',
        'JP-34': '広島県',
        'JP-35': '山口県',
        'JP-36': '徳島県',
        'JP-37': '香川県',
        'JP-38': '愛媛県',
        'JP-39': '高知県',
        'JP-40': '福岡県',
        'JP-41': '佐賀県',
        'JP-42': '長崎県',
        'JP-43': '熊本県',
        'JP-44': '大分県',
        'JP-45': '宮崎県',
        'JP-46': '鹿児島県',
        'JP-47': '沖縄県'
    };

    // 1. 日本語化設定を含めてロード
    google.charts.load('current', {
        'packages': ['geochart'],
        'language': 'ja'
    });

    // 2. ロード完了時に描画関数を実行
    google.charts.setOnLoadCallback(initMap);

    let currentRegion = 'JP'; // デフォルトは日本
    let panZoomInstance = null; // ズーム機能のインスタンス

    function initMap() {
        // 切り替えボタンのイベントリスナー
        const radios = document.querySelectorAll('input[name="mapRegion"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                currentRegion = this.value;
                drawRegionsMap();
            });
        });

        // リセットボタン
        document.getElementById('reset-zoom').addEventListener('click', function() {
            if (panZoomInstance) {
                panZoomInstance.reset();
            }
        });

        // 初回描画
        drawRegionsMap();
    }

    function drawRegionsMap() {
        // コントローラーから {'JP-28': 5, ...} 形式のデータを受け取る
        const rawMapData = @json($mapData);

        // データテーブルの作成
        // ヘッダー: [地域コード, 回数(数値), ツールチップ用HTML]
        const chartData = [
            ['Region', 'Visits', {
                role: 'tooltip',
                p: {
                    html: true
                }
            }]
        ];

        // 日本の都道府県コード (JP-01 〜 JP-47) をループしてデータを構築
        for (let i = 1; i <= 47; i++) {
            let codeNum = i.toString().padStart(2, '0');
            let isoCode = 'JP-' + codeNum;

            let visits = 0;
            if (rawMapData[isoCode]) {
                visits = rawMapData[isoCode];
            }

            // 色分けのための値を調整 (4回以上はすべて4扱い)
            let colorValue = visits;
            if (visits >= 4) colorValue = 4;

            // ★ (修正) ツールチップの表示内容をカスタマイズ
            // 日本語の県名を取得
            let prefName = regionNames[isoCode] || isoCode;

            let tooltipText = `<div style="padding:5px;">`;
            tooltipText += `<h6 style="margin:0 0 5px 0; font-weight:bold;">${prefName}</h6>`;

            if (visits > 0) {
                tooltipText += `<div>訪問回数: <strong>${visits}回</strong></div>`;
            } else {
                tooltipText += `<div class="text-muted">未訪問</div>`;
            }
            tooltipText += `</div>`;

            chartData.push([isoCode, colorValue, tooltipText]);
        }

        const data = google.visualization.arrayToDataTable(chartData);

        const options = {
            region: currentRegion, // 'JP' or 'world'
            displayMode: 'regions',
            resolution: currentRegion === 'JP' ? 'provinces' : 'countries',

            // 色の設定 (0回〜4回以上)
            colorAxis: {
                minValue: 0,
                maxValue: 4,
                colors: ['#ffffff', '#9ec5fe', '#6ea8fe', '#0d6efd', '#ffc107']
            },

            backgroundColor: '#eaf2f8', // 海の色
            datalessRegionColor: '#ffffff',
            defaultColor: '#ffffff',
            legend: 'none',
            keepAspectRatio: true,

            // ★ ツールチップでHTMLを許可
            tooltip: {
                isHtml: true
            }
        };

        const container = document.getElementById('map-container');
        const chart = new google.visualization.GeoChart(container);

        // 描画完了時のイベント（ズーム機能有効化）
        google.visualization.events.addListener(chart, 'ready', function() {
            setupZoom();
        });

        chart.draw(data, options);
    }

    function setupZoom() {
        // 既存のインスタンスがあれば破棄
        if (panZoomInstance) {
            panZoomInstance.destroy();
            panZoomInstance = null;
        }

        const svgElement = document.querySelector('#map-container svg');

        if (svgElement) {
            svgElement.style.width = "100%";
            svgElement.style.height = "100%";

            panZoomInstance = svgPanZoom(svgElement, {
                zoomEnabled: true,
                controlIconsEnabled: false,
                fit: true,
                center: true,
                minZoom: 0.8,
                maxZoom: 10,
                onZoom: function() {},
                onPan: function() {}
            });
        }
    }

    window.addEventListener('resize', drawRegionsMap);
</script>
@endpush