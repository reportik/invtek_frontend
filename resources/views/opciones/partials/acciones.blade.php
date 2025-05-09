<a href="{{ route('opciones.edit', $opcion->OPC_OpcionId) }}"
    class="btn btn-warning btn-sm btn-editar-opcion">Editar</a>
<form action="{{ route('opciones.destroy', $opcion->OPC_OpcionId) }}" method="POST" style="display:inline;"
    onsubmit="return confirm('¿Eliminar esta opción?')">
    @csrf @method('DELETE')
    <button class="btn btn-danger btn-sm">Eliminar</button>
</form>