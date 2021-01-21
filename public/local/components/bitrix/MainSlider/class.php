<?php

use \IQDEV\Controllers\InteractiveBanner;

class Slider extends \CBitrixComponent
{
    public function executeComponent()
    {
        $oBanner = new InteractiveBanner();
        $this->arResult = $oBanner->GetItems();

        $this->includeComponentTemplate();
    }
}