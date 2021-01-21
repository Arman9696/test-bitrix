<?php

namespace IQDEV\Base;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Data\Cache;

class HighLoadBlock
{
    /**
     * Символьный код
     *
     * @var string
     */
    public $code = null;

    /**
     * ID
     *
     * @var int
     */
    public $hlbId = null;

    /**
     * Имя класса для работы с HB
     *
     * @var object
     */
    public static $entityDataClassName = null;

    /**
     * Класс для работы с HB
     *
     * @var object
     */
    public static $entityDataClass = null;

    /**
     * HighLoadBlock constructor.
     *
     * @param $code
     */
    public function __construct($code)
    {
        Loader::includeModule('highloadblock');
        $this->code  = $code;
        $this->hlbId = self::getIdHlBlock();
        $this->preLoad();
    }

    /**
     * Автовызов стандартных методов
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return self::$entityDataClassName::$method(...$arguments);
    }

    /**
     * Получает ID для HB IB по символьному коду
     *
     * @return int
     */
    private function getIdHlBlock()
    {
        $id    = 0;
        $cache = Cache::createInstance();
        if ($cache->initCache(86400, "highLoadBlock" . $this->code, '/')) {
            $id = $cache->getVars();
        } elseif ($cache->startdataCache()) {
            $highLoadBlockCursor = HighloadBlockTable::getList(
                [
                    'filter' => [
                        'TABLE_NAME' => $this->code
                    ],
                    'select' => [
                        'ID'
                    ]
                ]
            );

            $ob = $highLoadBlockCursor->fetch();
            if ($ob) {
                $id = (int) $ob['ID'];
            }
            $cache->enddataCache($id);
        }
        return $id;
    }

    /**
     * Создает класс для работы с HB
     *
     * @return void
     */
    private function preLoad()
    {
        $aHlBlock = HighloadBlockTable::getById($this->hlbId)->fetch();

        static::$entityDataClass = HighloadBlockTable::compileEntity($aHlBlock);


        static::$entityDataClassName = self::$entityDataClass->getdataClass();
    }

    /**
     * Добавляет элемент в HB
     *
     * @param $aFields
     *
     * @return mixed
     */
    public function add($aFields)
    {
        $oResult = self::$entityDataClassName::add($aFields);
        $id      = $oResult->getId();
        return $id;
    }

    /**
     * Обновляет элемент HB
     *
     * @param $aFields
     * @param $elementID
     *
     * @return void
     */
    public function upd($aFields, $elementID)
    {
        self::$entityDataClassName::update($elementID, $aFields);
    }

    /**
     * Удаляет элемент HB
     *
     * @param $elementID
     *
     * @return void
     */
    public function del($elementID)
    {
        self::$entityDataClassName::delete($elementID);
    }

    /**
     * Получает элементы HB
     *
     * @param $filter
     * @param $bCache
     * @param $cacheTime
     *
     * @return mixed
     */
    public function getList($filter = [], $bCache = false, $cacheTime = 86400)
    {
        $baseFilter = [
            'select' => ['*'],
            'order' => [],
            'limit' => 999999,
            'filter' => []

        ];
        $filter = array_merge($baseFilter, $filter);

        if ($bCache) {
            $cache = Cache::createInstance();
            if ($cache->initCache($cacheTime, "highLoadBlockGetList" . $this->code.md5(json_encode($filter)), '/')) {
                $data = $cache->getVars();
            } elseif ($cache->startdataCache()) {
                $data = self::$entityDataClassName::getList($filter)->fetchAll();
                $cache->endDataCache($data);
            }
        } else {
            $data = self::$entityDataClassName::getList($filter)->fetchAll();
        }
        return $data;
    }
}
