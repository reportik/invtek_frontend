<a href="{{ route('productos.show', $opcion->OPC_OpcionId) }}" class="btn btn-info btn-sm btn.productos-opcion"
    title="Productos">
    <i class="bi bi-boxes"></i>
</a>
<a href="{{ route('opciones.edit', $opcion->OPC_OpcionId) }}" class="btn btn-warning btn-sm btn-editar-opcion">
    <i class="bi bi-pencil-square"></i>
</a>
@if($colocar_btnEliminar)
<form action="{{ route('opciones.destroy', $opcion->OPC_OpcionId) }}" method="POST" style="display:inline;" class="form-eliminar">
    @csrf @method('DELETE')
    <button class="btn btn-danger btn-sm btn-eliminar-opcion">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif