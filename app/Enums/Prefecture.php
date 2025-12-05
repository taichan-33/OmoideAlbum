<?php

namespace App\Enums;

enum Prefecture: string
{
    case HOKKAIDO = 'JP-01';
    case AOMORI = 'JP-02';
    case IWATE = 'JP-03';
    case MIYAGI = 'JP-04';
    case AKITA = 'JP-05';
    case YAMAGATA = 'JP-06';
    case FUKUSHIMA = 'JP-07';
    case IBARAKI = 'JP-08';
    case TOCHIGI = 'JP-09';
    case GUNMA = 'JP-10';
    case SAITAMA = 'JP-11';
    case CHIBA = 'JP-12';
    case TOKYO = 'JP-13';
    case KANAGAWA = 'JP-14';
    case NIIGATA = 'JP-15';
    case TOYAMA = 'JP-16';
    case ISHIKAWA = 'JP-17';
    case FUKUI = 'JP-18';
    case YAMANASHI = 'JP-19';
    case NAGANO = 'JP-20';
    case GIFU = 'JP-21';
    case SHIZUOKA = 'JP-22';
    case AICHI = 'JP-23';
    case MIE = 'JP-24';
    case SHIGA = 'JP-25';
    case KYOTO = 'JP-26';
    case OSAKA = 'JP-27';
    case HYOGO = 'JP-28';
    case NARA = 'JP-29';
    case WAKAYAMA = 'JP-30';
    case TOTTORI = 'JP-31';
    case SHIMANE = 'JP-32';
    case OKAYAMA = 'JP-33';
    case HIROSHIMA = 'JP-34';
    case YAMAGUCHI = 'JP-35';
    case TOKUSHIMA = 'JP-36';
    case KAGAWA = 'JP-37';
    case EHIME = 'JP-38';
    case KOCHI = 'JP-39';
    case FUKUOKA = 'JP-40';
    case SAGA = 'JP-41';
    case NAGASAKI = 'JP-42';
    case KUMAMOTO = 'JP-43';
    case OITA = 'JP-44';
    case MIYAZAKI = 'JP-45';
    case KAGOSHIMA = 'JP-46';
    case OKINAWA = 'JP-47';

    public function label(): string
    {
        return match ($this) {
            self::HOKKAIDO => '北海道',
            self::AOMORI => '青森県',
            self::IWATE => '岩手県',
            self::MIYAGI => '宮城県',
            self::AKITA => '秋田県',
            self::YAMAGATA => '山形県',
            self::FUKUSHIMA => '福島県',
            self::IBARAKI => '茨城県',
            self::TOCHIGI => '栃木県',
            self::GUNMA => '群馬県',
            self::SAITAMA => '埼玉県',
            self::CHIBA => '千葉県',
            self::TOKYO => '東京都',
            self::KANAGAWA => '神奈川県',
            self::NIIGATA => '新潟県',
            self::TOYAMA => '富山県',
            self::ISHIKAWA => '石川県',
            self::FUKUI => '福井県',
            self::YAMANASHI => '山梨県',
            self::NAGANO => '長野県',
            self::GIFU => '岐阜県',
            self::SHIZUOKA => '静岡県',
            self::AICHI => '愛知県',
            self::MIE => '三重県',
            self::SHIGA => '滋賀県',
            self::KYOTO => '京都府',
            self::OSAKA => '大阪府',
            self::HYOGO => '兵庫県',
            self::NARA => '奈良県',
            self::WAKAYAMA => '和歌山県',
            self::TOTTORI => '鳥取県',
            self::SHIMANE => '島根県',
            self::OKAYAMA => '岡山県',
            self::HIROSHIMA => '広島県',
            self::YAMAGUCHI => '山口県',
            self::TOKUSHIMA => '徳島県',
            self::KAGAWA => '香川県',
            self::EHIME => '愛媛県',
            self::KOCHI => '高知県',
            self::FUKUOKA => '福岡県',
            self::SAGA => '佐賀県',
            self::NAGASAKI => '長崎県',
            self::KUMAMOTO => '熊本県',
            self::OITA => '大分県',
            self::MIYAZAKI => '宮崎県',
            self::KAGOSHIMA => '鹿児島県',
            self::OKINAWA => '沖縄県',
        };
    }

    public static function fromName(string $name): ?self
    {
        $normalized = str_replace(['県', '府', '都'], '', $name);

        // 北海道はそのまま
        if ($name === '北海道')
            return self::HOKKAIDO;

        foreach (self::cases() as $case) {
            if (str_replace(['県', '府', '都'], '', $case->label()) === $normalized) {
                return $case;
            }
        }
        return null;
    }
}
