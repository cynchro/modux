<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body style="background-color:black;">
    <div class="container my-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-center">Detalles del Log</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Fecha:</strong></h6>
                        <p class="text-muted mb-1"><?= htmlspecialchars($logDetail['date']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 col-md-6">
                        <h6><strong>Nivel:</strong></h6>
                        <p><span class="badge bg-danger"><?= htmlspecialchars($logDetail['level']) ?></span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Mensaje Principal:</strong></h6>
                        <p class="text-muted"><?= htmlspecialchars($logDetail['message']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Detalles Adicionales:</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">URL</th>
                                        <td><?= htmlspecialchars($logDetail['full_message']['url'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Código</th>
                                        <td><?= htmlspecialchars($logDetail['full_message']['codigo'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Archivo</th>
                                        <td><code><?= htmlspecialchars($logDetail['full_message']['archivo'] ?? '') ?></code></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Línea</th>
                                        <td><?= htmlspecialchars($logDetail['full_message']['linea'] ?? '') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Stack Trace:</strong></h6>
                        <pre class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.9rem;"><?= htmlspecialchars($logDetail['full_message']['stack_trace'] ?? '') ?></pre>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Input Data:</strong></h6>
                        <pre class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.9rem;"><?= htmlspecialchars($logDetail['full_message']['input_data'] ?? '{}') ?></pre>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <h6><strong>Información del Usuario:</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th scope="row">User ID</th>
                                        <td><?= htmlspecialchars($logDetail['full_message']['user_id'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">IP Address</th>
                                        <td><?= htmlspecialchars($logDetail['full_message']['ip'] ?? '') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="/logs" class="btn btn-secondary">Volver a la Lista</a>
            </div>
        </div>
    </div>
</body>
</html>
