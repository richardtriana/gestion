// archivo: js/funciones.js

function mostrarFormularioCrear() {
    document.getElementById('modalCrear').style.display = 'block';
}

function cerrarFormularioCrear() {
    document.getElementById('modalCrear').style.display = 'none';
}

function mostrarFormularioEditar(id) {
    // Cargar los datos de la dependencia desde el servidor y rellenar el formulario
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "obtener_dependencia.php?id=" + id, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var dependencia = JSON.parse(xhr.responseText);
            document.getElementById('editar_id').value = dependencia.id;
            document.getElementById('editar_nombre').value = dependencia.nombre;
            document.getElementById('editar_tamaño').value = dependencia.tamaño;
            document.getElementById('modalEditar').style.display = 'block';
        }
    };
    xhr.send();
}

function cerrarFormularioEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

function eliminarDependencia(id) {
    if (confirm("¿Estás seguro de que deseas eliminar esta dependencia?")) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "eliminar_dependencia.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                window.location.reload();
            }
        };
        xhr.send("id=" + id);
    }
}

function mostrarMensajeExito(mensaje) {
    var modal = document.getElementById('modalExito');
    document.getElementById('mensajeExito').innerText = mensaje;
    modal.style.display = 'block';
    setTimeout(function() {
        modal.style.display = 'none';
        window.location.reload();
    }, 3000);
}

function mostrarMensajeError(mensaje) {
    var modal = document.getElementById('modalError');
    document.getElementById('mensajeError').innerText = mensaje;
    modal.style.display = 'block';
    setTimeout(function() {
        modal.style.display = 'none';
    }, 3000);
}

function cerrarModalError() {
    document.getElementById('modalError').style.display = 'none';
}




