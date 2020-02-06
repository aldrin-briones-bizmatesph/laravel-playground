<?php

namespace App\Repositories\Support;

use Illuminate\Support\Arr;

use Facades\App\Repositories\Support\QueryBuilder;

trait Searchable
{
    /**
     * Query Builder instance
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * Search for specific resources in the database.
     *
     * @return mixed
     */
    public function simpleSearch()
    {
        return $this->simpleSearchQuery()
            ->hasRelatedModels()
            ->doesntHaveRelatedModels()
            ->withRelatedModels()
            ->countRelatedModels()
            ->filter()
            ->sort()
            ->respond();
    }

    /**
     * Advanced search for specific resources in the database.
     *
     * @return mixed
     */
    public function advancedSearch()
    {
        return $this->advancedSearchQuery()
            ->hasRelatedModels()
            ->doesntHaveRelatedModels()
            ->withRelatedModels()
            ->countRelatedModels()
            ->filter()
            ->sort()
            ->respond();
    }

    /**
     * Builds the search query for the model.
     *
     */
    protected function simpleSearchQuery()
    {
        $this->query = $this->model->query()->where(function ($query) {
            foreach ($this->searchRules() as $column => $type) {
                if (request()->has($column)) {
                    QueryBuilder::query(
                        $column,
                        $type,
                        request()->$column,
                        $query,
                        request()->inverse,
                        request()->strict,
                        request()->null,
                        request()->notNull,
                        request()->limit
                    );
                }
            }
        });

        return $this;
    }

    /**
     * Builds the search strict query for the model.
     *
     */
    protected function advancedSearchQuery()
    {
        $this->query = $this->model->query();

        for ($i = 0; $i < count(request()->input('where')); $i++) {
            $this->query = $this->query->where(function ($query) use ($i) {
                foreach ($this->searchRules() as $column => $type) {
                    if (request()->has("where.$i.$column")) {
                        QueryBuilder::query(
                            $column,
                            $type,
                            request()->input("where.$i.$column"),
                            $query,
                            request()->input("where.$i.inverse"),
                            request()->input("where.$i.strict"),
                            request()->input("where.$i.$column.null"),
                            request()->input("where.$i.$column.notNull"),
                            request()->input("where.$i.limit")
                        );
                    }
                }
            });
        }

        return $this;
    }

    /**
     * Check if there are related models of the resource.
     *
     */
    protected function hasRelatedModels()
    {
        if (request()->has('has')) {
            foreach (request()->input('has') as $key => $value) {
                if (gettype(request()->input("has.$key")) === 'string') {
                    $this->query->has($value);

                    continue;
                }

                if (request()->has("has.$key.lessThan")) {
                    $this->query->has(request()->has("has.$key.relationship"), '<', request()->has("has.$key.lessThan"));
                } else if (request()->has("has.$key.lessThanEqualTo")) {
                    $this->query->has(request()->has("has.$key.relationship"), '<=', request()->has("has.$key.lessThanEqualTo"));
                } else if (request()->has("has.$key.greaterThan")) {
                    $this->query->has(request()->has("has.$key.relationship"), '>', request()->has("has.$key.greaterThanEqualTo"));
                } else if (request()->has("has.$key.greaterThanEqualTo")) {
                    $this->query->has(request()->has("has.$key.relationship"), '>=', request()->has("has.$key.greaterThanEqualTo"));
                }

                if (request()->has("has.$key.where")) {
                    $rules = Arr::get($this->relatedSearchRules(), request()->input("has.$key.relationship"));

                    $this->query = $this->query->whereHas(request()->input("has.$key.relationship"), function ($query) use ($rules, $value) {
                        $constraints = Arr::get($value, 'where');

                        for ($i = 0; $i < count($constraints); $i++) {
                            foreach ($rules as $column => $type) {
                                $where = Arr::get($constraints, $i);

                                if (Arr::has($where, $column)) {
                                    QueryBuilder::query(
                                        $column,
                                        $type,
                                        Arr::get($where, $column),
                                        $query,
                                        Arr::get($where, 'inverse'),
                                        Arr::get($where, 'strict'),
                                        Arr::get(Arr::get($where, $column), 'null'),
                                        Arr::get(Arr::get($where, $column), 'notNull'),
                                        Arr::get($where, 'limit')
                                    );
                                }
                            }
                        }
                    });
                }
            }
        }

        return $this;
    }

    /**
     * Check if there are no related models of the resource.
     *
     */
    protected function doesntHaveRelatedModels()
    {
        if (request()->has('doesntHave')) {
            foreach (request()->input('doesntHave') as $key => $value) {
                if (gettype(request()->input("has.$key")) === 'string') {
                    $this->query->doesntHave($value);

                    continue;
                }

                if (request()->has("doesntHave.$key.lessThan")) {
                    $this->query->doesntHave(request()->has("doesntHave.$key.relationship"), '<', request()->has("doesntHave.$key.lessThan"));
                } else if (request()->has("doesntHave.$key.lessThanEqualTo")) {
                    $this->query->doesntHave(request()->has("doesntHave.$key.relationship"), '<=', request()->has("doesntHave.$key.lessThanEqualTo"));
                } else if (request()->has("doesntHave.$key.greaterThan")) {
                    $this->query->doesntHave(request()->has("doesntHave.$key.relationship"), '>', request()->has("doesntHave.$key.greaterThanEqualTo"));
                } else if (request()->has("doesntHave.$key.greaterThanEqualTo")) {
                    $this->query->doesntHave(request()->has("doesntHave.$key.relationship"), '>=', request()->has("doesntHave.$key.greaterThanEqualTo"));
                }

                if (request()->has("doesntHave.$key.where")) {
                    $rules = Arr::get($this->relatedSearchRules(), request()->input("doesntHave.$key.relationship"));

                    $this->query = $this->query->whereDoesntHave(request()->input("doesntHave.$key.relationship"), function ($query) use ($rules, $value) {
                        $constraints = Arr::get($value, 'where');

                        for ($i = 0; $i < count($constraints); $i++) {
                            foreach ($rules as $column => $type) {
                                $where = Arr::get($constraints, $i);

                                if (Arr::has($where, $column)) {
                                    QueryBuilder::query(
                                        $column,
                                        $type,
                                        Arr::get($where, $column),
                                        $query,
                                        Arr::get($where, 'inverse'),
                                        Arr::get($where, 'strict'),
                                        Arr::get(Arr::get($where, $column), 'null'),
                                        Arr::get(Arr::get($where, $column), 'notNull'),
                                        Arr::get($where, 'limit')
                                    );
                                }
                            }
                        }
                    });
                }
            }
        }

        return $this;
    }

    /**
     * Get related models of the resource.
     *
     */
    protected function withRelatedModels()
    {
        if (request()->has('with')) {
            foreach (request()->input('with') as $key => $value) {
                if (gettype(request()->input("with.$key")) === 'string') {
                    $this->query->with($value);

                    continue;
                }

                if (request()->has("with.$key.orderBy")) {
                    $this->query->with([request()->input("with.$key.relationship") => function ($query) use ($key) {
                        $query->orderBy(request()->input("with.$key.orderBy"), request()->input("with.$key.direction") ?: 'asc');
                    }]);
                }

                if (request()->has("with.$key.where")) {
                    $rules = Arr::get($this->relatedSearchRules(), request()->input("with.$key.relationship"));

                    $this->query = $this->query->with([request()->input("with.$key.relationship") => function ($query) use ($rules, $value) {
                        $constraints = Arr::get($value, 'where');

                        for ($i = 0; $i < count($constraints); $i++) {
                            foreach ($rules as $column => $type) {
                                $where = Arr::get($constraints, $i);

                                if (Arr::has($where, $column)) {
                                    QueryBuilder::query(
                                        $column,
                                        $type,
                                        Arr::get($where, $column),
                                        $query,
                                        Arr::get($where, 'inverse'),
                                        Arr::get($where, 'strict'),
                                        Arr::get(Arr::get($where, $column), 'null'),
                                        Arr::get(Arr::get($where, $column), 'notNull'),
                                        Arr::get($where, 'limit')
                                    );
                                }
                            }
                        }
                    }]);
                }
            }
        }

        return $this;
    }

    /**
     * Count related models of the resource.
     *
     */
    protected function countRelatedModels()
    {
        if (request()->has('withCount')) {
            foreach (request()->input('withCount') as $key => $value) {
                if (gettype(request()->input("withCount.$key")) === 'string') {
                    $this->query->withCount($value);

                    continue;
                }

                if (request()->has("withCount.$key.orderBy")) {
                    $this->query->withCount([request()->input("withCount.$key.relationship") => function ($query) use ($key) {
                        $query->orderBy(request()->input("withCount.$key.orderBy"), request()->input("withCount.$key.direction") ?: 'asc');
                    }]);
                }

                if (request()->has("withCount.$key.where")) {
                    $rules = Arr::get($this->relatedSearchRules(), request()->input("withCount.$key.relationship"));

                    $this->query = $this->query->withCount([request()->input("withCount.$key.relationship") => function ($query) use ($rules, $value) {
                        $constraints = Arr::get($value, 'where');

                        for ($i = 0; $i < count($constraints); $i++) {
                            foreach ($rules as $column => $type) {
                                $where = Arr::get($constraints, $i);

                                if (Arr::has($where, $column)) {
                                    QueryBuilder::query(
                                        $column,
                                        $type,
                                        Arr::get($where, $column),
                                        $query,
                                        Arr::get($where, 'inverse'),
                                        Arr::get($where, 'strict'),
                                        Arr::get(Arr::get($where, $column), 'null'),
                                        Arr::get(Arr::get($where, $column), 'notNull'),
                                        Arr::get($where, 'limit')
                                    );
                                }
                            }
                        }
                    }]);
                }
            }
        }

        return $this;
    }

    /**
     * Filter the results of the query to show deleted records
     *
     */
    protected function filter()
    {
        if (request()->withTrashed) {
            $this->query->withTrashed();
        } else if (request()->onlyTrashed) {
            $this->query->onlyTrashed();
        }

        return $this;
    }

    /**
     * Sort the results of the query
     *
     */
    protected function sort()
    {
        if (request()->has('orderBy')) {
            switch (gettype(request()->orderBy)) {
                case 'array':
                    foreach (request()->orderBy as $orderBy) {
                        $this->query->orderBy(
                            Arr::get($orderBy, 'column'),
                            Arr::get($orderBy, 'descending') ? 'desc' : 'asc'
                        );
                    }
                    break;

                case 'string':
                    $this->query->orderBy(
                        request()->orderBy,
                        request()->descending ? 'desc' : 'asc'
                    );
                    break;
            }
        }

        return $this;
    }

    /**
     * Prepare the response.
     *
     */
    protected function respond()
    {
        $data = null;

        if (request()->first) {
            $data = $this->query->first();

            return $this->setResponseResource($data);
        } else {
            if (request()->perPage) {
                $data = $this->query->paginate(request()->perPage ?: $this->perPage());
            } else {
                $data = request()->has('with') || request()->has('withCount') ? $this->query->get() : $this->query->cursor();
            }

            return $this->setResponseCollection($data);
        }
    }
}
