<?php


namespace IQDEV\Base;

/**
 * Валидация ключей пользователей CAPTCHA на серверах google
 * v.3
 *
 * Class Recaptcha
 *
 * @package IQDEV\Base
 */
class Recaptcha
{
    /**
     * Проверка капчи
     *
     * @param $sUserCaptchaKey
     *
     * @return mixed
     */
    public static function recaptchaCheck($sUserCaptchaKey)
    {
        $sSecretKey = getenv('SECRET_RECAPTCHA_KEY');

        $sValidatingUrl = getenv('VALIDATING_API');

        if (empty($sSecretKey) || empty($sValidatingUrl)) {
            return true;
        }
            $aData = [
                'secret' => $sSecretKey,
                'response' => $sUserCaptchaKey
            ];

            $options = [
                'http' => [
                    'method' => 'POST',
                    'content' => http_build_query($aData)
                ]
            ];
            $context = stream_context_create($options);
            $sResult = file_get_contents($sValidatingUrl, false, $context);
            $oResult = json_decode($sResult);

            return $oResult->score > 0.5;
    }
}
