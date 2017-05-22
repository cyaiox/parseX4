<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 19/05/17
 * Time: 16:12
 */

namespace CallCenter\IntegrationCalls;
use CallCenter\ConectorDB;
use CallCenter\Record;
use ArrayObject;
use DateTime;


class IntegrationRecord extends Record
{
    protected $table = 'asterisk.cc_integracion';
    protected $id_movimiento;
    protected $id_padre;
    protected $id_hijo;
    protected $tipo = 'integracion';

    public function __construct(ConectorDB $db, $table)
    {
        parent::__construct($db, $table);
    }

    public function getRecords()
    {
        $sql = "SELECT id, id_campania, id_form, id_tarea, id_base, codificacion, interno, id_agente, pin, id_movimiento 
                FROM {$this->table} 
                WHERE estado = 10 AND fechahora < date_sub(NOW(), INTERVAL 1 MINUTE)";

        $this->records =  new ArrayObject($this->db->query($sql));
        $this->iterator = $this->records->getIterator();
        $this->iterator->rewind();
    }

    public function deleteRecord()
    {
        $sql = "DELETE FROM {$this->table} WHERE id = '{$this->id}'";

        return $this->db->query($sql);
    }

    public function getIDMovimientoSaldo()
    {
        return $this->id_movimiento;
    }

    public function getIDGestion()
    {
        return $this->id;
    }

    public function getIDPadre()
    {
        return $this->id_padre;
    }

    public function getIDHijo()
    {
        return $this->id_hijo;
    }
}