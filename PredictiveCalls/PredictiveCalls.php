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
        if ($this->record->getRecord()) {
            if ($this->record->getEstado() == $this->ESTADOS['ATENDIDOS']['HANGUP']) {
                if ($this->updateIntegracion($this->registrarMovimiento()) && $this->procesarContactado()) {
                    if (! $this->record->deleteRecord()) {
                        $this->log("Error eliminando el registro {$this->record->getTipo()} [{$this->record->getID()}]");
                    }
                }
            } else if($this->verificarEstado($this->record->getEstado(), ['CONTESTADOR'], $this->ESTADOS)) {
                if ($this->registrarMovimiento() && $this->procesarNoContactado()) {
                    if (! $this->record->deleteRecord()) {
                        $this->log("Error eliminando el registro {$this->record->getTipo()} [{$this->record->getID()}]");
                    }
                }
            } else if(
                $this->verificarEstado(
                    $this->record->getEstado(),
                    ['NO_CONTESTAN', 'FALLIDOS', 'OCUPADOS'],
                    $this->ESTADOS
                )
            ) {
                if (
                    $this->joinLlamadoGestion($this->registrarMovimiento(), $this->getIDGestion()) &&
                    $this->procesarNoContactado()
                ) {
                    if (! $this->record->deleteRecord()) {
                        $this->log("Error eliminando el registro {$this->record->getTipo()} [{$this->record->getID()}]");
                    }
                }
            }
        }
    }
}