<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifaModel extends Model
{
    protected $table            = 'tarifas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'concepto_id',
        'categoria_id',
        'periodo_id',
        'valor',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'concepto_id'    => 'required|integer',
        'periodo_id'     => 'required|integer',
        'valor'          => 'required|decimal',
        'vigente_desde'  => 'required|valid_date',
    ];

    /**
     * Get all tarifas with related data
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->select('tarifas.*, cc.nombre as concepto_nombre, cc.codigo as concepto_codigo, cat.nombre as categoria_nombre, p.nombre as periodo_nombre')
            ->join('conceptos_cobro cc', 'cc.id = tarifas.concepto_id')
            ->join('categorias cat', 'cat.id = tarifas.categoria_id', 'left')
            ->join('periodos p', 'p.id = tarifas.periodo_id');

        if (!empty($filters['concepto_id'])) {
            $builder->where('tarifas.concepto_id', $filters['concepto_id']);
        }

        if (!empty($filters['categoria_id'])) {
            $builder->where('tarifas.categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['periodo_id'])) {
            $builder->where('tarifas.periodo_id', $filters['periodo_id']);
        }

        return $builder->orderBy('cc.orden', 'ASC')
            ->orderBy('cat.nombre', 'ASC')
            ->findAll();
    }

    /**
     * Get tariff for a specific concept/category/period
     */
    public function getTarifa(int $conceptoId, ?int $categoriaId, int $periodoId): ?array
    {
        $builder = $this->where('concepto_id', $conceptoId)
            ->where('periodo_id', $periodoId)
            ->where('vigente_desde <=', date('Y-m-d'))
            ->groupStart()
                ->where('vigente_hasta IS NULL')
                ->orWhere('vigente_hasta >=', date('Y-m-d'))
            ->groupEnd();

        if ($categoriaId) {
            $builder->where('categoria_id', $categoriaId);
        } else {
            $builder->where('categoria_id IS NULL');
        }

        return $builder->orderBy('vigente_desde', 'DESC')->first();
    }

    /**
     * Get tarifa with details
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->select('tarifas.*, cc.nombre as concepto_nombre, cc.codigo as concepto_codigo, cat.nombre as categoria_nombre, p.nombre as periodo_nombre')
            ->join('conceptos_cobro cc', 'cc.id = tarifas.concepto_id')
            ->join('categorias cat', 'cat.id = tarifas.categoria_id', 'left')
            ->join('periodos p', 'p.id = tarifas.periodo_id')
            ->find($id);
    }
}
