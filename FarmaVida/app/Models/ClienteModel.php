<?php
class ClienteModel extends Model
{
    public function clientesExecutar($sql)
    {
        return $this->db()->prepare($sql);
    }
}
