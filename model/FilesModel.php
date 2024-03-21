<?php

namespace Model;

use Model\ExecuteQuery;

class FilesModel
{
    protected $table = 'files';

    public function update($data)
    {
        $executeQuery = new executeQuery();
        $result = $executeQuery->executeQuery($this->table, $data, 'update');
        return $result;
    }

    public function insert($data)
    {
        $executeQuery = new executeQuery();
        $result = $executeQuery->executeQuery($this->table, $data, 'insert');
        return $result;
    }

    public function get($filter = '', $orderBy = '', $limit = 300000000)
    {
        $executeQuery = new executeQuery();
        $result = $executeQuery->get($this->table, $filter, $orderBy, $limit);
        return $result;
    }

    public function close()
    {
        $executeQuery = new executeQuery();
        $executeQuery->close();
    }
}
