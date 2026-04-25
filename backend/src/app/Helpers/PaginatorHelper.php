<?php

namespace App\Helpers;

use App\Support\Validator;

class PaginatorHelper
{
    private \PDO   $connection;
    private string $query;
    private int    $page;
    private int    $perPage;

    public function __construct(\PDO $connection, string $query)
    {
        $this->connection = $connection;
        $this->query      = $query;
        $this->resolveParams();
    }

    private function resolveParams(): void
    {
        $data = [
            'page'    => $_GET['page'] ?? $_POST['page'] ?? 1,
            'perPage' => $_GET['perPage'] ?? $_POST['perPage'] ?? 10,
        ];

        $errors = Validator::validate($data, [
            'page'    => 'required|integer',
            'perPage' => 'required|integer',
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode(['status' => 400, 'errors' => $errors]));
        }

        $this->page    = max(1, (int) $data['page']);
        $this->perPage = max(1, (int) $data['perPage']);
    }

    private function getTotalItems(): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM ({$this->query}) AS sub");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getPaginatedResults(): array
    {
        $paginate = filter_var(
            $_GET['paginate'] ?? $_POST['paginate'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        );

        $offset = ($this->page - 1) * $this->perPage;

        if ($paginate) {
            $stmt = $this->connection->prepare("{$this->query} LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit',  $this->perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset,        \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->connection->prepare($this->query);
            $stmt->execute();
        }

        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($items)) {
            return [
                'status'             => 404,
                'total'              => 0,
                'cantidad_por_pagina'=> 0,
                'pagina'             => 0,
                'cantidad_total'     => 0,
                'results'            => [],
            ];
        }

        $total = $paginate ? $this->getTotalItems() : count($items);

        return [
            'status'             => 200,
            'total'              => $total,
            'cantidad_por_pagina'=> $this->perPage,
            'pagina'             => $this->page,
            'cantidad_total'     => $total,
            'results'            => $items,
        ];
    }
}
