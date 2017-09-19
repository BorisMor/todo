<?php

namespace app\component;

use app\AppTodo;
use app\BaseObject;
use app\Helper;
use app\models\TodoItems;

class TodoComponent extends BaseObject
{
    private static $_component;
    protected $attributes;
    protected $userId;

    public function __construct($config = null)
    {
        if  ($config instanceof AppTodo) {
           $this->attributes = $config->getBodyJson();
           $this->userId = $config->getTokenUserId();
        }
    }

    /**
     * Вернет экземляр компонента
     * @param AppTodo $config
     * @return static
     */
    public static function component($config = null)
    {
        if (!empty(static::$_component)) {
            return static::$_component;
        }

        static::$_component = new TodoComponent($config);
        return static::$_component;
    }

    /**
     * Добавить запись
     */
    public function actionInsertItems()
    {
        /** @var TodoItems $model */
        $model = TodoItems::createObject($this->attributes);
        $model->user_id = $this->userId;
        $model->id = null;
        $model->save();
        return $model->getAttributes();
    }

    /**
     * Список записей
     */
    public function actionListItems()
    {
        $sql = 'SELECT * FROM todo_items WHERE user_id = :user_id';
        $lst = Helper::getDb()->queryParam(
            $sql,
            [':user_id' => $this->userId]
        );
        return $lst;
    }

    /**
     * Вернет TodoItems
     * C проверкой что запись для текущего пользователя
     *
     * @param $id
     * @return TodoItems
     * @throws \Exception
     */
    protected function findTodoById($id)
    {
        /** @var TodoItems $model */
        $model = TodoItems::findById($id);
        if (empty($model)) {
            throw new \Exception('Запись не найдена ' . $id);
        }

        if ($model->user_id !== $this->userId) {
            throw new \Exception('Ошибка доступа до записи ' . $id);
        }

        return $model;
    }

    /**
     * Обновить запись
     * @param $id
     * @return TodoItems
     */
    public function actionUpdateItems($id)
    {
        $model = $this->findTodoById($id);
        $model->attributes = $this->attributes;
        $model->user_id = $this->userId;
        $model->save();

        return $model;
    }

    /**
     * Удалить запись
     * @param $id
     * @return bool
     */
    public function actionDeleteItems($id)
    {
        $model = $this->findTodoById($id);
        return $model->delete();
    }
}