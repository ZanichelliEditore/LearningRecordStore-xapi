<?php
namespace App\Http\Repositories;

use App\Models\Lrs;
use App\Http\Repositories\RepositoryInterface;


class LrsRepository implements RepositoryInterface
{

    public function all($query, $orderBy, $order, $limit)
    {
        if (empty($query)) {
            return  Lrs::orderBy($orderBy, $order)
                        ->paginate($limit);
        }

        return Lrs::where('title', 'LIKE', '%' . $query . '%')
                    ->orWhere('folder', 'LIKE', '%' . $query . '%')
                    ->orderBy($orderBy, $order)
                    ->paginate($limit);
    }

    public function save($lrs)
    {
        return $lrs->save();
    }

    public function delete($lrs)
    {
        return $lrs->delete();
    }

    public function find($id)
    {
        return Lrs::where('_id', $id)->first();
    }
}