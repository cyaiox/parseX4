<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 08/05/17
 * Time: 13:22
 */

namespace CallCenter\ManualCalls;
use CallCenter\CallCenterCalls;
use CallCenter\ConectorDB;
use CallCenter\Log;
use CallCenter\Record;

class ManualCalls extends CallCenterCalls
{
    public function __construct(Record $record, ConectorDB $db, Log $log)
    {
        parent::__construct($record, $db, $log);
    }

    public function parseCall()
    {
        if ($this->record->getRecord()) {
            if ($this->record->getEstado() == self::ESTADOS['FALLIDOS']['COLGADO']) {
                $sql = "UPDATE {$this->record->getBase()} SET estado = " . self::ESTADOS['FALLIDOS']['FAILED'] . " 
                        WHERE idtarea = '{$this->record->getIDTarea()}'";
                $this->db->query($sql);
            }

            if ($this->joinLlamadoGestion($this->registrarMovimiento(), $this->getIDGestion())) {
                if (! $this->record->deleteRecord()) {
                    $this->log("Error eliminando el registro {$this->record->getTipo()} [{$this->record->getID()}]");
                }
            }
        }
    }
}