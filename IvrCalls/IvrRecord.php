<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 20/05/17
 * Time: 11:32
 */

namespace CallCenter\IvrCalls;
use CallCenter\ConectorDB;
use CallCenter\Record;
use ArrayObject;
use DateTime;


class IvrRecord extends Record
{
    protected $table = 'asterisk.cc_ivr';
    protected $tipo = 'ivr';

    public function __construct(ConectorDB $db, $table)
    {
        parent::__construct($db, $table);
    }

    public function getRecords()
    {
        $sql = "SELECT idtarea AS id_tarea, idcampania, subagrupacion, telefono, estado, id_campania, base, id, 
                (SELECT pin FROM {$this->campaign_table} WHERE id = id_campania limit 1) AS pin, id_canal, id_base
                FROM {$this->table} 
                WHERE estado IN (3, 4, 5, 6, 7, 9, 10, 12, 13, 14, 15, 17, 18, 19) AND subagrupacion = 'IVR'";

        $this->records =  new ArrayObject($this->db->query($sql));
        $this->iterator = $this->records->getIterator();
        $this->iterator->rewind();
    }

    public function deleteRecord()
    {
        $sql = "DELETE FROM {$this->table} WHERE id = '{$this->id}'";

        return $this->db->query($sql);
    }
}