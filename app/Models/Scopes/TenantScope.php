<?php

namespace App\Models\Scopes;

use App\Support\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantManager::class)->id();

        if ($tenantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);

        if (auth()->check()) {
            $allowedStores = DB::table('user_store_access')
                ->where('user_id', auth()->id())
                ->pluck('store_id')
                ->all();

            if (! empty($allowedStores)) {
                $tablesWithStoreId = ['products', 'sales', 'stock_mutations'];
                if (in_array($model->getTable(), $tablesWithStoreId)) {
                    $builder->whereIn($model->getTable().'.store_id', $allowedStores);
                } elseif ($model->getTable() === 'stores') {
                    $builder->whereIn($model->getTable().'.id', $allowedStores);
                }
            }
        }
    }
}
