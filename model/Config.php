<?php

/**
 *
 *      ___                       ___           ___           ___     
 *     /\  \          ___        /\  \         /\  \         /\__\    
 *     \:\  \        /\  \       \:\  \       /::\  \       /::|  |   
 *      \:\  \       \:\  \       \:\  \     /:/\:\  \     /:|:|  |   
 *      /::\  \      /::\__\      /::\  \   /::\~\:\  \   /:/|:|  |__ 
 *     /:/\:\__\  __/:/\/__/     /:/\:\__\ /:/\:\ \:\__\ /:/ |:| /\__\
 *    /:/  \/__/ /\/:/  /       /:/  \/__/ \/__\:\/:/  / \/__|:|/:/  /
 *   /:/  /      \::/__/       /:/  /           \::/  /      |:/:/  / 
 *   \/__/        \:\__\       \/__/            /:/  /       |::/  /  
 *                 \/__/                       /:/  /        /:/  /   
 *                                             \/__/         \/__/    
 *
 */

namespace Model;

class Config
{
    private $servername;
    private $username;
    private $password;
    private $database;
    private $conn;

    public function __construct()
    {
        $this->servername = '152.53.16.231';
        $this->username = 'toan-users-api';
        $this->password = 'gQd3lL9ml43PJLBzRYoG';
        $this->database = 'toan-users-api';

    }

    public function connect()
    {
        $this->conn = new \mysqli($this->servername, $this->username, $this->password, $this->database);
		
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
		return $this->conn;

    }

    public function close()
    {
        // Đóng kết nối
        $this->conn->close();
    }
}


