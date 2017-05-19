<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 08/05/17
 * Time: 13:37
 */

namespace CallCenter;
use DateTime;


class Record
{
    protected $records;
    protected $iterator;
    protected $table;
    protected $campaign_table = 'asterisk.campanias';
    protected $db;
    protected $id;
    protected $id_campania;
    protected $id_tarea;
    protected $id_base;
    protected $base;
    protected $backoffice;
    protected $id_grabaciones;
    protected $unique_id;
    protected $telefono;
    protected $estado;
    protected $id_canal;
    protected $id_destinos_softswitch;
    protected $id_agente;
    protected $interno;
    protected $pin;
    protected $channel;
    protected $billsec;
    protected $id_agendado;
    protected $estado_softswitch;
    protected $formato_discado;
    protected $fecha_inicio;
    protected $fecha_fin;
    protected $fecha_inicio_talking;
    protected $tipo;
    protected $id_destino = 0;

    public function __construct(ConectorDB $db, $table)
    {
        $this->table = $table;
        $this->db = $db;
    }

    public function getRecords()
    {
        //
    }

    public function getRecord()
    {
        if ($this->iterator->valid()) {
            $this->setValues($this->iterator->current());
            $this->iterator->next();
            return true;
        }

        return false;
    }

    public function deleteRecord()
    {
        return false;
    }

    public function setValues(array $result)
    {
        foreach ($result as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function getBase()
    {
        if (! $this->base) {
            $sql = "SELECT tabla_prospectos FROM asterisk.bases WHERE id_extra = '{$this->id_base}'";
            $record = $this->db->query($sql);
            $this->base = $record['tabla_prospectos'];
        }

        return $this->base;
    }

    public function getBackOffice()
    {
        if (! $this->backoffice) {
            $sql = "SELECT tabla_backoffice FROM {$this->campaign_table} WHERE id = '$$this->id_campania'";
            $record = $this->db->query($sql);
            $this->base = $record['tabla_backoffice'];
        }

        return $this->backoffice;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getBill()
    {
        $fecha_inicio = new DateTime($this->fecha_inicio);
        $fecha_fin = new DateTime($this->fecha_fin);
        return max(0, ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp()));
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function getEstadoSoftswitch()
    {
        return $this->estado_softswitch;
    }

    public function getPin()
    {
        return $this->pin;
    }

    public function getFormatoDiscado()
    {
        return $this->formato_discado;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getCampaignTable()
    {
        return $this->campaign_table;
    }

    public function getIDDestino()
    {
        return $this->id_destino;
    }

    public function getInterno()
    {
        return $this->interno;
    }

    public function getIDAgente()
    {
        return $this->id_agente;
    }

    public function getIDBase()
    {
        return $this->id_base;
    }

    public function getIDTarea()
    {
        return $this->id_tarea;
    }

    public function getIDCampania()
    {
        return $this->id_campania;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function getIDDestinosSoftSwitch()
    {
        return $this->id_destinos_softswitch;
    }

    public function getIDCanal()
    {
        return $this->id_canal;
    }

    public function getIDGrabaciones()
    {
        return $this->id_grabaciones;
    }

    public function getIDAgendado()
    {
        return $this->id_agendado;
    }

    public function getTalkingTime()
    {
        return max(0, ceil($this->getBill() / 60));
    }

    public function getHoldingSec()
    {
        $fecha_inicio = new DateTime($this->fecha_inicio);
        $fecha_fin = new DateTime($this->fecha_inicio_talking);
        return max(0, ($fecha_fin->getTimeStamp() - $fecha_inicio->getTimestamp()));
    }

    public function getHoldingSecFailed()
    {
        return 0;
    }

    public function getTotalTime()
    {
        return max(0, ($this->getBill() + $this->getHoldingSec()));
    }

    public function getPrecio()
    {
        return max(0, 0);
    }
}