<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #ffffff; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(255, 255, 255, 0.05); }
        .table-striped tbody tr:nth-of-type(even) { background-color: rgba(255, 255, 255, 0.1); }
        .table-bordered { border-color: #444; }
        .table-dark { background-color: #222; }
        .btn-danger, .btn-warning { color: #fff; }
        th { cursor: pointer; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mb-4">Logs del Sistema</h1>

    <form method="POST">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th onclick="sortTable(0)">Fecha <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(1)">Nivel <i class="fas fa-sort"></i></th>
                        <th onclick="sortTable(2)">Mensaje <i class="fas fa-sort"></i></th>
                        <th class="text-center">Eliminar</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody style="color: white;">
                    <?php foreach ($paginatedLogs as $index => $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['date']) ?></td>
                            <td><?= htmlspecialchars($log['level']) ?></td>
                            <td><?= htmlspecialchars($log['message']) ?></td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="logs[]" value="<?= $index ?>">
                            </td>
                            <td class="text-center">
                                <a href="logs/show/<?= $index ?>" class="btn btn-info btn-sm">Ver más</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" name="delete" class="btn btn-danger">Eliminar Seleccionados</button>
            <button type="submit" name="delete_all" class="btn btn-warning">Borrar Todo</button>
        </div>
    </form>

    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $paginationData['totalPages']; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script>
    function sortTable(n) {
        const table = document.querySelector("table tbody");
        let rows = Array.from(table.rows);
        const asc = table.dataset.sortOrder !== 'asc';
        rows.sort((a, b) => {
            const x = a.cells[n].innerText.toLowerCase();
            const y = b.cells[n].innerText.toLowerCase();
            return x.localeCompare(y) * (asc ? 1 : -1);
        });
        rows.forEach(row => table.appendChild(row));
        table.dataset.sortOrder = asc ? 'asc' : 'desc';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
