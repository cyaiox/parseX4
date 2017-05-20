<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 08/05/17
 * Time: 13:23
 */

namespace CallCenter\PredictiveCalls;
use CallCenter\CallCenterCalls;
use CallCenter\ConectorDB;
use CallCenter\Log;
use CallCenter\Record;


class PredictiveCalls extends CallCenterCalls
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
                if ($this->updateIntegracion($this->registrarMovimiento()) && $this->procesarContactado()) {
                    $this->setTime('interno', $this->record->getInterno());
                    $this->setTime('id_agente', $this->record->getIDAgente());
                    $this->log->log(
                        "Record contactado procesado correctamente",
                        $this->record->getTipo(),
                        $this->record->getID()
                    );
                    if (! $this->record->deleteRecord()) {
                        $this->log->log("Error eliminando el registro", $this->record->getTipo(), $this->record->getID());
                    }
                }
            } else if($this->verificarEstado($this->record->getEstado(), ['CONTESTADOR'], $this->ESTADOS)) {
                $this->procesarNoContactado();
                if ($this->registrarMovimiento()) {
                    $this->log->log(
                        "Record no contactado procesado correctamente",
                        $this->record->getTipo(),
                        $this->record->getID()
                    );
                    if (! $this->record->deleteRecord()) {
                        $this->log->log(
                            "Error eliminando el registro",
                            $this->record->getTipo(),
                            $this->record->getID()
                        );
                    }
                }
            } else if(
                $this->verificarEstado(
                    $this->record->getEstado(),
                    ['NO_CONTESTAN', 'FALLIDOS', 'OCUPADOS'],
                    $this->ESTADOS
                )
            ) {
                $this->joinLlamadoGestion($this->registrarMovimiento(), $this->record->getIDGestion());
                $this->procesarNoContactado();
                if (! $this->record->deleteRecord()) {
                    $this->log->log(
                        "Error eliminando el registro",
                        $this->record->getTipo(),
                        $this->record->getID()
                    );
                }
            }
        }

        $this->record->getRecords();
    }
}