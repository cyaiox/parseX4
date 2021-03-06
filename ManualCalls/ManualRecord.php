<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 08/05/17
 * Time: 14:39
 */

namespace CallCenter\ManualCalls;
use CallCenter\ConectorDB;
use CallCenter\Record;
use ArrayObject;
use DateTime;


class ManualRecord extends Record
{
    protected $table = 'asterisk.cc_manual';
    protected $tipo = 'manual';
    protected $pin_interno;

    public function __construct(ConectorDB $db, $table)
    {
        parent::__construct($db, $table);
    }

    public function getRecords()
    {
        $sql = "SELECT M.idmanual AS id, M.unique_id AS unique_id, M.id_grabaciones AS id_grabaciones, 
                       M.telefono AS telefono, M.estado AS estado, M.id_canal AS id_canal, 
                       M.id_destinos_softswitch AS id_destinos_softswitch, M.id_agente AS id_agente,
                       M.interno AS interno, C.pin AS pin, I.pin AS pin_interno, M.channel AS channel,
                       M.fecha_ini AS fecha_inicio, M.fecha_fin AS fecha_fin, M.fechahora AS fecha_inicio_talking, 
                       M.id_campania AS id_campania, M.idtarea AS id_tarea, M.id_base AS id_base, 
                       M.estado_softswitch AS estado_softswitch, M.formato_discado AS formato_discado,
                       M.id_cc_integracion AS id_gestion 
                 FROM {$this->table} AS M
                 LEFT JOIN {$this->campaign_table} C ON C.id = M.id_campania
                 LEFT JOIN OP.Internos I ON I.interno = (if (M.interno = 0, -1, M.interno))
                 WHERE M.estado IN (-3, 3, -4, 4, -5, 5, -6, 6, -7, 7, -8, -9, 9, 10, 17, 15, 31)";

        $this->records =  new ArrayObject($this->db->query($sql));
        $this->iterator = $this->records->getIterator();
        $this->iterator->rewind();
    }

    public function deleteRecord()
    {
        $sql = "DELETE FROM {$this->table} WHERE idmanual = '{$this->id}'";

        return $this->db->query($sql);
    }

    public function getBillSec()
    {
        if ($this->fecha_inicio && $this->fecha_inicio != '0000-00-00 00:00:00' && $this->fecha_fin) {
            $fecha_inicio = new DateTime($this->fecha_inicio);
            $fecha_fin = new DateTime($this->fecha_fin);

            return ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp());
        }

        return 0;
    }

    public function getBillSecProb()
    {
        if ($this->fecha_inicio_talking && $this->fecha_inicio_talking != '0000-00-00 00:00:00' && $this->fecha_fin) {
            $fecha_hora = new DateTime($this->fecha_inicio_talking);
            $fecha_fin = new DateTime($this->fecha_fin);

            return ($fecha_fin->getTimeStamp() - $fecha_hora->getTimestamp());
        }

        return 0;
    }

    public function getBill()
    {
        return max($this->getBillSec(), $this->getBillSecProb());
    }

    public function getPin()
    {
        if (isset($this->pin)) {
            return $this->pin;
        }

        return $this->pin_interno;
    }

    public function getHoldingSec()
    {
        return max(0, 0);
    }
}