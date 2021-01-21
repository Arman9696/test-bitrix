<?php

namespace IQDEV\Base;

use IQDEV\Forms\FormsHandler;

class Actions
{
    /**
     * Request Action
     *
     * @var mixed
     */
    public $oRequest;
    /**
     * Название Action
     *
     * @var mixed|string
     */
    private $sAction = '';
    /**
     * Тип Action
     *
     * @var string
     */
    private $sTypeAction;
    /**
     * Ответ Action
     *
     * @var mixed
     */
    private $dResponse;

    /**
     * Actions constructor.
     */
    public function __construct()
    {
        $aJson = json_decode(file_get_contents('php://input'), true);
        if (is_array($aJson)) {
            $this->oRequest = array_merge($_REQUEST, $aJson);
        } else {
            $this->oRequest = $_REQUEST;
        }

        if (isset($this->oRequest["ajaxAction"])) {
            $this->sTypeAction = "AjaxAction";
            $this->sAction     = $this->oRequest["ajaxAction"];
        }
        if (isset($this->oRequest["iqDevAction"])) {
            $this->sTypeAction = "Action";
            $this->sAction     = $this->oRequest["iqDevAction"];
        }
        if (isset($this->sAction) && isset($this->sTypeAction)) {
            $sName = $this->sAction . $this->sTypeAction;
            $this->$sName();
        }
    }

    /**
     * Автоматически вызывает нужные экшены
     *
     * @param $methodName
     * @param $arguments
     *
     * @return void
     */
    public function __call($methodName, $arguments)
    {
        $this->setAjaxResponse(
            [
                'method' => $methodName,
                'response' => 'Такого метода API не существует',
                'params' => $arguments
            ]
        );
    }

    /**
     * Установка ответа для Ajax Action
     *
     * @param $result
     *
     * @return void
     */
    private function setAjaxResponse($result)
    {
        Header('Access-Control-Allow-Origin:*');
        Header('Content-Type: application/json');
        Header('Access-Control-Allow-Credentials:false');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Получение ответа для non-Ajax Action
     *
     * @param $sAction
     *
     * @return mixed
     */
    public function response($sAction)
    {
        if ($sAction == $this->sAction) {
            return $this->dResponse;
        } else {
            return [
                'success' => false,
                'message' => [
                    'Такого обработчика не зарегистрировано',
                ],
            ];
        }
    }

    /**
     * Установка ответа для non-Ajax Action
     *
     * @param $result
     *
     * @return void
     */
    private function setResponse($result)
    {
        $this->dResponse = $result;
    }

    /**
     * Тестовая отправка
     *
     * @return void
     */
    public function testSendAjaxAction()
    {
        $this->setAjaxResponse($this->oRequest);
    }

    /**
     * Обработка формы подписки
     *
     * @return void
     */
    public function formSubscribeAjaxAction()
    {
        $aResult = FormsHandler::subscribeNewsAjaxAction($this->oRequest);
        $this->setAjaxResponse($aResult);
    }

    /**
     * Обработка формы калькулятор
     *
     * @return void
     */
    public function formCalculationAjaxAction()
    {
        $aResult = FormsHandler::calculatebuyerAjaxAction($this->oRequest);
        $this->setAjaxResponse($aResult);
    }

    /**
     * Обработка формы на добавление отзывов
     *
     * @return void
     */
    public function formReviewAjaxAction()
    {
        $aResult = FormsHandler::reviewAjaxAction($this->oRequest);
        $this->setAjaxResponse($aResult);
    }

    /**
     * Обработка формы на вопросы , страница - service
     *
     * @return void
     */
    public function formQuestionServiceAjaxAction()
    {
        $aResult = FormsHandler::questionserviceAjaxAction($this->oRequest);
        $this->setAjaxResponse($aResult);
    }

    /**
     * Обработка формы вопросов с деталок новостей
     *
     * @return void
     */
    public function formQuestionAjaxAction()
    {
        $aResult = FormsHandler::questionAjaxAction($this->oRequest);
        $this->setAjaxResponse($aResult);
    }

    /**
     * Обработка формы консультации
     *
     * @return void
     */
    public function formInstallmentAjaxAction()
    {
        $arResult = FormsHandler::installmentAjaxAction($this->oRequest);
        $this->setAjaxResponse($arResult);
    }
}
