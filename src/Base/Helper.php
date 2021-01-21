<?php

namespace IQDEV\Base;

class Helper
{
    /**
     * Get IblockID by code
     *
     * @param $sCode
     *
     * @return int
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getIblockId($sCode = '')
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        $iID    = null;
        $oCache = \Bitrix\Main\Data\Cache::createInstance();
        if ($oCache->initCache(86400, 'iblock_id_g'.$sCode, '/')) {
            $iID = $oCache->getVars();
        } elseif ($oCache->startDataCache()) {
            $res = \CIBlock::GetList([], ['CODE' => $sCode, 'CHECK_PERMISSIONS' => 'N'], false);
            $ob  = $res->GetNext();
            if ($ob) {
                $iID = (int) $ob['ID'];
            }

            $oCache->endDataCache($iID);
        }

        return (int) $iID;
    }

    /**
     * Get CRubricID by code
     *
     * @param $sCode
     *
     * @return int
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getCrubricId($sCode = '')
    {
        \Bitrix\Main\Loader::includeModule("subscribe");
        $iID    = null;
        $oCache = \Bitrix\Main\Data\Cache::createInstance();
        if ($oCache->initCache(86400, 'CRubric_id_g'.$sCode, '/')) {
            $iID = $oCache->getVars();
        } elseif ($oCache->startDataCache()) {
            $res = \CRubric::GetList([], ['CODE' => $sCode, 'CHECK_PERMISSIONS' => 'N']);
            $ob  = $res->GetNext();
            if ($ob) {
                $iID = (int) $ob['ID'];
            }

            $oCache->endDataCache($iID);
        }

        return (int) $iID;
    }

    /**
     * Получение ID раздела информационного блока
     *
     * @param string $iBlockCode        Код инфоблока
     * @param string $iBlockSectionCode Код раздела инфоблока
     *
     * @return int $iID раздела инфоблока
     */
    public static function getIblockSectionId($iBlockCode, $iBlockSectionCode)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        $iID    = null;
        $oCache = \Bitrix\Main\Data\Cache::createInstance();

        if ($oCache->initCache(86400, 'iblock_section_id_g'.$iBlockSectionCode, '/')) {
            $iID = $oCache->getVars();
        } elseif ($oCache->startDataCache()) {
            $res = \CIBlockSection::GetList([], [
                'IBLOCK_CODE' => $iBlockCode,
                'CODE' => $iBlockSectionCode
            ], false);
            $ob  = $res->GetNext();
            if ($ob) {
                $iID = (int) $ob['ID'];
            }

            $oCache->endDataCache($iID);
        }

        return (int) $iID;
    }

    /**
     * Function rus2translit
     *
     * @param $string
     *
     * @return string
     */
    public static function rus2translit($string = '')
    {
        $converter = [
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        ];
        return strtr($string, $converter);
    }

    /**
     * Funtion str2url
     *
     * @param $str
     *
     * @return null|string|string[]
     */
    public static function str2url($str)
    {
        // переводим в транслит
        $str = self::rus2translit($str);
        // в нижний регистр
        $str = strtolower($str);
        // заменям все ненужное нам на "-"
        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
        // удаляем начальные и конечные '-'
        $str = trim($str, "-");
        return $str;
    }

    /**
     * Function clearPhone
     *
     * @param $s_phone
     *
     * @return mixed
     */
    public static function clearPhone($s_phone)
    {
        return str_replace([" ", "-", "(", ")", "+"], ["","","","",""], $s_phone);
    }

    /**
     * Function view_phone
     *
     * @param $s_phone
     *
     * @return string
     */
    public static function viewPhone($s_phone)
    {
        if (strlen($s_phone) != 11) {
            return ($s_phone);
        }
        $s_area    = substr($s_phone, 1, 3);
        $s_prefix  = substr($s_phone, 4, 3);
        $s_number  = substr($s_phone, 7, 2);
        $s_number2 = substr($s_phone, 9, 2);
        $s_phone   = "+7 (".$s_area.") ".$s_prefix." ".$s_number."-".$s_number2;

        return ($s_phone);
    }

    /**
     * * Возврат окончания слова при склонении
     *
     * Функция возвращает окончание слова, в зависимости от примененного к ней числа
     * Например: 5 товаров, 1 товар, 3 товара
     *
     * @param int $value
     * @param array $status
     *
     * @return mixed
     */
    public static function bitGetDeclNum($value = 1, $status = ['','а','ов'])
    {
        $array = [2,0,1,1,1,2];
        return $status[($value%100>4 && $value%100<20)? 2 : $array[($value%10<5)?$value%10:5]];
    }
}
