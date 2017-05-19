<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 19/05/17
 * Time: 16:12
 */

namespace CallCenter\IntegrationCalls;
use CallCenter\CallCenterCalls;
use CallCenter\ConectorDB;
use CallCenter\Log;
use CallCenter\Record;


class IntegrationCalls extends CallCenterCalls
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
            if ($this->joinLlamadoGestion($this->record->getIDMovimientoSaldo(), $this->record->getIDGestion())) {
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