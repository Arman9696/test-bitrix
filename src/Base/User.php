<?php

namespace IQDEV\Base;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

class User
{
    /**
     * Добавление пользователя
     *
     * @param array $aData
     *
     * @return array
     */
    public static function add($aData = [])
    {
        $oUser = new \CUser;

        $ID = $oUser->Add($aData);
        if ($ID > 0) {
            return [
                "type" => "success",
                "id" => $ID,
            ];
        } else {
            return [
                "type" => "error",
                "error" => $ID->LAST_ERROR
            ];
        }
    }

    /**
     * Редактирование пользователя
     *
     * @param int $ID
     * @param array $aData
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function edit($ID = 0, $aData = [])
    {
        if (!($ID > 0) && !is_array($aData)) {
            return [
                "type" => "error",
                "error" => "Нет данных, или пользователя",
            ];
        }

        $aCurrentData = current(self::get(["filter" => ["ID" => $ID]]));

        if (!$aCurrentData) {
            return [
                "type" => "error",
                "error" => "Такого пользователя не существует",
            ];
        }

        if ($aCurrentData["ID"]) {
            $oUser = new \CUser;

            $oUser->Update($aCurrentData["ID"], $aData);

            if ($oUser->LAST_ERROR) {
                return [
                    "type" => "error",
                    "error" => $oUser->LAST_ERROR,
                ];
            } else {
                return [
                    "type" => "success",
                    "id" => $aCurrentData["ID"],
                ];
            }
        }
        return [
            "type" => "error",
            "error" => 'Непредвиденная ошибка',
        ];
    }

    /**
     * Возвращает пользователей
     *
     * @param array $aParams
     *
     * @return mixed
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function get($aParams = [])
    {
        $aUsers = false;
        if (Loader::includeModule('iblock')) {
            $aFilter = [
                "select" => ['*', 'UF_*'],
                "filter" => [],
                "order" => ["LAST_NAME" => "ASC"],
                "limit" => 99999
            ];
            if ($aParams["filter"]) {
                $aFilter["filter"] = array_merge($aFilter["filter"], $aParams["filter"]);
            }

            $oRes = UserTable::getList($aFilter);

            $aUsers = $oRes->fetchAll();
        }
        return $aUsers;
    }
}
