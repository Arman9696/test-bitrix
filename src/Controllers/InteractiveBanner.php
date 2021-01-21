<?php


namespace IQDEV\Controllers;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Data\Cache;
use IQDEV\Base\Helper;

class InteractiveBanner
{
    /**
     * @var int
     */
    private $iBlockId;

    /**
     *Конструктор
     */
    public function __construct()
    {
        $this->iBlockId = Helper::getIblockId('banner');
    }

    /**
     * Поля для выборки баннера
     * @return array
     */
    protected function getSelect()
    {
        return [
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'TEXT' => 'PROPERTY_TEXT.VALUE',
            'IMG' => 'PROPERTY_IMG.VALUE'
        ];
    }

    /**
     * Фильтр для выборки баннеров
     * @return array
     */
    protected function getFilter()
    {
        return [
            'IBLOCK_ID' => $this->iBlockId,
        ];
    }

    /**
     * Метод возвращает баннеры
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function GetItems()
    {
        $aSelect  = $this->getSelect();
        $aFilter = $this->getFilter();
        $oCache = Cache::createInstance();
        $cacheID = md5( strtolower( __METHOD__ ) .  serialize( $aFilter ) );
        $isCache = $oCache->initCache(86400,$cacheID,'InteractiveBanner');
        $aData = [];
        if ($isCache) {
            $aData = $oCache->getVars();
        } elseif ($oCache->startDataCache()) {
            $query = ElementTable::query()
                ->setSelect($aSelect)
                ->setFilter($aFilter)
                ->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField('TEXT_PROPERTY',
                        \Bitrix\Iblock\PropertyTable::class,
                        \Bitrix\Main\ORM\Query\Join::on('this.IBLOCK_ID', 'ref.IBLOCK_ID')
                            ->where(
                                'ref.CODE',
                                'TEXT'
                            )
                    )
                )
                ->registerRuntimeField('PROPERTY_TEXT',
                    [
                        'data_type' => 'Bitrix\Iblock\ElementPropertyTable',
                        'reference' => [
                            '=this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                            '=this.TEXT_PROPERTY.ID' => 'ref.IBLOCK_PROPERTY_ID',
                        ],
                    ])
                ->registerRuntimeField(new \Bitrix\Main\Entity\ReferenceField('IMG_PROPERTY',
                        \Bitrix\Iblock\PropertyTable::class,
                        \Bitrix\Main\ORM\Query\Join::on('this.IBLOCK_ID', 'ref.IBLOCK_ID')
                            ->where(
                                'ref.CODE',
                                'IMG'
                            )
                    )
                )
                ->registerRuntimeField('PROPERTY_IMG',
                    [
                        'data_type' => 'Bitrix\Iblock\ElementPropertyTable',
                        'reference' => [
                            '=this.ID' => 'ref.IBLOCK_ELEMENT_ID',
                            '=this.IMG_PROPERTY.ID' => 'ref.IBLOCK_PROPERTY_ID',
                        ],
                    ])
                ->exec();

            if($query->getSelectedRowsCount()) {
                while ($aQuery = $query->fetch()) {
                    $aData = [
                        'ID' => $aQuery['ID'],
                        'NAME' => $aQuery['NAME'],
                        'PREVIEW_PICTURE' => $aQuery['PREVIEW_PICTURE'] ? \CFile::GetPath($aQuery['PREVIEW_PICTURE']) : null,
                        'TEXT' => $aQuery['TEXT'],
                        'IMG' => $aQuery['IMG'] ? \CFile::GetPath($aQuery['IMG']) : null,
                    ];
                }
            }

            $oCache->endDataCache($aData);

            if(!$aData) {
                $oCache->abortDataCache();
            }
        }
        return $aData;
    }

}