<!-- Modal -->
<div class="modal fade" id="modalBuscadorClientes" tabindex="-1" aria-labelledby="BuscadorDeClientes" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Seleccionar Cliente</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
                <div class="row">
                        <div class="col-md-9">
                            <label for="buscadorNombreCliente">Nombre</label>
                            <input type="text" class="form-control" id="buscadorNombreCliente" >
                            
                        </div>
                        <div class="col-lg-1 col-md-3 col-sm-12" style="padding-top: 3px;">
                            <div class="form-group">
                                <br>
                                <button type="button" class="btn btn-primary me-2" id="buscarCliente" >Buscar</button>
                            </div>
                        </div>
                        
                </div>
                <hr>
                <div class="row table-responsive">
                    <table id="tablaBuscadortClientes" class="table table-striped table-bordered"  style="width:100%">
                        <thead>
                            <tr>
                                <td>Nombre</td>
                                <td>Documento</td>
                                <td></td>
                            </tr>
                        </thead>
                    </table>
                </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
        
      </div>
    </div>
  </div>
</div>