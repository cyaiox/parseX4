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
    protected $id_gestion;
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
    protected $pin_padre;
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
    protected $saldo;
    protected $id_moneda;
    protected $tipo_cliente;
    protected $costo_llamado;
    protected $valor_llamado = 0;

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

        $this->initBase();
        $this->initBackOffice();
        $this->initIDDestino();
        $this->getParametrosTarificar();
    }

    protected function initBase()
    {
        if (! $this->base) {
            $sql = "SELECT tabla_prospectos FROM asterisk.bases WHERE id_extra = '{$this->id_base}'";
            $record = $this->db->query($sql);
            $this->base = $record[0]['tabla_prospectos'];
        }
    }

    public function getBase()
    {
        return $this->base;
    }

    protected function initBackOffice()
    {
        if (! $this->backoffice) {
            $sql = "SELECT tabla_backoffice FROM {$this->campaign_table} WHERE id = '{$this->id_campania}'";
            $record = $this->db->query($sql);
            $this->base = $record[0]['tabla_backoffice'];
        }
    }

    public function getBackOffice()
    {
        return $this->backoffice;
    }

    public function getID()
    {
        return $this->id;
    }

    public function getIDGestion()
    {
        return $this->id_gestion;
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

    public function setIDDestino($id_destino)
    {
        $this->id_destino = $id_destino;
    }

    protected function initIDDestino()
    {
        $sql = "SELECT idDestino AS id_destino, Caracteristica AS digitos 
                FROM asterisk.Destino 
                WHERE pin = '{$this->pin_padre}' 
                AND Nombre = 'local'
                AND Caracteristica = LENGTH('{$this->telefono}')";

        $record = $this->db->query($sql);

        if ($record) {
            $this->id_destino = $record[0]['id_destino'];
        } else {
            $sql = "SELECT idDestino AS id_destino, Caracteristica AS digitos
                    FROM asterisk.Destino 
                    WHERE pin = '{$this->pin_padre}' 
                    AND Nombre <> 'local' 
                    AND Caracteristica = LEFT('{$this->telefono}', LENGTH(Caracteristica))
                    ORDER BY idDestino DESC";

            $record = $this->db->query($sql);
            if ($record) {
                $this->id_destino = $record[0]['id_destino'];
            }
        }
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

    public function getPinPadre()
    {
        if (! isset($this->pin_padre)) {
            $sql = "SELECT padre from OP.clientes where pin = {$this->pin}";
            $record = $this->db->query($sql);

            if ($record) {
                $this->pin_padre = $record[0]['padre'];
            }
        }

        return $this->pin_padre;
    }

    protected function getParametrosTarificar()
    {
        $sql = "SELECT saldo, idMoneda AS id_moneda, tipo_cliente 
                FROM OP.clientes 
                WHERE pin = '{$this->pin}'";

        $record = $this->db->query($sql);

        if ($record) {
            $this->saldo = $record[0]['saldo'];
            $this->id_moneda = $record[0]['id_moneda'];
            $this->tipo_cliente = $record[0]['tipo_cliente'];
        }

        $sql = "SELECT valor 
                FROM Tarifa 
                WHERE idMoneda = '{$this->id_moneda}' 
                AND idDestino = '{$this->id_destino}' 
                AND idTarifa IN (SELECT idTarifa FROM Tarifa_cliente WHERE pin = '{$this->pin}')";

        $record = $this->db->query($sql);

        if ($record) {
            $this->valor_llamado = $record[0]['valor'];
        }
    }

    public function getSaldo()
    {
        return $this->saldo;
    }

    public function getIDMoneda()
    {
        return $this->id_moneda;
    }

    public function getTipoCliente()
    {
        return $this->tipo_cliente;
    }

    public function getValorLlamado()
    {
        return $this->valor_llamado;
    }

    public function getCostoLlamado()
    {
        return $this->costo_llamado;
    }

    public function setCostoLlamado($costo_llamado)
    {
        $this->costo_llamado = $costo_llamado;
    }
}