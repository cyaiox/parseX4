<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 08/05/17
 * Time: 13:07
 */

namespace CallCenter;


class CallCenterCalls
{
    protected $record;
    protected $db;
    protected $log;
    public $ESTADOS = [
        'ATENDIDOS' => [
            'ANSWER' => 2,
            'HANGUP' => 10,
        ],
        'NO_CONTESTAN' => [
            'NOANSWERED' => 3,
            'CANCEL' => 7,
            'NORESPONSE' => 8,
        ],
        'FALLIDOS' => [
            'FAILED' => 4,
            'CONGESTION' => 6,
            'CHANUNAVAIL' => 9,
            'DONTCALL' => 13,
            'TORTURE' => 14,
            'INVALIDARGS' => 15,
            'COLGADO' => 17,
        ],
        'OCUPADOS' => [
            'BUSY' => 5,
        ],
        'CONTESTADOR' => [
            12,
            20
        ]
    ];
    public $PARSEESTADOS = [
        -2 => 2,
        2 => 2,
        3 => 3,
        7 => 3,
        8 => 3,
        4 => 4,
        5 => 5,
        6 => 4,
        9 => 4,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 4,
        14 => 4,
        15 => 4,
        16 => 16,
        17 => 4,
        18 => 18,
        19 => 19,
        21 => 21,
    ];

    public function __construct(Record $record, ConectorDB $db, Log $log)
    {
        $this->record = $record;
        $this->db = $db;
        $this->log = $log;
        $this->record->getRecords();
    }

    public function parseCall()
    {
        //
    }

    public function parseEstado($estado)
    {
        return $this->PARSEESTADOS[$estado];
    }

    public function registrarMovimiento()
    {
        if ($this->record->getEstado() == $this->ESTADOS['NO_CONTESTAN']['CANCEL'] &&
            strtoupper($this->record->getEstadoSoftswitch()) == 'ANSWER') {
            $this->record->setEstado($this->ESTADOS['ATENDIDOS']['HANGUP']);
        }

        $sql = "INSERT INTO OP.movimiento_saldo SET 
                  valor = {$this->record->getPrecio()}, 
                  id_canal = '{$this->record->getIDCanal()}', 
                  id_destinos_softswitch = '{$this->record->getIDDestinosSoftSwitch()}', 
                  telefono = '{$this->record->getTelefono()}', 
                  pin = '{$this->record->getPin()}', 
                  id_campania = '{$this->record->getIDCampania()}', 
                  id_tarea = '{$this->record->getIDTarea()}', 
                  id_base = '{$this->record->getIDBase()}', 
                  id_agente = '{$this->record->getIDAgente()}', 
                  interno = '{$this->record->getInterno()}', 
                  id_grabaciones = '{$this->record->getIDGrabaciones()}', 
                  talking_time_seconds = '{$this->record->getBill()}', 
                  talking_time = '{$this->record->getTalkingTime()}', 
                  holding_time = '{$this->record->getHoldingSec()}',
                  total_time = '{$this->record->getTotalTime()}', 
                  fecha = Now(), 
                  costo_prov = 0,
                  id_destinos = '{$this->record->getIDDestino()}',
                  tipo = '{$this->record->getTipo()}',
                  estado = '{$this->parseEstado($this->record->getEstado())}',
                  test_pid = '',
                  test_usuario = '', 
                  formato_discado = '{$this->record->getFormatoDiscado()}',
                  parse = 1";

        if($this->db->query($sql)) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function verificarEstado($estado_a_buscar, $grupo_a_buscar, $estados)
    {
        $buscar_estado = function($carry, $arr) use ($estado_a_buscar, $grupo_a_buscar, $estados) {
            $grupo = array_keys($estados, $arr);
            if (in_array($grupo[0], $grupo_a_buscar))
                return $carry || in_array($estado_a_buscar, $arr);

            return $carry;
        };

        return array_reduce($estados, $buscar_estado, false);
    }

    public function getIDGestion()
    {
        $sql = "SELECT id FROM asterisk.cc_integracion 
                WHERE id_agente = '{$this->record->getIDAgente()}' 
                  AND (primer_plano = 1 OR telefono = '{$this->record->getTelefono()}') 
                LIMIT 1";
        $record = $this->db->query($sql);

        if ($record) {
            return $record[0]['id'];
        }

        return false;
    }

    public function joinLlamadoGestion($id_movimiento, $id_gestion)
    {
        if ($id_movimiento && $id_gestion) {
            $sql = "INSERT INTO OP.comunicaciones_gestiones 
                    SET comunicaciones_tabla = 'movimiento_saldo', 
                        id_comunicacion = '{$id_movimiento}', 
                        id_gestion = '{$id_gestion}'";
            return $this->db->query($sql);
        }

        return false;
    }

    public function updateIntegracion($id_movimiento)
    {
        $sql = "UPDATE asterisk.cc_integracion 
                SET id_movimiento = '{$id_movimiento}' 
                WHERE id_tarea = '{$this->record->getIDTarea()}' AND id_base = '{$this->record->getIDBase()}'";

        return $this->db->query($sql);
    }

    public function procesarContactado()
    {
        $sql = "UPDATE {$this->record->getBase()} 
                SET estado = '{$this->record->getEstado()}', repite_resultado = 0
                WHERE idtarea = '{$this->record->getIDTarea()}'";

        return $this->db->query($sql);
    }

    public function procesarNoContactado()
    {
        $sql = "UPDATE {$this->record->getBase()} 
                SET procesar = 9, 
                    subprocesar = NULL, 
                    ultima_fecha = NOW(), 
                    cod_resultado_contacto = 0,
                    repite_resultado = repite_resultado + 1 
                WHERE idtarea = '{$this->record->getIDTarea()}'
                AND estado = {$this->record->getEstado()}";
        $this->db->query($sql);

        # $this->setAgendadaPerdida();

        return $this->verAgendadoSinContactar($this->esModoPropietaria());
    }

    public function setAgendadaPerdida()
    {
        $sql = "UPDATE {$this->record->getBase()} 
                SET clase = '40' 
                WHERE idtarea = '{$this->record->getIDTarea()}' AND clase = 2";
        return $this->db->query($sql);
    }

    public function esModoPropietaria()
    {
        $sql = "SELECT id_agendado 
                FROM OP.agendados_agente 
                WHERE id_agendado = '{$this->record->getIDAgendado()}' AND modo_agenda = '0'";
        $record = $this->db->query($sql);

        if ($record) {
            return true;
        }

        return false;
    }

    public function aumentarPenalty()
    {
        $sql = "SELECT usar_penalty FROM asterisk.campanias WHERE id = '{$this->record->getIDCampania()}'";
        list($usar_penalty) = $this->db->query($sql, 1);

        if ($usar_penalty) {
            $sql = "UPDATE {$this->record->getBase()} 
                    SET penalty = penalty + 1 
                    WHERE idtarea = '{$this->record->getIDTarea()}'";
        }
    }

    public function verAgendadoSinContactar($es_propietaria)
    {
        $sql = "UPDATE asterisk.queue_login 
                SET estado = '" . (($es_propietaria) ? 'IN' : 'OUT') . "' 
                WHERE id_agente = '{$this->record->getIDAgente()}' AND estado = 'AGENDA'";

        return $this->db->query($sql);
    }
}