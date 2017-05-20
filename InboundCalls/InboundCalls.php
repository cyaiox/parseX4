<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 18/05/17
 * Time: 18:22
 */

namespace CallCenter\InboundCalls;
use CallCenter\CallCenterCalls;
use CallCenter\ConectorDB;
use CallCenter\Log;
use CallCenter\Record;
use CallCenter\KillProcess;


class InboundCalls extends CallCenterCalls
{
    public function __construct(Record $record, ConectorDB $db, Log $log)
    {
        parent::__construct($record, $db, $log);
    }

    public function parseCall()
    {
        parent::parseCall();
        while ($this->record->getRecord()) {
            $this->log->log(
                "Record obtenido correctamente de {$this->record->getTable()}",
                $this->record->getTipo(),
                $this->record->getID()
            );
            $this->log->log(
                "Record con estado [{$this->record->getEstado()}]",
                $this->record->getTipo(),
                $this->record->getID()
            );
            if ($this->record->getEstado() == $this->ESTADOS['ATENDIDOS']['HANGUP']) {
                $this->log->log(
                    "Record contactado procesado correctamente",
                    $this->record->getTipo(),
                    $this->record->getID()
                );
                $this->setTime('id_agente', $this->record->getIDAgente());
                $this->setTime('interno', $this->record->getInterno());
            } else if ($this->record->getEstado() == $this->ESTADOS['NO_CONTESTAN']['NOANSWERED'] || $this->record->getEstado() == 107) {
                if ($this->record->getTelefono()) {
                    $this->log->log(
                        "Preparando Record para insertarlo en asterisk.cc_integracion",
                        $this->record->getTipo(),
                        $this->record->getID()
                    );
                    $sql = "INSERT INTO asterisk.cc_integracion SET 
                              id_campania = '{$this->record->getIDCampania()}', 
                              id_tarea = '{$this->record->getIDTarea()}', 
                              telefono = '{$this->record->getTelefono()}', 
                              id_form = '0', 
                              clase = '8', 
                              fechahora = NOW(), 
                              disa_id = '{$this->record->getDisaID()}',
                              pin = {$this->record->getPin()}";
                    $this->log->log(
                        "SQL a realizar {$sql}",
                        $this->record->getTipo(),
                        $this->record->getID()
                    );
                    if ($this->db->query($sql)) {
                        $this->log->log(
                            "Record procesado correctamente",
                            $this->record->getTipo(),
                            $this->record->getID()
                        );
                        KillProcess::kill('USR1', 'call_array_perdidos.pm');
                    }
                }
            }

            $this->setPenalty('id_agente', $this->record->getIDAgente());
            $this->setPenalty('interno', $this->record->getInterno());

            if ($this->registrarMovimiento()) {
                if (!$this->record->deleteRecord()) {
                    $this->log->log("Error eliminando el registro", $this->record->getTipo(), $this->record->getID());
                }
            }
        }

        $this->record->getRecords();
    }
}