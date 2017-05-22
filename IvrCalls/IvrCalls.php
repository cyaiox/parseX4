<?php
/**
 * Created by PhpStorm.
 * User: programador2
 * Date: 20/05/17
 * Time: 11:31
 */

namespace CallCenter\IvrCalls;
use CallCenter\CallCenterCalls;
use CallCenter\ConectorDB;
use CallCenter\Log;
use CallCenter\Record;
use CallCenter\KillProcess;


class IvrCalls extends CallCenterCalls
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
                $this->procesarContactado();

                $this->log->log(
                    "Record contactado procesado correctamente",
                    $this->record->getTipo(),
                    $this->record->getID()
                );

                $this->tarifar();
            } else if ($this->record->getEstado() == $this->ESTADOS['CONTESTADOR'][0]) {
                $this->tarifar();
            } else {
                $this->procesarNoContactado();

                $this->log->log(
                    "Record no contactado procesado correctamente",
                    $this->record->getTipo(),
                    $this->record->getID()
                );
            }

            if ($this->registrarMovimiento()) {
                if (!$this->record->deleteRecord()) {
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

    public function procesarContactado()
    {
        $this->log->log(
            "Preparacion para realizar la actualizacion en [{$this->record->getBase()}]",
            $this->record->getTipo(),
            $this->record->getID()
        );
        $sql = "UPDATE {$this->record->getBase()} 
                SET finalizado = '1'
                WHERE idtarea = '{$this->record->getIDTarea()}'";
        $this->log->log("SQL a realizar: [{$sql}]", $this->record->getTipo(), $this->record->getID());
        return $this->db->query($sql);
    }

    public function procesarNoContactado()
    {
        $this->log->log(
            "Preparacion para realizar la actualizacion en [{$this->record->getBase()}]",
            $this->record->getTipo(),
            $this->record->getID()
        );
        $sql = "UPDATE {$this->record->getBase()} 
                SET estado = {$this->record->getEstado()},
                    procesar = '9'
                WHERE idtarea = '{$this->record->getIDTarea()}'";
        $this->log->log("SQL a realizar: [{$sql}]", $this->record->getTipo(), $this->record->getID());
        return $this->db->query($sql);
    }
}