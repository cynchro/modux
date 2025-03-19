<?php

namespace App\Helpers;

class PaginatorHelper
{
    private $connection;
    private $query;
    private $page;
    private $perPage;

    public function __construct($connection, $query)
    {
        $this->connection = $connection;
        $this->query = $query;
        $this->validateAndSetParameters();
    }

    private function validateAndSetParameters()
    {
        $data = [
            'page' => $_GET['page'] ?? $_POST['page'] ?? 1,
            'perPage' => $_GET['perPage'] ?? $_POST['perPage'] ?? 10
        ];

        $rules = [
            'page' => 'required|integer|min:1',
            'perPage' => 'required|integer|min:1'
        ];

        $errors = ValidatorHelper::validate($data, $rules);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode([
                'status' => 400,
                'errors' => $errors
            ]));
        }

        $this->page = (int)$data['page'];
        $this->perPage = (int)$data['perPage'];
    }

    private function getTotalItems(): int
    {
        $countQuery = "SELECT COUNT(*) as total FROM ({$this->query}) as subquery";
        $stmt = $this->connection->query($countQuery);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)$data['total'];
    }

    public function getPaginatedResults(): array
    {
        $paginate = filter_var($_GET['paginate'] ?? $_POST['paginate'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $offset = ($this->page - 1) * $this->perPage;

        $paginatedQuery = $paginate ? "{$this->query} LIMIT {$this->perPage} OFFSET {$offset}" : $this->query;

        $stmt = $this->connection->query($paginatedQuery);
        $items = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

        if (empty($items)) {
            return [
                'status' => 404,
                'total' => 0,
                'cantidad_por_pagina' => 0,
                'pagina' => 0,
                'cantidad_total' => 0,
                'results' => []
            ];
        }

        $totalItems = $paginate ? $this->getTotalItems() : null;

        if ($paginate) {
            return [
                'status' => 200,
                'total' => $totalItems,
                'cantidad_por_pagina' => $this->perPage,
                'pagina' => $this->page,
                'cantidad_total' => $totalItems,
                'results' => $items
            ];
        }

        return $items;
    }
}
