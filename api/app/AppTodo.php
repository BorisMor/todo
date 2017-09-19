<?php

namespace app;

use app\models\Users;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Slim\Slim;


/**
 * Обертка над основным объектом Slim
 * Class AppTodo
 * @package app
 */
class AppTodo extends Slim
{
    use traitDb;

    private $_token;
    private $_tokenUser;

    public function __construct(array $userSettings = array())
    {
        parent::__construct($userSettings);
        $this->error([$this, 'answerError']); // обработчик ошибок
    }

    /**
     * Вернуть тело запроса как json объект
     * @return array
     * @throws \Exception
     */
    public function getBodyJson()
    {
        $body = $this->request->getBody();
        if (empty($body)) {
            return [];
        }

        try {
            return json_decode($body, true);
        } catch (\Exception $e) {
            throw new \Exception('Неверное json тело запроса');
        }
    }

    /**
     * json ответ
     * @param $data
     * @param int $code Код ответа
     */
    public function json($data, $code = 200)
    {
        $response = $this->response();
        $response['Content-Type'] = 'application/json';
        $response->status($code);
        $response->body(json_encode($data));
    }

    /**
     * Стандартный ответ
     * @param $data
     */
    public function answer($data)
    {
        $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Вывод сообщени об ошибках
     * @param \Exception|string $error
     */
    public function answerError($error)
    {
        $res = [
            'success' => false,
        ];

        if ($error instanceof \Exception) {
            $res['data'] = $error->getMessage();
            $res['stack'] = $error->getTraceAsString();
        } else {
            $res['data'] = $error;
        }

        $this->json($res, 500);
    }

    public function getToken()
    {
        if (!empty($this->_token)) {
            return $this->_token;
        }

        $this->_token = $this->request->headers('X-authorization');
        if (empty($this->_token)) {
            throw new \Exception('Ошибка авторизации');
        }

        return $this->_token;
    }

    /**
     * Полуичть
     * @return Users
     * @throws \Exception
     */
    public function getTokenUser()
    {
        if (!empty($this->_tokenUser)) {
            return $this->_tokenUser;
        }

        $this->_tokenUser = Users::findByToken($this->getToken());
        if (empty($this->_tokenUser)) {
            throw new \Exception('Перезайдите');
        }

        return $this->_tokenUser;
    }

    /**
     * Вернет только ID пользователя
     * @return mixed
     */
    public function getTokenUserId()
    {
        return $this->getTokenUser()->id;
    }
}