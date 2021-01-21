<?php

namespace IQDEV\Base;

use Bitrix\Main\Data\Cache;

class Load
{

    /**
     * Вызывается по событию endBufferContent и рендерит весь шаблон
     *
     * @param $content
     *
     * @return void
     */
    public static function pugRender(&$content)
    {
        if ($GLOBALS['IQDEV_SHARE']['global']['path']) {
            global $APPLICATION;
            $navChain = self::getNavChain();

            $navChain = array_map(
                function ($value) {
                    return ['name' => $value['TITLE'], 'link' => $value['LINK']];
                },
                $navChain
            );

            $GLOBALS['IQDEV_SHARE']['local']['breadcrumbs'] = $navChain;

            array_walk_recursive($GLOBALS['IQDEV_SHARE']['local'], function (&$item) {
                if (is_string($item)) {
                    $item = html_entity_decode($item);
                }
            });
            $arFooterContent = self::getFooterContent();
            $arSocialLinks   = self::getSocialLinks();
            $sCurPage        = $APPLICATION->GetCurPage();
            $scripts         = self::getScripts();
            $arCaptchaKeys   = self::getCaptchaKeys();

            $variables = [
                'global' => [
                    'route' => $sCurPage,
                    'headHtml' => $scripts['headHtml'],
                    'bodyHtml' => $scripts['bodyHtml'],
                    'socialLinks' => $arSocialLinks,
                    'menuHeader' => self::getMenu('main_menu'),
                    'menuFooter' => self::getMenu('footer_menu'),
                    'bitrixHead' => $content,
                    'headerNumber' => self::getHeaderContacts($sCurPage),
                    'recaptchaKey' => $arCaptchaKeys['recaptchaKey'],
                    'footer' => $arFooterContent,
                    'policy' => $arFooterContent['forms']['policy']
                ],
                'local' => $GLOBALS['IQDEV_SHARE']['local']
            ];

            $sPath = $GLOBALS['IQDEV_SHARE']['global']['path'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/cache_pug/' . md5($sPath))) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/cache_pug/' . md5($sPath), 0755, true);
            }
            $options = [
                'up_to_date_check' => !! (int) getenv('UP_TO_DATE_CHECK'),
                'debug' => !! (int) getenv('DEBUG'),
                'pugjs' => true,
                'nodePath' => getenv('NODE_PATH'),
                'cache_dir' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/cache_pug/' . md5($sPath),
                'baseDir' => $_SERVER['DOCUMENT_ROOT'] . '/local/markup/src/markup/',
                'paths' => [
                    $_SERVER['DOCUMENT_ROOT'] . '/local/markup/src/markup/',
                ]
            ];

            $content = \Pug\Optimizer::call(
                'renderFile',
                [
                    $_SERVER['DOCUMENT_ROOT'] . $GLOBALS['IQDEV_SHARE']['global']['path'],
                    $variables
                ],
                $options
            );
            if (!! (int) getenv('DEBUG')) {
                ob_start();
                echo "<pre style='display: none'>", var_dump($variables), "</pre>";
                $content .= ob_get_clean();
            }
        }
    }

    /**
     * Задает темплейт отработки на паге
     *
     * @param $sPath
     *
     * @return void
     */
    public static function setTemplate($sPath)
    {
        $GLOBALS['IQDEV_SHARE']['global']['path'] = $sPath;
    }

    /**
     * Расшаривает одно свойство для пага
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public static function setProperty($key, $value)
    {
        global $GLOBALS;
        if (array_key_exists($key, $GLOBALS['IQDEV_SHARE'])) {
            return false;
        }

        $GLOBALS['IQDEV_SHARE']['local'][$key] = $value;

        return true;
    }

    /**
     * Расшаривает массив для пага
     *
     * @param $array
     *
     * @return bool
     */
    public static function setPropertyArray($array)
    {
        //Массив с ключами, зависымыми от времени года
        $aSeasonsKeys = [
            'video',
            'poster',
            'imageBanner',
        ];
        foreach ($array as $key => $value) {
            if (array_key_exists($key, $GLOBALS['IQDEV_SHARE']['local'])) {
                return false;
            }
            if (in_array($key, $aSeasonsKeys)) {
                $GLOBALS['IQDEV_SHARE']['local'][$key] = $value[$array['season']];
            } else {
                $GLOBALS['IQDEV_SHARE']['local'][$key] = $value;
            }
        }

        return true;
    }

    /**
     * Метод для предопределения параметров юзера на хит, если необходимо
     *
     * @return void
     */
    public function initUser()
    {
        global $USER;
    }

    /**
     * Инициирует классы
     *
     * @return void
     */
    public function initClasses()
    {

        $GLOBALS["IQDEV_ACTIONS"] = new Actions();

        $GLOBALS['IQDEV_SHARE'] = [];
    }

    /**
     * Установка етмплейта для крошек
     *
     * @param $DOC_ROOT
     *
     * @return string
     */
    public function setChainTemplate($DOC_ROOT)
    {
        $strChainTemplate = $DOC_ROOT . SITE_TEMPLATE_PATH . "/chain_template.php";
        if (!file_exists($strChainTemplate)) {
            $template = getLocalPath("templates/.default/chain_template.php", BX_PERSONAL_ROOT);
            if ($template !== false) {
                $strChainTemplate = $DOC_ROOT . $template;
            }
        }
        return $strChainTemplate;
    }

    /**
     * Возвращает массив хлебных крошек
     *
     * @param bool $path
     * @param int $iNumFrom
     * @param bool $sNavChainPath
     *
     * @return array
     */
    public function getNavChain($path = false, $iNumFrom = 0, $sNavChainPath = false)
    {
        global $APPLICATION;

        if ($APPLICATION->GetProperty("NOT_SHOW_NAV_CHAIN") == "Y") {
            return [];
        }

        \CMain::InitPathVars($site, $path);
        $DOC_ROOT = \CSite::GetSiteDocRoot($site);

        if ($path === false) {
            $path = $APPLICATION->GetCurDir();
        }

        $arChain = [];

        $strChainTemplate = self::setChainTemplate($DOC_ROOT);

        $io = \CBXVirtualIo::GetInstance();

        while (true) {
            $path = rtrim($path, "/");

            $chain_file_name = $DOC_ROOT . $path . "/.section.php";
            if ($io->FileExists($chain_file_name)) {
                $sChainTemplate = "";

                $sSectionName = "";
                include($io->GetPhysicalName($chain_file_name));
                if (strlen($sSectionName) > 0) {
                    $arChain[] = ["TITLE" => $sSectionName, "LINK" => $path . "/"];
                }
                if (strlen($sChainTemplate) > 0) {
                    $strChainTemplate = $sChainTemplate;
                }
            }
            //file or folder
            $pos = bxstrrpos($path, "/");
            if ($path . '/' == SITE_DIR || strlen($path) <= 0 || $pos === false) {
                break;
            }

            //parent folder
            $path = substr($path, 0, $pos + 1);
        }

        if ($sNavChainPath !== false) {
            $strChainTemplate = $DOC_ROOT . $sNavChainPath;
        }

        $arChain = array_reverse($arChain);

        $arChain = array_merge($arChain, $APPLICATION->arAdditionalChain);

        if ($iNumFrom > 0) {
            $arChain = array_slice($arChain, $iNumFrom);
        }

        return $arChain;
    }

    /**
     * Метод возвращает массив элементов меню
     *
     * @param string $sIblockCode
     *
     * @return array
     */
    public function getMenu($sIblockCode = 'main_menu')
    {

        $oCache = Cache::createInstance();

        if ($oCache->initCache(86400, $sIblockCode, '/')) {
            $aResult = $oCache->getVars();
        } elseif ($oCache->startDataCache()) {
            \Bitrix\Main\Loader::includeModule("iblock");

            $aCat = [];

            $aByGroups = [];

            $res = \CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => \IQDEV\Base\Helper::getIblockId($sIblockCode),
                    'ACTIVE_DATE' => 'Y',
                    'ACTIVE' => 'Y'
                ],
                false,
                false,
                [
                    'ID',
                    'NAME',
                    'CODE',
                    'IBLOCK_ID',
                    'IBLOCK_SECTION_ID'
                ]
            );

            while ($ob = $res->GetNextElement()) {
                $aBlock  = $ob->GetFields();
                $aTarget = $ob->GetProperty("TARGET_BLANK");

                if ($aBlock['IBLOCK_SECTION_ID']) {
                    $aByGroups[$aBlock['IBLOCK_SECTION_ID']][] = [
                        'name' => $aBlock['NAME'],
                        'route' => $aBlock['CODE'],
                        'target' => $aTarget['VALUE'],
                    ];
                } else {
                    $aByGroups[$aBlock['ID']] = [
                        'name' => $aBlock['NAME'],
                        'route' => $aBlock['CODE'],
                        'target' => $aTarget['VALUE'],
                    ];
                }
            }

            $res = \CIBlockSection::GetList(
                [
                    'left_margin' => 'asc',
                    'sort' => 'asc',
                ],
                [
                    'IBLOCK_ID' => \IQDEV\Base\Helper::getIblockId($sIblockCode),
                    'ACTIVE_DATE' => 'Y',
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y',
                    'CNT_ACTIVE' => 'Y'
                ],
                true,
                ['IBLOCK_SECTION_ID', 'ID', 'NAME', 'CODE']
            );

            $aGroupsLink = [];
            while ($oGroup = $res->GetNextElement()) {
                $aFieldsGroup = $oGroup->GetFields();

                if ($aFieldsGroup['ELEMENT_CNT'] == 0) {
                    continue;
                }

                $idGroup = (int) $aFieldsGroup['ID'];

                $idParentGroup = (int) $aFieldsGroup['IBLOCK_SECTION_ID'];

                $aGroup = [
                    'name' => $aFieldsGroup['NAME'],
                    'route' => $aFieldsGroup['CODE']
                ];

                if ($aByGroups[$idGroup]) {
                    $aGroup['submenu'] = $aByGroups[$idGroup];
                    unset($aByGroups[$idGroup]);
                }

                if ($idParentGroup === 0) {
                    $aCurrentGroup = &$aCat[];
                } else {
                    $aCurrentGroup = &$aGroupsLink[$idParentGroup][];
                }

                $aCurrentGroup = $aGroup;

                end($aCurrentGroup);

                $lastKey = key($aCurrentGroup);

                $aGroupsLink[$idGroup] = &$aCurrentGroup[$lastKey];
            }

            $aResult = array_values($aCat + $aByGroups);

            $oCache->endDataCache($aResult);
        }

        return $aResult;
    }

    /**
     * Метод возвращает контент подвала сайта
     *
     * @return array
     */
    public static function getFooterContent()
    {
        if (\CModule::IncludeModule('iqdev.options')) {
            return \iqdev\options\Options::getPageOptions('footer');
        }
    }

    /**
     * Метод возвращает скритпы для сайта
     *
     * @return array
     */
    public static function getScripts()
    {
        if (\CModule::IncludeModule('iqdev.options')) {
            return \iqdev\options\Options::getPageOptions('scripts');
        }
    }


    /**
     * Метод возвращает ключи для reCAPTCHA
     *
     * @return array
     */
    public static function getCaptchaKeys()
    {
        if (\CModule::IncludeModule('iqdev.options')) {
            return \iqdev\options\Options::getPageOptions('captcha');
        }
    }


    /**
     * Метод возвращает ссылки на социальные сети
     *
     * @return array
     */
    public static function getSocialLinks()
    {
        if (\CModule::IncludeModule('iqdev.options')) {
            return \iqdev\options\Options::getPageOptions('social');
        }
    }


    /**
     * Метод возвращает номера телефонов для шапки
     *
     * @param $sCurPage
     *
     * @return mixed
     */
    public static function getHeaderContacts($sCurPage)
    {
        if (\CModule::IncludeModule('iqdev.options')) {
            $arOptionValues = \iqdev\options\Options::getPageOptions('headerNumber');
            if ($sCurPage == '/obsluzhivayuschaya-kompaniya/') {
                $arRes['link']   = $arOptionValues['ok']['link'];
                $arRes['number'] = $arOptionValues['ok']['number'];
            } else {
                $arRes['link']   = $arOptionValues['link'];
                $arRes['number'] = $arOptionValues['number'];
            }
            return $arRes;
        }
    }
}
