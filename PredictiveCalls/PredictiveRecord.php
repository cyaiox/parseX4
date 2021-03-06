<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 10/05/17
 * Time: 10:22
 */

namespace CallCenter\PredictiveCalls;
use CallCenter\Record;
use CallCenter\ConectorDB;
use ArrayObject;
use DateTime;


class PredictiveRecord extends Record
{
    protected $table = 'asterisk.cc_predictivo';
    protected $tipo = 'predictivo';

    public function __construct(ConectorDB $db, $table)
    {
        parent::__construct($db, $table);
    }

    public function getRecords()
    {
        $sql = "SELECT id, idtarea AS id_tarea, id_base, idcampania AS extendido, subagrupacion, telefono, 
                       interno, estado, agente, id_agente, base, id_campania, pin, id_grabaciones,
					   fecha_fin, fecha_inicio_talking, fecha_inicio, id_canal, id_destinos_softswitch, 
					   id_agendados_discador AS id_agendado, estado_softswitch, formato_discado, 
					   id_cc_integracion AS id_gestion
                FROM {$this->table}
                WHERE estado IN (3, 4, 5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19)";

        $this->records =  new ArrayObject($this->db->query($sql));
        $this->iterator = $this->records->getIterator();
        $this->iterator->rewind();
    }

    public function deleteRecord()
    {
        $sql = "DELETE FROM {$this->table} WHERE id = '{$this->id}' AND telefono = '{$this->telefono}'";

        return $this->db->query($sql);
    }

    public function getBill()
    {
        if ($this->fecha_inicio_talking && $this->fecha_fin) {
            $fecha_inicio = new DateTime($this->fecha_inicio_talking);
            $fecha_fin = new DateTime($this->fecha_fin);
            return ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp());
        }

        return 0;
    }

    public function getHoldingSec()
    {
        if ($this->fecha_inicio && $this->fecha_inicio_talking) {
            $fecha_inicio = new DateTime($this->fecha_inicio);
            $fecha_fin = new DateTime($this->fecha_inicio_talking);

            return ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp());
        }

        return 0;
    }

    public function getHoldingSecFailed()
    {
        if ($this->fecha_inicio && $this->fecha_fin) {
            $fecha_inicio = new DateTime($this->fecha_inicio);
            $fecha_fin = new DateTime($this->fecha_fin);

            return ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp());
        }

        return 0;
    }
}