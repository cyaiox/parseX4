<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 18/05/17
 * Time: 18:22
 */

namespace CallCenter\InboundCalls;
use CallCenter\ConectorDB;
use CallCenter\Record;
use ArrayObject;
use DateTime;


class InboundRecord extends Record
{
    protected $table = 'asterisk.cc_entrantes';
    protected $tipo = 'entrante';
    protected $disa_id;

    public function __construct(ConectorDB $db, $table)
    {
        parent::__construct($db, $table);
    }

    public function getRecords()
    {
        $sql = "SELECT idtarea AS id_tarea, id_campania, subagrupacion, telefono, interno, estado, 
                       agente, id_agente, id_grabaciones, PID, disa_id, fecha_inicio_talking, 
                       fecha_inicio, fecha_fin, id_cc_integracion AS id_gestion,
                       (SELECT C.pin FROM {$this->campaign_table} C WHERE C.id = id_campania limit 1) AS pin  
               FROM {$this->table}	
               WHERE estado IN (3, 4, 5, 6, 7, 9, 10, 13, 14, 15, 107)";

        $this->records =  new ArrayObject($this->db->query($sql));
        $this->iterator = $this->records->getIterator();
        $this->iterator->rewind();
    }

    public function deleteRecord()
    {
        $sql = "DELETE FROM {$this->table} WHERE idtarea = '{$this->id_tarea}'";

        return $this->db->query($sql);
    }

    public function getDisaID()
    {
        return $this->disa_id;
    }

}