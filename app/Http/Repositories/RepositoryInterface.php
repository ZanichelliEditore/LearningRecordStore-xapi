<?php
namespace App\Http\Repositories;

interface RepositoryInterface
{

    /**
     * Get all instances of model filtered by parameter:
     *
     * @param string $query
     * @param string $orderBy
     * @param string $order ASC/DESC
     * @param int $limit set pagination limited
     * @return Collection
     */
    public function all($query, $orderBy, $order, $limit);

    /**
     * Save $model in DB
     *
     * @param Object $model
     * @return boolean
     */
    public function save($model);

    /**
     * Delete $model from DB
     *
     * @param Object $model
     * @return boolean
     */
    public function delete($model);

    /**
     * Find $model by $id
     *
     * @param integer $id
     * @return Lrs
     */
    public function find($id);
}
