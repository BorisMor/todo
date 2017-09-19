<?php
namespace app\models;

use app\BaseModel;
use app\Helper;

class TodoItems extends BaseModel
{
    protected static $pk = 'id';

    protected static $_attributes = [
        'id', 'user_id', 'title', 'completed'
    ];

    public $id;
    public $user_id;
    public $title;
    public $completed;


    /**
     * Добавить запись
     */
    function insert()
    {
        $sql = '
            INSERT INTO todo_items(user_id, title, completed) 
            VALUES(:user_id, :title, :completed);
        ';

        $this->db->execute($sql, [
            ':user_id' => $this->user_id,
            ':title' => $this->title,
            ':completed' => $this->completed,
        ]);

        $this->id = $this->db->getLastInsertId();
    }

    /**
     * Обвноить запись
     */
    function update()
    {
        $sql = '
          UPDATE todo_items 
          SET user_id = :user_id, title = :title, completed = :completed 
          WHERE id = :id;
        ';

        $this->db->execute($sql, [
            ':user_id' => $this->user_id,
            ':title' => $this->title,
            ':completed' => $this->completed,
            ':id' => $this->id
        ]);
    }

    /**
     * Найти запсь по ID
     * @param $id
     * @return null|static
     */
    public static function findById($id)
    {
        $sql = 'SELECT * FROM todo_items WHERE id = :id';
        $result = Helper::getDb()->queryParamOne($sql, [':id' => $id]);
        return static::createObject($result);
    }

    public function delete()
    {
        $sql = 'DELETE FROM todo_items WHERE id = :id ';
        $res = $this->db->execute($sql, ['id' => $this->id]);
        return $res;
    }
}