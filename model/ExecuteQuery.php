<?php

namespace Model;

use Model\Config;

class ExecuteQuery
{
    private $db;

    public function __construct()
    {
        $config = new Config();
        $this->db = $config->connect();
    }
    public function executeQuery($tableName, $data, $queryType)
    {
        $query = "";
        $params = array();
        $types = "";

        if ($queryType === "update") {
            $query = "UPDATE $tableName SET ";
            $condition = $data['condition'];
            unset($data['condition']);
        } elseif ($queryType === "insert") {
            $query = "INSERT INTO $tableName (";
        }

        foreach ($data as $key => $value) {
            if ($queryType === "update") {
                $query .= "`$key`" . " = ?, ";
            } elseif ($queryType === "insert") {
                $query .= "`$key`" . ", ";
            }
            $params[] = &$data[$key];
            $types .= $this->getVariableType($value);
        }

        if ($queryType === "update") {
            $query = rtrim($query, ", ");
            $query .= " WHERE " . $condition;
        } elseif ($queryType === "insert") {
            $query = rtrim($query, ", ");
            $query .= ") VALUES (";

            for ($i = 0; $i < count($data); $i++) {
                $query .= "?, ";
            }

            $query = rtrim($query, ", ");
            $query .= ")";
        }


        $stmt = $this->db->prepare($query);
        $errorInfo = $this->db->error;

        if($errorInfo) {
            echo json_encode($errorInfo);die;
        }
        if ($stmt) {
            $bindParams = array_merge(array($types), $params);
            $bindParamsRefs = array();

            foreach ($bindParams as $key => $value) {
                $bindParamsRefs[$key] = &$bindParams[$key];
            }

            call_user_func_array(array($stmt, 'bind_param'), $bindParamsRefs);

            $stmt->execute();
            $result = $stmt->affected_rows;

            return $result;
        } else {
            return false;
        }
    }

    public function get($tableName, $filter = '', $orderBy = '', $limit = 10)
    {
        $sql = "SELECT * FROM $tableName";

        if (!empty($filter)) {
            $sql .= " WHERE " . $filter;
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }

        $result = $this->db->query($sql);

        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }

    public function close()
    {
        $this->db->close();
    }
    
    private function getVariableType($var)
    {
        if (is_int($var)) {
            return "i";
        } elseif (is_float($var)) {
            return "d";
        } elseif (is_string($var)) {
            return "s";
        } else {
            return "b";
        }
    }
}
