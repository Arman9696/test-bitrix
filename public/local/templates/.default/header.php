<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
};

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?
    global $APPLICATION, $USER;

    if ($USER->IsAdmin()) {
        $APPLICATION->ShowHead();
        $APPLICATION->ShowPanel();
    }
        $APPLICATION->ShowMeta("robots", false, true);
        $APPLICATION->ShowMeta("keywords", false, true);
        $APPLICATION->ShowMeta("description", false, true);
        $APPLICATION->ShowLink("canonical", null, true);
        $APPLICATION->ShowHeadStrings();


    if (array_filter($_REQUEST, function ($key) {
        return $key == strpos($key, 'PAGEN');
    }, ARRAY_FILTER_USE_KEY)
    ) {
        $APPLICATION->SetPageProperty('robots', 'noindex,follow');
    } ?>
    <title><?= $APPLICATION->ShowTitle(); ?></title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <link rel="stylesheet" href="/assets/css/application.css?v=1"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png"/>
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png"/>
    <link rel="manifest" href="/assets/icons/site.webmanifest"/>
    <link rel="mask-icon" href="/assets/icons/safari-pinned-tab.svg" color="#6bbd45"/>
    <link rel="shortcut icon" href="/assets/icons/favicon.ico"/>
    <meta name="msapplication-TileColor" content="#ffffff"/>
    <meta name="msapplication-config" content="/assets/icons/browserconfig.xml"/>
    <meta name="theme-color" content="#ffffff"/>
    <title>Global</title></head>
<body class="body js-body">

