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
            if ($this->record->getEstado() == $this->ESTADOS['FALLIDOS']['COLGADO']) {
                $sql = "UPDATE {$this->record->getBase()} SET estado = " . $this->ESTADOS['FALLIDOS']['FAILED'] . " 
                        WHERE idtarea = '{$this->record->getIDTarea()}'";
                $this->db->query($sql);
                $this->log->log(
                    "Actualizar Record en la base [{$this->record->getBase()}]",
                    $this->record->getTipo(),
                    $this->record->getID()
                );
            }

            $this->joinLlamadoGestion($this->registrarMovimiento(), $this->getIDGestion());
            $this->log->log(
                "Record procesado correctamente",
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

        $this->record->getRecords();
    }
}